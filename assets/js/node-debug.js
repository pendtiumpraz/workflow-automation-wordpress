/**
 * Node Debug Script - Find out why nodes aren't appearing
 */

jQuery(document).ready(function($) {
    
    // Debug when node is added
    if (window.WorkflowBuilder) {
        var originalAddNode = window.WorkflowBuilder.addNode;
        window.WorkflowBuilder.addNode = function(type, label, icon, color, position) {
            console.log('DEBUG: Adding node', {
                type: type,
                label: label,
                position: position,
                nodesBeforeAdd: this.nodes.length
            });
            
            var result = originalAddNode.call(this, type, label, icon, color, position);
            
            console.log('DEBUG: After adding node', {
                nodesAfterAdd: this.nodes.length,
                nodesInDOM: $('.wa-workflow-node').length,
                canvasSize: {
                    width: $('#wa-workflow-canvas').width(),
                    height: $('#wa-workflow-canvas').height()
                }
            });
            
            // Log all nodes in DOM
            $('.wa-workflow-node').each(function(index) {
                var $node = $(this);
                console.log('Node ' + index + ':', {
                    id: $node.attr('id'),
                    position: {
                        left: $node.css('left'),
                        top: $node.css('top')
                    },
                    size: {
                        width: $node.width(),
                        height: $node.height()
                    },
                    visible: $node.is(':visible'),
                    zIndex: $node.css('z-index')
                });
            });
            
            return result;
        };
        
        // Debug when node is rendered
        var originalRenderNode = window.WorkflowBuilder.renderNode;
        window.WorkflowBuilder.renderNode = function(node) {
            console.log('DEBUG: Rendering node', node);
            
            var result = originalRenderNode.call(this, node);
            
            var $renderedNode = $('#' + node.id);
            console.log('DEBUG: Node rendered', {
                nodeId: node.id,
                found: $renderedNode.length > 0,
                position: {
                    left: $renderedNode.css('left'),
                    top: $renderedNode.css('top')
                },
                parent: $renderedNode.parent().attr('id')
            });
            
            // Force visibility
            if ($renderedNode.length > 0) {
                $renderedNode.css({
                    'visibility': 'visible',
                    'opacity': '1',
                    'display': 'block',
                    'z-index': 100 + $('.wa-workflow-node').length
                });
            }
            
            return result;
        };
    }
    
    // Monitor for DOM changes
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('wa-workflow-node')) {
                        console.log('DEBUG: New node added to DOM', {
                            id: node.id,
                            classes: node.className,
                            position: {
                                left: node.style.left,
                                top: node.style.top
                            }
                        });
                    }
                });
            }
        });
    });
    
    var canvas = document.getElementById('wa-workflow-canvas');
    if (canvas) {
        observer.observe(canvas, {
            childList: true,
            subtree: true
        });
        
        console.log('DEBUG: Canvas monitoring started', {
            canvasId: canvas.id,
            canvasSize: {
                width: $(canvas).width(),
                height: $(canvas).height()
            },
            canvasPosition: $(canvas).position()
        });
    }
    
    // Add debug button
    $('body').append(`
        <button id="debug-nodes" style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #f59e0b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 10000;
        ">Debug Nodes</button>
    `);
    
    $('#debug-nodes').on('click', function() {
        console.log('=== NODE DEBUG INFO ===');
        console.log('Canvas:', $('#wa-workflow-canvas'));
        console.log('Nodes in DOM:', $('.wa-workflow-node').length);
        console.log('WorkflowBuilder nodes array:', window.WorkflowBuilder ? window.WorkflowBuilder.nodes : 'Not found');
        
        $('.wa-workflow-node').each(function(index) {
            var $node = $(this);
            console.log(`Node ${index}:`, {
                id: $node.attr('id'),
                type: $node.attr('data-node-type'),
                visible: $node.is(':visible'),
                position: $node.position(),
                offset: $node.offset(),
                css: {
                    position: $node.css('position'),
                    left: $node.css('left'),
                    top: $node.css('top'),
                    zIndex: $node.css('z-index'),
                    display: $node.css('display'),
                    visibility: $node.css('visibility')
                }
            });
        });
    });
});