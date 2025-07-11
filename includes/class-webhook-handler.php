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
     * The webhook endpoint base
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $endpoint_base    The webhook endpoint base
     */
    private $endpoint_base = 'workflow-webhook';

    /**
     * Initialize the webhook handler
     *
     * @since    1.0.0
     */
    public function __construct() {
        // Hook into WordPress init
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('parse_request', array($this, 'handle_webhook_request'));
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    /**
     * Add rewrite rules for webhook endpoints
     *
     * @since    1.0.0
     */
    public function add_rewrite_rules() {
        // Add rewrite rule for webhook endpoint
        add_rewrite_rule(
            '^' . $this->endpoint_base . '/([^/]+)/?$',
            'index.php?wa_webhook=1&wa_webhook_key=$matches[1]',
            'top'
        );
        
        // Add LINE webhook endpoint
        add_rewrite_rule(
            '^line-webhook/([^/]+)/?$',
            'index.php?wa_webhook=1&wa_webhook_type=line&wa_webhook_key=$matches[1]',
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
        $vars[] = 'wa_webhook_key';
        $vars[] = 'wa_webhook_type';
        return $vars;
    }

    /**
     * Handle webhook request
     *
     * @since    1.0.0
     * @param    WP    $wp    WordPress object
     */
    public function handle_webhook_request($wp) {
        if (!isset($wp->query_vars['wa_webhook'])) {
            return;
        }
        
        $webhook_key = isset($wp->query_vars['wa_webhook_key']) ? $wp->query_vars['wa_webhook_key'] : '';
        $webhook_type = isset($wp->query_vars['wa_webhook_type']) ? $wp->query_vars['wa_webhook_type'] : 'generic';
        
        if (empty($webhook_key)) {
            $this->send_response(array('error' => 'Missing webhook key'), 400);
            exit;
        }
        
        // Process webhook based on type
        switch ($webhook_type) {
            case 'line':
                $this->handle_line_webhook($webhook_key);
                break;
            default:
                $this->handle_generic_webhook($webhook_key);
                break;
        }
        
        exit;
    }

    /**
     * Handle generic webhook
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     */
    private function handle_generic_webhook($webhook_key) {
        // Verify webhook key and find associated workflows
        $workflows = $this->get_workflows_by_webhook_key($webhook_key);
        
        if (empty($workflows)) {
            $this->send_response(array('error' => 'Invalid webhook key'), 404);
            return;
        }
        
        // Get request data
        $method = $_SERVER['REQUEST_METHOD'];
        $headers = $this->get_request_headers();
        $body = file_get_contents('php://input');
        
        // Parse body based on content type
        $content_type = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
        $parsed_body = $this->parse_request_body($body, $content_type);
        
        // Prepare webhook data
        $webhook_data = array(
            'method' => $method,
            'headers' => $headers,
            'body' => $parsed_body,
            'raw_body' => $body,
            'query_params' => $_GET,
            'timestamp' => current_time('mysql'),
            'ip_address' => $this->get_client_ip()
        );
        
        // Execute workflows
        $results = array();
        $executor = new Workflow_Executor();
        
        foreach ($workflows as $workflow) {
            $result = $executor->execute_webhook($workflow->id, $webhook_data, 'webhook');
            $results[] = array(
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'success' => $result['success'],
                'execution_id' => $result['execution_id']
            );
        }
        
        // Send response
        $this->send_response(array(
            'success' => true,
            'message' => 'Webhook processed',
            'executions' => $results
        ));
    }

    /**
     * Handle LINE webhook
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     */
    private function handle_line_webhook($webhook_key) {
        // Get request body
        $body = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';
        
        // Get workflows configured for this LINE webhook
        $workflows = $this->get_workflows_by_webhook_key($webhook_key);
        
        if (empty($workflows)) {
            $this->send_response(array('error' => 'Invalid webhook key'), 404);
            return;
        }
        
        // Verify LINE signature for each workflow's channel secret
        $verified = false;
        $verified_workflow = null;
        
        foreach ($workflows as $workflow) {
            // Get LINE integration settings from workflow
            $channel_secret = $this->get_line_channel_secret($workflow->id);
            
            if ($channel_secret && $this->verify_line_signature($body, $signature, $channel_secret)) {
                $verified = true;
                $verified_workflow = $workflow;
                break;
            }
        }
        
        if (!$verified) {
            $this->send_response(array('error' => 'Invalid signature'), 403);
            return;
        }
        
        // Parse LINE webhook data
        $data = json_decode($body, true);
        
        if (!$data || !isset($data['events'])) {
            $this->send_response(array('error' => 'Invalid request body'), 400);
            return;
        }
        
        // Process each event
        $executor = new Workflow_Executor();
        
        foreach ($data['events'] as $event) {
            // Prepare webhook data
            $webhook_data = array(
                'type' => 'line',
                'event' => $event,
                'event_type' => $event['type'] ?? '',
                'source' => $event['source'] ?? array(),
                'timestamp' => $event['timestamp'] ?? time(),
                'reply_token' => $event['replyToken'] ?? '',
                'message' => $event['message'] ?? array(),
                'postback' => $event['postback'] ?? array()
            );
            
            // Execute workflow
            $executor->execute_webhook($verified_workflow->id, $webhook_data, 'webhook');
        }
        
        // LINE expects 200 OK response
        $this->send_response(array('success' => true), 200);
    }

    /**
     * Get workflows by webhook key
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     * @return   array     Array of workflow objects
     */
    private function get_workflows_by_webhook_key($webhook_key) {
        global $wpdb;
        
        // Query workflows that have webhook_start nodes with this key
        $sql = $wpdb->prepare("
            SELECT DISTINCT w.* 
            FROM {$wpdb->prefix}wa_workflows w
            JOIN {$wpdb->prefix}wa_nodes n ON w.id = n.workflow_id
            WHERE w.status = 'active'
            AND n.type = 'webhook_start'
            AND n.data LIKE %s
        ", '%"webhook_key":"' . esc_sql($webhook_key) . '"%');
        
        return $wpdb->get_results($sql);
    }

    /**
     * Get LINE channel secret from workflow
     *
     * @since    1.0.0
     * @param    int    $workflow_id    The workflow ID
     * @return   string|null
     */
    private function get_line_channel_secret($workflow_id) {
        global $wpdb;
        
        // Find LINE node in workflow and get its integration
        $sql = $wpdb->prepare("
            SELECT n.data 
            FROM {$wpdb->prefix}wa_nodes n
            WHERE n.workflow_id = %d
            AND n.type = 'line'
            LIMIT 1
        ", $workflow_id);
        
        $node = $wpdb->get_row($sql);
        
        if (!$node) {
            return null;
        }
        
        $node_data = json_decode($node->data, true);
        $integration_id = $node_data['integration_id'] ?? 0;
        
        if (!$integration_id) {
            return null;
        }
        
        // Get integration settings
        $integration_model = new Integration_Settings_Model();
        $integration = $integration_model->get($integration_id);
        
        if (!$integration || $integration->integration_type !== 'line') {
            return null;
        }
        
        $settings = $integration_model->decrypt_settings($integration->settings);
        return $settings['channel_secret'] ?? null;
    }

    /**
     * Verify LINE signature
     *
     * @since    1.0.0
     * @param    string    $body           Request body
     * @param    string    $signature      X-Line-Signature header
     * @param    string    $channel_secret Channel secret
     * @return   bool
     */
    private function verify_line_signature($body, $signature, $channel_secret) {
        $hash = hash_hmac('sha256', $body, $channel_secret, true);
        $expected_signature = base64_encode($hash);
        return $signature === $expected_signature;
    }

    /**
     * Parse request body
     *
     * @since    1.0.0
     * @param    string    $body           Raw body
     * @param    string    $content_type   Content type
     * @return   mixed
     */
    private function parse_request_body($body, $content_type) {
        if (empty($body)) {
            return null;
        }
        
        // JSON
        if (strpos($content_type, 'application/json') !== false) {
            return json_decode($body, true);
        }
        
        // Form data
        if (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
            parse_str($body, $parsed);
            return $parsed;
        }
        
        // XML
        if (strpos($content_type, 'application/xml') !== false || strpos($content_type, 'text/xml') !== false) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body);
            if ($xml !== false) {
                return json_decode(json_encode($xml), true);
            }
        }
        
        // Default: return raw body
        return $body;
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
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
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
     * Send JSON response
     *
     * @since    1.0.0
     * @param    mixed    $data    Response data
     * @param    int      $status  HTTP status code
     */
    private function send_response($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Get webhook URL for a key
     *
     * @since    1.0.0
     * @param    string    $webhook_key    The webhook key
     * @param    string    $type           The webhook type
     * @return   string
     */
    public static function get_webhook_url($webhook_key, $type = 'generic') {
        if ($type === 'line') {
            return home_url('line-webhook/' . $webhook_key);
        }
        
        return home_url('workflow-webhook/' . $webhook_key);
    }

    /**
     * Generate webhook key
     *
     * @since    1.0.0
     * @return   string
     */
    public static function generate_webhook_key() {
        return wp_generate_password(32, false);
    }
}