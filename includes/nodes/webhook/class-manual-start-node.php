<?php
/**
 * Manual Start Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/webhook
 */

/**
 * Manual Start Node class
 *
 * Represents a manually triggered workflow start point
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/webhook
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Manual_Start_Node extends WA_Abstract_Node {

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Manual Trigger', 'workflow-automation'),
            'description' => __('Manually start a workflow execution', 'workflow-automation'),
            'category' => 'triggers',
            'can_be_start' => true,
            'icon' => 'dashicons-admin-users',
            'color' => '#9C27B0'
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
                'key' => 'title',
                'label' => __('Trigger Title', 'workflow-automation'),
                'type' => 'text',
                'required' => false,
                'default' => 'Manual Start',
                'placeholder' => __('Enter trigger title...', 'workflow-automation'),
                'description' => __('Optional title for this manual trigger', 'workflow-automation')
            ),
            array(
                'key' => 'description',
                'label' => __('Description', 'workflow-automation'),
                'type' => 'textarea',
                'required' => false,
                'rows' => 3,
                'placeholder' => __('Describe when this workflow should be triggered...', 'workflow-automation'),
                'description' => __('Optional description of the trigger conditions', 'workflow-automation')
            ),
            array(
                'key' => 'require_confirmation',
                'label' => __('Require Confirmation', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Require user confirmation before executing the workflow', 'workflow-automation')
            ),
            array(
                'key' => 'allowed_roles',
                'label' => __('Allowed User Roles', 'workflow-automation'),
                'type' => 'select',
                'multiple' => true,
                'default' => array('administrator'),
                'options' => $this->get_user_roles(),
                'description' => __('Which user roles can trigger this workflow', 'workflow-automation')
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
        // Manual start node doesn't need execution logic
        // It just provides the entry point for the workflow
        
        $title = $this->get_setting('title', 'Manual Start');
        $description = $this->get_setting('description', '');
        
        $this->log('Manual workflow started: ' . $title);
        
        return array(
            'trigger_type' => 'manual',
            'trigger_title' => $title,
            'trigger_description' => $description,
            'triggered_by' => wp_get_current_user()->user_login,
            'triggered_at' => current_time('mysql'),
            'context' => $context
        );
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        // Basic validation - manual triggers are always valid
        return true;
    }

    /**
     * Get available user roles
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_user_roles() {
        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        
        $roles = get_editable_roles();
        $role_options = array();
        
        foreach ($roles as $role_key => $role_info) {
            $role_options[$role_key] = $role_info['name'];
        }
        
        return $role_options;
    }

    /**
     * Check if current user can trigger this workflow
     *
     * @since    1.0.0
     * @return   bool
     */
    public function can_user_trigger() {
        $allowed_roles = $this->get_setting('allowed_roles', array('administrator'));
        
        if (empty($allowed_roles)) {
            return false;
        }
        
        $current_user = wp_get_current_user();
        
        if (!$current_user->exists()) {
            return false;
        }
        
        // Check if user has any of the allowed roles
        foreach ($allowed_roles as $role) {
            if (in_array($role, $current_user->roles)) {
                return true;
            }
        }
        
        return false;
    }
}