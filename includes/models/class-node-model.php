<?php
/**
 * Node Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Node Model class
 *
 * Handles CRUD operations for nodes
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Node_Model {

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
        $this->table_name = $wpdb->prefix . 'wa_nodes';
    }

    /**
     * Get nodes by workflow ID
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
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $result) {
            if ($result->settings) {
                $result->settings = json_decode($result->settings, true);
            }
        }
        
        return $results;
    }

    /**
     * Save nodes for a workflow
     *
     * @since    1.0.0
     * @param    int      $workflow_id    The workflow ID
     * @param    array    $nodes          Array of nodes
     * @return   bool
     */
    public function save_workflow_nodes($workflow_id, $nodes) {
        global $wpdb;
        
        // Delete existing nodes
        $this->delete_by_workflow($workflow_id);
        
        // Insert new nodes
        foreach ($nodes as $node) {
            $data = array(
                'workflow_id' => $workflow_id,
                'node_id' => $node['id'],
                'node_type' => $node['type'],
                'settings' => json_encode(isset($node['data']) ? $node['data'] : array()),
                'position_x' => isset($node['position']['x']) ? $node['position']['x'] : 0,
                'position_y' => isset($node['position']['y']) ? $node['position']['y'] : 0
            );
            
            $wpdb->insert(
                $this->table_name,
                $data,
                array('%d', '%s', '%s', '%s', '%d', '%d')
            );
        }
        
        return true;
    }

    /**
     * Delete nodes by workflow ID
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
}