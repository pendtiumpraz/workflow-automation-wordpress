<?php
/**
 * Node API
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 */

/**
 * Node API class
 *
 * Handles REST API endpoints for nodes
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/api
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Node_API {

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
        // GET /nodes/types
        register_rest_route($this->namespace, '/nodes/types', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_node_types'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));

        // GET /nodes/categories
        register_rest_route($this->namespace, '/nodes/categories', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_node_categories'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));

        // GET /nodes/icons
        register_rest_route($this->namespace, '/nodes/icons', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_node_icons'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));

        // POST /nodes/validate
        register_rest_route($this->namespace, '/nodes/validate', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'validate_node'),
                'permission_callback' => array($this, 'check_permission'),
                'args' => array(
                    'type' => array(
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'settings' => array(
                        'required' => true,
                        'type' => 'object'
                    )
                )
            )
        ));
    }

    /**
     * Get available node types
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_node_types($request) {
        $executor = new Workflow_Executor();
        $node_types = $executor->get_available_node_types();
        
        $result = array();
        
        foreach ($node_types as $type => $class_name) {
            if (class_exists($class_name)) {
                $node = new $class_name('temp_' . $type);
                $options = $node->get_options();
                
                $result[] = array(
                    'type' => $type,
                    'label' => $options['label'] ?? ucwords(str_replace('_', ' ', $type)),
                    'description' => $options['description'] ?? '',
                    'category' => $options['category'] ?? 'actions',
                    'can_be_start' => $options['can_be_start'] ?? false,
                    'icon' => array(
                        'dashicon' => Node_Icons::get_icon($type, 'dashicon'),
                        'svg' => Node_Icons::get_icon($type, 'svg'),
                        'color' => Node_Icons::get_icon($type, 'color')
                    ),
                    'settings_fields' => $node->get_settings_fields()
                );
            }
        }
        
        return new WP_REST_Response($result);
    }

    /**
     * Get node categories
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_node_categories($request) {
        $categories = Node_Icons::get_categories();
        
        $result = array();
        foreach ($categories as $key => $category) {
            $result[] = array_merge(array('key' => $key), $category);
        }
        
        return new WP_REST_Response($result);
    }

    /**
     * Get all node icons
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response
     */
    public function get_node_icons($request) {
        $icons = Node_Icons::get_all_icons();
        return new WP_REST_Response($icons);
    }

    /**
     * Validate node settings
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    The REST request
     * @return   WP_REST_Response|WP_Error
     */
    public function validate_node($request) {
        $type = $request->get_param('type');
        $settings = $request->get_param('settings');
        
        $executor = new Workflow_Executor();
        $node_types = $executor->get_available_node_types();
        
        if (!isset($node_types[$type])) {
            return new WP_Error('invalid_node_type', __('Invalid node type', 'workflow-automation'), array('status' => 400));
        }
        
        $class_name = $node_types[$type];
        if (!class_exists($class_name)) {
            return new WP_Error('node_class_not_found', __('Node class not found', 'workflow-automation'), array('status' => 500));
        }
        
        try {
            $node = new $class_name('temp_' . $type);
            $node->set_settings($settings);
            
            $validation = $node->validate_settings();
            
            if (is_wp_error($validation)) {
                return new WP_REST_Response(array(
                    'valid' => false,
                    'errors' => array(
                        array(
                            'code' => $validation->get_error_code(),
                            'message' => $validation->get_error_message()
                        )
                    )
                ));
            }
            
            return new WP_REST_Response(array(
                'valid' => true,
                'errors' => array()
            ));
            
        } catch (Exception $e) {
            return new WP_Error('validation_error', $e->getMessage(), array('status' => 500));
        }
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