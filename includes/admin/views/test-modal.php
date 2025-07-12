<?php
/**
 * Test Modal with Static Content
 * This will help us see if the modal itself works
 */
?>

<script>
jQuery(document).ready(function($) {
    // Override the loadNodeConfigFields function with static content
    if (window.WorkflowBuilder) {
        window.WorkflowBuilder.loadNodeConfigFields = function(node) {
            console.log('Static test: Loading config for node type:', node.type);
            
            var html = '<h3>Test Configuration Fields</h3>';
            html += '<p>Node Type: <strong>' + node.type + '</strong></p>';
            html += '<p>Node Label: <strong>' + node.label + '</strong></p>';
            
            // Add some test form fields
            html += '<div class="wa-form-group">';
            html += '<label for="test_field">Test Field</label>';
            html += '<input type="text" name="test_field" id="test_field" class="regular-text" placeholder="Enter some text" />';
            html += '<p class="description">This is a test field to verify the modal is working.</p>';
            html += '</div>';
            
            html += '<div class="wa-form-group">';
            html += '<label for="test_select">Test Select</label>';
            html += '<select name="test_select" id="test_select" class="regular-text">';
            html += '<option value="option1">Option 1</option>';
            html += '<option value="option2">Option 2</option>';
            html += '<option value="option3">Option 3</option>';
            html += '</select>';
            html += '<p class="description">This is a test select field.</p>';
            html += '</div>';
            
            html += '<div class="wa-form-group">';
            html += '<label for="test_textarea">Test Textarea</label>';
            html += '<textarea name="test_textarea" id="test_textarea" class="large-text" rows="4" placeholder="Enter some longer text here..."></textarea>';
            html += '<p class="description">This is a test textarea field.</p>';
            html += '</div>';
            
            // Add node-specific content
            if (node.type === 'email') {
                html += '<h4>Email Specific Fields</h4>';
                html += '<div class="wa-form-group">';
                html += '<label for="email_to">To Email</label>';
                html += '<input type="email" name="email_to" id="email_to" class="regular-text" placeholder="recipient@example.com" />';
                html += '</div>';
                
                html += '<div class="wa-form-group">';
                html += '<label for="email_subject">Subject</label>';
                html += '<input type="text" name="email_subject" id="email_subject" class="regular-text" placeholder="Email subject" />';
                html += '</div>';
                
                html += '<div class="wa-form-group">';
                html += '<label for="email_body">Body</label>';
                html += '<textarea name="email_body" id="email_body" class="large-text" rows="6" placeholder="Email body content..."></textarea>';
                html += '</div>';
            } else if (node.type === 'slack') {
                html += '<h4>Slack Specific Fields</h4>';
                html += '<div class="wa-form-group">';
                html += '<label for="slack_channel">Channel</label>';
                html += '<input type="text" name="slack_channel" id="slack_channel" class="regular-text" placeholder="#general" />';
                html += '</div>';
                
                html += '<div class="wa-form-group">';
                html += '<label for="slack_message">Message</label>';
                html += '<textarea name="slack_message" id="slack_message" class="large-text" rows="4" placeholder="Slack message..."></textarea>';
                html += '</div>';
            } else if (node.type === 'openai') {
                html += '<h4>OpenAI Specific Fields</h4>';
                html += '<div class="wa-form-group">';
                html += '<label for="openai_model">Model</label>';
                html += '<select name="openai_model" id="openai_model" class="regular-text">';
                html += '<option value="gpt-4">GPT-4</option>';
                html += '<option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>';
                html += '</select>';
                html += '</div>';
                
                html += '<div class="wa-form-group">';
                html += '<label for="openai_prompt">Prompt</label>';
                html += '<textarea name="openai_prompt" id="openai_prompt" class="large-text" rows="4" placeholder="Enter your prompt..."></textarea>';
                html += '</div>';
            }
            
            html += '<p><em>This is static test content. The real API call would load dynamic fields.</em></p>';
            
            // Find the config fields container and insert content
            var configFields = $('#wa-node-config-fields');
            if (configFields.length > 0) {
                configFields.html(html);
                console.log('Static content inserted successfully');
            } else {
                console.error('Could not find #wa-node-config-fields element');
                // Try alternative selectors
                var modalBody = $('.wa-modal-body');
                if (modalBody.length > 0) {
                    modalBody.html('<div id="wa-node-config-fields">' + html + '</div>');
                    console.log('Static content inserted into modal body');
                } else {
                    console.error('Could not find modal body either');
                }
            }
        };
        
        console.log('Static test mode enabled for node configuration');
    } else {
        console.error('WorkflowBuilder not found - cannot enable static test mode');
    }
});
</script>

<style>
/* Ensure modal content is visible */
#wa-node-config-fields {
    padding: 20px !important;
    background: white !important;
    min-height: 200px !important;
}

.wa-form-group {
    margin-bottom: 20px !important;
}

.wa-form-group label {
    display: block !important;
    margin-bottom: 5px !important;
    font-weight: 600 !important;
    color: #333 !important;
}

.wa-form-group input,
.wa-form-group select,
.wa-form-group textarea {
    width: 100% !important;
    padding: 8px 12px !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    font-size: 14px !important;
}

.wa-form-group .description {
    margin-top: 5px !important;
    color: #666 !important;
    font-style: italic !important;
    font-size: 12px !important;
}

#wa-node-config-modal {
    z-index: 999999 !important;
}

.wa-modal-content {
    background: white !important;
    border-radius: 8px !important;
    max-width: 600px !important;
}

.wa-modal-body {
    background: white !important;
    max-height: 70vh !important;
    overflow-y: auto !important;
}
</style>