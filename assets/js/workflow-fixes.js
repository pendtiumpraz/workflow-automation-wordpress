/**
 * Workflow Fixes - Save functionality and connection visualization
 */

jQuery(document).ready(function($) {
    console.log('Workflow fixes loading...');
    
    // Fix 1: Ensure save button works
    function fixSaveButton() {
        console.log('Fixing save button...');
        
        // Find all possible save buttons
        var saveButtons = $('#wa-save-workflow, .wa-save-workflow, button[data-action="save-workflow"]');
        console.log('Found save buttons:', saveButtons.length);
        
        // Remove any existing click handlers and add new one
        saveButtons.off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Save button clicked');
            
            if (window.WorkflowBuilder && typeof window.WorkflowBuilder.saveWorkflow === 'function') {
                console.log('Calling WorkflowBuilder.saveWorkflow()');
                window.WorkflowBuilder.saveWorkflow();
            } else {
                console.error('WorkflowBuilder.saveWorkflow not found! Attempting direct save...');
                directSaveWorkflow();
            }
        });
        
        // Also add auto-save indicator
        if (window.WorkflowBuilder) {
            var originalMarkDirty = window.WorkflowBuilder.markDirty;
            window.WorkflowBuilder.markDirty = function() {
                originalMarkDirty.call(this);
                $('.wa-save-indicator').addClass('saving');
                $('.wa-save-message').text('Changes pending...');
            };
            
            var originalShowSaveSuccess = window.WorkflowBuilder.showSaveSuccess;
            window.WorkflowBuilder.showSaveSuccess = function() {
                if (originalShowSaveSuccess) {
                    originalShowSaveSuccess.call(this);
                }
                $('.wa-save-indicator').removeClass('saving');
                $('.wa-save-message').text('All changes saved');
                
                // Show success message
                showNotification('Workflow saved successfully!', 'success');
            };
        }
    }
    
    // Direct save function as fallback
    function directSaveWorkflow() {
        console.log('Attempting direct save...');
        
        if (!window.WorkflowBuilder || !window.WorkflowBuilder.workflow) {
            console.error('No workflow data available');
            showNotification('Error: No workflow data to save', 'error');
            return;
        }
        
        var workflow = window.WorkflowBuilder.workflow;
        var nodes = window.WorkflowBuilder.nodes || [];
        var connections = window.WorkflowBuilder.connections || [];
        
        var workflowData = {
            name: workflow.name || 'Untitled Workflow',
            status: workflow.status || 'draft',
            flow_data: {
                nodes: nodes.map(function(node) {
                    return {
                        node_id: node.id,
                        node_type: node.type,
                        settings: JSON.stringify(node.data || {}),
                        position_x: node.position ? node.position.x : 100,
                        position_y: node.position ? node.position.y : 100
                    };
                }),
                edges: connections.map(function(conn) {
                    return {
                        source: conn.source,
                        target: conn.target
                    };
                })
            }
        };
        
        console.log('Saving workflow data:', workflowData);
        
        $.ajax({
            url: wa_builder.api_url + '/workflows/' + workflow.id,
            method: 'PUT',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                xhr.setRequestHeader('Content-Type', 'application/json');
            },
            data: JSON.stringify(workflowData),
            success: function(response) {
                console.log('Save successful:', response);
                showNotification('Workflow saved successfully!', 'success');
                if (window.WorkflowBuilder) {
                    window.WorkflowBuilder.isDirty = false;
                }
            },
            error: function(xhr, status, error) {
                console.error('Save failed:', {status: xhr.status, error: error, response: xhr.responseText});
                showNotification('Failed to save workflow: ' + error, 'error');
            }
        });
    }
    
    // Fix 2: Ensure connections are visible
    function fixConnections() {
        console.log('Fixing connections...');
        
        // Ensure SVG layer exists and is properly sized
        var canvas = $('#wa-workflow-canvas');
        if (canvas.length === 0) {
            console.error('Canvas not found!');
            return;
        }
        
        // Remove any existing SVG layers to start fresh
        canvas.find('.wa-connections-layer, .wa-temp-connection').remove();
        
        // Create new SVG layer
        console.log('Creating SVG layer...');
        var svgLayer = $('<svg class="wa-connections-layer" xmlns="http://www.w3.org/2000/svg"></svg>');
        
        // Set initial size
        var canvasWidth = Math.max(canvas.width(), 1000);
        var canvasHeight = Math.max(canvas.height(), 800);
        
        svgLayer.attr({
            'width': canvasWidth,
            'height': canvasHeight,
            'viewBox': '0 0 ' + canvasWidth + ' ' + canvasHeight,
            'style': 'position: absolute; top: 0; left: 0; pointer-events: none; z-index: 5;'
        });
        
        // Insert at beginning so it's behind nodes
        canvas.prepend(svgLayer);
        
        // Update WorkflowBuilder reference
        if (window.WorkflowBuilder) {
            window.WorkflowBuilder.connectionsLayer = svgLayer[0];
            console.log('Updated WorkflowBuilder.connectionsLayer');
            
            // Redraw existing connections
            if (window.WorkflowBuilder.connections && window.WorkflowBuilder.connections.length > 0) {
                console.log('Redrawing', window.WorkflowBuilder.connections.length, 'existing connections');
                window.WorkflowBuilder.connections.forEach(function(conn) {
                    window.WorkflowBuilder.drawConnection(conn);
                });
            }
        }
        
        // Style for connection paths
        if ($('#connection-styles').length === 0) {
            $('head').append(`
                <style id="connection-styles">
                    .wa-connections-layer {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        pointer-events: none;
                        z-index: 5;
                    }
                    
                    .wa-connection-path {
                        stroke: #3b82f6;
                        stroke-width: 3;
                        fill: none;
                        pointer-events: stroke;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        marker-end: url(#arrowhead);
                    }
                    
                    .wa-connection-path:hover {
                        stroke: #ef4444;
                        stroke-width: 4;
                        filter: drop-shadow(0 0 4px rgba(239, 68, 68, 0.4));
                    }
                    
                    .wa-temp-connection-line {
                        stroke: #3b82f6;
                        stroke-width: 3;
                        stroke-dasharray: 8, 4;
                        fill: none;
                        pointer-events: none;
                        animation: dash-animation 0.5s linear infinite;
                    }
                    
                    @keyframes dash-animation {
                        to { stroke-dashoffset: -12; }
                    }
                    
                    /* Execution order indicators */
                    .wa-node-order {
                        position: absolute;
                        top: -10px;
                        left: -10px;
                        background: #3b82f6;
                        color: white;
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 12px;
                        z-index: 1000;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    }
                </style>
            `);
            
            // Add arrow marker definition to SVG
            if ($('.wa-connections-layer').length > 0 && $('#arrowhead').length === 0) {
                var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
                defs.innerHTML = `
                    <marker id="arrowhead" markerWidth="10" markerHeight="10" 
                            refX="9" refY="3" orient="auto">
                        <polygon points="0 0, 10 3, 0 6" fill="#3b82f6" />
                    </marker>
                `;
                $('.wa-connections-layer')[0].appendChild(defs);
            }
        }
    }
    
    // Fix 3: Add visual connection helper
    function addConnectionHelper() {
        // Override createConnection to ensure it works
        if (window.WorkflowBuilder) {
            var originalCreateConnection = window.WorkflowBuilder.createConnection;
            window.WorkflowBuilder.createConnection = function(sourceNodeId, targetNodeId) {
                console.log('Creating connection:', sourceNodeId, '->', targetNodeId);
                
                // Ensure connections layer exists
                if (!this.connectionsLayer || !document.contains(this.connectionsLayer)) {
                    console.log('Connections layer missing, recreating...');
                    fixConnections();
                }
                
                // Call original
                var result = originalCreateConnection.call(this, sourceNodeId, targetNodeId);
                
                // Ensure connection is visible
                setTimeout(() => {
                    var paths = $('.wa-connection-path');
                    console.log('Total connections in DOM:', paths.length);
                    
                    // Force visibility
                    paths.css({
                        'visibility': 'visible',
                        'opacity': '1'
                    });
                    
                    // Update execution order
                    updateExecutionOrder();
                }, 100);
                
                // Show success
                showNotification('Connection created!', 'success');
                
                // Mark as dirty for auto-save
                this.markDirty();
                
                // Trigger custom event
                $(document).trigger('connectionCreated', [{
                    source: sourceNodeId,
                    target: targetNodeId
                }]);
                
                return result;
            };
            
            // Override drawConnection to ensure paths are drawn
            var originalDrawConnection = window.WorkflowBuilder.drawConnection;
            window.WorkflowBuilder.drawConnection = function(connection) {
                console.log('Drawing connection:', connection);
                
                // Ensure SVG layer exists
                if (!this.connectionsLayer) {
                    fixConnections();
                }
                
                // Call original
                if (originalDrawConnection) {
                    originalDrawConnection.call(this, connection);
                } else {
                    // Fallback implementation
                    this.drawConnectionFallback(connection);
                }
            };
            
            // Fallback drawing method
            window.WorkflowBuilder.drawConnectionFallback = function(connection) {
                console.log('Using fallback connection drawing for:', connection);
                
                var sourceNode = $('#' + connection.source);
                var targetNode = $('#' + connection.target);
                
                if (sourceNode.length === 0 || targetNode.length === 0) {
                    console.error('Nodes not found for connection:', connection);
                    return;
                }
                
                var sourcePort = sourceNode.find('.wa-port-out');
                var targetPort = targetNode.find('.wa-port-in');
                
                if (sourcePort.length === 0 || targetPort.length === 0) {
                    console.error('Ports not found for connection');
                    return;
                }
                
                var sourcePos = this.getPortPosition(sourcePort);
                var targetPos = this.getPortPosition(targetPort);
                
                var path = this.createConnectionPath(sourcePos.x, sourcePos.y, targetPos.x, targetPos.y);
                
                // Remove existing connection with same ID if exists
                $('[data-connection-id="' + connection.id + '"]').remove();
                
                var pathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                pathElement.setAttribute('class', 'wa-connection-path');
                pathElement.setAttribute('d', path);
                pathElement.setAttribute('data-connection-id', connection.id);
                pathElement.setAttribute('data-source', connection.source);
                pathElement.setAttribute('data-target', connection.target);
                pathElement.setAttribute('marker-end', 'url(#arrowhead)');
                
                this.connectionsLayer.appendChild(pathElement);
                
                // Add click handler for deletion
                var self = this;
                $(pathElement).on('click', function(e) {
                    e.stopPropagation();
                    if (confirm('Delete this connection?')) {
                        self.deleteConnection(connection.id);
                        updateExecutionOrder();
                    }
                });
                
                console.log('Connection drawn:', pathElement);
            };
            
            // Override getPortPosition for more accurate positioning
            var originalGetPortPosition = window.WorkflowBuilder.getPortPosition;
            window.WorkflowBuilder.getPortPosition = function($port) {
                if (!$port || $port.length === 0) {
                    console.error('Invalid port element');
                    return {x: 0, y: 0};
                }
                
                var portOffset = $port.offset();
                var canvasOffset = this.canvas.offset();
                
                // Account for port size and positioning
                var x = portOffset.left - canvasOffset.left + ($port.outerWidth() / 2);
                var y = portOffset.top - canvasOffset.top + ($port.outerHeight() / 2);
                
                return {x: x, y: y};
            };
        }
    }
    
    // Fix 4: Add connection guide button
    function addConnectionGuideButton() {
        if ($('#connection-guide-btn').length === 0) {
            var btn = $(`
                <button id="connection-guide-btn" style="
                    position: fixed;
                    bottom: 60px;
                    right: 20px;
                    background: #3b82f6;
                    color: white;
                    padding: 10px 20px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    z-index: 10000;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                ">
                    <span class="dashicons dashicons-admin-links"></span>
                    Connect Nodes
                </button>
            `);
            
            $('body').append(btn);
            
            btn.on('click', function() {
                showConnectionGuide();
            });
        }
    }
    
    function showConnectionGuide() {
        var guide = $(`
            <div id="connection-guide-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.8);
                z-index: 10001;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 500px;
                    text-align: center;
                ">
                    <h2>How to Connect Nodes</h2>
                    <ol style="text-align: left; margin: 20px 0;">
                        <li>Click and hold on the <strong>OUTPUT port</strong> (right side) of a node</li>
                        <li>Drag to the <strong>INPUT port</strong> (left side) of another node</li>
                        <li>Release to create connection</li>
                        <li>Click on a connection line to delete it</li>
                    </ol>
                    <p><strong>Note:</strong> Connections show the execution flow from one node to the next.</p>
                    <button onclick="jQuery('#connection-guide-overlay').remove()" style="
                        background: #3b82f6;
                        color: white;
                        padding: 10px 30px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        margin-top: 20px;
                    ">Got it!</button>
                </div>
            </div>
        `);
        
        $('body').append(guide);
    }
    
    // Add execution order visualization
    function updateExecutionOrder() {
        console.log('Updating execution order...');
        
        // Remove existing order indicators
        $('.wa-node-order').remove();
        
        if (!window.WorkflowBuilder || !window.WorkflowBuilder.nodes) {
            return;
        }
        
        // Find start nodes (nodes with no incoming connections)
        var startNodes = [];
        var nodeMap = {};
        var incomingCount = {};
        
        window.WorkflowBuilder.nodes.forEach(function(node) {
            nodeMap[node.id] = node;
            incomingCount[node.id] = 0;
        });
        
        window.WorkflowBuilder.connections.forEach(function(conn) {
            incomingCount[conn.target] = (incomingCount[conn.target] || 0) + 1;
        });
        
        // Find nodes with no incoming connections
        for (var nodeId in incomingCount) {
            if (incomingCount[nodeId] === 0) {
                startNodes.push(nodeId);
            }
        }
        
        // Traverse graph and assign order numbers
        var visited = {};
        var orderNumber = 1;
        
        function assignOrder(nodeId) {
            if (visited[nodeId]) return;
            visited[nodeId] = true;
            
            // Add order indicator to node
            var $node = $('#' + nodeId);
            if ($node.length > 0) {
                var $orderIndicator = $('<div class="wa-node-order">' + orderNumber + '</div>');
                $node.append($orderIndicator);
                orderNumber++;
            }
            
            // Find outgoing connections
            window.WorkflowBuilder.connections.forEach(function(conn) {
                if (conn.source === nodeId) {
                    assignOrder(conn.target);
                }
            });
        }
        
        // Start from each start node
        startNodes.forEach(function(nodeId) {
            assignOrder(nodeId);
        });
        
        console.log('Execution order updated. Total nodes ordered:', orderNumber - 1);
    }
    
    // Notification helper
    function showNotification(message, type = 'info') {
        var backgroundColor = '#3b82f6'; // default blue
        if (type === 'success') backgroundColor = '#10b981';
        if (type === 'error') backgroundColor = '#ef4444';
        
        var notification = $(`
            <div style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${backgroundColor};
                color: white;
                padding: 15px 20px;
                border-radius: 6px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                z-index: 10002;
                animation: slideIn 0.3s ease-out;
                max-width: 400px;
            ">
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                notification.remove();
            });
        }, 3000);
    }
    
    // Initialize all fixes
    setTimeout(function() {
        console.log('Initializing workflow fixes...');
        
        // Check if WorkflowBuilder exists
        if (!window.WorkflowBuilder) {
            console.error('WorkflowBuilder not found, retrying in 500ms...');
            setTimeout(arguments.callee, 500);
            return;
        }
        
        fixSaveButton();
        fixConnections();
        addConnectionHelper();
        addConnectionGuideButton();
        updateExecutionOrder();
        
        // Add keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                $('#wa-save-workflow').click();
            }
        });
        
        console.log('Workflow fixes applied!');
        showNotification('Workflow builder enhanced! Use Ctrl+S to save.', 'info');
    }, 1000);
    
    // Re-apply fixes when canvas changes
    var observer = new MutationObserver(function(mutations) {
        var needsConnectionFix = false;
        var needsOrderUpdate = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.classList && node.classList.contains('wa-workflow-node')) {
                        needsOrderUpdate = true;
                    }
                });
                mutation.removedNodes.forEach(function(node) {
                    if (node.classList && (node.classList.contains('wa-workflow-node') || node.classList.contains('wa-connections-layer'))) {
                        needsConnectionFix = true;
                        needsOrderUpdate = true;
                    }
                });
            }
        });
        
        if (needsConnectionFix) {
            setTimeout(fixConnections, 100);
        }
        if (needsOrderUpdate) {
            setTimeout(updateExecutionOrder, 200);
        }
    });
    
    var canvas = document.getElementById('wa-workflow-canvas');
    if (canvas) {
        observer.observe(canvas, {
            childList: true,
            subtree: true
        });
    }
    
    // Listen for connection events
    $(document).on('connectionCreated connectionDeleted', function() {
        updateExecutionOrder();
    });
    
    // Add CSS animation for notifications
    if ($('#notification-styles').length === 0) {
        $('head').append(`
            <style id="notification-styles">
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            </style>
        `);
    }
});