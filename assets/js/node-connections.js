/**
 * Node Connections - Enhanced connection system
 */

jQuery(document).ready(function($) {
    
    // Enhanced connection guide
    function showConnectionInstructions() {
        var instructionsHtml = `
            <div id="connection-instructions" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 20px;
                border-radius: 10px;
                max-width: 350px;
                z-index: 10000;
                font-size: 14px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            ">
                <h3 style="margin: 0 0 15px 0; color: #60a5fa;">🔗 Cara Menghubungkan Nodes</h3>
                
                <div style="margin-bottom: 15px;">
                    <strong>1. Persiapan:</strong><br>
                    • Drag 2+ nodes ke canvas<br>
                    • Pastikan ada trigger node (Manual Trigger)
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>2. Menghubungkan:</strong><br>
                    • Hover pada port OUTPUT (kanan) ⭕<br>
                    • Click dan drag ke port INPUT (kiri) ⭕<br>
                    • Lepas mouse untuk membuat koneksi
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>3. Urutan Eksekusi:</strong><br>
                    • Mulai dari trigger node<br>
                    • Flow mengikuti panah koneksi<br>
                    • Data pass dari node ke node
                </div>
                
                <div style="margin-bottom: 15px;">
                    <strong>4. Tips:</strong><br>
                    • Port hijau = bisa connect ✅<br>
                    • Port merah = tidak bisa ❌<br>
                    • Click connection untuk delete
                </div>
                
                <button onclick="$(this).parent().fadeOut()" style="
                    background: #3b82f6;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    width: 100%;
                    margin-top: 10px;
                ">Mengerti!</button>
            </div>
        `;
        
        if (!$('#connection-instructions').length) {
            $('body').append(instructionsHtml);
            
            // Auto hide after 30 seconds
            setTimeout(function() {
                $('#connection-instructions').fadeOut();
            }, 30000);
        }
    }
    
    // Show instructions when canvas loads
    setTimeout(showConnectionInstructions, 2000);
    
    // Enhanced port hover effects
    $(document).on('mouseenter', '.wa-node-port', function() {
        var $port = $(this);
        var isOutput = $port.hasClass('wa-port-out');
        var $node = $port.closest('.wa-workflow-node');
        var nodeType = $node.attr('data-node-type');
        
        // Add glowing effect
        $port.css({
            'box-shadow': '0 0 0 4px rgba(59, 130, 246, 0.3), 0 0 20px rgba(59, 130, 246, 0.2)',
            'transform': 'translateY(-50%) scale(1.2)',
            'z-index': '100'
        });
        
        // Show connection hint
        var hintText = isOutput ? 
            'Drag dari sini ke input port node lain →' : 
            '← Drop koneksi dari output port ke sini';
            
        var hintHtml = `
            <div class="port-hint" style="
                position: absolute;
                ${isOutput ? 'left: 35px;' : 'right: 35px;'}
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
            ">${hintText}</div>
        `;
        
        $port.append(hintHtml);
        
        // Highlight compatible ports
        if (isOutput) {
            $('.wa-port-in').not($port).each(function() {
                var $targetPort = $(this);
                var $targetNode = $targetPort.closest('.wa-workflow-node');
                var targetNodeType = $targetNode.attr('data-node-type');
                
                // Check if connection is valid
                if (canConnect(nodeType, targetNodeType)) {
                    $targetPort.addClass('valid-target').css({
                        'background': '#10b981',
                        'border-color': '#10b981',
                        'transform': 'translateY(-50%) scale(1.1)'
                    });
                } else {
                    $targetPort.addClass('invalid-target').css({
                        'background': '#ef4444',
                        'border-color': '#ef4444',
                        'transform': 'translateY(-50%) scale(0.9)'
                    });
                }
            });
        }
    });
    
    $(document).on('mouseleave', '.wa-node-port', function() {
        var $port = $(this);
        
        // Remove effects
        $port.css({
            'box-shadow': '',
            'transform': $port.hasClass('wa-port-in') ? 'translateY(-50%)' : 'translateY(-50%)',
            'z-index': ''
        });
        
        // Remove hint
        $port.find('.port-hint').remove();
        
        // Reset all port states
        $('.wa-node-port').removeClass('valid-target invalid-target').css({
            'background': '',
            'border-color': '',
            'transform': function() {
                return $(this).hasClass('wa-port-in') ? 'translateY(-50%)' : 'translateY(-50%)';
            }
        });
    });
    
    // Function to check if two node types can be connected
    function canConnect(sourceType, targetType) {
        // Define connection rules
        var rules = {
            'manual_start': ['email', 'slack', 'openai', 'line'], // Triggers can connect to actions
            'email': ['slack', 'openai'], // Email can trigger notifications or AI
            'slack': ['email', 'openai'], // Slack can trigger email or AI
            'openai': ['email', 'slack'], // AI results can trigger notifications
            'line': ['email', 'slack', 'openai'] // LINE can trigger other actions
        };
        
        return rules[sourceType] && rules[sourceType].includes(targetType);
    }
    
    // Add workflow execution preview
    function showWorkflowPreview() {
        var previewHtml = `
            <div id="workflow-preview" style="
                position: fixed;
                bottom: 20px;
                left: 20px;
                background: white;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                padding: 20px;
                max-width: 300px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            ">
                <h4 style="margin: 0 0 10px 0; color: #374151;">📋 Workflow Preview</h4>
                <div id="execution-order" style="font-size: 13px; color: #6b7280;">
                    <p>Tambahkan dan hubungkan nodes untuk melihat urutan eksekusi...</p>
                </div>
                <button onclick="$(this).parent().fadeOut()" style="
                    background: #f3f4f6;
                    border: 1px solid #d1d5db;
                    padding: 4px 8px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    margin-top: 10px;
                ">Hide</button>
            </div>
        `;
        
        if (!$('#workflow-preview').length) {
            $('body').append(previewHtml);
            updateWorkflowPreview();
        }
    }
    
    // Update workflow preview based on current nodes and connections
    function updateWorkflowPreview() {
        var nodes = $('.wa-workflow-node');
        var connections = $('.wa-connection-path');
        
        var previewText = '';
        
        if (nodes.length === 0) {
            previewText = 'Tidak ada nodes. Drag nodes dari sidebar ke canvas.';
        } else if (connections.length === 0) {
            previewText = `${nodes.length} nodes tersedia. Hubungkan nodes untuk membuat workflow.`;
        } else {
            previewText = `
                <strong>Urutan Eksekusi:</strong><br>
                1. Manual Trigger memulai workflow<br>
                2. Data mengalir mengikuti koneksi<br>
                3. Setiap node memproses dan meneruskan data<br>
                <br>
                <strong>Status:</strong> ${nodes.length} nodes, ${connections.length} connections
            `;
        }
        
        $('#execution-order').html(previewText);
    }
    
    // Show workflow preview
    setTimeout(showWorkflowPreview, 3000);
    
    // Update preview when DOM changes
    var observer = new MutationObserver(function(mutations) {
        updateWorkflowPreview();
    });
    
    var canvas = document.getElementById('wa-workflow-canvas');
    if (canvas) {
        observer.observe(canvas, {
            childList: true,
            subtree: true
        });
    }
    
    // Add connection success feedback
    $(document).on('connectionCreated', function(event, connection) {
        // Show success message
        var successHtml = `
            <div class="connection-success" style="
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #10b981;
                color: white;
                padding: 15px 25px;
                border-radius: 8px;
                font-weight: 600;
                z-index: 10000;
                animation: fadeInOut 2s ease-in-out forwards;
            ">
                ✅ Koneksi berhasil dibuat!
            </div>
            <style>
                @keyframes fadeInOut {
                    0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                    20% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                }
            </style>
        `;
        
        $('body').append(successHtml);
        
        setTimeout(function() {
            $('.connection-success').remove();
        }, 2000);
    });
});