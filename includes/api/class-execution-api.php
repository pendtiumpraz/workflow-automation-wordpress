<?php
/**
 * Execution API
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 */

/**
 * Execution API class
 *
 * Handles REST API endpoints for workflow executions
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Execution_API {

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
        // GET /executions
        register_rest_route($this->namespace, '/executions', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_executions'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'workflow_id' => array(
                        'default' => 0,
                        'sanitize_callback' => 'absint',
                    ),
                    'status' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'page' => array(
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ));

        // GET /executions/{id}
        register_rest_route($this->namespace, '/executions/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_execution'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));

        // POST /executions/trigger
        register_rest_route($this->namespace, '/executions/trigger', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'trigger_execution'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'workflow_id' => array(
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ),
                    'trigger_data' => array(
                        'default' => array(),
                        'validate_callback' => function($param) {
                            return is_array($param);
                        }
                    ),
                ),
            ),
        ));

        // PUT /executions/{id}/status
        register_rest_route($this->namespace, '/executions/(?P<id>\d+)/status', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_execution_status'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                    'status' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function($param) {
                            return in_array($param, array('pending', 'running', 'completed', 'failed'));
                        }
                    ),
                    'error_message' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
        ));

        // DELETE /executions/{id}
        register_rest_route($this->namespace, '/executions/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_execution'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));
    }

    /**
     * Get executions
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_executions($request) {
        $execution_model = new Execution_Model();
        
        $workflow_id = $request->get_param('workflow_id');
        $status = $request->get_param('status');
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        
        if ($workflow_id) {
            // Get executions for specific workflow
            $args = array(
                'status' => $status,
                'limit' => $per_page,
                'offset' => ($page - 1) * $per_page
            );
            
            $executions = $execution_model->get_by_workflow($workflow_id, $args);
        } else {
            // Get all executions
            global $wpdb;
            $table_name = $wpdb->prefix . 'wa_executions';
            
            $where = array('1=1');
            $where_values = array();
            
            if ($status) {
                $where[] = 'status = %s';
                $where_values[] = $status;
            }
            
            $where_clause = implode(' AND ', $where);
            
            $sql = "SELECT * FROM {$table_name} WHERE {$where_clause}";
            if (!empty($where_values)) {
                $sql = $wpdb->prepare($sql, $where_values);
            }
            
            $sql .= " ORDER BY started_at DESC";
            $sql .= sprintf(" LIMIT %d OFFSET %d", $per_page, ($page - 1) * $per_page);
            
            $executions = $wpdb->get_results($sql);
            
            foreach ($executions as $execution) {
                if ($execution->trigger_data) {
                    $execution->trigger_data = json_decode($execution->trigger_data, true);
                }
                if ($execution->execution_data) {
                    $execution->execution_data = json_decode($execution->execution_data, true);
                }
            }
        }
        
        // Get workflow names
        $workflow_model = new Workflow_Model();
        foreach ($executions as $execution) {
            $workflow = $workflow_model->get($execution->workflow_id);
            $execution->workflow_name = $workflow ? $workflow->name : 'Unknown';
        }
        
        return new WP_REST_Response($executions);
    }

    /**
     * Get a single execution
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function get_execution($request) {
        $id = $request->get_param('id');
        $execution_model = new Execution_Model();
        
        $execution = $execution_model->get($id);
        
        if (!$execution) {
            return new WP_Error('not_found', __('Execution not found', 'workflow-automation'), array('status' => 404));
        }
        
        // Get workflow info
        $workflow_model = new Workflow_Model();
        $workflow = $workflow_model->get($execution->workflow_id);
        $execution->workflow_name = $workflow ? $workflow->name : 'Unknown';
        
        return new WP_REST_Response($execution);
    }

    /**
     * Trigger a workflow execution
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function trigger_execution($request) {
        $workflow_id = $request->get_param('workflow_id');
        $trigger_data = $request->get_param('trigger_data');
        
        // Verify workflow exists and is active
        $workflow_model = new Workflow_Model();
        $workflow = $workflow_model->get($workflow_id);
        
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'), array('status' => 404));
        }
        
        if ($workflow->status !== 'active') {
            return new WP_Error('not_active', __('Workflow is not active', 'workflow-automation'), array('status' => 400));
        }
        
        // Create execution
        $execution_model = new Execution_Model();
        $execution_id = $execution_model->create(array(
            'workflow_id' => $workflow_id,
            'status' => 'pending',
            'trigger_type' => 'manual',
            'trigger_data' => $trigger_data
        ));
        
        if (!$execution_id) {
            return new WP_Error('create_failed', __('Failed to create execution', 'workflow-automation'), array('status' => 500));
        }
        
        // Queue execution
        wp_schedule_single_event(time(), 'wa_execute_workflow', array($execution_id));
        
        $execution = $execution_model->get($execution_id);
        
        return new WP_REST_Response($execution, 201);
    }

    /**
     * Update execution status
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function update_execution_status($request) {
        $id = $request->get_param('id');
        $status = $request->get_param('status');
        $error_message = $request->get_param('error_message');
        
        $execution_model = new Execution_Model();
        
        // Verify execution exists
        $execution = $execution_model->get($id);
        if (!$execution) {
            return new WP_Error('not_found', __('Execution not found', 'workflow-automation'), array('status' => 404));
        }
        
        $data = array('status' => $status);
        
        if ($status === 'completed' || $status === 'failed') {
            $data['completed_at'] = current_time('mysql');
        }
        
        if ($status === 'failed' && $error_message) {
            $data['error_message'] = $error_message;
        }
        
        $result = $execution_model->update($id, $data);
        
        if (!$result) {
            return new WP_Error('update_failed', __('Failed to update execution', 'workflow-automation'), array('status' => 500));
        }
        
        $execution = $execution_model->get($id);
        
        return new WP_REST_Response($execution);
    }

    /**
     * Delete an execution
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function delete_execution($request) {
        $id = $request->get_param('id');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wa_executions';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('delete_failed', __('Failed to delete execution', 'workflow-automation'), array('status' => 500));
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', __('Execution not found', 'workflow-automation'), array('status' => 404));
        }
        
        return new WP_REST_Response(null, 204);
    }

    /**
     * Check permission
     *
     * @since    1.0.0
     * @return   bool
     */
    public function check_permission() {
        return current_user_can('manage_options');
    }
}