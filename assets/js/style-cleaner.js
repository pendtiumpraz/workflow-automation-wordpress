/**
 * Style Cleaner - Force remove unwanted styles
 */

jQuery(document).ready(function($) {
    
    function cleanNodeStyles() {
        // Remove all inline background-color styles from node headers
        $('.wa-node-header[style*="background-color"]').each(function() {
            var $header = $(this);
            var style = $header.attr('style') || '';
            
            // Remove background-color but keep other styles
            style = style.replace(/background-color[^;]*;?/gi, '');
            
            if (style.trim()) {
                $header.attr('style', style);
            } else {
                $header.removeAttr('style');
            }
        });
        
        // Force add data attributes for styling
        $('.wa-workflow-node').each(function() {
            var $node = $(this);
            var nodeId = $node.attr('id');
            
            if (nodeId && !$node.attr('data-node-type')) {
                // Try to extract node type from the workflow builder data
                if (window.WorkflowBuilder && window.WorkflowBuilder.nodes) {
                    var node = window.WorkflowBuilder.nodes.find(function(n) {
                        return n.id === nodeId;
                    });
                    
                    if (node && node.type) {
                        $node.attr('data-node-type', node.type);
                    }
                }
            }
        });
        
        // Remove font-family overrides that might be coming from other plugins
        $('.wa-workflow-node, .wa-workflow-node *').each(function() {
            var $el = $(this);
            var style = $el.attr('style') || '';
            
            // Remove font-family styles
            style = style.replace(/font-family[^;]*;?/gi, '');
            
            if (style.trim()) {
                $el.attr('style', style);
            } else {
                $el.removeAttr('style');
            }
        });
    }
    
    // Clean styles on load
    setTimeout(cleanNodeStyles, 100);
    
    // Clean styles whenever nodes are added
    if (window.WorkflowBuilder) {
        var originalRenderNode = window.WorkflowBuilder.renderNode;
        if (originalRenderNode) {
            window.WorkflowBuilder.renderNode = function(node) {
                var result = originalRenderNode.call(this, node);
                setTimeout(cleanNodeStyles, 10);
                return result;
            };
        }
    }
    
    // Observer to clean styles when DOM changes
    var observer = new MutationObserver(function(mutations) {
        var shouldClean = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (
                        node.classList.contains('wa-workflow-node') ||
                        node.querySelector('.wa-workflow-node')
                    )) {
                        shouldClean = true;
                    }
                });
            }
        });
        
        if (shouldClean) {
            setTimeout(cleanNodeStyles, 10);
        }
    });
    
    // Start observing
    var canvas = document.getElementById('wa-workflow-canvas');
    if (canvas) {
        observer.observe(canvas, {
            childList: true,
            subtree: true
        });
    }
    
    // Clean on window resize (sometimes triggers style recalculation)
    $(window).on('resize', function() {
        setTimeout(cleanNodeStyles, 100);
    });
});

// Force style injection via JavaScript for immediate effect
(function() {
    var forceStyles = `
        .wa-workflow-node {
            background: #ffffff !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 12px !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .wa-node-header {
            background: #6366f1 !important;
            color: white !important;
            padding: 16px 18px !important;
            border-radius: 10px 10px 0 0 !important;
        }
        .wa-workflow-node[data-node-type="email"] .wa-node-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }
        .wa-workflow-node[data-node-type="slack"] .wa-node-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
        }
        .wa-workflow-node[data-node-type="openai"] .wa-node-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        }
        .wa-workflow-node[data-node-type="line"] .wa-node-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important;
        }
    `;
    
    var styleSheet = document.createElement('style');
    styleSheet.type = 'text/css';
    styleSheet.innerHTML = forceStyles;
    document.head.appendChild(styleSheet);
})();