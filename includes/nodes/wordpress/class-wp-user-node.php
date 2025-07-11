<?php
/**
 * WordPress User Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 */

/**
 * WordPress User node for creating/updating users
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Wp_User_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'wp_user';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('WordPress User', 'workflow-automation'),
            'category' => 'wordpress',
            'description' => __('Create, update or manage WordPress users', 'workflow-automation'),
            'icon' => 'wa-icon-wp-user',
            'color' => '#21759B',
            'inputs' => array(
                array(
                    'name' => 'user_data',
                    'type' => 'object',
                    'required' => false
                )
            ),
            'outputs' => array(
                array(
                    'name' => 'user_id',
                    'type' => 'number'
                ),
                array(
                    'name' => 'user',
                    'type' => 'object'
                )
            )
        );
    }
    
    /**
     * Get settings fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_settings_fields() {
        // Get available roles
        $roles = wp_roles()->get_names();
        
        $fields = array(
            array(
                'key' => 'action',
                'label' => __('Action', 'workflow-automation'),
                'type' => 'select',
                'default' => 'create',
                'required' => true,
                'options' => array(
                    'create' => __('Create New User', 'workflow-automation'),
                    'update' => __('Update Existing User', 'workflow-automation'),
                    'get' => __('Get User Info', 'workflow-automation'),
                    'delete' => __('Delete User', 'workflow-automation'),
                    'update_meta' => __('Update User Meta', 'workflow-automation')
                ),
                'description' => __('Choose the action to perform', 'workflow-automation')
            ),
            array(
                'key' => 'user_identifier',
                'label' => __('User Identifier', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.user_id}} or email/username', 'workflow-automation'),
                'description' => __('User ID, email, or username (required for update/get/delete)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('update', 'get', 'delete', 'update_meta')
                )
            ),
            array(
                'key' => 'username',
                'label' => __('Username', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.username}} or custom username', 'workflow-automation'),
                'description' => __('Username for the new user', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'create'
                )
            ),
            array(
                'key' => 'email',
                'label' => __('Email', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.email}} or email address', 'workflow-automation'),
                'description' => __('Email address', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'password',
                'label' => __('Password', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('Leave empty to auto-generate', 'workflow-automation'),
                'description' => __('User password (leave empty to auto-generate)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'first_name',
                'label' => __('First Name', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.first_name}}', 'workflow-automation'),
                'description' => __('User\'s first name', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'last_name',
                'label' => __('Last Name', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.last_name}}', 'workflow-automation'),
                'description' => __('User\'s last name', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'display_name',
                'label' => __('Display Name', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.display_name}}', 'workflow-automation'),
                'description' => __('Name to display publicly', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'role',
                'label' => __('Role', 'workflow-automation'),
                'type' => 'select',
                'default' => 'subscriber',
                'options' => $roles,
                'description' => __('User role', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'website',
                'label' => __('Website', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.website}}', 'workflow-automation'),
                'description' => __('User\'s website URL', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update')
                )
            ),
            array(
                'key' => 'user_meta',
                'label' => __('User Meta', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => __('meta_key1: value1' . "\n" . 'meta_key2: {{variable}}', 'workflow-automation'),
                'description' => __('User meta in format "key: value" (one per line)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('create', 'update', 'update_meta')
                )
            ),
            array(
                'key' => 'send_notification',
                'label' => __('Send Email Notification', 'workflow-automation'),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __('Yes', 'workflow-automation'),
                    'no' => __('No', 'workflow-automation')
                ),
                'description' => __('Send email notification to new user', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'create'
                )
            ),
            array(
                'key' => 'reassign_to',
                'label' => __('Reassign Content To', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('User ID to reassign content to', 'workflow-automation'),
                'description' => __('User ID to reassign deleted user\'s content to', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'delete'
                )
            )
        );
        
        return array_merge($fields, $this->get_error_handling_fields());
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
        try {
            $action = $this->get_setting('action', 'create');
            
            switch ($action) {
                case 'create':
                    return $this->create_user($context);
                    
                case 'update':
                    return $this->update_user($context);
                    
                case 'get':
                    return $this->get_user($context);
                    
                case 'delete':
                    return $this->delete_user($context);
                    
                case 'update_meta':
                    return $this->update_user_meta($context);
                    
                default:
                    throw new Exception(__('Invalid action specified', 'workflow-automation'));
            }
            
        } catch (Exception $e) {
            $this->log('WordPress User node error: ' . $e->getMessage(), 'error');
            
            // Handle error based on settings
            $error_handling = $this->get_setting('error_handling', 'stop');
            
            if ($error_handling === 'use_default') {
                $default_output = $this->get_setting('default_output', '{"success": false}');
                return json_decode($default_output, true);
            }
            
            throw $e;
        }
    }
    
    /**
     * Create a new user
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function create_user($context) {
        $username = $this->replace_variables($this->get_setting('username', ''), $context);
        $email = $this->replace_variables($this->get_setting('email', ''), $context);
        $password = $this->replace_variables($this->get_setting('password', ''), $context);
        
        if (empty($username)) {
            throw new Exception(__('Username is required', 'workflow-automation'));
        }
        
        if (empty($email)) {
            throw new Exception(__('Email is required', 'workflow-automation'));
        }
        
        // Generate password if not provided
        if (empty($password)) {
            $password = wp_generate_password();
        }
        
        // Prepare user data
        $userdata = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'role' => $this->get_setting('role', 'subscriber')
        );
        
        // Add optional fields
        $optional_fields = array('first_name', 'last_name', 'display_name', 'user_url' => 'website');
        foreach ($optional_fields as $key => $field) {
            if (is_string($key)) {
                $setting_key = $field;
                $data_key = $key;
            } else {
                $setting_key = $field;
                $data_key = $field;
            }
            
            $value = $this->replace_variables($this->get_setting($setting_key, ''), $context);
            if (!empty($value)) {
                $userdata[$data_key] = $value;
            }
        }
        
        // Create user
        $user_id = wp_insert_user($userdata);
        
        if (is_wp_error($user_id)) {
            throw new Exception($user_id->get_error_message());
        }
        
        // Handle user meta
        $this->handle_user_meta($user_id, $context);
        
        // Send notification
        if ($this->get_setting('send_notification', 'yes') === 'yes') {
            wp_new_user_notification($user_id, null, 'both');
        }
        
        $user = get_userdata($user_id);
        
        $this->log(sprintf('User created successfully. ID: %d, Username: %s', $user_id, $username));
        
        return array(
            'success' => true,
            'user_id' => $user_id,
            'user' => $this->format_user_data($user),
            'action' => 'created',
            'password_generated' => empty($this->get_setting('password', ''))
        );
    }
    
    /**
     * Update existing user
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function update_user($context) {
        $user = $this->get_user_by_identifier($context);
        
        $userdata = array(
            'ID' => $user->ID
        );
        
        // Update fields if provided
        $fields = array(
            'user_email' => 'email',
            'user_pass' => 'password',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'display_name' => 'display_name',
            'user_url' => 'website',
            'role' => 'role'
        );
        
        foreach ($fields as $data_key => $setting_key) {
            $value = $this->replace_variables($this->get_setting($setting_key, ''), $context);
            if (!empty($value)) {
                $userdata[$data_key] = $value;
            }
        }
        
        // Update user
        $user_id = wp_update_user($userdata);
        
        if (is_wp_error($user_id)) {
            throw new Exception($user_id->get_error_message());
        }
        
        // Handle user meta
        $this->handle_user_meta($user_id, $context);
        
        $user = get_userdata($user_id);
        
        $this->log(sprintf('User updated successfully. ID: %d, Username: %s', $user_id, $user->user_login));
        
        return array(
            'success' => true,
            'user_id' => $user_id,
            'user' => $this->format_user_data($user),
            'action' => 'updated'
        );
    }
    
    /**
     * Get user information
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function get_user($context) {
        $user = $this->get_user_by_identifier($context);
        
        $this->log(sprintf('User retrieved successfully. ID: %d, Username: %s', $user->ID, $user->user_login));
        
        return array(
            'success' => true,
            'user_id' => $user->ID,
            'user' => $this->format_user_data($user),
            'action' => 'retrieved'
        );
    }
    
    /**
     * Delete user
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function delete_user($context) {
        $user = $this->get_user_by_identifier($context);
        $reassign_to = $this->replace_variables($this->get_setting('reassign_to', ''), $context);
        
        $reassign = !empty($reassign_to) ? intval($reassign_to) : null;
        
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        
        if (!wp_delete_user($user->ID, $reassign)) {
            throw new Exception(__('Failed to delete user', 'workflow-automation'));
        }
        
        $this->log(sprintf('User deleted successfully. ID: %d, Username: %s', $user->ID, $user->user_login));
        
        return array(
            'success' => true,
            'user_id' => $user->ID,
            'username' => $user->user_login,
            'action' => 'deleted',
            'reassigned_to' => $reassign
        );
    }
    
    /**
     * Update user meta only
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function update_user_meta($context) {
        $user = $this->get_user_by_identifier($context);
        
        $this->handle_user_meta($user->ID, $context);
        
        $user = get_userdata($user->ID);
        
        $this->log(sprintf('User meta updated successfully. ID: %d, Username: %s', $user->ID, $user->user_login));
        
        return array(
            'success' => true,
            'user_id' => $user->ID,
            'user' => $this->format_user_data($user),
            'action' => 'meta_updated'
        );
    }
    
    /**
     * Get user by identifier
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   WP_User
     */
    private function get_user_by_identifier($context) {
        $identifier = $this->replace_variables($this->get_setting('user_identifier', ''), $context);
        
        if (empty($identifier)) {
            throw new Exception(__('User identifier is required', 'workflow-automation'));
        }
        
        // Try by ID first
        if (is_numeric($identifier)) {
            $user = get_userdata(intval($identifier));
            if ($user) {
                return $user;
            }
        }
        
        // Try by email
        $user = get_user_by('email', $identifier);
        if ($user) {
            return $user;
        }
        
        // Try by username
        $user = get_user_by('login', $identifier);
        if ($user) {
            return $user;
        }
        
        throw new Exception(sprintf(__('User not found: %s', 'workflow-automation'), $identifier));
    }
    
    /**
     * Handle user meta fields
     *
     * @since    1.0.0
     * @param    int      $user_id    The user ID
     * @param    array    $context    The execution context
     */
    private function handle_user_meta($user_id, $context) {
        $meta_fields = $this->get_setting('user_meta', '');
        if (empty($meta_fields)) {
            return;
        }
        
        $lines = explode("\n", $meta_fields);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = $this->replace_variables(trim($parts[1]), $context);
                update_user_meta($user_id, $key, $value);
            }
        }
    }
    
    /**
     * Format user data for output
     *
     * @since    1.0.0
     * @param    WP_User    $user    The user object
     * @return   array
     */
    private function format_user_data($user) {
        return array(
            'ID' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'user_registered' => $user->user_registered,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_url' => $user->user_url,
            'roles' => $user->roles,
            'capabilities' => $user->get_role_caps()
        );
    }
    
    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $action = $this->get_setting('action', 'create');
        
        if ($action === 'create') {
            if (empty($this->get_setting('username'))) {
                return new WP_Error(
                    'missing_username',
                    __('Username is required for creating a user', 'workflow-automation')
                );
            }
            
            if (empty($this->get_setting('email'))) {
                return new WP_Error(
                    'missing_email',
                    __('Email is required for creating a user', 'workflow-automation')
                );
            }
        } elseif (in_array($action, array('update', 'get', 'delete', 'update_meta'))) {
            if (empty($this->get_setting('user_identifier'))) {
                return new WP_Error(
                    'missing_identifier',
                    __('User identifier is required', 'workflow-automation')
                );
            }
        }
        
        return true;
    }
}