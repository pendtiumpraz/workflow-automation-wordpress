<?php
/**
 * Plugin Functions
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Get available node types
 *
 * @since    1.0.0
 * @return   array
 */
function wa_get_available_nodes() {
    $nodes = array(
        'triggers' => array(
            'webhook_start' => array(
                'label' => __('Webhook Trigger', 'workflow-automation'),
                'description' => __('Start workflow when webhook is received', 'workflow-automation'),
                'icon' => 'dashicons-admin-links',
                'color' => '#4CAF50'
            ),
            'schedule_start' => array(
                'label' => __('Schedule Trigger', 'workflow-automation'),
                'description' => __('Start workflow on a schedule', 'workflow-automation'),
                'icon' => 'dashicons-clock',
                'color' => '#2196F3'
            ),
            'manual_start' => array(
                'label' => __('Manual Trigger', 'workflow-automation'),
                'description' => __('Start workflow manually', 'workflow-automation'),
                'icon' => 'dashicons-admin-users',
                'color' => '#9C27B0'
            )
        ),
        'actions' => array(
            'email' => array(
                'label' => __('Send Email', 'workflow-automation'),
                'description' => __('Send an email notification', 'workflow-automation'),
                'icon' => 'dashicons-email',
                'color' => '#FF9800'
            ),
            'slack' => array(
                'label' => __('Slack Message', 'workflow-automation'),
                'description' => __('Send a message to Slack', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'color' => '#4A154B'
            ),
            'line' => array(
                'label' => __('LINE Message', 'workflow-automation'),
                'description' => __('Send a message via LINE', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'color' => '#00C300'
            ),
            'telegram' => array(
                'label' => __('Telegram Message', 'workflow-automation'),
                'description' => __('Send a message via Telegram', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'color' => '#0088CC'
            ),
            'whatsapp' => array(
                'label' => __('WhatsApp Message', 'workflow-automation'),
                'description' => __('Send a message via WhatsApp', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'color' => '#25D366'
            ),
            'google_sheets' => array(
                'label' => __('Google Sheets', 'workflow-automation'),
                'description' => __('Add or update data in Google Sheets', 'workflow-automation'),
                'icon' => 'dashicons-media-spreadsheet',
                'color' => '#0F9D58'
            ),
            'http' => array(
                'label' => __('HTTP Request', 'workflow-automation'),
                'description' => __('Make an HTTP request', 'workflow-automation'),
                'icon' => 'dashicons-admin-site',
                'color' => '#607D8B'
            )
        ),
        'ai' => array(
            'openai' => array(
                'label' => __('OpenAI', 'workflow-automation'),
                'description' => __('Use OpenAI GPT models', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'color' => '#00A67E'
            ),
            'claude' => array(
                'label' => __('Claude', 'workflow-automation'),
                'description' => __('Use Anthropic Claude', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'color' => '#8B6914'
            ),
            'gemini' => array(
                'label' => __('Google Gemini', 'workflow-automation'),
                'description' => __('Use Google Gemini AI', 'workflow-automation'),
                'icon' => 'dashicons-star-filled',
                'color' => '#4285F4'
            )
        ),
        'wordpress' => array(
            'wp_post' => array(
                'label' => __('WordPress Post', 'workflow-automation'),
                'description' => __('Create, update or get posts', 'workflow-automation'),
                'icon' => 'dashicons-admin-post',
                'color' => '#0073AA'
            ),
            'wp_user' => array(
                'label' => __('WordPress User', 'workflow-automation'),
                'description' => __('Manage WordPress users', 'workflow-automation'),
                'icon' => 'dashicons-admin-users',
                'color' => '#0073AA'
            ),
            'wp_media' => array(
                'label' => __('WordPress Media', 'workflow-automation'),
                'description' => __('Upload or manage media files', 'workflow-automation'),
                'icon' => 'dashicons-admin-media',
                'color' => '#0073AA'
            )
        ),
        'logic' => array(
            'filter' => array(
                'label' => __('Filter', 'workflow-automation'),
                'description' => __('Filter data based on conditions', 'workflow-automation'),
                'icon' => 'dashicons-filter',
                'color' => '#795548'
            ),
            'loop' => array(
                'label' => __('Loop', 'workflow-automation'),
                'description' => __('Loop through array data', 'workflow-automation'),
                'icon' => 'dashicons-controls-repeat',
                'color' => '#3F51B5'
            ),
            'delay' => array(
                'label' => __('Delay', 'workflow-automation'),
                'description' => __('Add a delay to the workflow', 'workflow-automation'),
                'icon' => 'dashicons-backup',
                'color' => '#009688'
            ),
            'code' => array(
                'label' => __('Custom Code', 'workflow-automation'),
                'description' => __('Execute custom PHP code', 'workflow-automation'),
                'icon' => 'dashicons-editor-code',
                'color' => '#E91E63'
            )
        )
    );
    
    return apply_filters('wa_available_nodes', $nodes);
}

/**
 * Get node type info
 *
 * @since    1.0.0
 * @param    string    $type    Node type
 * @return   array|null
 */
function wa_get_node_type_info($type) {
    $nodes = wa_get_available_nodes();
    
    foreach ($nodes as $category => $category_nodes) {
        if (isset($category_nodes[$type])) {
            return $category_nodes[$type];
        }
    }
    
    return null;
}

/**
 * Format execution status
 *
 * @since    1.0.0
 * @param    string    $status    Status
 * @return   string
 */
function wa_format_execution_status($status) {
    $statuses = array(
        'pending' => __('Pending', 'workflow-automation'),
        'running' => __('Running', 'workflow-automation'),
        'completed' => __('Completed', 'workflow-automation'),
        'failed' => __('Failed', 'workflow-automation'),
        'cancelled' => __('Cancelled', 'workflow-automation')
    );
    
    return isset($statuses[$status]) ? $statuses[$status] : ucfirst($status);
}

/**
 * Get workflow status badge
 *
 * @since    1.0.0
 * @param    string    $status    Status
 * @return   string
 */
function wa_get_status_badge($status) {
    $classes = array(
        'active' => 'wa-badge-success',
        'inactive' => 'wa-badge-warning',
        'draft' => 'wa-badge-default',
        'completed' => 'wa-badge-success',
        'failed' => 'wa-badge-danger',
        'running' => 'wa-badge-info',
        'pending' => 'wa-badge-secondary'
    );
    
    $class = isset($classes[$status]) ? $classes[$status] : 'wa-badge-default';
    $label = wa_format_execution_status($status);
    
    return sprintf('<span class="wa-badge %s">%s</span>', $class, $label);
}

/**
 * Check if plugin dependencies are met
 *
 * @since    1.0.0
 * @return   bool|array    True if met, array of errors if not
 */
function wa_check_dependencies() {
    $errors = array();
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $errors[] = sprintf(
            __('Workflow Automation requires PHP 7.4 or higher. Your current version is %s.', 'workflow-automation'),
            PHP_VERSION
        );
    }
    
    // Check WordPress version
    if (version_compare(get_bloginfo('version'), '5.6', '<')) {
        $errors[] = sprintf(
            __('Workflow Automation requires WordPress 5.6 or higher. Your current version is %s.', 'workflow-automation'),
            get_bloginfo('version')
        );
    }
    
    // Check if required PHP extensions are loaded
    $required_extensions = array('curl', 'json', 'openssl');
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = sprintf(
                __('Workflow Automation requires the PHP %s extension.', 'workflow-automation'),
                $ext
            );
        }
    }
    
    return empty($errors) ? true : $errors;
}