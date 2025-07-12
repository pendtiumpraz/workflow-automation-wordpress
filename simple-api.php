<?php
/**
 * Simple API Endpoint
 * Add to functions.php or create as mu-plugin
 */

add_action('rest_api_init', function() {
    // Simple working endpoint for node schemas
    register_rest_route('wa/v1', '/nodes/types/(?P<type>[a-zA-Z0-9_-]+)/schema', array(
        'methods' => 'GET',
        'callback' => 'wa_get_node_schema_simple',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'args' => array(
            'type' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
});

function wa_get_node_schema_simple($request) {
    $type = $request->get_param('type');
    
    error_log('Simple API: Getting schema for type: ' . $type);
    
    // Load integration manager - use plugin_dir_path instead of WA_PLUGIN_DIR
    $plugin_dir = plugin_dir_path(dirname(__FILE__));
    $integration_file = $plugin_dir . 'includes/integrations/class-integration-manager.php';
    
    $defaults = array();
    
    if (file_exists($integration_file)) {
        try {
            require_once $integration_file;
            $integration_manager = WA_Integration_Manager::getInstance();
            $defaults = $integration_manager->get_node_defaults($type);
        } catch (Exception $e) {
            error_log('Simple API: Error loading integration manager: ' . $e->getMessage());
        }
    }
    
    // Define schemas for different node types
    $schemas = array(
        'email' => array(
            'type' => 'email',
            'label' => 'Send Email',
            'description' => 'Send an email message to recipients',
            'category' => 'integrations',
            'integration_status' => isset($integration_manager) && $integration_manager->is_integration_active('email') ? 'active' : 'inactive',
            'settings_fields' => array(
                array(
                    'key' => 'to',
                    'label' => 'To',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'recipient@example.com',
                    'description' => 'Email recipient address'
                ),
                array(
                    'key' => 'from_name',
                    'label' => 'From Name',
                    'type' => 'text',
                    'required' => false,
                    'default' => $defaults['from_name'] ?? get_bloginfo('name'),
                    'placeholder' => 'Your Name',
                    'description' => 'Sender name (auto-filled from WordPress settings)'
                ),
                array(
                    'key' => 'from_email',
                    'label' => 'From Email',
                    'type' => 'email',
                    'required' => false,
                    'default' => $defaults['from_email'] ?? get_option('admin_email'),
                    'placeholder' => 'sender@example.com',
                    'description' => 'Sender email address (auto-filled from WordPress settings)'
                ),
                array(
                    'key' => 'subject',
                    'label' => 'Subject',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Email subject',
                    'description' => 'Email subject line'
                ),
                array(
                    'key' => 'body',
                    'label' => 'Body',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 8,
                    'placeholder' => 'Email body content...',
                    'description' => 'Email message body. Use {{variables}} for dynamic content.'
                ),
                array(
                    'key' => 'content_type',
                    'label' => 'Content Type',
                    'type' => 'select',
                    'default' => 'text/plain',
                    'options' => array(
                        'text/plain' => 'Plain Text',
                        'text/html' => 'HTML'
                    ),
                    'description' => 'Email content format'
                )
            )
        ),
        
        'slack' => array(
            'type' => 'slack',
            'label' => 'Slack Message',
            'description' => 'Send a message to Slack channel',
            'category' => 'integrations',
            'settings_fields' => array(
                array(
                    'key' => 'webhook_url',
                    'label' => 'Webhook URL',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://hooks.slack.com/services/...',
                    'description' => 'Slack webhook URL from your Slack app'
                ),
                array(
                    'key' => 'channel',
                    'label' => 'Channel',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => '#general',
                    'description' => 'Channel to send message to (optional)'
                ),
                array(
                    'key' => 'username',
                    'label' => 'Username',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Bot Name',
                    'description' => 'Display name for the bot (optional)'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 5,
                    'placeholder' => 'Your message here...',
                    'description' => 'Message to send. Use {{variables}} for dynamic content.'
                ),
                array(
                    'key' => 'emoji',
                    'label' => 'Emoji',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => ':robot_face:',
                    'description' => 'Emoji icon for the bot (optional)'
                )
            )
        ),
        
        'openai' => array(
            'type' => 'openai',
            'label' => 'OpenAI',
            'description' => 'Generate content using OpenAI GPT models',
            'category' => 'ai',
            'settings_fields' => array(
                array(
                    'key' => 'api_key',
                    'label' => 'API Key',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'sk-...',
                    'description' => 'Your OpenAI API key'
                ),
                array(
                    'key' => 'model',
                    'label' => 'Model',
                    'type' => 'select',
                    'default' => 'gpt-3.5-turbo',
                    'options' => array(
                        'gpt-4' => 'GPT-4',
                        'gpt-4-turbo' => 'GPT-4 Turbo',
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
                    ),
                    'description' => 'OpenAI model to use'
                ),
                array(
                    'key' => 'prompt',
                    'label' => 'Prompt',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 6,
                    'placeholder' => 'Enter your prompt here...',
                    'description' => 'The prompt to send to OpenAI. Use {{variables}} for dynamic content.'
                ),
                array(
                    'key' => 'max_tokens',
                    'label' => 'Max Tokens',
                    'type' => 'number',
                    'default' => 500,
                    'placeholder' => '500',
                    'description' => 'Maximum number of tokens to generate'
                ),
                array(
                    'key' => 'temperature',
                    'label' => 'Temperature',
                    'type' => 'number',
                    'default' => 0.7,
                    'placeholder' => '0.7',
                    'description' => 'Creativity level (0.0 - 2.0)'
                )
            )
        ),
        
        'line' => array(
            'type' => 'line',
            'label' => 'LINE Message',
            'description' => 'Send a message via LINE',
            'category' => 'integrations',
            'settings_fields' => array(
                array(
                    'key' => 'channel_access_token',
                    'label' => 'Channel Access Token',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Your LINE channel access token',
                    'description' => 'LINE channel access token from LINE Developers'
                ),
                array(
                    'key' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'U1234567890abcdef...',
                    'description' => 'LINE user ID to send message to'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                    'placeholder' => 'Your message here...',
                    'description' => 'Message to send. Use {{variables}} for dynamic content.'
                )
            )
        ),
        
        'manual_start' => array(
            'type' => 'manual_start',
            'label' => 'Manual Trigger',
            'description' => 'Manually start workflow execution',
            'category' => 'triggers',
            'settings_fields' => array(
                array(
                    'key' => 'title',
                    'label' => 'Trigger Title',
                    'type' => 'text',
                    'required' => false,
                    'default' => 'Manual Start',
                    'placeholder' => 'Enter trigger title...',
                    'description' => 'Optional title for this trigger'
                ),
                array(
                    'key' => 'description',
                    'label' => 'Description',
                    'type' => 'textarea',
                    'required' => false,
                    'rows' => 3,
                    'placeholder' => 'Describe when this should be triggered...',
                    'description' => 'Optional description of trigger conditions'
                ),
                array(
                    'key' => 'require_confirmation',
                    'label' => 'Require Confirmation',
                    'type' => 'checkbox',
                    'default' => false,
                    'description' => 'Require user confirmation before executing'
                )
            )
        )
    );
    
    if (isset($schemas[$type])) {
        error_log('Simple API: Returning schema for: ' . $type);
        return $schemas[$type];
    }
    
    error_log('Simple API: No schema found for type: ' . $type);
    return new WP_Error('invalid_node_type', 'Invalid node type: ' . $type, array('status' => 404));
}

// Add debug endpoint
add_action('rest_api_init', function() {
    register_rest_route('wa/v1', '/debug', array(
        'methods' => 'GET',
        'callback' => function() {
            return array(
                'message' => 'Simple API is working!',
                'timestamp' => current_time('mysql'),
                'available_schemas' => array('email', 'slack', 'openai', 'line', 'manual_start')
            );
        },
        'permission_callback' => '__return_true'
    ));
});
?>