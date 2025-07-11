<?php
/**
 * Integration API
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 */

/**
 * Integration API class
 *
 * Handles REST API endpoints for integrations
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Integration_API {

    /**
     * The namespace for the REST API
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $namespace    The API namespace
     */
    private $namespace = 'wa/v1';

    /**
     * Register REST API routes
     *
     * @since    1.0.0
     */
    public function register_routes() {
        // GET /integrations
        register_rest_route($this->namespace, '/integrations', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_integrations'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'type' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'active_only' => array(
                        'default' => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                ),
            ),
        ));

        // GET /integrations/{id}
        register_rest_route($this->namespace, '/integrations/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_integration'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));
        
        // POST /integrations
        register_rest_route($this->namespace, '/integrations', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_integration'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'integration_type' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'name' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'settings' => array(
                        'required' => true,
                        'validate_callback' => array($this, 'validate_settings'),
                    ),
                    'is_active' => array(
                        'default' => true,
                        'sanitize_callback' => 'rest_sanitize_boolean',
                    ),
                ),
            ),
        ));

        // POST /integrations/test
        register_rest_route($this->namespace, '/integrations/test', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'test_integration'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'integration_id' => array(
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ));

        // POST /integrations/oauth/callback
        register_rest_route($this->namespace, '/integrations/oauth/callback', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'oauth_callback'),
                'permission_callback' => '__return_true', // Public endpoint for OAuth callbacks
            ),
        ));
    }

    /**
     * Get integrations
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_integrations($request) {
        $type = $request->get_param('type');
        $active_only = $request->get_param('active_only');
        
        $integration_model = new Integration_Settings_Model();
        
        if ($type) {
            $integrations = $integration_model->get_by_type($type);
        } else {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wa_integration_settings';
            
            $where = array('1=1');
            if ($active_only) {
                $where[] = 'is_active = 1';
            }
            
            $where_clause = implode(' AND ', $where);
            $sql = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY integration_type, name";
            
            $integrations = $wpdb->get_results($sql);
            
            foreach ($integrations as $integration) {
                if ($integration->settings) {
                    $integration->settings = json_decode($integration->settings, true);
                    // Don't expose sensitive data
                    $integration->settings = $this->sanitize_settings_for_output($integration->settings);
                }
            }
        }
        
        return new WP_REST_Response($integrations);
    }

    /**
     * Get single integration
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function get_integration($request) {
        $id = $request->get_param('id');
        
        $integration_model = new Integration_Settings_Model();
        $integration = $integration_model->get($id);
        
        if (!$integration) {
            return new WP_Error('not_found', __('Integration not found', 'workflow-automation'), array('status' => 404));
        }
        
        // Don't expose sensitive data
        $integration->settings = $this->sanitize_settings_for_output($integration->settings);
        
        return new WP_REST_Response($integration);
    }
    
    /**
     * Create integration
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function create_integration($request) {
        $integration_model = new Integration_Settings_Model();
        
        $data = array(
            'integration_type' => $request->get_param('integration_type'),
            'name' => $request->get_param('name'),
            'settings' => $request->get_param('settings'),
            'is_active' => $request->get_param('is_active') ? 1 : 0,
            'created_by' => get_current_user_id()
        );
        
        // Validate required fields
        if (empty($data['integration_type'])) {
            return new WP_Error('missing_integration_type', __('Integration type is required', 'workflow-automation'), array('status' => 400));
        }
        
        if (empty($data['name'])) {
            return new WP_Error('missing_name', __('Integration name is required', 'workflow-automation'), array('status' => 400));
        }
        
        if (empty($data['settings']) || !is_array($data['settings'])) {
            return new WP_Error('missing_settings', __('Integration settings are required', 'workflow-automation'), array('status' => 400));
        }
        
        $result = $integration_model->create($data);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (!$result) {
            return new WP_Error('create_failed', __('Failed to create integration', 'workflow-automation'), array('status' => 500));
        }
        
        $integration = $integration_model->get($result);
        if (!$integration) {
            return new WP_Error('get_failed', __('Integration created but could not be retrieved', 'workflow-automation'), array('status' => 500));
        }
        
        $integration->settings = $this->sanitize_settings_for_output($integration->settings);
        
        return new WP_REST_Response($integration, 201);
    }
    
    /**
     * Validate settings
     *
     * @since    1.0.0
     * @param    mixed             $value     The value to validate
     * @param    WP_REST_Request   $request   The REST request
     * @param    string            $param     The parameter name
     * @return   bool
     */
    public function validate_settings($value, $request, $param) {
        if (!is_array($value)) {
            return false;
        }
        
        // Additional validation can be added here based on integration type
        return true;
    }

    /**
     * Test integration connection
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function test_integration($request) {
        $integration_id = $request->get_param('integration_id');
        
        $integration_model = new Integration_Settings_Model();
        $integration = $integration_model->get($integration_id);
        
        if (!$integration) {
            return new WP_Error('not_found', __('Integration not found', 'workflow-automation'), array('status' => 404));
        }
        
        // Decrypt settings for testing
        $settings = $integration_model->decrypt_settings($integration->settings);
        
        $result = array(
            'success' => false,
            'message' => ''
        );
        
        switch ($integration->integration_type) {
            case 'slack':
                $result = $this->test_slack_integration($settings);
                break;
                
            case 'email':
                $result = $this->test_email_integration($settings);
                break;
                
            case 'openai':
                $result = $this->test_openai_integration($settings);
                break;
                
            case 'google':
                $result = $this->test_google_integration($settings);
                break;
                
            default:
                $result['message'] = __('Test not implemented for this integration type', 'workflow-automation');
        }
        
        return new WP_REST_Response($result);
    }

    /**
     * Handle OAuth callback
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function oauth_callback($request) {
        $provider = $request->get_param('provider');
        $code = $request->get_param('code');
        $state = $request->get_param('state');
        $error = $request->get_param('error');
        
        if ($error) {
            return new WP_Error('oauth_error', $error, array('status' => 400));
        }
        
        if (!$code || !$state) {
            return new WP_Error('missing_params', __('Missing required OAuth parameters', 'workflow-automation'), array('status' => 400));
        }
        
        // Verify state to prevent CSRF
        $saved_state = get_transient('wa_oauth_state_' . $state);
        if (!$saved_state) {
            return new WP_Error('invalid_state', __('Invalid OAuth state', 'workflow-automation'), array('status' => 400));
        }
        
        delete_transient('wa_oauth_state_' . $state);
        
        // Handle based on provider
        switch ($provider) {
            case 'google':
                return $this->handle_google_oauth($code, $saved_state);
                
            case 'microsoft':
                return $this->handle_microsoft_oauth($code, $saved_state);
                
            default:
                return new WP_Error('unknown_provider', __('Unknown OAuth provider', 'workflow-automation'), array('status' => 400));
        }
    }

    /**
     * Test Slack integration
     *
     * @since    1.0.0
     * @param    array    $settings    The integration settings
     * @return   array
     */
    private function test_slack_integration($settings) {
        if (empty($settings['webhook_url'])) {
            return array(
                'success' => false,
                'message' => __('Webhook URL is missing', 'workflow-automation')
            );
        }
        
        // Send test message
        $response = wp_remote_post($settings['webhook_url'], array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode(array(
                'text' => __('Test message from Workflow Automation plugin', 'workflow-automation')
            ))
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($code === 200 && $body === 'ok') {
            return array(
                'success' => true,
                'message' => __('Connection successful!', 'workflow-automation')
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf(__('Slack API error: %s', 'workflow-automation'), $body)
            );
        }
    }

    /**
     * Test email integration
     *
     * @since    1.0.0
     * @param    array    $settings    The integration settings
     * @return   array
     */
    private function test_email_integration($settings) {
        // For SMTP, we can't really test without sending an email
        // Just verify required fields are present
        $required = array('smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email');
        
        foreach ($required as $field) {
            if (empty($settings[$field])) {
                return array(
                    'success' => false,
                    'message' => sprintf(__('Missing required field: %s', 'workflow-automation'), $field)
                );
            }
        }
        
        // Validate email
        if (!is_email($settings['from_email'])) {
            return array(
                'success' => false,
                'message' => __('Invalid from email address', 'workflow-automation')
            );
        }
        
        return array(
            'success' => true,
            'message' => __('Configuration appears valid. A test email will be sent when used in a workflow.', 'workflow-automation')
        );
    }

    /**
     * Test OpenAI integration
     *
     * @since    1.0.0
     * @param    array    $settings    The integration settings
     * @return   array
     */
    private function test_openai_integration($settings) {
        if (empty($settings['api_key'])) {
            return array(
                'success' => false,
                'message' => __('API key is missing', 'workflow-automation')
            );
        }
        
        // Test with a simple models endpoint
        $response = wp_remote_get('https://api.openai.com/v1/models', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $settings['api_key'],
                'OpenAI-Organization' => isset($settings['organization_id']) ? $settings['organization_id'] : ''
            )
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            return array(
                'success' => true,
                'message' => __('OpenAI API connection successful!', 'workflow-automation')
            );
        } elseif ($code === 401) {
            return array(
                'success' => false,
                'message' => __('Invalid API key', 'workflow-automation')
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf(__('OpenAI API error (HTTP %d)', 'workflow-automation'), $code)
            );
        }
    }

    /**
     * Test Google integration
     *
     * @since    1.0.0
     * @param    array    $settings    The integration settings
     * @return   array
     */
    private function test_google_integration($settings) {
        if (empty($settings['access_token'])) {
            return array(
                'success' => false,
                'message' => __('Not authenticated. Please complete OAuth setup.', 'workflow-automation')
            );
        }
        
        // Test with user info endpoint
        $response = wp_remote_get('https://www.googleapis.com/oauth2/v1/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $settings['access_token']
            )
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return array(
                'success' => true,
                'message' => sprintf(__('Connected as: %s', 'workflow-automation'), $body['email'])
            );
        } elseif ($code === 401) {
            // Token might be expired, try to refresh
            if (!empty($settings['refresh_token'])) {
                return array(
                    'success' => false,
                    'message' => __('Access token expired. Please re-authenticate.', 'workflow-automation')
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Not authenticated. Please complete OAuth setup.', 'workflow-automation')
                );
            }
        } else {
            return array(
                'success' => false,
                'message' => sprintf(__('Google API error (HTTP %d)', 'workflow-automation'), $code)
            );
        }
    }

    /**
     * Sanitize settings for output
     *
     * @since    1.0.0
     * @param    array    $settings    The settings
     * @return   array
     */
    private function sanitize_settings_for_output($settings) {
        $sensitive_fields = array('password', 'api_key', 'secret', 'token', 'webhook_url', 'smtp_password', 'client_secret', 'refresh_token', 'access_token');
        
        foreach ($settings as $key => $value) {
            foreach ($sensitive_fields as $field) {
                if (stripos($key, $field) !== false) {
                    // Show only last 4 characters
                    if (strlen($value) > 4) {
                        $settings[$key] = str_repeat('*', strlen($value) - 4) . substr($value, -4);
                    } else {
                        $settings[$key] = str_repeat('*', strlen($value));
                    }
                    break;
                }
            }
        }
        
        return $settings;
    }

    /**
     * Check permission
     *
     * @since    1.0.0
     * @return   bool
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }
}