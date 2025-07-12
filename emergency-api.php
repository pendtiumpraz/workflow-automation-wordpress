<?php
/**
 * Emergency API - Absolutely minimal, no errors possible
 * 
 * Add to your theme's functions.php:
 * include_once(WP_PLUGIN_DIR . '/workflow-automation/emergency-api.php');
 */

// Register endpoint directly without dependencies
add_action('init', function() {
    add_action('rest_api_init', function() {
        register_rest_route('wa/v1', '/nodes/types/(?P<type>[a-zA-Z0-9_-]+)/schema', array(
            'methods' => 'GET',
            'callback' => function($request) {
                $type = sanitize_text_field($request->get_param('type'));
                
                // Return schema for ANY node type
                return array(
                    'type' => $type,
                    'label' => ucwords(str_replace('_', ' ', $type)),
                    'description' => 'Configure ' . $type . ' node',
                    'category' => 'general',
                    'settings_fields' => wa_get_fields_for_type($type)
                );
            },
            'permission_callback' => '__return_true' // Allow all for now
        ));
    });
});

// Get fields based on node type
function wa_get_fields_for_type($type) {
    switch($type) {
        case 'email':
            return array(
                array(
                    'key' => 'to',
                    'label' => 'To Email',
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
                    'description' => 'Subject line'
                ),
                array(
                    'key' => 'body',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 5,
                    'placeholder' => 'Email message...',
                    'description' => 'Email body'
                )
            );
            
        case 'slack':
            return array(
                array(
                    'key' => 'webhook_url',
                    'label' => 'Webhook URL',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'https://hooks.slack.com/...',
                    'description' => 'Slack webhook URL'
                ),
                array(
                    'key' => 'channel',
                    'label' => 'Channel',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => '#general',
                    'description' => 'Target channel'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                    'placeholder' => 'Slack message...',
                    'description' => 'Message text'
                )
            );
            
        case 'openai':
            return array(
                array(
                    'key' => 'api_key',
                    'label' => 'API Key',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'sk-...',
                    'description' => 'OpenAI API key'
                ),
                array(
                    'key' => 'model',
                    'label' => 'Model',
                    'type' => 'select',
                    'required' => true,
                    'default' => 'gpt-3.5-turbo',
                    'options' => array(
                        'gpt-4' => 'GPT-4',
                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
                    ),
                    'description' => 'AI model'
                ),
                array(
                    'key' => 'prompt',
                    'label' => 'Prompt',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 4,
                    'placeholder' => 'Enter prompt...',
                    'description' => 'AI prompt'
                )
            );
            
        case 'line':
            return array(
                array(
                    'key' => 'token',
                    'label' => 'Access Token',
                    'type' => 'password',
                    'required' => true,
                    'placeholder' => 'Channel access token',
                    'description' => 'LINE channel token'
                ),
                array(
                    'key' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'U123...',
                    'description' => 'Target user ID'
                ),
                array(
                    'key' => 'message',
                    'label' => 'Message',
                    'type' => 'textarea',
                    'required' => true,
                    'rows' => 3,
                    'placeholder' => 'LINE message...',
                    'description' => 'Message text'
                )
            );
            
        case 'google_sheets':
            return array(
                array(
                    'key' => 'spreadsheet_id',
                    'label' => 'Spreadsheet ID',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '1234567890abcdef...',
                    'description' => 'Google Sheets ID'
                ),
                array(
                    'key' => 'range',
                    'label' => 'Range',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Sheet1!A1:B10',
                    'description' => 'Cell range'
                ),
                array(
                    'key' => 'action',
                    'label' => 'Action',
                    'type' => 'select',
                    'required' => true,
                    'default' => 'read',
                    'options' => array(
                        'read' => 'Read Data',
                        'write' => 'Write Data',
                        'append' => 'Append Data'
                    ),
                    'description' => 'Operation type'
                )
            );
            
        case 'manual_start':
        case 'manual_trigger':
            return array(
                array(
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'required' => false,
                    'default' => 'Manual Trigger',
                    'placeholder' => 'Trigger name...',
                    'description' => 'Display name'
                ),
                array(
                    'key' => 'description',
                    'label' => 'Description',
                    'type' => 'textarea',
                    'required' => false,
                    'rows' => 3,
                    'placeholder' => 'When to trigger...',
                    'description' => 'Trigger notes'
                )
            );
            
        default:
            // Generic fields for any unknown node type
            return array(
                array(
                    'key' => 'config1',
                    'label' => 'Configuration 1',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Enter value...',
                    'description' => 'Basic configuration'
                ),
                array(
                    'key' => 'config2',
                    'label' => 'Configuration 2',
                    'type' => 'textarea',
                    'required' => false,
                    'rows' => 3,
                    'placeholder' => 'Additional settings...',
                    'description' => 'Extra configuration'
                )
            );
    }
}