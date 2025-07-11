<?php
/**
 * Workflow API
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 */

/**
 * Workflow API class
 *
 * Handles REST API endpoints for workflows
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_API {

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
        // GET /workflows
        register_rest_route($this->namespace, '/workflows', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_workflows'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
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

        // POST /workflows
        register_rest_route($this->namespace, '/workflows', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_workflow'),
                'permission_callback' => array($this, 'check_write_permission'),
                'args' => array(
                    'name' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'description' => array(
                        'default' => '',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'flow_data' => array(
                        'default' => array('nodes' => array(), 'edges' => array()),
                        'validate_callback' => array($this, 'validate_flow_data'),
                    ),
                    'status' => array(
                        'default' => 'draft',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
        ));

        // GET /workflows/{id}
        register_rest_route($this->namespace, '/workflows/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_workflow'),
                'permission_callback' => array($this, 'check_read_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));

        // PUT /workflows/{id}
        register_rest_route($this->namespace, '/workflows/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_workflow'),
                'permission_callback' => array($this, 'check_write_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                    'name' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'description' => array(
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'flow_data' => array(
                        'validate_callback' => array($this, 'validate_flow_data'),
                    ),
                    'status' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            ),
        ));

        // DELETE /workflows/{id}
        register_rest_route($this->namespace, '/workflows/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_workflow'),
                'permission_callback' => array($this, 'check_write_permission'),
                'args' => array(
                    'id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ),
                ),
            ),
        ));
    }

    /**
     * Get workflows
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_workflows($request) {
        $workflow_model = new Workflow_Model();
        
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        $status = $request->get_param('status');
        
        $args = array(
            'status' => $status,
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page
        );
        
        $workflows = $workflow_model->get_all($args);
        $total = $workflow_model->get_count(array('status' => $status));
        
        $response = new WP_REST_Response($workflows);
        $response->header('X-WP-Total', $total);
        $response->header('X-WP-TotalPages', ceil($total / $per_page));
        
        return $response;
    }

    /**
     * Get a single workflow
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function get_workflow($request) {
        $id = $request->get_param('id');
        $workflow_model = new Workflow_Model();
        
        $workflow = $workflow_model->get($id);
        
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'), array('status' => 404));
        }
        
        // Get nodes
        $node_model = new Node_Model();
        $workflow->nodes = $node_model->get_by_workflow($id);
        
        return new WP_REST_Response($workflow);
    }

    /**
     * Create a workflow
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function create_workflow($request) {
        $workflow_model = new Workflow_Model();
        
        $data = array(
            'name' => $request->get_param('name'),
            'description' => $request->get_param('description'),
            'flow_data' => $request->get_param('flow_data'),
            'status' => $request->get_param('status')
        );
        
        $workflow_id = $workflow_model->create($data);
        
        if (!$workflow_id) {
            return new WP_Error('create_failed', __('Failed to create workflow', 'workflow-automation'), array('status' => 500));
        }
        
        // Save nodes if provided
        if (!empty($data['flow_data']['nodes'])) {
            $node_model = new Node_Model();
            $node_model->save_workflow_nodes($workflow_id, $data['flow_data']['nodes']);
        }
        
        $workflow = $workflow_model->get($workflow_id);
        
        return new WP_REST_Response($workflow, 201);
    }

    /**
     * Update a workflow
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function update_workflow($request) {
        $id = $request->get_param('id');
        $workflow_model = new Workflow_Model();
        
        // Check if workflow exists
        $workflow = $workflow_model->get($id);
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'), array('status' => 404));
        }
        
        $data = array();
        
        if ($request->has_param('name')) {
            $data['name'] = $request->get_param('name');
        }
        
        if ($request->has_param('description')) {
            $data['description'] = $request->get_param('description');
        }
        
        if ($request->has_param('flow_data')) {
            $data['flow_data'] = $request->get_param('flow_data');
        }
        
        if ($request->has_param('status')) {
            $data['status'] = $request->get_param('status');
        }
        
        $result = $workflow_model->update($id, $data);
        
        if (!$result) {
            return new WP_Error('update_failed', __('Failed to update workflow', 'workflow-automation'), array('status' => 500));
        }
        
        // Update nodes if flow_data provided
        if (isset($data['flow_data']) && isset($data['flow_data']['nodes'])) {
            $node_model = new Node_Model();
            $node_model->save_workflow_nodes($id, $data['flow_data']['nodes']);
        }
        
        $workflow = $workflow_model->get($id);
        
        return new WP_REST_Response($workflow);
    }

    /**
     * Delete a workflow
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function delete_workflow($request) {
        $id = $request->get_param('id');
        $workflow_model = new Workflow_Model();
        
        // Check if workflow exists
        $workflow = $workflow_model->get($id);
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'), array('status' => 404));
        }
        
        $result = $workflow_model->delete($id);
        
        if (!$result) {
            return new WP_Error('delete_failed', __('Failed to delete workflow', 'workflow-automation'), array('status' => 500));
        }
        
        return new WP_REST_Response(null, 204);
    }

    /**
     * Check read permission
     *
     * @since    1.0.0
     * @return   bool
     */
    public function check_read_permission() {
        return current_user_can('edit_posts');
    }

    /**
     * Check write permission
     *
     * @since    1.0.0
     * @return   bool
     */
    public function check_write_permission() {
        return current_user_can('manage_options');
    }

    /**
     * Validate flow data
     *
     * @since    1.0.0
     * @param    mixed    $value    The value to validate
     * @return   bool
     */
    public function validate_flow_data($value) {
        if (!is_array($value)) {
            return false;
        }
        
        if (!isset($value['nodes']) || !is_array($value['nodes'])) {
            return false;
        }
        
        if (!isset($value['edges']) || !is_array($value['edges'])) {
            return false;
        }
        
        return true;
    }
}