<?php
/**
 * Node Connection Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Node Connection Model class
 *
 * Handles CRUD operations for node connections
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Node_Connection_Model {

    /**
     * The table name
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name
     */
    private $table_name;

    /**
     * Initialize the model
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wa_node_connections';
    }

    /**
     * Get connections by workflow ID
     *
     * @since    1.0.0
     * @param    int    $workflow_id    The workflow ID
     * @return   array
     */
    public function get_by_workflow($workflow_id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE workflow_id = %d",
            $workflow_id
        );
        
        return $wpdb->get_results($sql);
    }

    /**
     * Create a new connection
     *
     * @since    1.0.0
     * @param    array    $data    Connection data
     * @return   int|false         Connection ID or false on failure
     */
    public function create($data) {
        global $wpdb;
        
        $defaults = array(
            'workflow_id' => 0,
            'source_node_id' => 0,
            'target_node_id' => 0,
            'source_output' => 'default',
            'target_input' => 'default',
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array('%d', '%d', '%d', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete connections by workflow ID
     *
     * @since    1.0.0
     * @param    int    $workflow_id    The workflow ID
     * @return   bool
     */
    public function delete_by_workflow($workflow_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('workflow_id' => $workflow_id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Save connections for a workflow
     *
     * @since    1.0.0
     * @param    int      $workflow_id    The workflow ID
     * @param    array    $connections    Array of connections
     * @return   bool
     */
    public function save_workflow_connections($workflow_id, $connections) {
        // Delete existing connections
        $this->delete_by_workflow($workflow_id);
        
        // Insert new connections
        foreach ($connections as $connection) {
            $this->create(array(
                'workflow_id' => $workflow_id,
                'source_node_id' => $connection['source'],
                'target_node_id' => $connection['target'],
                'source_output' => isset($connection['sourceHandle']) ? $connection['sourceHandle'] : 'default',
                'target_input' => isset($connection['targetHandle']) ? $connection['targetHandle'] : 'default'
            ));
        }
        
        return true;
    }
}