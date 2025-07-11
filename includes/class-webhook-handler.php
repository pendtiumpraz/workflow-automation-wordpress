<?php
/**
 * Webhook Handler
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Webhook Handler class
 *
 * Handles incoming webhooks and triggers workflows
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Webhook_Handler {

    /**
     * Initialize the webhook handler
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Add rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_webhook_request'));
        
        // Flush rewrite rules on activation
        register_activation_hook(WA_PLUGIN_FILE, array($this, 'flush_rewrite_rules'));
    }

    /**
     * Add rewrite rules for webhooks
     *
     * @since    1.0.0
     */
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^wa-webhook/([a-zA-Z0-9-_]+)/?$',
            'index.php?wa_webhook=$matches[1]',
            'top'
        );
    }

    /**
     * Add query vars
     *
     * @since    1.0.0
     * @param    array    $vars    Query vars
     * @return   array
     */
    public function add_query_vars($vars) {
        $vars[] = 'wa_webhook';
        return $vars;
    }

    /**
     * Flush rewrite rules
     *
     * @since    1.0.0
     */
    public function flush_rewrite_rules() {
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Handle webhook request
     *
     * @since    1.0.0
     */
    public function handle_webhook_request() {
        $webhook_key = get_query_var('wa_webhook');
        
        if (!$webhook_key) {
            return;
        }
        
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Get webhook
            $webhook_model = new Webhook_Model();
            $webhook = $webhook_model->get_by_key($webhook_key);
            
            if (!$webhook || !$webhook->is_active) {
                $this->send_response(array(
                    'success' => false,
                    'error' => 'Invalid or inactive webhook'
                ), 404);
            }
            
            // Update last triggered
            $webhook_model->update($webhook->id, array(
                'last_triggered' => current_time('mysql')
            ));
            
            // Get workflow
            $workflow_model = new Workflow_Model();
            $workflow = $workflow_model->get($webhook->workflow_id);
            
            if (!$workflow || $workflow->status !== 'active') {
                $this->send_response(array(
                    'success' => false,
                    'error' => 'Workflow not active'
                ), 400);
            }
            
            // Prepare trigger data
            $trigger_data = $this->prepare_trigger_data();
            
            // Create execution
            $execution_model = new Execution_Model();
            $execution_id = $execution_model->create(array(
                'workflow_id' => $webhook->workflow_id,
                'trigger_type' => 'webhook',
                'trigger_data' => $trigger_data,
                'status' => 'pending'
            ));
            
            if (!$execution_id) {
                $this->send_response(array(
                    'success' => false,
                    'error' => 'Failed to create execution'
                ), 500);
            }
            
            // Schedule execution
            $scheduled = wp_schedule_single_event(time(), 'wa_execute_workflow', array($execution_id));
            
            if ($scheduled === false) {
                // Try direct execution if scheduling fails
                $executor = new Workflow_Executor();
                $executor->execute($execution_id);
            }
            
            // Send response
            $this->send_response(array(
                'success' => true,
                'message' => 'Webhook received and workflow triggered',
                'execution_id' => $execution_id,
                'webhook_id' => $webhook->id,
                'workflow_id' => $webhook->workflow_id
            ));
            
        } catch (Exception $e) {
            error_log('[Workflow Automation] Webhook error: ' . $e->getMessage());
            
            $this->send_response(array(
                'success' => false,
                'error' => 'Internal server error'
            ), 500);
        }
    }

    /**
     * Prepare trigger data from request
     *
     * @since    1.0.0
     * @return   array
     */
    private function prepare_trigger_data() {
        $data = array(
            'method' => $_SERVER['REQUEST_METHOD'],
            'headers' => $this->get_request_headers(),
            'query_params' => $_GET,
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_client_ip()
        );
        
        // Get body data
        $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        
        if (strpos($content_type, 'application/json') !== false) {
            // JSON body
            $raw_body = file_get_contents('php://input');
            $json_data = json_decode($raw_body, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['body'] = $json_data;
                $data['body_raw'] = $raw_body;
            } else {
                $data['body_raw'] = $raw_body;
                $data['json_error'] = json_last_error_msg();
            }
        } elseif (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
            // Form data
            $data['body'] = $_POST;
        } elseif (strpos($content_type, 'multipart/form-data') !== false) {
            // Multipart form data
            $data['body'] = $_POST;
            $data['files'] = $this->process_uploaded_files();
        } else {
            // Raw body
            $data['body_raw'] = file_get_contents('php://input');
        }
        
        // Add content type
        $data['content_type'] = $content_type;
        
        return $data;
    }

    /**
     * Get request headers
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_request_headers() {
        $headers = array();
        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for nginx
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $header_name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$header_name] = $value;
                }
            }
        }
        
        return $headers;
    }

    /**
     * Get client IP address
     *
     * @since    1.0.0
     * @return   string
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Process uploaded files
     *
     * @since    1.0.0
     * @return   array
     */
    private function process_uploaded_files() {
        $files = array();
        
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (is_array($file['name'])) {
                    // Multiple files
                    for ($i = 0; $i < count($file['name']); $i++) {
                        if ($file['error'][$i] === UPLOAD_ERR_OK) {
                            $files[$key][] = array(
                                'name' => $file['name'][$i],
                                'type' => $file['type'][$i],
                                'tmp_name' => $file['tmp_name'][$i],
                                'size' => $file['size'][$i]
                            );
                        }
                    }
                } else {
                    // Single file
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $files[$key] = array(
                            'name' => $file['name'],
                            'type' => $file['type'],
                            'tmp_name' => $file['tmp_name'],
                            'size' => $file['size']
                        );
                    }
                }
            }
        }
        
        return $files;
    }

    /**
     * Send JSON response
     *
     * @since    1.0.0
     * @param    array    $data          Response data
     * @param    int      $status_code   HTTP status code
     */
    private function send_response($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }

    /**
     * Generate webhook URL
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     * @return   string
     */
    public static function get_webhook_url($webhook_key) {
        return home_url('/wa-webhook/' . $webhook_key . '/');
    }

    /**
     * Validate webhook signature
     *
     * @since    1.0.0
     * @param    string    $payload      Request payload
     * @param    string    $signature    Signature to validate
     * @param    string    $secret       Webhook secret
     * @return   bool
     */
    public static function validate_signature($payload, $signature, $secret) {
        $calculated = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($calculated, $signature);
    }
}