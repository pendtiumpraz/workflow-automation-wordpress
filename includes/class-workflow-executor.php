<?php
/**
 * Workflow Executor
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Workflow Executor class
 *
 * Handles the execution of workflows
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Executor {

    /**
     * The current execution ID
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $execution_id    The execution ID
     */
    private $execution_id;

    /**
     * The execution context
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $context    The execution context
     */
    private $context;

    /**
     * Registered node types
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $node_types    Available node types
     */
    private $node_types;

    /**
     * Initialize the executor
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->context = array();
        $this->load_node_types();
    }

    /**
     * Load available node types
     *
     * @since    1.0.0
     */
    private function load_node_types() {
        $this->node_types = array();
        
        // Load node classes
        $nodes_dir = WA_PLUGIN_DIR . 'includes/nodes/';
        
        // Base abstract class
        if (file_exists($nodes_dir . 'abstract-node.php')) {
            require_once $nodes_dir . 'abstract-node.php';
        }
        
        // Webhook nodes
        if (file_exists($nodes_dir . 'webhook/class-webhook-start-node.php')) {
            require_once $nodes_dir . 'webhook/class-webhook-start-node.php';
            $this->node_types['webhook_start'] = 'WA_Webhook_Start_Node';
        }
        
        // Integration nodes
        $integration_nodes = array(
            'email' => 'class-email-node.php',
            'slack' => 'class-slack-node.php',
            'http' => 'class-http-node.php',
            'google_sheets' => 'class-google-sheets-node.php',
            'line' => 'class-line-node.php'
        );
        
        foreach ($integration_nodes as $type => $file) {
            $path = $nodes_dir . 'integrations/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'WA_' . ucfirst($type) . '_Node';
                if (class_exists($class_name)) {
                    $this->node_types[$type] = $class_name;
                }
            }
        }
        
        // Logic nodes
        $logic_nodes = array(
            'filter' => 'class-filter-node.php',
            'loop' => 'class-loop-node.php'
        );
        
        // Data nodes
        $data_nodes = array(
            'transform' => 'class-transform-node.php',
            'formatter' => 'class-formatter-node.php',
            'parser' => 'class-parser-node.php'
        );
        
        // AI nodes
        $ai_nodes = array(
            'openai' => 'class-openai-node.php',
            'claude' => 'class-claude-node.php',
            'gemini' => 'class-gemini-node.php'
        );
        
        foreach ($logic_nodes as $type => $file) {
            $path = $nodes_dir . 'logic/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'WA_' . ucfirst($type) . '_Node';
                if (class_exists($class_name)) {
                    $this->node_types[$type] = $class_name;
                }
            }
        }
        
        foreach ($data_nodes as $type => $file) {
            $path = $nodes_dir . 'data/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'WA_' . ucfirst($type) . '_Node';
                if (class_exists($class_name)) {
                    $this->node_types[$type] = $class_name;
                }
            }
        }
        
        foreach ($ai_nodes as $type => $file) {
            $path = $nodes_dir . 'ai/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'WA_' . ucfirst($type) . '_Node';
                if (class_exists($class_name)) {
                    $this->node_types[$type] = $class_name;
                }
            }
        }
        
        // WordPress nodes
        $wp_nodes = array(
            'wp_post' => 'class-wp-post-node.php',
            'wp_user' => 'class-wp-user-node.php',
            'wp_media' => 'class-wp-media-node.php'
        );
        
        foreach ($wp_nodes as $type => $file) {
            $path = $nodes_dir . 'wordpress/' . $file;
            if (file_exists($path)) {
                require_once $path;
                $class_name = 'WA_' . str_replace('_', '_', ucfirst($type)) . '_Node';
                if (class_exists($class_name)) {
                    $this->node_types[$type] = $class_name;
                }
            }
        }
        
        // Allow plugins to register additional node types
        $this->node_types = apply_filters('wa_node_types', $this->node_types);
    }

    /**
     * Get available node types
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_available_node_types() {
        if (empty($this->node_types)) {
            $this->load_node_types();
        }
        return $this->node_types;
    }

    /**
     * Execute a workflow from webhook trigger
     *
     * @since    1.0.0
     * @param    int       $workflow_id    The workflow ID
     * @param    array     $trigger_data   The trigger data
     * @param    string    $trigger_type   The trigger type
     * @return   array
     */
    public function execute_webhook($workflow_id, $trigger_data, $trigger_type = 'webhook') {
        // Create execution record
        $execution_model = new Execution_Model();
        $execution_id = $execution_model->create(array(
            'workflow_id' => $workflow_id,
            'status' => 'pending',
            'trigger_type' => $trigger_type,
            'trigger_data' => json_encode($trigger_data),
            'created_at' => current_time('mysql')
        ));
        
        if (!$execution_id) {
            return array(
                'success' => false,
                'error' => 'Failed to create execution record'
            );
        }
        
        // Execute the workflow
        $success = $this->execute($execution_id);
        
        return array(
            'success' => $success,
            'execution_id' => $execution_id
        );
    }

    /**
     * Execute a workflow
     *
     * @since    1.0.0
     * @param    int    $execution_id    The execution ID
     * @return   bool
     */
    public function execute($execution_id) {
        $this->execution_id = $execution_id;
        
        // Get execution details
        $execution_model = new Execution_Model();
        $execution = $execution_model->get($execution_id);
        
        if (!$execution || $execution->status !== 'pending') {
            return false;
        }
        
        // Update status to running
        $execution_model->update($execution_id, array(
            'status' => 'running',
            'started_at' => current_time('mysql')
        ));
        
        try {
            // Get workflow
            $workflow_model = new Workflow_Model();
            $workflow = $workflow_model->get($execution->workflow_id);
            
            if (!$workflow || $workflow->status !== 'active') {
                throw new Exception('Workflow not active');
            }
            
            // Initialize context with trigger data
            $trigger_data = json_decode($execution->trigger_data, true);
            if ($trigger_data === null) {
                $trigger_data = array();
            }
            
            $this->context = array(
                'trigger' => $trigger_data,
                'workflow_id' => $workflow->id,
                'execution_id' => $execution_id,
                'variables' => array(),
                'node_outputs' => array()
            );
            
            // Get nodes
            $node_model = new Node_Model();
            $nodes = $node_model->get_by_workflow($workflow->id);
            
            // Build node map
            $node_map = array();
            foreach ($nodes as $node) {
                $node_map[$node->node_id] = $node;
            }
            
            // Find start node
            $start_node = $this->find_start_node($nodes, $execution->trigger_type, $execution->trigger_data);
            
            if (!$start_node) {
                throw new Exception('No matching start node found');
            }
            
            // Execute workflow from start node
            $this->execute_from_node($start_node, $node_map, $workflow->flow_data);
            
            // Update execution status to completed
            $execution_model->update($execution_id, array(
                'status' => 'completed',
                'completed_at' => current_time('mysql'),
                'execution_data' => $this->context
            ));
            
            return true;
            
        } catch (Exception $e) {
            // Update execution status to failed
            $execution_model->update($execution_id, array(
                'status' => 'failed',
                'completed_at' => current_time('mysql'),
                'error_message' => $e->getMessage(),
                'execution_data' => $this->context
            ));
            
            // Log error
            error_log(sprintf(
                '[Workflow Automation] Execution %d failed: %s',
                $execution_id,
                $e->getMessage()
            ));
            
            return false;
        }
    }

    /**
     * Find the start node for execution
     *
     * @since    1.0.0
     * @param    array     $nodes          All workflow nodes
     * @param    string    $trigger_type   The trigger type
     * @param    array     $trigger_data   The trigger data
     * @return   object|null
     */
    private function find_start_node($nodes, $trigger_type, $trigger_data) {
        foreach ($nodes as $node) {
            // Check if it's a start node type
            if (strpos($node->node_type, '_start') === false && $node->node_type !== 'webhook_start') {
                continue;
            }
            
            // For webhook triggers, match by node_id
            if ($trigger_type === 'webhook' && isset($trigger_data['node_id'])) {
                if ($node->node_id === $trigger_data['node_id']) {
                    return $node;
                }
            }
            
            // For manual triggers, use first start node
            if ($trigger_type === 'manual') {
                return $node;
            }
        }
        
        return null;
    }

    /**
     * Execute from a specific node
     *
     * @since    1.0.0
     * @param    object    $node         The node to execute
     * @param    array     $node_map     Map of all nodes
     * @param    array     $flow_data    The workflow flow data
     * @return   mixed
     */
    private function execute_from_node($node, $node_map, $flow_data) {
        // Check if node has already been executed (prevent loops)
        if (isset($this->context['node_outputs'][$node->node_id])) {
            return $this->context['node_outputs'][$node->node_id];
        }
        
        // Execute the node
        $output = $this->execute_node($node);
        
        // Store output in context
        $this->context['node_outputs'][$node->node_id] = $output;
        
        // Find connected nodes
        $next_nodes = $this->find_next_nodes($node->node_id, $node_map, $flow_data);
        
        // Execute connected nodes
        foreach ($next_nodes as $next_node) {
            $this->execute_from_node($next_node, $node_map, $flow_data);
        }
        
        return $output;
    }

    /**
     * Execute a single node
     *
     * @since    1.0.0
     * @param    object    $node    The node to execute
     * @return   mixed
     */
    private function execute_node($node) {
        // Get node class
        if (!isset($this->node_types[$node->node_type])) {
            throw new Exception(sprintf('Unknown node type: %s', $node->node_type));
        }
        
        $node_class = $this->node_types[$node->node_type];
        
        if (!class_exists($node_class)) {
            throw new Exception(sprintf('Node class not found: %s', $node_class));
        }
        
        // Create node instance
        $node_instance = new $node_class($node->node_id, $node->settings);
        
        // Get previous node output
        $previous_data = null;
        if (!empty($this->context['last_output'])) {
            $previous_data = $this->context['last_output'];
        }
        
        // Get error handler
        $error_handler = Workflow_Error_Handler::get_instance();
        
        // Get node error config
        $error_config = array(
            'on_error' => isset($node->settings['error_handling']) ? $node->settings['error_handling'] : 'stop',
            'max_retries' => isset($node->settings['max_retries']) ? intval($node->settings['max_retries']) : 3,
            'retry_delay' => isset($node->settings['retry_delay']) ? intval($node->settings['retry_delay']) : 1000
        );
        
        // Execute node with error handling
        $output = $error_handler->execute_node_safely(
            $node_instance,
            $this->context,
            $previous_data,
            $error_config
        );
        
        // Store as last output
        $this->context['last_output'] = $output;
        
        // Log node execution
        error_log(sprintf(
            '[Workflow Automation] Executed node %s (type: %s) in execution %d',
            $node->node_id,
            $node->node_type,
            $this->execution_id
        ));
        
        return $output;
    }

    /**
     * Find next nodes to execute
     *
     * @since    1.0.0
     * @param    string    $node_id      The current node ID
     * @param    array     $node_map     Map of all nodes
     * @param    array     $flow_data    The workflow flow data
     * @return   array
     */
    private function find_next_nodes($node_id, $node_map, $flow_data) {
        $next_nodes = array();
        
        if (!isset($flow_data['edges']) || !is_array($flow_data['edges'])) {
            return $next_nodes;
        }
        
        foreach ($flow_data['edges'] as $edge) {
            if ($edge['source'] === $node_id && isset($node_map[$edge['target']])) {
                $next_nodes[] = $node_map[$edge['target']];
            }
        }
        
        return $next_nodes;
    }
}