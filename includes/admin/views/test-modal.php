<?php
/**
 * Test Modal with Static Content
 * This will help us see if the modal itself works
 */
?>

<script>
jQuery(document).ready(function($) {
    // Override the loadNodeConfigFields function with actual API call + fallback
    if (window.WorkflowBuilder) {
        window.WorkflowBuilder.loadNodeConfigFields = function(node) {
            console.log('Loading actual config for node type:', node.type);
            
            var self = this;
            
            // Show loading
            $('#wa-node-config-fields').html('<p>Loading configuration...</p>');
            
            // Try the actual API first
            $.ajax({
                url: wa_builder.api_url + '/nodes/types/' + node.type + '/schema',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wa_builder.nonce);
                },
                success: function(schema) {
                    console.log('API Success! Schema loaded:', schema);
                    self.renderActualConfigFields(node, schema);
                },
                error: function(xhr, status, error) {
                    console.warn('API failed, using fallback. Error:', error);
                    self.renderFallbackConfigFields(node);
                }
            });
        };
        
        // Add method to render actual config fields from API
        window.WorkflowBuilder.renderActualConfigFields = function(node, schema) {
            console.log('Rendering actual fields from schema:', schema);
            
            var html = '<h3>' + (schema.label || node.label) + ' Configuration</h3>';
            
            // Show integration status
            if (schema.integration_status) {
                var statusColor = schema.integration_status === 'active' ? '#10b981' : '#f59e0b';
                var statusText = schema.integration_status === 'active' ? 'Integration Active' : 'Integration Inactive';
                var statusIcon = schema.integration_status === 'active' ? '✅' : '⚠️';
                
                html += '<div style="background: ' + statusColor + '; color: white; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">';
                html += statusIcon + ' ' + statusText;
                if (schema.integration_status === 'inactive') {
                    html += ' - Configure this integration in settings';
                }
                html += '</div>';
            }
            
            if (schema.description) {
                html += '<p class="description">' + schema.description + '</p>';
            }
            
            // Render actual settings fields from schema
            if (schema.settings_fields && schema.settings_fields.length > 0) {
                schema.settings_fields.forEach(function(field) {
                    html += '<div class="wa-form-group">';
                    html += '<label for="field_' + field.key + '">' + field.label;
                    if (field.required) {
                        html += ' <span style="color: red;">*</span>';
                    }
                    html += '</label>';
                    
                    var value = node.data && node.data[field.key] ? node.data[field.key] : (field.default || '');
                    
                    if (field.type === 'select') {
                        html += '<select name="' + field.key + '" id="field_' + field.key + '" class="regular-text"';
                        if (field.required) html += ' required';
                        html += '>';
                        
                        if (field.options) {
                            for (var optKey in field.options) {
                                var selected = value === optKey ? ' selected' : '';
                                html += '<option value="' + optKey + '"' + selected + '>' + field.options[optKey] + '</option>';
                            }
                        }
                        html += '</select>';
                        
                    } else if (field.type === 'textarea') {
                        html += '<textarea name="' + field.key + '" id="field_' + field.key + '" class="large-text"';
                        if (field.rows) html += ' rows="' + field.rows + '"';
                        if (field.required) html += ' required';
                        if (field.placeholder) html += ' placeholder="' + field.placeholder + '"';
                        html += '>' + value + '</textarea>';
                        
                    } else if (field.type === 'checkbox') {
                        var checked = value ? ' checked' : '';
                        html += '<label><input type="checkbox" name="' + field.key + '" id="field_' + field.key + '" value="1"' + checked + ' /> ' + (field.label || 'Enable') + '</label>';
                        
                    } else {
                        // Default to text input
                        html += '<input type="' + (field.type || 'text') + '" name="' + field.key + '" id="field_' + field.key + '" class="regular-text"';
                        if (field.required) html += ' required';
                        if (field.placeholder) html += ' placeholder="' + field.placeholder + '"';
                        html += ' value="' + value + '" />';
                    }
                    
                    if (field.description) {
                        html += '<p class="description">' + field.description + '</p>';
                    }
                    
                    html += '</div>';
                });
            } else {
                html += '<p>No configuration fields available for this node type.</p>';
            }
            
            $('#wa-node-config-fields').html(html);
        };
        
        // Add fallback method for when API fails
        window.WorkflowBuilder.renderFallbackConfigFields = function(node) {
            console.log('Rendering fallback fields for node type:', node.type);
            
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