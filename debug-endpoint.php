<?php
/**
 * Debug endpoint for testing REST API
 * 
 * Add this to WordPress functions.php or create a simple plugin
 */

// Simple REST endpoint for testing
add_action('rest_api_init', function() {
    register_rest_route('wa/v1', '/debug', array(
        'methods' => 'GET',
        'callback' => function() {
            return array(
                'message' => 'Workflow Automation REST API is working!',
                'timestamp' => current_time('mysql'),
                'user' => wp_get_current_user()->user_login
            );
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
    // Simple schema endpoint
    register_rest_route('wa/v1', '/nodes/types/(?P<type>[a-zA-Z0-9_-]+)/schema', array(
        'methods' => 'GET',
        'callback' => function($request) {
            $type = $request->get_param('type');
            
            // Mock response for email node
            if ($type === 'email') {
                return array(
                    'type' => 'email',
                    'label' => 'Send Email',
                    'description' => 'Send an email message',
                    'category' => 'integrations',
                    'settings_fields' => array(
                        array(
                            'key' => 'to',
                            'label' => 'To',
                            'type' => 'text',
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
                            'label' => 'Body',
                            'type' => 'textarea',
                            'required' => true,
                            'placeholder' => 'Email body content',
                            'description' => 'Email message body'
                        )
                    )
                );
            }
            
            // Mock response for other nodes
            return array(
                'type' => $type,
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'description' => 'Configuration for ' . $type . ' node',
                'category' => 'actions',
                'settings_fields' => array(
                    array(
                        'key' => 'test_field',
                        'label' => 'Test Field',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Enter value',
                        'description' => 'Test configuration field'
                    )
                )
            );
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
});

// Test if the endpoint works
function test_wa_endpoint() {
    $response = wp_remote_get(get_rest_url(null, 'wa/v1/debug'), array(
        'headers' => array(
            'X-WP-Nonce' => wp_create_nonce('wp_rest')
        )
    ));
    
    if (is_wp_error($response)) {
        error_log('WA Debug: Failed to call endpoint: ' . $response->get_error_message());
    } else {
        error_log('WA Debug: Endpoint response: ' . wp_remote_retrieve_body($response));
    }
}

// Run test on admin_init
add_action('admin_init', 'test_wa_endpoint');
?>