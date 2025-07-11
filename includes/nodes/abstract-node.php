<?php
/**
 * Abstract Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes
 */

/**
 * Abstract base class for all node types
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes
 * @author     OpsGuide Team <support@opsguide.com>
 */
abstract class WA_Abstract_Node {

    /**
     * The node ID
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $id    The node ID
     */
    protected $id;

    /**
     * The node type
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $type    The node type
     */
    protected $type;

    /**
     * The node settings
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $settings    The node settings
     */
    protected $settings;

    /**
     * Initialize the node
     *
     * @since    1.0.0
     * @param    string    $id         The node ID
     * @param    array     $settings   The node settings
     */
    public function __construct($id, $settings = array()) {
        $this->id = $id;
        $this->settings = is_array($settings) ? $settings : array();
        $this->type = $this->get_type();
    }

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    abstract public function get_type();
    
    /**
     * Get the node ID
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get node options/configuration fields
     *
     * @since    1.0.0
     * @return   array
     */
    abstract public function get_options();

    /**
     * Get settings fields for the node
     *
     * @since    1.0.0
     * @return   array
     */
    abstract public function get_settings_fields();
    
    /**
     * Get common error handling fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_error_handling_fields() {
        return array(
            array(
                'key' => 'error_handling',
                'label' => __('Error Handling', 'workflow-automation'),
                'type' => 'select',
                'default' => 'stop',
                'options' => array(
                    'stop' => __('Stop Workflow', 'workflow-automation'),
                    'continue' => __('Continue to Next Node', 'workflow-automation'),
                    'retry' => __('Retry with Backoff', 'workflow-automation'),
                    'use_default' => __('Use Default Value', 'workflow-automation')
                ),
                'description' => __('How to handle errors in this node', 'workflow-automation'),
                'group' => 'advanced'
            ),
            array(
                'key' => 'max_retries',
                'label' => __('Max Retries', 'workflow-automation'),
                'type' => 'number',
                'default' => 3,
                'min' => 0,
                'max' => 10,
                'description' => __('Maximum number of retry attempts', 'workflow-automation'),
                'condition' => array(
                    'field' => 'error_handling',
                    'operator' => '==',
                    'value' => 'retry'
                ),
                'group' => 'advanced'
            ),
            array(
                'key' => 'retry_delay',
                'label' => __('Retry Delay (ms)', 'workflow-automation'),
                'type' => 'number',
                'default' => 1000,
                'min' => 100,
                'max' => 60000,
                'step' => 100,
                'description' => __('Delay between retry attempts in milliseconds', 'workflow-automation'),
                'condition' => array(
                    'field' => 'error_handling',
                    'operator' => '==',
                    'value' => 'retry'
                ),
                'group' => 'advanced'
            ),
            array(
                'key' => 'default_output',
                'label' => __('Default Output', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => '{"success": false, "error": "Node failed"}',
                'description' => __('Default output to use on error (JSON format)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'error_handling',
                    'operator' => '==',
                    'value' => 'use_default'
                ),
                'group' => 'advanced'
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
    abstract public function execute($context, $previous_data);

    /**
     * Validate node settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        return true;
    }

    /**
     * Replace variables in text
     *
     * @since    1.0.0
     * @param    string    $text       The text with variables
     * @param    array     $context    The execution context
     * @return   string
     */
    protected function replace_variables($text, $context) {
        // Pattern: {{node_id.field_name}} or {{variable_name}}
        return preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function($matches) use ($context) {
                $path = explode('.', $matches[1]);
                return $this->get_nested_value($context, $path);
            },
            $text
        );
    }

    /**
     * Get nested value from array
     *
     * @since    1.0.0
     * @param    array    $array    The array to search
     * @param    array    $path     The path to the value
     * @return   mixed|null
     */
    protected function get_nested_value($array, $path) {
        $current = $array;
        
        foreach ($path as $key) {
            // Check if we're accessing node outputs
            if ($key === 'trigger' && isset($current['trigger'])) {
                $current = $current['trigger'];
            } elseif (isset($current['node_outputs']) && isset($current['node_outputs'][$key])) {
                $current = $current['node_outputs'][$key];
            } elseif (isset($current['variables']) && isset($current['variables'][$key])) {
                $current = $current['variables'][$key];
            } elseif (is_array($current) && isset($current[$key])) {
                $current = $current[$key];
            } elseif (is_object($current) && isset($current->$key)) {
                $current = $current->$key;
            } else {
                return null;
            }
        }
        
        return $current;
    }

    /**
     * Get a setting value
     *
     * @since    1.0.0
     * @param    string    $key        The setting key
     * @param    mixed     $default    The default value
     * @return   mixed
     */
    protected function get_setting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    /**
     * Log a message
     *
     * @since    1.0.0
     * @param    string    $message    The message to log
     * @param    string    $level      The log level
     */
    protected function log($message, $level = 'info') {
        $settings = get_option('wa_settings', array());
        
        if (!empty($settings['enable_debug'])) {
            error_log(sprintf(
                '[Workflow Automation] [%s] [Node %s] %s',
                strtoupper($level),
                $this->id,
                $message
            ));
        }
    }

    /**
     * Make an HTTP request
     *
     * @since    1.0.0
     * @param    string    $url       The URL
     * @param    array     $args      Request arguments
     * @return   array|WP_Error
     */
    protected function http_request($url, $args = array()) {
        $defaults = array(
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            'blocking' => true,
            'headers' => array(),
            'cookies' => array(),
            'body' => null,
            'compress' => false,
            'decompress' => true,
            'sslverify' => true,
            'stream' => false,
            'filename' => null
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $this->log('HTTP request failed: ' . $response->get_error_message(), 'error');
            return $response;
        }
        
        return array(
            'code' => wp_remote_retrieve_response_code($response),
            'message' => wp_remote_retrieve_response_message($response),
            'headers' => wp_remote_retrieve_headers($response),
            'body' => wp_remote_retrieve_body($response)
        );
    }
}