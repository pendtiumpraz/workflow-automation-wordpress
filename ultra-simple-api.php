<?php
/**
 * Ultra Simple API - No dependencies
 * 
 * Add this to functions.php:
 * include_once(plugin_dir_path(__FILE__) . 'workflow-automation/ultra-simple-api.php');
 */

add_action('rest_api_init', function() {
    register_rest_route('wa/v1', '/nodes/types/(?P<type>[a-zA-Z0-9_-]+)/schema', array(
        'methods' => 'GET',
        'callback' => 'wa_ultra_simple_get_schema',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
});

function wa_ultra_simple_get_schema($request) {
    $type = $request->get_param('type');
    
    // Basic schemas - no external dependencies
    $schemas = array(
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
                )
            )
        ),
        
        'email' => array(
            'type' => 'email',
            'label' => 'Send Email',
            'description' => 'Send an email message',
            'category' => 'integrations',
            'settings_fields' => array(
                array(
                    'key' => 'to',
                    'label' => 'To',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'recipient@example.com',
                    'description' => 'Email recipient'
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
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 5,
                    'placeholder' => 'Your message here...',
                    'description' => 'Email body content'
                )
            )
        ),
        
        'slack' => array(
            'type' => 'slack',
            'label' => 'Slack Message',
            'description' => 'Send message to Slack',
            'category' => 'integrations',
            'settings_fields' => array(
                array(
                    'key' => 'webhook_url',
                    'label' => 'Webhook URL',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://hooks.slack.com/services/...',
                    'description' => 'Slack webhook URL'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                    'placeholder' => 'Your message...',
                    'description' => 'Message to send'
                )
            )
        ),
        
        'openai' => array(
            'type' => 'openai',
            'label' => 'OpenAI',
            'description' => 'Generate content with AI',
            'category' => 'ai',
            'settings_fields' => array(
                array(
                    'key' => 'api_key',
                    'label' => 'API Key',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'sk-...',
                    'description' => 'OpenAI API key'
                ),
                array(
                    'key' => 'prompt',
                    'label' => 'Prompt',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                    'placeholder' => 'Enter your prompt...',
                    'description' => 'AI prompt'
                )
            )
        ),
        
        'line' => array(
            'type' => 'line',
            'label' => 'LINE Message',
            'description' => 'Send LINE message',
            'category' => 'integrations',
            'settings_fields' => array(
                array(
                    'key' => 'access_token',
                    'label' => 'Access Token',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Your LINE access token',
                    'description' => 'LINE channel access token'
                ),
                array(
                    'key' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'U1234567890...',
                    'description' => 'LINE user ID'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 3,
                    'placeholder' => 'Your message...',
                    'description' => 'Message to send'
                )
            )
        )
    );
    
    if (isset($schemas[$type])) {
        return $schemas[$type];
    }
    
    // Return generic schema for unknown types
    return array(
        'type' => $type,
        'label' => ucwords(str_replace('_', ' ', $type)),
        'description' => 'Configure ' . $type . ' node',
        'category' => 'other',
        'settings_fields' => array(
            array(
                'key' => 'setting1',
                'label' => 'Setting 1',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'Enter value...',
                'description' => 'Basic setting'
            )
        )
    );
}