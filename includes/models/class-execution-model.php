<?php
/**
 * Execution Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Execution Model class
 *
 * Handles CRUD operations for workflow executions
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Execution_Model {

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
        $this->table_name = $wpdb->prefix . 'wa_executions';
    }

    /**
     * Get an execution by ID
     *
     * @since    1.0.0
     * @param    int    $id    The execution ID
     * @return   object|null
     */
    public function get($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($sql);
        
        if ($result) {
            if ($result->trigger_data) {
                $result->trigger_data = json_decode($result->trigger_data, true);
            }
            if ($result->execution_data) {
                $result->execution_data = json_decode($result->execution_data, true);
            }
        }
        
        return $result;
    }

    /**
     * Create a new execution
     *
     * @since    1.0.0
     * @param    array    $data    Execution data
     * @return   int|false    The execution ID or false on failure
     */
    public function create($data) {
        global $wpdb;
        
        $defaults = array(
            'workflow_id' => 0,
            'status' => 'pending',
            'trigger_type' => '',
            'trigger_data' => array(),
            'execution_data' => array(),
            'started_at' => current_time('mysql'),
            'completed_at' => null,
            'error_message' => null
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Encode JSON fields
        if (is_array($data['trigger_data'])) {
            $data['trigger_data'] = json_encode($data['trigger_data']);
        }
        if (is_array($data['execution_data'])) {
            $data['execution_data'] = json_encode($data['execution_data']);
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            $data
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update an execution
     *
     * @since    1.0.0
     * @param    int      $id      The execution ID
     * @param    array    $data    Execution data
     * @return   bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        // Encode JSON fields if present
        if (isset($data['trigger_data']) && is_array($data['trigger_data'])) {
            $data['trigger_data'] = json_encode($data['trigger_data']);
        }
        if (isset($data['execution_data']) && is_array($data['execution_data'])) {
            $data['execution_data'] = json_encode($data['execution_data']);
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id)
        );
        
        return $result !== false;
    }

    /**
     * Get executions by workflow ID
     *
     * @since    1.0.0
     * @param    int      $workflow_id    The workflow ID
     * @param    array    $args           Query arguments
     * @return   array
     */
    public function get_by_workflow($workflow_id, $args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'orderby' => 'started_at',
            'order' => 'DESC',
            'limit' => 20,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('workflow_id = %d');
        $where_values = array($workflow_id);
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause}";
        $sql = $wpdb->prepare($sql, $where_values);
        $sql .= " ORDER BY {$args['orderby']} {$args['order']}";
        $sql .= " LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $result) {
            if ($result->trigger_data) {
                $result->trigger_data = json_decode($result->trigger_data, true);
            }
            if ($result->execution_data) {
                $result->execution_data = json_decode($result->execution_data, true);
            }
        }
        
        return $results;
    }

    /**
     * Delete executions by workflow ID
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
     * Clean up old executions
     *
     * @since    1.0.0
     * @param    int    $days    Number of days to keep
     * @return   int    Number of deleted records
     */
    public function cleanup_old_executions($days) {
        global $wpdb;
        
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = $wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE started_at < %s",
            $date
        );
        
        return $wpdb->query($sql);
    }
}