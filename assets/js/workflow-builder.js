/**
 * Workflow Builder JavaScript
 *
 * @package Workflow_Automation
 * @since 1.0.0
 */

(function($) {
    'use strict';

    var WorkflowBuilder = {
        workflow: null,
        nodes: [],
        connections: [],
        selectedNode: null,
        isDirty: false,
        autoSaveTimer: null,
        canvas: null,

        init: function() {
            if (typeof waWorkflowData === 'undefined') {
                return;
            }

            this.workflow = waWorkflowData;
            this.bindEvents();
            this.initializeSidebar();
            this.initializeCanvas();
            
            // Enable auto-save if configured
            if (wa_builder.auto_save) {
                this.startAutoSave();
            }
        },

        bindEvents: function() {
            var self = this;

            // Edit workflow name
            $('.wa-edit-name').on('click', function() {
                self.editWorkflowName();
            });

            // Save workflow
            $('#wa-save-workflow').on('click', function() {
                self.saveWorkflow();
            });

            // Test workflow
            $('#wa-test-workflow').on('click', function() {
                self.showTestModal();
            });

            // Toggle workflow status
            $('#wa-workflow-active').on('change', function() {
                self.toggleWorkflowStatus($(this).is(':checked'));
            });

            // Canvas controls
            $('#wa-zoom-in').on('click', function() {
                self.zoomIn();
            });

            $('#wa-zoom-out').on('click', function() {
                self.zoomOut();
            });

            $('#wa-fit-view').on('click', function() {
                self.fitView();
            });

            $('#wa-center-view').on('click', function() {
                self.centerView();
            });

            // Node search
            $('#wa-node-search').on('input', function() {
                self.filterNodes($(this).val());
            });

            // Category toggle
            $('.wa-category-title').on('click', function() {
                self.toggleCategory($(this));
            });

            // Modal events
            $('.wa-modal-close').on('click', function() {
                $(this).closest('.wa-modal').hide();
            });

            $('#wa-save-node-config').on('click', function() {
                self.saveNodeConfiguration();
            });

            $('#wa-run-test').on('click', function() {
                self.runTest();
            });

            // Warn about unsaved changes
            $(window).on('beforeunload', function() {
                if (self.isDirty) {
                    return wa_builder.i18n.unsaved_changes;
                }
            });
        },

        initializeSidebar: function() {
            var self = this;

            // Make nodes draggable
            $('.wa-draggable-node').draggable({
                helper: 'clone',
                cursor: 'move',
                revert: 'invalid',
                start: function(event, ui) {
                    ui.helper.css('z-index', 10000);
                }
            });
        },

        initializeCanvas: function() {
            var self = this;

            // Initialize canvas container
            this.canvas = $('#wa-workflow-canvas');

            // Make canvas droppable
            this.canvas.droppable({
                accept: '.wa-draggable-node',
                drop: function(event, ui) {
                    var nodeType = ui.draggable.data('node-type');
                    var nodeLabel = ui.draggable.data('node-label');
                    var nodeIcon = ui.draggable.data('node-icon');
                    var nodeColor = ui.draggable.data('node-color');

                    var position = {
                        x: ui.position.left - $(this).offset().left,
                        y: ui.position.top - $(this).offset().top
                    };

                    self.addNode(nodeType, nodeLabel, nodeIcon, nodeColor, position);
                }
            });

            // Load existing nodes
            if (this.workflow.nodes && this.workflow.nodes.length > 0) {
                this.loadNodes(this.workflow.nodes);
            }
        },

        addNode: function(type, label, icon, color, position) {
            var nodeId = 'node_' + Date.now();
            
            var node = {
                id: nodeId,
                type: type,
                label: label,
                icon: icon,
                color: color,
                position: position,
                data: {}
            };

            this.nodes.push(node);
            this.renderNode(node);
            this.markDirty();
        },

        renderNode: function(node) {
            var self = this;
            
            var nodeHtml = $('<div>')
                .addClass('wa-workflow-node')
                .attr('id', node.id)
                .css({
                    left: node.position.x + 'px',
                    top: node.position.y + 'px',
                    borderColor: node.color
                })
                .html('<div class="wa-node-header" style="background-color: ' + node.color + '">' +
                      '<span class="dashicons ' + node.icon + '"></span>' +
                      '<span class="wa-node-label">' + node.label + '</span>' +
                      '<button type="button" class="wa-node-delete" title="Delete node">&times;</button>' +
                      '</div>' +
                      '<div class="wa-node-body">' +
                      '<div class="wa-node-ports">' +
                      '<div class="wa-node-port wa-port-in" data-port="in"></div>' +
                      '<div class="wa-node-port wa-port-out" data-port="out"></div>' +
                      '</div>' +
                      '</div>');

            this.canvas.append(nodeHtml);

            // Make node draggable
            nodeHtml.draggable({
                containment: 'parent',
                handle: '.wa-node-header',
                drag: function() {
                    self.updateConnections(node.id);
                },
                stop: function() {
                    node.position = {
                        x: $(this).position().left,
                        y: $(this).position().top
                    };
                    self.markDirty();
                }
            });

            // Node click event
            nodeHtml.on('click', function(e) {
                if (!$(e.target).hasClass('wa-node-delete')) {
                    self.selectNode(node);
                }
            });

            // Delete node
            nodeHtml.find('.wa-node-delete').on('click', function(e) {
                e.stopPropagation();
                self.deleteNode(node.id);
            });

            // Double click to configure
            nodeHtml.on('dblclick', function() {
                self.showNodeConfiguration(node);
            });
        },

        selectNode: function(node) {
            this.selectedNode = node;
            
            // Update UI
            $('.wa-workflow-node').removeClass('selected');
            $('#' + node.id).addClass('selected');
            
            // Show properties
            this.showNodeProperties(node);
        },

        showNodeProperties: function(node) {
            var propertiesHtml = '<div class="wa-node-properties">' +
                                '<div class="wa-property-group">' +
                                '<h3>Basic Information</h3>' +
                                '<div class="wa-property-row">' +
                                '<label>Node ID</label>' +
                                '<input type="text" value="' + node.id + '" readonly>' +
                                '</div>' +
                                '<div class="wa-property-row">' +
                                '<label>Type</label>' +
                                '<input type="text" value="' + node.type + '" readonly>' +
                                '</div>' +
                                '<div class="wa-property-row">' +
                                '<label>Label</label>' +
                                '<input type="text" id="node-label" value="' + node.label + '">' +
                                '</div>' +
                                '</div>' +
                                '<div class="wa-property-group">' +
                                '<button type="button" class="button button-primary" id="wa-configure-node">Configure Node</button>' +
                                '</div>' +
                                '</div>';

            $('#wa-properties-content').html(propertiesHtml);

            // Bind property events
            var self = this;
            $('#node-label').on('input', function() {
                node.label = $(this).val();
                $('#' + node.id + ' .wa-node-label').text(node.label);
                self.markDirty();
            });

            $('#wa-configure-node').on('click', function() {
                self.showNodeConfiguration(node);
            });
        },

        showNodeConfiguration: function(node) {
            $('#wa-node-config-title').text('Configure ' + node.label);
            
            // Load node-specific configuration fields
            this.loadNodeConfigFields(node);
            
            $('#wa-node-config-modal').show();
        },

        loadNodeConfigFields: function(node) {
            var fieldsHtml = '';
            
            // Add fields based on node type
            switch(node.type) {
                case 'webhook_start':
                    fieldsHtml = this.getWebhookFields(node);
                    break;
                case 'email':
                    fieldsHtml = this.getEmailFields(node);
                    break;
                case 'slack':
                    fieldsHtml = this.getSlackFields(node);
                    break;
                case 'line':
                    fieldsHtml = this.getLineFields(node);
                    break;
                case 'google_sheets':
                    fieldsHtml = this.getGoogleSheetsFields(node);
                    break;
                case 'openai':
                case 'claude':
                case 'gemini':
                    fieldsHtml = this.getAIFields(node);
                    break;
                case 'wp_post':
                    fieldsHtml = this.getWPPostFields(node);
                    break;
                case 'filter':
                    fieldsHtml = this.getFilterFields(node);
                    break;
                case 'code':
                    fieldsHtml = this.getCodeFields(node);
                    break;
                default:
                    fieldsHtml = '<p>No configuration options available for this node type.</p>';
            }
            
            $('#wa-node-config-fields').html(fieldsHtml).data('node-id', node.id);
        },

        getWebhookFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Webhook Method</label>' +
                   '<select name="method">' +
                   '<option value="POST"' + (data.method === 'POST' ? ' selected' : '') + '>POST</option>' +
                   '<option value="GET"' + (data.method === 'GET' ? ' selected' : '') + '>GET</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Authentication</label>' +
                   '<select name="auth_type">' +
                   '<option value="none"' + (data.auth_type === 'none' ? ' selected' : '') + '>None</option>' +
                   '<option value="token"' + (data.auth_type === 'token' ? ' selected' : '') + '>Token</option>' +
                   '<option value="signature"' + (data.auth_type === 'signature' ? ' selected' : '') + '>Signature</option>' +
                   '</select>' +
                   '</div>';
        },

        getEmailFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>To Email</label>' +
                   '<input type="email" name="to_email" value="' + (data.to_email || '') + '" placeholder="recipient@example.com">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Subject</label>' +
                   '<input type="text" name="subject" value="' + (data.subject || '') + '" placeholder="Email subject">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Message Template</label>' +
                   '<textarea name="message" rows="5" placeholder="Email message template">' + (data.message || '') + '</textarea>' +
                   '<p class="description">Use {{variable}} for dynamic content</p>' +
                   '</div>';
        },

        getSlackFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Integration</label>' +
                   '<select name="integration_id">' +
                   '<option value="">Select Slack integration</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Channel</label>' +
                   '<input type="text" name="channel" value="' + (data.channel || '') + '" placeholder="#general">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Message</label>' +
                   '<textarea name="message" rows="5" placeholder="Message template">' + (data.message || '') + '</textarea>' +
                   '</div>';
        },

        getLineFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Integration</label>' +
                   '<select name="integration_id">' +
                   '<option value="">Select LINE integration</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Reply Token</label>' +
                   '<input type="text" name="reply_token" value="' + (data.reply_token || '{{webhook.replyToken}}') + '">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Message Type</label>' +
                   '<select name="message_type">' +
                   '<option value="text"' + (data.message_type === 'text' ? ' selected' : '') + '>Text</option>' +
                   '<option value="template"' + (data.message_type === 'template' ? ' selected' : '') + '>Template</option>' +
                   '<option value="flex"' + (data.message_type === 'flex' ? ' selected' : '') + '>Flex Message</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Message</label>' +
                   '<textarea name="message" rows="5">' + (data.message || '') + '</textarea>' +
                   '</div>';
        },

        getGoogleSheetsFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Integration</label>' +
                   '<select name="integration_id">' +
                   '<option value="">Select Google integration</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Spreadsheet ID</label>' +
                   '<input type="text" name="spreadsheet_id" value="' + (data.spreadsheet_id || '') + '">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Sheet Name</label>' +
                   '<input type="text" name="sheet_name" value="' + (data.sheet_name || 'Sheet1') + '">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Action</label>' +
                   '<select name="action">' +
                   '<option value="append"' + (data.action === 'append' ? ' selected' : '') + '>Append Row</option>' +
                   '<option value="update"' + (data.action === 'update' ? ' selected' : '') + '>Update Row</option>' +
                   '<option value="read"' + (data.action === 'read' ? ' selected' : '') + '>Read Data</option>' +
                   '</select>' +
                   '</div>';
        },

        getAIFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Integration</label>' +
                   '<select name="integration_id">' +
                   '<option value="">Select ' + node.label + ' integration</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Model</label>' +
                   '<select name="model">' +
                   this.getModelOptions(node.type, data.model) +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>System Prompt</label>' +
                   '<textarea name="system_prompt" rows="3">' + (data.system_prompt || '') + '</textarea>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>User Prompt</label>' +
                   '<textarea name="user_prompt" rows="5">' + (data.user_prompt || '') + '</textarea>' +
                   '<p class="description">Use {{variable}} for dynamic content</p>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Temperature</label>' +
                   '<input type="number" name="temperature" value="' + (data.temperature || 0.7) + '" min="0" max="2" step="0.1">' +
                   '</div>';
        },

        getModelOptions: function(type, selected) {
            var options = '';
            var models = {
                'openai': ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
                'claude': ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'],
                'gemini': ['gemini-pro', 'gemini-pro-vision']
            };
            
            if (models[type]) {
                models[type].forEach(function(model) {
                    options += '<option value="' + model + '"' + (selected === model ? ' selected' : '') + '>' + model + '</option>';
                });
            }
            
            return options;
        },

        getWPPostFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Action</label>' +
                   '<select name="action">' +
                   '<option value="create"' + (data.action === 'create' ? ' selected' : '') + '>Create Post</option>' +
                   '<option value="update"' + (data.action === 'update' ? ' selected' : '') + '>Update Post</option>' +
                   '<option value="get"' + (data.action === 'get' ? ' selected' : '') + '>Get Post</option>' +
                   '<option value="delete"' + (data.action === 'delete' ? ' selected' : '') + '>Delete Post</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Post Title</label>' +
                   '<input type="text" name="post_title" value="' + (data.post_title || '') + '">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Post Content</label>' +
                   '<textarea name="post_content" rows="5">' + (data.post_content || '') + '</textarea>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Post Status</label>' +
                   '<select name="post_status">' +
                   '<option value="draft"' + (data.post_status === 'draft' ? ' selected' : '') + '>Draft</option>' +
                   '<option value="publish"' + (data.post_status === 'publish' ? ' selected' : '') + '>Published</option>' +
                   '<option value="private"' + (data.post_status === 'private' ? ' selected' : '') + '>Private</option>' +
                   '</select>' +
                   '</div>';
        },

        getFilterFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Filter Condition</label>' +
                   '<select name="operator">' +
                   '<option value="equals"' + (data.operator === 'equals' ? ' selected' : '') + '>Equals</option>' +
                   '<option value="not_equals"' + (data.operator === 'not_equals' ? ' selected' : '') + '>Not Equals</option>' +
                   '<option value="contains"' + (data.operator === 'contains' ? ' selected' : '') + '>Contains</option>' +
                   '<option value="greater_than"' + (data.operator === 'greater_than' ? ' selected' : '') + '>Greater Than</option>' +
                   '<option value="less_than"' + (data.operator === 'less_than' ? ' selected' : '') + '>Less Than</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Field</label>' +
                   '<input type="text" name="field" value="' + (data.field || '') + '" placeholder="{{data.field}}">' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Value</label>' +
                   '<input type="text" name="value" value="' + (data.value || '') + '">' +
                   '</div>';
        },

        getCodeFields: function(node) {
            var data = node.data || {};
            return '<div class="wa-form-group">' +
                   '<label>Language</label>' +
                   '<select name="language">' +
                   '<option value="php"' + (data.language === 'php' ? ' selected' : '') + '>PHP</option>' +
                   '<option value="javascript"' + (data.language === 'javascript' ? ' selected' : '') + '>JavaScript</option>' +
                   '</select>' +
                   '</div>' +
                   '<div class="wa-form-group">' +
                   '<label>Code</label>' +
                   '<textarea name="code" rows="10" style="font-family: monospace;">' + (data.code || '') + '</textarea>' +
                   '<p class="description">Access input data via $input variable</p>' +
                   '</div>';
        },

        saveNodeConfiguration: function() {
            var nodeId = $('#wa-node-config-fields').data('node-id');
            var node = this.nodes.find(function(n) { return n.id === nodeId; });
            
            if (!node) return;
            
            // Collect form data
            var formData = {};
            $('#wa-node-config-form').find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    formData[name] = $(this).val();
                }
            });
            
            node.data = formData;
            this.markDirty();
            
            $('#wa-node-config-modal').hide();
        },

        deleteNode: function(nodeId) {
            if (!confirm('Are you sure you want to delete this node?')) {
                return;
            }
            
            // Remove node from array
            this.nodes = this.nodes.filter(function(n) { return n.id !== nodeId; });
            
            // Remove connections
            this.connections = this.connections.filter(function(c) {
                return c.source !== nodeId && c.target !== nodeId;
            });
            
            // Remove from DOM
            $('#' + nodeId).remove();
            
            // Clear properties if this was selected
            if (this.selectedNode && this.selectedNode.id === nodeId) {
                this.selectedNode = null;
                $('#wa-properties-content').html('<div class="wa-empty-properties"><p>Select a node to view its properties</p></div>');
            }
            
            this.markDirty();
        },

        updateConnections: function(nodeId) {
            // Update visual connections when node is moved
            // This would update SVG paths in a full implementation
        },

        editWorkflowName: function() {
            var currentName = $('#wa-workflow-name').text();
            var newName = prompt('Enter new workflow name:', currentName);
            
            if (newName && newName !== currentName) {
                $('#wa-workflow-name').text(newName);
                this.workflow.name = newName;
                this.markDirty();
            }
        },

        toggleWorkflowStatus: function(active) {
            this.workflow.status = active ? 'active' : 'inactive';
            $('.wa-status-label').text(active ? wa_builder.i18n.active : wa_builder.i18n.inactive);
            this.markDirty();
        },

        saveWorkflow: function() {
            var self = this;
            var $button = $('#wa-save-workflow');
            
            $button.prop('disabled', true).text(wa_builder.i18n.saving);
            $('.wa-save-indicator').addClass('saving');
            
            var data = {
                name: this.workflow.name,
                status: this.workflow.status,
                nodes: this.nodes,
                connections: this.connections
            };
            
            $.ajax({
                url: wa_builder.api_url + '/workflows/' + this.workflow.id,
                method: 'PUT',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function() {
                    self.isDirty = false;
                    $('.wa-save-message').text(wa_builder.i18n.saved);
                    $button.text(wa_builder.i18n.save);
                    
                    setTimeout(function() {
                        $('.wa-save-message').text('All changes saved');
                    }, 2000);
                },
                error: function() {
                    alert(wa_builder.i18n.save_failed);
                    $button.text(wa_builder.i18n.save);
                },
                complete: function() {
                    $button.prop('disabled', false);
                    $('.wa-save-indicator').removeClass('saving');
                }
            });
        },

        markDirty: function() {
            this.isDirty = true;
            $('.wa-save-message').text('Unsaved changes');
            
            // Reset auto-save timer
            if (this.autoSaveTimer) {
                clearTimeout(this.autoSaveTimer);
                this.startAutoSave();
            }
        },

        startAutoSave: function() {
            var self = this;
            var interval = (wa_builder.auto_save_interval || 2) * 1000;
            
            this.autoSaveTimer = setTimeout(function() {
                if (self.isDirty) {
                    self.saveWorkflow();
                }
            }, interval);
        },

        filterNodes: function(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            
            $('.wa-draggable-node').each(function() {
                var label = $(this).text().toLowerCase();
                if (label.indexOf(searchTerm) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        },

        toggleCategory: function($title) {
            $title.toggleClass('expanded');
            $title.next('.wa-category-nodes').slideToggle();
        },

        showTestModal: function() {
            $('#wa-test-modal').show();
        },

        runTest: function() {
            var self = this;
            var $button = $('#wa-run-test');
            var triggerType = $('#test-trigger-type').val();
            var testData = $('#test-data').val();
            
            try {
                testData = JSON.parse(testData);
            } catch(e) {
                alert('Invalid JSON data');
                return;
            }
            
            $button.prop('disabled', true).text('Running...');
            
            $.ajax({
                url: wa_builder.api_url + '/workflows/' + this.workflow.id + '/test',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                data: JSON.stringify({
                    trigger_type: triggerType,
                    trigger_data: testData
                }),
                contentType: 'application/json',
                success: function(response) {
                    alert('Test completed successfully. Check the executions page for details.');
                    $('#wa-test-modal').hide();
                },
                error: function(xhr) {
                    var message = 'Test failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message += ': ' + xhr.responseJSON.message;
                    }
                    alert(message);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Run Test');
                }
            });
        },

        zoomIn: function() {
            // Zoom functionality would be implemented with transform scale
            console.log('Zoom in');
        },

        zoomOut: function() {
            // Zoom functionality would be implemented with transform scale
            console.log('Zoom out');
        },

        fitView: function() {
            // Fit all nodes in view
            console.log('Fit view');
        },

        centerView: function() {
            // Center the canvas
            console.log('Center view');
        },

        loadNodes: function(nodes) {
            // Load existing nodes from database
            var self = this;
            nodes.forEach(function(node) {
                self.nodes.push(node);
                self.renderNode(node);
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WorkflowBuilder.init();
    });

})(jQuery);

// Add basic styles for workflow nodes
var style = document.createElement('style');
style.textContent = `
.wa-workflow-node {
    position: absolute;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 4px;
    min-width: 180px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.wa-workflow-node.selected {
    border-color: #0073aa;
    box-shadow: 0 2px 8px rgba(0,115,170,0.3);
}

.wa-node-header {
    padding: 10px;
    color: #fff;
    border-radius: 2px 2px 0 0;
    cursor: move;
    display: flex;
    align-items: center;
}

.wa-node-header .dashicons {
    margin-right: 8px;
}

.wa-node-label {
    flex: 1;
    font-weight: 600;
}

.wa-node-delete {
    background: none;
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    padding: 0;
    margin-left: 10px;
    opacity: 0.7;
}

.wa-node-delete:hover {
    opacity: 1;
}

.wa-node-body {
    padding: 10px;
    position: relative;
}

.wa-node-ports {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
}

.wa-node-port {
    position: absolute;
    width: 12px;
    height: 12px;
    background: #fff;
    border: 2px solid #0073aa;
    border-radius: 50%;
    cursor: crosshair;
}

.wa-port-in {
    left: -8px;
}

.wa-port-out {
    right: -8px;
}

.wa-node-port:hover {
    background: #0073aa;
}
`;
document.head.appendChild(style);