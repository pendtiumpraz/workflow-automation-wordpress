<?php
/**
 * Workflow Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Workflow Model class
 *
 * Handles CRUD operations for workflows
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Model {

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
        $this->table_name = $wpdb->prefix . 'wa_workflows';
    }

    /**
     * Get a single workflow by ID
     *
     * @since    1.0.0
     * @param    int    $id    The workflow ID
     * @return   object|null
     */
    public function get($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($sql);
        
        if ($result && $result->flow_data) {
            $result->flow_data = json_decode($result->flow_data, true);
        }
        
        return $result;
    }

    /**
     * Get all workflows
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments
     * @return   array
     */
    public function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'created_by' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['created_by'])) {
            $where[] = 'created_by = %d';
            $where_values[] = $args['created_by'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        $sql .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $result) {
            if ($result->flow_data) {
                $result->flow_data = json_decode($result->flow_data, true);
            }
        }
        
        return $results;
    }

    /**
     * Create a new workflow
     *
     * @since    1.0.0
     * @param    array    $data    Workflow data
     * @return   int|false    The workflow ID or false on failure
     */
    public function create($data) {
        global $wpdb;
        
        $defaults = array(
            'name' => '',
            'description' => '',
            'flow_data' => array(),
            'status' => 'draft',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Encode flow_data as JSON
        if (is_array($data['flow_data'])) {
            $data['flow_data'] = wp_json_encode($data['flow_data']);
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update a workflow
     *
     * @since    1.0.0
     * @param    int      $id      The workflow ID
     * @param    array    $data    Workflow data
     * @return   bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        // Always update the updated_at timestamp
        $data['updated_at'] = current_time('mysql');
        
        // Encode flow_data as JSON if it's an array
        if (isset($data['flow_data']) && is_array($data['flow_data'])) {
            $data['flow_data'] = json_encode($data['flow_data']);
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id)
        );
        
        return $result !== false;
    }

    /**
     * Delete a workflow
     *
     * @since    1.0.0
     * @param    int    $id    The workflow ID
     * @return   bool
     */
    public function delete($id) {
        global $wpdb;
        
        // Delete associated nodes
        $node_model = new Node_Model();
        $node_model->delete_by_workflow($id);
        
        // Delete associated webhooks
        $webhook_model = new Webhook_Model();
        $webhook_model->delete_by_workflow($id);
        
        // Delete associated executions
        $execution_model = new Execution_Model();
        $execution_model->delete_by_workflow($id);
        
        // Delete the workflow
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get workflow count
     *
     * @since    1.0.0
     * @param    array    $args    Query arguments
     * @return   int
     */
    public function get_count($args = array()) {
        global $wpdb;
        
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['created_by'])) {
            $where[] = 'created_by = %d';
            $where_values[] = $args['created_by'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return (int) $wpdb->get_var($sql);
    }
}