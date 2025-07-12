<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/admin
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of this plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        // Only load on our plugin pages
        if (strpos($screen->id, 'workflow-automation') !== false) {
            // Load modern admin CSS first
            wp_enqueue_style(
                $this->plugin_name . '-modern-admin',
                WA_PLUGIN_URL . 'assets/css/modern-admin.css',
                array(),
                $this->version,
                'all'
            );
            
            wp_enqueue_style(
                $this->plugin_name . '-admin',
                WA_PLUGIN_URL . 'assets/css/workflow-admin.css',
                array($this->plugin_name . '-modern-admin'),
                $this->version,
                'all'
            );
            
            // Enqueue icon styles
            wp_enqueue_style(
                $this->plugin_name . '-icons',
                WA_PLUGIN_URL . 'admin/css/workflow-icons.css',
                array(),
                $this->version,
                'all'
            );
            
            // Enqueue workflow builder styles if on builder page
            if (strpos($screen->id, 'workflow-builder') !== false) {
                wp_enqueue_style(
                    $this->plugin_name . '-builder',
                    WA_PLUGIN_URL . 'assets/css/workflow-builder.css',
                    array($this->plugin_name . '-modern-admin'),
                    $this->version,
                    'all'
                );
                
                // Enqueue enhanced builder styles
                wp_enqueue_style(
                    $this->plugin_name . '-builder-enhanced',
                    WA_PLUGIN_URL . 'assets/css/workflow-builder-enhanced.css',
                    array($this->plugin_name . '-builder'),
                    $this->version,
                    'all'
                );
                
                // Enqueue modern builder styles with higher priority
                wp_enqueue_style(
                    $this->plugin_name . '-builder-modern',
                    WA_PLUGIN_URL . 'assets/css/workflow-builder-modern.css',
                    array($this->plugin_name . '-builder-enhanced'),
                    $this->version,
                    'all'
                );
                
                // Enqueue force styles with maximum priority
                wp_enqueue_style(
                    $this->plugin_name . '-builder-force',
                    WA_PLUGIN_URL . 'assets/css/workflow-builder-force.css',
                    array($this->plugin_name . '-builder-modern'),
                    $this->version . '-' . time(), // Force cache bust
                    'all'
                );
                
                // Enqueue node visibility fix
                wp_enqueue_style(
                    $this->plugin_name . '-node-visibility-fix',
                    WA_PLUGIN_URL . 'assets/css/node-visibility-fix.css',
                    array($this->plugin_name . '-builder-force'),
                    $this->version . '-' . time(),
                    'all'
                );
            }
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        // Only load on our plugin pages
        if (strpos($screen->id, 'workflow-automation') !== false) {
            // Enqueue WordPress dependencies
            wp_enqueue_script('wp-api');
            wp_enqueue_script('wp-i18n');
            
            // Enqueue admin scripts
            wp_enqueue_script(
                $this->plugin_name . '-admin',
                WA_PLUGIN_URL . 'assets/js/workflow-admin.js',
                array('jquery', 'wp-api'),
                $this->version,
                true
            );
            
            // Localize script
            wp_localize_script(
                $this->plugin_name . '-admin',
                'wa_admin',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'api_url' => home_url('/wp-json/wa/v1'),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'i18n' => array(
                        'confirm_delete' => __('Are you sure you want to delete this workflow?', 'workflow-automation'),
                        'delete_failed' => __('Failed to delete workflow', 'workflow-automation'),
                        'save_success' => __('Workflow saved successfully', 'workflow-automation'),
                        'save_failed' => __('Failed to save workflow', 'workflow-automation'),
                    )
                )
            );
            
            // Enqueue workflow builder scripts if on builder page
            if (strpos($screen->id, 'workflow-builder') !== false || 
                (isset($_GET['page']) && $_GET['page'] === 'workflow-automation-builder')) {
                // jQuery UI for drag and drop
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('jquery-ui-droppable');
                wp_enqueue_script('jquery-ui-sortable');
                
                // jQuery UI styles
                wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
                
                // Workflow builder script
                wp_enqueue_script(
                    $this->plugin_name . '-builder',
                    WA_PLUGIN_URL . 'assets/js/workflow-builder.js',
                    array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-api'),
                    $this->version,
                    true
                );
                
                // Connection helper script
                wp_enqueue_script(
                    $this->plugin_name . '-connection-helper',
                    WA_PLUGIN_URL . 'assets/js/workflow-connection-helper.js',
                    array($this->plugin_name . '-builder'),
                    $this->version,
                    true
                );
                
                // Style cleaner script
                wp_enqueue_script(
                    $this->plugin_name . '-style-cleaner',
                    WA_PLUGIN_URL . 'assets/js/style-cleaner.js',
                    array('jquery'),
                    $this->version . '-' . time(), // Force cache bust
                    false // Load in head for immediate effect
                );
                
                // Node connections script
                wp_enqueue_script(
                    $this->plugin_name . '-node-connections',
                    WA_PLUGIN_URL . 'assets/js/node-connections.js',
                    array('jquery', $this->plugin_name . '-builder'),
                    $this->version . '-' . time(),
                    true
                );
                
                // Node debug script
                wp_enqueue_script(
                    $this->plugin_name . '-node-debug',
                    WA_PLUGIN_URL . 'assets/js/node-debug.js',
                    array('jquery', $this->plugin_name . '-builder'),
                    $this->version . '-' . time(),
                    true
                );
                
                // Workflow fixes script
                wp_enqueue_script(
                    $this->plugin_name . '-workflow-fixes',
                    WA_PLUGIN_URL . 'assets/js/workflow-fixes.js',
                    array('jquery', $this->plugin_name . '-builder'),
                    $this->version . '-' . time(),
                    true
                );
                
                // Localize builder script
                wp_localize_script(
                    $this->plugin_name . '-builder',
                    'wa_builder',
                    array(
                        'api_url' => home_url('/wp-json/wa/v1'),
                        'nonce' => wp_create_nonce('wp_rest'),
                        'workflow_id' => isset($_GET['workflow']) ? intval($_GET['workflow']) : 0,
                        'node_types' => $this->get_available_node_types(),
                        'auto_save' => true,
                        'auto_save_interval' => 2,
                        'i18n' => array(
                            'save' => __('Save', 'workflow-automation'),
                            'saving' => __('Saving...', 'workflow-automation'),
                            'saved' => __('Saved', 'workflow-automation'),
                            'active' => __('Active', 'workflow-automation'),
                            'inactive' => __('Inactive', 'workflow-automation'),
                            'unsaved_changes' => __('You have unsaved changes. Are you sure you want to leave?', 'workflow-automation'),
                            'save_failed' => __('Failed to save workflow', 'workflow-automation'),
                        )
                    )
                );
            }
        }
    }

    /**
     * Add plugin admin menu
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __('Workflow Automation', 'workflow-automation'),
            __('Workflows', 'workflow-automation'),
            'manage_options',
            'workflow-automation',
            array($this, 'display_workflows_page'),
            'dashicons-randomize',
            30
        );
        
        // Workflows submenu (same as main)
        add_submenu_page(
            'workflow-automation',
            __('All Workflows', 'workflow-automation'),
            __('All Workflows', 'workflow-automation'),
            'manage_options',
            'workflow-automation',
            array($this, 'display_workflows_page')
        );
        
        // Add New Workflow
        add_submenu_page(
            'workflow-automation',
            __('Add New Workflow', 'workflow-automation'),
            __('Add New', 'workflow-automation'),
            'manage_options',
            'workflow-automation-new',
            array($this, 'display_new_workflow_page')
        );
        
        // Workflow Builder (hidden from menu)
        add_submenu_page(
            null,
            __('Workflow Builder', 'workflow-automation'),
            __('Workflow Builder', 'workflow-automation'),
            'manage_options',
            'workflow-automation-builder',
            array($this, 'display_workflow_builder_page')
        );
        
        // Executions
        add_submenu_page(
            'workflow-automation',
            __('Executions', 'workflow-automation'),
            __('Executions', 'workflow-automation'),
            'manage_options',
            'workflow-automation-executions',
            array($this, 'display_executions_page')
        );
        
        // Integrations
        add_submenu_page(
            'workflow-automation',
            __('Integrations', 'workflow-automation'),
            __('Integrations', 'workflow-automation'),
            'manage_options',
            'workflow-automation-integrations',
            array($this, 'display_integrations_page')
        );
        
        // Settings
        add_submenu_page(
            'workflow-automation',
            __('Settings', 'workflow-automation'),
            __('Settings', 'workflow-automation'),
            'manage_options',
            'workflow-automation-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display workflows list page
     *
     * @since    1.0.0
     */
    public function display_workflows_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/workflows-list.php';
    }

    /**
     * Display new workflow page
     *
     * @since    1.0.0
     */
    public function display_new_workflow_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/workflow-new.php';
    }

    /**
     * Display workflow builder page
     *
     * @since    1.0.0
     */
    public function display_workflow_builder_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/workflow-builder.php';
    }

    /**
     * Display executions page
     *
     * @since    1.0.0
     */
    public function display_executions_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/executions-list.php';
    }

    /**
     * Display integrations page
     *
     * @since    1.0.0
     */
    public function display_integrations_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/integrations.php';
    }

    /**
     * Display settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once WA_PLUGIN_DIR . 'includes/admin/views/settings.php';
    }

    /**
     * Get available node types
     *
     * @since    1.0.0
     * @return   array
     */
    private function get_available_node_types() {
        // Use the centralized function
        return wa_get_available_nodes();
    }
}