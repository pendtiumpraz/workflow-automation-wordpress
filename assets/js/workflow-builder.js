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
            
            // Add SVG layer for connections
            var svgLayer = $('<svg class="wa-connections-layer"></svg>');
            this.canvas.append(svgLayer);
            this.connectionsLayer = svgLayer[0];

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
            
            // Load existing connections after nodes are loaded
            var self = this;
            setTimeout(function() {
                if (self.workflow.flow_data && self.workflow.flow_data.edges) {
                    self.loadConnections(self.workflow.flow_data.edges);
                }
            }, 100);
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
                .attr('data-node-type', node.type)
                .css({
                    left: node.position.x + 'px',
                    top: node.position.y + 'px'
                })
                .html('<div class="wa-node-header" style="--node-bg-color: ' + node.color + '">' +
                      '<span class="dashicons ' + node.icon + '"></span>' +
                      '<span class="wa-node-label">' + node.label + '</span>' +
                      '<button type="button" class="wa-node-delete" title="Delete node">&times;</button>' +
                      '</div>' +
                      '<div class="wa-node-body">' +
                      '<div class="wa-node-ports">' +
                      '<div class="wa-node-port wa-port-in" data-port="in" data-node-id="' + node.id + '"></div>' +
                      '<div class="wa-node-port wa-port-out" data-port="out" data-node-id="' + node.id + '"></div>' +
                      '</div>' +
                      '</div>');

            this.canvas.append(nodeHtml);
            
            // Make ports connectable
            this.makePortsConnectable(nodeHtml);

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
        
        loadConnections: function(edges) {
            var self = this;
            if (!edges || !Array.isArray(edges)) {
                return;
            }
            
            edges.forEach(function(edge) {
                if (edge.source && edge.target) {
                    var connection = {
                        id: 'conn_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                        source: edge.source,
                        target: edge.target
                    };
                    
                    self.connections.push(connection);
                    self.drawConnection(connection);
                }
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
            
            console.log('showNodeConfiguration called for node:', node);
            
            // Check if modal exists
            var modal = $('#wa-node-config-modal');
            console.log('Modal found:', modal.length > 0);
            
            // Set title
            var title = $('#wa-node-config-title');
            console.log('Title element found:', title.length > 0);
            if (title.length > 0) {
                title.text('Configure ' + node.label);
            }
            
            // Show modal
            if (modal.length > 0) {
                modal.show();
                console.log('Modal should be visible now');
            } else {
                console.error('Modal not found!');
            }
            
            // Load node configuration fields
            this.loadNodeConfigFields(node);
        },

        loadNodeConfigFields: function(node) {
            var self = this;
            
            console.log('loadNodeConfigFields called, making API request to:', wa_builder.api_url + '/nodes/types/' + node.type + '/schema');
            console.log('Node object:', node);
            console.log('wa_builder object:', wa_builder);
            
            // Debug: Check if element exists
            var configFieldsElement = $('#wa-node-config-fields');
            console.log('Config fields element found:', configFieldsElement.length > 0);
            console.log('Config fields element:', configFieldsElement);
            
            // Show loading message and test button
            if (configFieldsElement.length > 0) {
                configFieldsElement.html('<p>Loading configuration fields...</p>' +
                    '<button onclick="WorkflowBuilder.testAPI(\'' + node.type + '\')">Test API Manually</button>');
            } else {
                console.error('Could not find #wa-node-config-fields element!');
                // Try to find modal body and add content there
                var modalBody = $('.wa-modal-body');
                console.log('Modal body found:', modalBody.length > 0);
                if (modalBody.length > 0) {
                    modalBody.html('<div id="wa-node-config-fields"><p>Loading configuration fields...</p>' +
                        '<button onclick="WorkflowBuilder.testAPI(\'' + node.type + '\')">Test API Manually</button></div>');
                }
            }
            
            // Get node configuration schema via API
            $.ajax({
                url: wa_builder.api_url + '/nodes/types/' + node.type + '/schema',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                success: function(schema) {
                    console.log('Node schema loaded:', schema);
                    self.renderConfigFields(node, schema);
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load node schema:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    var errorHtml = '<div style="color: red; padding: 20px;">' +
                        '<p><strong>Failed to load configuration fields.</strong></p>' +
                        '<p>Status: ' + xhr.status + ' ' + xhr.statusText + '</p>' +
                        '<p>Error: ' + error + '</p>' +
                        '<p>Check browser console for details.</p>' +
                        '<button onclick="WorkflowBuilder.testAPI(\'' + node.type + '\')">Test API Manually</button>' +
                        '</div>';
                    
                    var configFields = $('#wa-node-config-fields');
                    if (configFields.length > 0) {
                        configFields.html(errorHtml);
                    } else {
                        $('.wa-modal-body').html('<div id="wa-node-config-fields">' + errorHtml + '</div>');
                    }
                }
            });
        },

        testAPI: function(nodeType) {
            console.log('Testing API for node type:', nodeType);
            fetch(wa_builder.api_url + '/nodes/types/' + nodeType + '/schema', {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': wa_builder.nonce,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const json = JSON.parse(text);
                    console.log('Parsed JSON:', json);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
        },

        renderConfigFields: function(node, schema) {
            var html = '';
            
            if (schema && schema.settings_fields) {
                schema.settings_fields.forEach(function(field) {
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
                    edges: this.connections.map(function(conn) {
                        return {
                            source: conn.source,
                            target: conn.target
                        };
                    })
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
        },
        
        // Connection handling methods
        makePortsConnectable: function(nodeElement) {
            var self = this;
            var isConnecting = false;
            var startPort = null;
            var tempLine = null;
            
            // Handle port mousedown (start connection)
            nodeElement.find('.wa-node-port').on('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $port = $(this);
                var portType = $port.data('port');
                var nodeId = $port.data('node-id');
                
                // Only allow connections from output ports
                if (portType !== 'out') {
                    return;
                }
                
                isConnecting = true;
                startPort = {
                    element: $port,
                    type: portType,
                    nodeId: nodeId
                };
                
                $port.addClass('connecting');
                
                // Create temporary line
                var startPos = self.getPortPosition($port);
                tempLine = self.createTempConnection(startPos.x, startPos.y);
                
                // Track mouse movement
                $(document).on('mousemove.connection', function(e) {
                    if (isConnecting && tempLine) {
                        var canvasOffset = self.canvas.offset();
                        var endX = e.pageX - canvasOffset.left;
                        var endY = e.pageY - canvasOffset.top;
                        self.updateTempConnection(tempLine, startPos.x, startPos.y, endX, endY);
                    }
                });
                
                // Handle mouseup
                $(document).on('mouseup.connection', function(e) {
                    if (isConnecting) {
                        // Check if we're over a valid target port
                        var $target = $(e.target);
                        if ($target.hasClass('wa-node-port') && $target.data('port') === 'in') {
                            var targetNodeId = $target.data('node-id');
                            
                            // Don't connect to same node
                            if (targetNodeId !== startPort.nodeId) {
                                // Create connection
                                self.createConnection(startPort.nodeId, targetNodeId);
                            }
                        }
                        
                        // Clean up
                        startPort.element.removeClass('connecting');
                        $('.wa-node-port').removeClass('valid-target invalid-target');
                        if (tempLine) {
                            tempLine.remove();
                        }
                        isConnecting = false;
                        startPort = null;
                        tempLine = null;
                        
                        $(document).off('.connection');
                    }
                });
            });
            
            // Handle port hover during connection
            nodeElement.find('.wa-node-port').on('mouseenter', function() {
                if (isConnecting) {
                    var $port = $(this);
                    var portType = $port.data('port');
                    var nodeId = $port.data('node-id');
                    
                    if (portType === 'in' && nodeId !== startPort.nodeId) {
                        $port.addClass('valid-target');
                    } else {
                        $port.addClass('invalid-target');
                    }
                }
            }).on('mouseleave', function() {
                $(this).removeClass('valid-target invalid-target');
            });
        },
        
        getPortPosition: function($port) {
            var portOffset = $port.offset();
            var canvasOffset = this.canvas.offset();
            return {
                x: portOffset.left - canvasOffset.left + $port.width() / 2,
                y: portOffset.top - canvasOffset.top + $port.height() / 2
            };
        },
        
        createTempConnection: function(x1, y1) {
            var svg = $('<svg class="wa-temp-connection" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">' +
                       '<path class="wa-temp-connection-line" d="M' + x1 + ',' + y1 + ' L' + x1 + ',' + y1 + '"/>' +
                       '</svg>');
            this.canvas.append(svg);
            return svg;
        },
        
        updateTempConnection: function(svg, x1, y1, x2, y2) {
            var path = this.createConnectionPath(x1, y1, x2, y2);
            svg.find('path').attr('d', path);
        },
        
        createConnection: function(sourceNodeId, targetNodeId) {
            // Check if connection already exists
            var existingConnection = this.connections.find(function(conn) {
                return conn.source === sourceNodeId && conn.target === targetNodeId;
            });
            
            if (existingConnection) {
                return;
            }
            
            // Add to connections array
            var connection = {
                id: 'conn_' + Date.now(),
                source: sourceNodeId,
                target: targetNodeId
            };
            
            this.connections.push(connection);
            this.drawConnection(connection);
            this.markDirty();
            
            console.log('Connection created:', connection);
        },
        
        drawConnection: function(connection) {
            var sourceNode = $('#' + connection.source);
            var targetNode = $('#' + connection.target);
            
            if (sourceNode.length === 0 || targetNode.length === 0) {
                return;
            }
            
            var sourcePort = sourceNode.find('.wa-port-out');
            var targetPort = targetNode.find('.wa-port-in');
            
            var sourcePos = this.getPortPosition(sourcePort);
            var targetPos = this.getPortPosition(targetPort);
            
            var path = this.createConnectionPath(sourcePos.x, sourcePos.y, targetPos.x, targetPos.y);
            
            var pathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            pathElement.setAttribute('class', 'wa-connection-path');
            pathElement.setAttribute('d', path);
            pathElement.setAttribute('data-connection-id', connection.id);
            pathElement.setAttribute('data-source', connection.source);
            pathElement.setAttribute('data-target', connection.target);
            
            this.connectionsLayer.appendChild(pathElement);
            
            // Add click handler for deletion
            var self = this;
            $(pathElement).on('click', function(e) {
                e.stopPropagation();
                if (confirm('Delete this connection?')) {
                    self.deleteConnection(connection.id);
                }
            });
        },
        
        createConnectionPath: function(x1, y1, x2, y2) {
            // Create a curved path (bezier curve)
            var dx = x2 - x1;
            var dy = y2 - y1;
            var cx1 = x1 + dx * 0.5;
            var cy1 = y1;
            var cx2 = x2 - dx * 0.5;
            var cy2 = y2;
            
            return 'M' + x1 + ',' + y1 + ' C' + cx1 + ',' + cy1 + ' ' + cx2 + ',' + cy2 + ' ' + x2 + ',' + y2;
        },
        
        updateConnections: function(nodeId) {
            var self = this;
            
            // Update all connections related to this node
            this.connections.forEach(function(connection) {
                if (connection.source === nodeId || connection.target === nodeId) {
                    // Remove old path
                    $('[data-connection-id="' + connection.id + '"]').remove();
                    // Redraw
                    self.drawConnection(connection);
                }
            });
        },
        
        deleteConnection: function(connectionId) {
            // Remove from array
            this.connections = this.connections.filter(function(conn) {
                return conn.id !== connectionId;
            });
            
            // Remove from DOM
            $('[data-connection-id="' + connectionId + '"]').remove();
            
            this.markDirty();
        },
        
        removeNodeConnections: function(nodeId) {
            var self = this;
            
            // Find all connections for this node
            var connectionsToRemove = this.connections.filter(function(conn) {
                return conn.source === nodeId || conn.target === nodeId;
            });
            
            // Remove each connection
            connectionsToRemove.forEach(function(conn) {
                self.deleteConnection(conn.id);
            });
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