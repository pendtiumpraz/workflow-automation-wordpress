<?php
/**
 * Webhook Start Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/webhook
 */

/**
 * Webhook start node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/webhook
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Webhook_Start_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'webhook_start';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Webhook Trigger', 'workflow-automation'),
            'description' => __('Start workflow when a webhook is received', 'workflow-automation'),
            'icon' => 'dashicons-admin-links',
            'category' => 'triggers',
            'can_be_start' => true,
            'can_have_multiple' => false
        );
    }

    /**
     * Get settings fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_settings_fields() {
        return array(
            array(
                'key' => 'method',
                'label' => __('HTTP Method', 'workflow-automation'),
                'type' => 'select',
                'default' => 'any',
                'options' => array(
                    'any' => __('Any Method', 'workflow-automation'),
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                    'DELETE' => 'DELETE',
                    'PATCH' => 'PATCH'
                ),
                'description' => __('Accept webhooks only from specific HTTP method', 'workflow-automation')
            ),
            array(
                'key' => 'security_token',
                'label' => __('Security Token', 'workflow-automation'),
                'type' => 'text',
                'default' => '',
                'description' => __('Optional security token that must be included in webhook', 'workflow-automation')
            ),
            array(
                'key' => 'token_location',
                'label' => __('Token Location', 'workflow-automation'),
                'type' => 'select',
                'default' => 'header',
                'options' => array(
                    'header' => __('Header (X-Webhook-Token)', 'workflow-automation'),
                    'query' => __('Query Parameter (token)', 'workflow-automation'),
                    'body' => __('Body Parameter (token)', 'workflow-automation')
                ),
                'description' => __('Where to look for the security token', 'workflow-automation'),
                'condition' => array(
                    'field' => 'security_token',
                    'operator' => '!=',
                    'value' => ''
                )
            ),
            array(
                'key' => 'response_type',
                'label' => __('Response Type', 'workflow-automation'),
                'type' => 'select',
                'default' => 'json',
                'options' => array(
                    'json' => __('JSON', 'workflow-automation'),
                    'text' => __('Plain Text', 'workflow-automation'),
                    'empty' => __('Empty (204)', 'workflow-automation')
                ),
                'description' => __('Type of response to send back', 'workflow-automation')
            ),
            array(
                'key' => 'response_message',
                'label' => __('Response Message', 'workflow-automation'),
                'type' => 'textarea',
                'default' => 'Webhook received successfully',
                'description' => __('Message to send back in response', 'workflow-automation'),
                'condition' => array(
                    'field' => 'response_type',
                    'operator' => '!=',
                    'value' => 'empty'
                )
            )
        );
    }

    /**
     * Execute the node
     *
     * @since    1.0.0
     * @param    array    $context         The execution context
     * @param    mixed    $previous_data   Data from previous node
     * @return   mixed
     */
    public function execute($context, $previous_data) {
        // For webhook start nodes, the data comes from the trigger
        if (!isset($context['trigger']) || $context['trigger']['trigger_type'] !== 'webhook') {
            throw new Exception('Invalid trigger type for webhook start node');
        }
        
        $trigger_data = $context['trigger'];
        $webhook_data = isset($trigger_data['trigger_data']) ? $trigger_data['trigger_data'] : array();
        
        // Validate method if specified
        $allowed_method = $this->get_setting('method', 'any');
        if ($allowed_method !== 'any' && isset($webhook_data['method'])) {
            if ($webhook_data['method'] !== $allowed_method) {
                throw new Exception(sprintf(
                    'Invalid HTTP method. Expected %s, got %s',
                    $allowed_method,
                    $webhook_data['method']
                ));
            }
        }
        
        // Validate security token if specified
        $security_token = $this->get_setting('security_token', '');
        if (!empty($security_token)) {
            $token_location = $this->get_setting('token_location', 'header');
            $received_token = '';
            
            switch ($token_location) {
                case 'header':
                    $headers = isset($webhook_data['headers']) ? $webhook_data['headers'] : array();
                    $received_token = isset($headers['X-Webhook-Token']) ? $headers['X-Webhook-Token'] : '';
                    break;
                    
                case 'query':
                    $params = isset($webhook_data['params']) ? $webhook_data['params'] : array();
                    $received_token = isset($params['token']) ? $params['token'] : '';
                    break;
                    
                case 'body':
                    $body = isset($webhook_data['body']) ? $webhook_data['body'] : array();
                    if (is_array($body)) {
                        $received_token = isset($body['token']) ? $body['token'] : '';
                    }
                    break;
            }
            
            if ($received_token !== $security_token) {
                throw new Exception('Invalid security token');
            }
        }
        
        // Return webhook data for next nodes
        $output = array(
            'method' => isset($webhook_data['method']) ? $webhook_data['method'] : 'unknown',
            'headers' => isset($webhook_data['headers']) ? $webhook_data['headers'] : array(),
            'params' => isset($webhook_data['params']) ? $webhook_data['params'] : array(),
            'body' => isset($webhook_data['body']) ? $webhook_data['body'] : null,
            'webhook_key' => isset($webhook_data['webhook_key']) ? $webhook_data['webhook_key'] : '',
            'received_at' => current_time('mysql')
        );
        
        $this->log('Webhook start node executed successfully');
        
        return $output;
    }

    /**
     * Get webhook URL for this node
     *
     * @since    1.0.0
     * @param    int       $workflow_id    The workflow ID
     * @param    string    $node_id        The node ID
     * @return   string|null
     */
    public function get_webhook_url($workflow_id, $node_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wa_webhooks';
        $webhook = $wpdb->get_row($wpdb->prepare(
            "SELECT webhook_key FROM {$table_name} WHERE workflow_id = %d AND node_id = %s",
            $workflow_id,
            $node_id
        ));
        
        if ($webhook && $webhook->webhook_key) {
            $webhook_model = new Webhook_Model();
            return $webhook_model->get_webhook_url($webhook->webhook_key);
        }
        
        return null;
    }
}