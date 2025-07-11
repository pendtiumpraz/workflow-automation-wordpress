<?php
/**
 * Webhook API
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 */

/**
 * Webhook API class
 *
 * Handles webhook endpoints and webhook reception
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Webhook_API {

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
        // POST /webhooks/create
        register_rest_route($this->namespace, '/webhooks/create', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_webhook'),
                'permission_callback' => array($this, 'check_admin_permission'),
                'args' => array(
                    'workflow_id' => array(
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ),
                    'node_id' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'settings' => array(
                        'default' => array(),
                        'validate_callback' => function($param) {
                            return is_array($param);
                        }
                    ),
                ),
            ),
        ));

        // GET /webhooks/{workflow_id}
        register_rest_route($this->namespace, '/webhooks/(?P<workflow_id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_webhooks'),
                'permission_callback' => array($this, 'check_admin_permission'),
                'args' => array(
                    'workflow_id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));
    }

    /**
     * Handle webhook request (called from template_redirect)
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     */
    public function handle_webhook_request($webhook_key) {
        // Set JSON header
        header('Content-Type: application/json; charset=utf-8');
        
        // Get webhook by key
        $webhook_model = new Webhook_Model();
        $webhook = $webhook_model->get_by_key($webhook_key);
        
        if (!$webhook) {
            wp_send_json_error(array('message' => 'Webhook not found'), 404);
            return;
        }
        
        // Get workflow
        $workflow_model = new Workflow_Model();
        $workflow = $workflow_model->get($webhook->workflow_id);
        
        if (!$workflow || $workflow->status !== 'active') {
            wp_send_json_error(array('message' => 'Workflow not active'), 400);
            return;
        }
        
        // Capture webhook data
        $method = $_SERVER['REQUEST_METHOD'];
        $headers = $this->get_request_headers();
        $body = file_get_contents('php://input');
        $params = $_GET;
        
        // Parse body if JSON
        $parsed_body = $body;
        if ($this->is_json($body)) {
            $parsed_body = json_decode($body, true);
        }
        
        // Create execution
        $execution_model = new Execution_Model();
        $execution_id = $execution_model->create(array(
            'workflow_id' => $webhook->workflow_id,
            'status' => 'pending',
            'trigger_type' => 'webhook',
            'trigger_data' => array(
                'webhook_key' => $webhook_key,
                'node_id' => $webhook->node_id,
                'method' => $method,
                'headers' => $headers,
                'body' => $parsed_body,
                'params' => $params,
                'settings' => $webhook->settings
            )
        ));
        
        if (!$execution_id) {
            wp_send_json_error(array('message' => 'Failed to create execution'), 500);
            return;
        }
        
        // Queue execution
        wp_schedule_single_event(time(), 'wa_execute_workflow', array($execution_id));
        
        // Log webhook if enabled
        $settings = get_option('wa_settings', array());
        if (!empty($settings['enable_webhook_logging'])) {
            error_log(sprintf(
                '[Workflow Automation] Webhook received: key=%s, workflow=%d, execution=%d',
                $webhook_key,
                $webhook->workflow_id,
                $execution_id
            ));
        }
        
        // Send response
        wp_send_json_success(array(
            'status' => 'queued',
            'execution_id' => $execution_id,
            'message' => 'Workflow execution queued'
        ));
    }

    /**
     * Create a webhook
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function create_webhook($request) {
        $workflow_id = $request->get_param('workflow_id');
        $node_id = $request->get_param('node_id');
        $settings = $request->get_param('settings');
        
        // Verify workflow exists
        $workflow_model = new Workflow_Model();
        $workflow = $workflow_model->get($workflow_id);
        
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'), array('status' => 404));
        }
        
        // Create webhook
        $webhook_model = new Webhook_Model();
        $webhook_data = $webhook_model->create_webhook($workflow_id, $node_id, $settings);
        
        if (!$webhook_data) {
            return new WP_Error('create_failed', __('Failed to create webhook', 'workflow-automation'), array('status' => 500));
        }
        
        return new WP_REST_Response($webhook_data, 201);
    }

    /**
     * Get webhooks for a workflow
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_webhooks($request) {
        global $wpdb;
        
        $workflow_id = $request->get_param('workflow_id');
        $webhook_model = new Webhook_Model();
        
        $table_name = $wpdb->prefix . 'wa_webhooks';
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE workflow_id = %d",
            $workflow_id
        );
        
        $webhooks = $wpdb->get_results($sql);
        
        foreach ($webhooks as $webhook) {
            if ($webhook->settings) {
                $webhook->settings = json_decode($webhook->settings, true);
            }
            $webhook->webhook_url = $webhook_model->get_webhook_url($webhook->webhook_key);
        }
        
        return new WP_REST_Response($webhooks);
    }

    /**
     * Check admin permission
     *
     * @since    1.0.0
     * @return   bool
     */
    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Get request headers
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_request_headers() {
        $headers = array();
        
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Check if string is JSON
     *
     * @since    1.0.0
     * @param    string    $string    The string to check
     * @return   bool
     */
    private function is_json($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}