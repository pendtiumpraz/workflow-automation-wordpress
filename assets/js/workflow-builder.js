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
                console.error('Workflow data not found');
                return;
            }

            this.workflow = waWorkflowData;
            this.bindEvents();
            this.initializeSidebar();
            this.initializeCanvas();
            
            console.log('Workflow Builder initialized');
            
            // Enable auto-save if configured
            if (wa_builder && wa_builder.auto_save) {
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
            
            console.log('Initializing sidebar, found nodes:', $('.wa-draggable-node').length);

            // Make nodes draggable
            $('.wa-draggable-node').draggable({
                helper: 'clone',
                cursor: 'move',
                revert: 'invalid',
                connectToSortable: false,
                start: function(event, ui) {
                    console.log('Drag started:', ui.helper);
                    ui.helper.css({
                        'z-index': 10000,
                        'opacity': 0.8
                    });
                },
                stop: function(event, ui) {
                    console.log('Drag stopped');
                }
            });
            
            // Initialize all categories as expanded by default
            $('.wa-category-title').each(function() {
                $(this).addClass('expanded');
                $(this).next('.wa-category-nodes').show();
            });
        },

        initializeCanvas: function() {
            var self = this;

            // Initialize canvas container
            this.canvas = $('#wa-workflow-canvas');
            
            console.log('Canvas element:', this.canvas.length);

            // Make canvas droppable
            this.canvas.droppable({
                accept: '.wa-draggable-node',
                tolerance: 'fit',
                drop: function(event, ui) {
                    console.log('Node dropped!');
                    
                    // Check if it's from the sidebar (not already on canvas)
                    if (!ui.draggable.hasClass('wa-workflow-node')) {
                        var nodeType = ui.draggable.data('node-type');
                        var nodeLabel = ui.draggable.data('node-label');
                        var nodeIcon = ui.draggable.data('node-icon');
                        var nodeColor = ui.draggable.data('node-color');

                        // Calculate position relative to canvas
                        var canvasOffset = $(this).offset();
                        var position = {
                            x: ui.offset.left - canvasOffset.left,
                            y: ui.offset.top - canvasOffset.top
                        };

                        self.addNode(nodeType, nodeLabel, nodeIcon, nodeColor, position);
                    }
                },
                over: function(event, ui) {
                    console.log('Node over canvas');
                    $(this).addClass('wa-canvas-hover');
                },
                out: function(event, ui) {
                    console.log('Node out of canvas');
                    $(this).removeClass('wa-canvas-hover');
                }
            });

            // Load existing nodes
            if (this.workflow.nodes && this.workflow.nodes.length > 0) {
                this.loadNodes(this.workflow.nodes);
            }
        },

        addNode: function(type, label, icon, color, position) {
            var nodeId = 'node_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
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
            
            console.log('Node added:', node);
        },

        renderNode: function(node) {
            var self = this;
            
            var nodeHtml = $('<div>')
                .addClass('wa-workflow-node')
                .attr('id', node.id)
                .attr('data-node-id', node.id)
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

            // Make node draggable within canvas
            nodeHtml.draggable({
                containment: 'parent',
                handle: '.wa-node-header',
                grid: [10, 10],
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
            nodeHtml.on('dblclick', function(e) {
                e.stopPropagation();
                self.showNodeConfiguration(node);
            });
        },

        loadNodes: function(nodes) {
            var self = this;
            nodes.forEach(function(nodeData) {
                // Convert stored node data to our format
                var node = {
                    id: nodeData.node_id,
                    type: nodeData.node_type,
                    label: self.getNodeLabel(nodeData.node_type),
                    icon: self.getNodeIcon(nodeData.node_type),
                    color: self.getNodeColor(nodeData.node_type),
                    position: {
                        x: nodeData.position_x || 100,
                        y: nodeData.position_y || 100
                    },
                    data: JSON.parse(nodeData.settings || '{}')
                };
                
                self.nodes.push(node);
                self.renderNode(node);
            });
        },

        getNodeLabel: function(type) {
            // Get label from available nodes data
            if (this.workflow.availableNodes) {
                for (var category in this.workflow.availableNodes) {
                    if (this.workflow.availableNodes[category][type]) {
                        return this.workflow.availableNodes[category][type].label;
                    }
                }
            }
            return type;
        },

        getNodeIcon: function(type) {
            // Get icon from available nodes data
            if (this.workflow.availableNodes) {
                for (var category in this.workflow.availableNodes) {
                    if (this.workflow.availableNodes[category][type]) {
                        return this.workflow.availableNodes[category][type].icon;
                    }
                }
            }
            return 'dashicons-admin-generic';
        },

        getNodeColor: function(type) {
            // Get color from available nodes data
            if (this.workflow.availableNodes) {
                for (var category in this.workflow.availableNodes) {
                    if (this.workflow.availableNodes[category][type]) {
                        return this.workflow.availableNodes[category][type].color;
                    }
                }
            }
            return '#555';
        },

        selectNode: function(node) {
            // Remove previous selection
            $('.wa-workflow-node').removeClass('selected');
            
            // Select new node
            $('#' + node.id).addClass('selected');
            this.selectedNode = node;
            
            // Show properties
            this.showNodeProperties(node);
        },

        showNodeProperties: function(node) {
            var html = '<div class="wa-node-properties">';
            html += '<div class="wa-property-group">';
            html += '<h3>General</h3>';
            html += '<div class="wa-property-row">';
            html += '<label>ID</label>';
            html += '<input type="text" value="' + node.id + '" readonly>';
            html += '</div>';
            html += '<div class="wa-property-row">';
            html += '<label>Type</label>';
            html += '<input type="text" value="' + node.type + '" readonly>';
            html += '</div>';
            html += '<div class="wa-property-row">';
            html += '<label>Label</label>';
            html += '<input type="text" value="' + node.label + '" readonly>';
            html += '</div>';
            html += '</div>';
            html += '<div class="wa-property-group">';
            html += '<button type="button" class="button button-primary" onclick="WorkflowBuilder.showNodeConfiguration(WorkflowBuilder.selectedNode)">Configure Node</button>';
            html += '</div>';
            html += '</div>';
            
            $('#wa-properties-content').html(html);
        },

        showNodeConfiguration: function(node) {
            var self = this;
            
            $('#wa-node-config-title').text('Configure ' + node.label);
            $('#wa-node-config-modal').show();
            
            // Load node configuration fields
            this.loadNodeConfigFields(node);
        },

        loadNodeConfigFields: function(node) {
            var self = this;
            
            // Get node configuration schema via API
            $.ajax({
                url: wa_builder.api_url + '/nodes/types/' + node.type + '/schema',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                success: function(schema) {
                    self.renderConfigFields(node, schema);
                },
                error: function() {
                    $('#wa-node-config-fields').html('<p>Failed to load configuration fields.</p>');
                }
            });
        },

        renderConfigFields: function(node, schema) {
            var html = '';
            
            if (schema && schema.fields) {
                schema.fields.forEach(function(field) {
                    html += '<div class="wa-form-group">';
                    html += '<label>' + field.label + '</label>';
                    
                    var value = node.data[field.key] || field.default || '';
                    
                    switch (field.type) {
                        case 'select':
                            html += '<select name="' + field.key + '" class="regular-text">';
                            for (var optValue in field.options) {
                                html += '<option value="' + optValue + '"' + (value === optValue ? ' selected' : '') + '>';
                                html += field.options[optValue] + '</option>';
                            }
                            html += '</select>';
                            break;
                            
                        case 'textarea':
                            html += '<textarea name="' + field.key + '" class="large-text" rows="5">' + value + '</textarea>';
                            break;
                            
                        default:
                            html += '<input type="' + field.type + '" name="' + field.key + '" value="' + value + '" class="regular-text">';
                    }
                    
                    if (field.description) {
                        html += '<p class="description">' + field.description + '</p>';
                    }
                    
                    html += '</div>';
                });
            }
            
            $('#wa-node-config-fields').html(html);
        },

        saveNodeConfiguration: function() {
            var self = this;
            
            if (!this.selectedNode) return;
            
            // Get form data
            var formData = {};
            $('#wa-node-config-form').find('input, select, textarea').each(function() {
                formData[$(this).attr('name')] = $(this).val();
            });
            
            // Update node data
            this.selectedNode.data = formData;
            
            // Close modal
            $('#wa-node-config-modal').hide();
            
            // Mark as dirty
            this.markDirty();
        },

        deleteNode: function(nodeId) {
            if (!confirm('Are you sure you want to delete this node?')) {
                return;
            }
            
            // Remove from nodes array
            this.nodes = this.nodes.filter(function(node) {
                return node.id !== nodeId;
            });
            
            // Remove from DOM
            $('#' + nodeId).remove();
            
            // Remove connections
            this.removeNodeConnections(nodeId);
            
            // Clear selection if this was selected
            if (this.selectedNode && this.selectedNode.id === nodeId) {
                this.selectedNode = null;
                $('#wa-properties-content').html('<div class="wa-empty-properties"><p>Select a node to view its properties</p></div>');
            }
            
            this.markDirty();
        },

        updateConnections: function(nodeId) {
            // Update connection lines when node is moved
            // This would update SVG paths or similar
        },

        removeNodeConnections: function(nodeId) {
            // Remove connections to/from this node
            this.connections = this.connections.filter(function(conn) {
                return conn.source !== nodeId && conn.target !== nodeId;
            });
        },

        toggleCategory: function($title) {
            $title.toggleClass('expanded');
            $title.next('.wa-category-nodes').slideToggle(200);
        },

        filterNodes: function(searchTerm) {
            var term = searchTerm.toLowerCase();
            
            $('.wa-draggable-node').each(function() {
                var label = $(this).text().toLowerCase();
                if (label.indexOf(term) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Show all categories that have visible nodes
            $('.wa-node-category').each(function() {
                var hasVisibleNodes = $(this).find('.wa-draggable-node:visible').length > 0;
                if (hasVisibleNodes) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        },

        editWorkflowName: function() {
            var currentName = $('#wa-workflow-name').text();
            var newName = prompt('Enter workflow name:', currentName);
            
            if (newName && newName !== currentName) {
                $('#wa-workflow-name').text(newName);
                this.workflow.name = newName;
                this.markDirty();
            }
        },

        toggleWorkflowStatus: function(isActive) {
            this.workflow.status = isActive ? 'active' : 'inactive';
            $('.wa-status-label').text(isActive ? wa_builder.i18n.active : wa_builder.i18n.inactive);
            this.markDirty();
        },

        saveWorkflow: function() {
            var self = this;
            var $button = $('#wa-save-workflow');
            
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> ' + wa_builder.i18n.saving);
            
            // Prepare workflow data
            var workflowData = {
                name: this.workflow.name,
                status: this.workflow.status || 'draft',
                flow_data: {
                    nodes: this.nodes.map(function(node) {
                        return {
                            node_id: node.id,
                            node_type: node.type,
                            settings: JSON.stringify(node.data),
                            position_x: node.position.x,
                            position_y: node.position.y
                        };
                    }),
                    edges: this.connections
                }
            };
            
            // Save via API
            $.ajax({
                url: wa_builder.api_url + '/workflows/' + this.workflow.id,
                method: 'PUT',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                data: JSON.stringify(workflowData),
                contentType: 'application/json',
                success: function(response) {
                    self.isDirty = false;
                    self.showSaveSuccess();
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> ' + wa_builder.i18n.save);
                },
                error: function() {
                    alert(wa_builder.i18n.save_failed);
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> ' + wa_builder.i18n.save);
                }
            });
        },

        showSaveSuccess: function() {
            $('.wa-save-indicator').removeClass('saving');
            $('.wa-save-message').text(wa_builder.i18n.saved);
            
            setTimeout(function() {
                $('.wa-save-message').text('All changes saved');
            }, 2000);
        },

        markDirty: function() {
            this.isDirty = true;
            $('.wa-save-indicator').addClass('saving');
            $('.wa-save-message').text('Unsaved changes');
            
            // Reset auto-save timer
            if (this.autoSaveTimer) {
                clearTimeout(this.autoSaveTimer);
            }
            
            if (wa_builder.auto_save) {
                this.autoSaveTimer = setTimeout(function() {
                    this.saveWorkflow();
                }.bind(this), wa_builder.auto_save_interval * 1000);
            }
        },

        startAutoSave: function() {
            // Auto-save is triggered by markDirty()
        },

        showTestModal: function() {
            $('#wa-test-modal').show();
        },

        runTest: function() {
            var self = this;
            var $button = $('#wa-run-test');
            
            $button.prop('disabled', true).text('Running...');
            
            var testData = {
                trigger_type: $('#test-trigger-type').val(),
                trigger_data: JSON.parse($('#test-data').val() || '{}')
            };
            
            // Execute workflow
            $.ajax({
                url: wa_builder.api_url + '/workflows/' + this.workflow.id + '/execute',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                data: JSON.stringify(testData),
                contentType: 'application/json',
                success: function(response) {
                    alert('Workflow executed successfully. Check execution history for details.');
                    $('#wa-test-modal').hide();
                    $button.prop('disabled', false).text('Run Test');
                },
                error: function() {
                    alert('Failed to execute workflow.');
                    $button.prop('disabled', false).text('Run Test');
                }
            });
        },

        // Canvas control methods
        zoomIn: function() {
            // Implement zoom in
            console.log('Zoom in');
        },

        zoomOut: function() {
            // Implement zoom out
            console.log('Zoom out');
        },

        fitView: function() {
            // Implement fit view
            console.log('Fit view');
        },

        centerView: function() {
            // Implement center view
            console.log('Center view');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#wa-workflow-canvas').length > 0) {
            WorkflowBuilder.init();
        }
    });

    // Expose globally for debugging
    window.WorkflowBuilder = WorkflowBuilder;

})(jQuery);