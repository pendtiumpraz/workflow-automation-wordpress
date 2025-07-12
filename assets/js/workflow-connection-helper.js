/**
 * Workflow Connection Helper
 * Visual guide for connecting nodes
 */

jQuery(document).ready(function($) {
    // Add connection instructions when page loads
    function showConnectionGuide() {
        if ($('.wa-workflow-canvas').length && !$('.wa-connection-guide').length) {
            var guide = $('<div class="wa-connection-guide" style="' +
                'position: fixed; ' +
                'bottom: 20px; ' +
                'left: 50%; ' +
                'transform: translateX(-50%); ' +
                'background: rgba(0, 0, 0, 0.9); ' +
                'color: white; ' +
                'padding: 20px 30px; ' +
                'border-radius: 10px; ' +
                'font-size: 16px; ' +
                'z-index: 10000; ' +
                'box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); ' +
                'max-width: 600px; ' +
                'text-align: center;' +
                '">' +
                '<h3 style="margin: 0 0 10px 0; color: #60a5fa;">How to Connect Nodes</h3>' +
                '<p style="margin: 0 0 15px 0;">1. <strong>Hover</strong> over the output port (right side) of a node</p>' +
                '<p style="margin: 0 0 15px 0;">2. <strong>Click and drag</strong> from the output port</p>' +
                '<p style="margin: 0 0 15px 0;">3. <strong>Drop</strong> on the input port (left side) of another node</p>' +
                '<div style="display: flex; justify-content: center; gap: 20px; align-items: center; margin-top: 20px;">' +
                '<div style="text-align: center;">' +
                '<div style="width: 20px; height: 20px; background: white; border: 3px solid #3b82f6; border-radius: 50%; margin: 0 auto 5px;"></div>' +
                '<small>Input Port</small>' +
                '</div>' +
                '<div style="color: #60a5fa; font-size: 24px;">→</div>' +
                '<div style="text-align: center;">' +
                '<div style="width: 20px; height: 20px; background: white; border: 3px solid #3b82f6; border-radius: 50%; margin: 0 auto 5px;"></div>' +
                '<small>Output Port</small>' +
                '</div>' +
                '</div>' +
                '<button onclick="jQuery(this).parent().fadeOut();" style="' +
                'margin-top: 20px; ' +
                'background: #3b82f6; ' +
                'color: white; ' +
                'border: none; ' +
                'padding: 8px 20px; ' +
                'border-radius: 6px; ' +
                'cursor: pointer; ' +
                'font-size: 14px;' +
                '">Got it!</button>' +
                '</div>');
            
            $('body').append(guide);
            
            // Auto-hide after 10 seconds
            setTimeout(function() {
                guide.fadeOut();
            }, 10000);
        }
    }
    
    // Show guide when canvas is ready
    setTimeout(showConnectionGuide, 1000);
    
    // Add visual feedback when hovering over ports
    $(document).on('mouseenter', '.wa-node-port', function() {
        var $port = $(this);
        var isOutput = $port.hasClass('wa-port-out');
        
        // Add hover class
        $port.addClass('port-hover');
        
        // Show tooltip
        var tooltip = $('<div class="port-tooltip" style="' +
            'position: absolute; ' +
            (isOutput ? 'right: 30px;' : 'left: 30px;') +
            'top: 50%; ' +
            'transform: translateY(-50%); ' +
            'background: rgba(0, 0, 0, 0.8); ' +
            'color: white; ' +
            'padding: 5px 10px; ' +
            'border-radius: 4px; ' +
            'font-size: 12px; ' +
            'white-space: nowrap; ' +
            'z-index: 1000;' +
            '">' +
            (isOutput ? 'Drag to connect →' : '← Drop here to connect') +
            '</div>');
        
        $port.append(tooltip);
    });
    
    $(document).on('mouseleave', '.wa-node-port', function() {
        $(this).removeClass('port-hover').find('.port-tooltip').remove();
    });
    
    // Highlight compatible ports when dragging
    var originalMakePortsConnectable = window.WorkflowBuilder ? window.WorkflowBuilder.makePortsConnectable : null;
    if (originalMakePortsConnectable) {
        window.WorkflowBuilder.makePortsConnectable = function(node) {
            originalMakePortsConnectable.call(this, node);
            
            // Add enhanced drag feedback
            node.find('.wa-port-out').on('mousedown', function() {
                // Highlight all input ports
                $('.wa-port-in').addClass('potential-target');
            });
            
            $(document).on('mouseup', function() {
                $('.wa-port-in').removeClass('potential-target');
            });
        };
    }
});