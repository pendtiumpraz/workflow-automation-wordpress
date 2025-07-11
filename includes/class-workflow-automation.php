<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Automation {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Workflow_Automation_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WA_VERSION')) {
            $this->version = WA_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'workflow-automation';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_api_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // Load helper functions
        require_once WA_PLUGIN_DIR . 'includes/functions.php';
        
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once WA_PLUGIN_DIR . 'includes/class-workflow-automation-loader.php';

        // The class responsible for defining internationalization functionality
        require_once WA_PLUGIN_DIR . 'includes/class-workflow-automation-i18n.php';

        // Load admin classes
        require_once WA_PLUGIN_DIR . 'includes/admin/class-workflow-admin.php';

        // Load API classes
        require_once WA_PLUGIN_DIR . 'includes/api/class-workflow-api.php';
        require_once WA_PLUGIN_DIR . 'includes/api/class-webhook-api.php';
        require_once WA_PLUGIN_DIR . 'includes/api/class-execution-api.php';
        require_once WA_PLUGIN_DIR . 'includes/api/class-integration-api.php';
        require_once WA_PLUGIN_DIR . 'includes/api/class-node-api.php';

        // Load model classes
        require_once WA_PLUGIN_DIR . 'includes/models/class-workflow-model.php';
        require_once WA_PLUGIN_DIR . 'includes/models/class-node-model.php';
        require_once WA_PLUGIN_DIR . 'includes/models/class-node-connection-model.php';
        require_once WA_PLUGIN_DIR . 'includes/models/class-execution-model.php';
        require_once WA_PLUGIN_DIR . 'includes/models/class-webhook-model.php';
        require_once WA_PLUGIN_DIR . 'includes/models/class-integration-settings-model.php';

        // Load workflow executor
        require_once WA_PLUGIN_DIR . 'includes/class-workflow-executor.php';
        
        // Load error handler
        require_once WA_PLUGIN_DIR . 'includes/class-workflow-error-handler.php';
        
        // Load icon manager
        require_once WA_PLUGIN_DIR . 'includes/class-node-icons.php';
        
        // Load webhook handler
        require_once WA_PLUGIN_DIR . 'includes/class-webhook-handler.php';
        
        // Load workflow templates
        require_once WA_PLUGIN_DIR . 'includes/class-workflow-templates.php';

        $this->loader = new Workflow_Automation_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Workflow_Automation_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Workflow_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Initialize webhook handler
        $webhook_handler = new Webhook_Handler();
        
        // Add cron hooks
        $this->loader->add_action('wa_execute_workflow', $this, 'execute_workflow_cron');
        $this->loader->add_action('wa_cleanup_old_executions', $this, 'cleanup_old_executions');
    }

    /**
     * Register all of the hooks related to the API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_api_hooks() {
        // Workflow API
        $workflow_api = new Workflow_API();
        $this->loader->add_action('rest_api_init', $workflow_api, 'register_routes');

        // Webhook API
        $webhook_api = new Webhook_API();
        $this->loader->add_action('rest_api_init', $webhook_api, 'register_routes');

        // Execution API
        $execution_api = new Execution_API();
        $this->loader->add_action('rest_api_init', $execution_api, 'register_routes');

        // Integration API
        $integration_api = new Integration_API();
        $this->loader->add_action('rest_api_init', $integration_api, 'register_routes');
        
        // Node API
        $node_api = new Node_API();
        $this->loader->add_action('rest_api_init', $node_api, 'register_routes');
    }


    /**
     * Execute workflow via cron
     *
     * @since    1.0.0
     * @param    int    $execution_id    The execution ID to process
     */
    public function execute_workflow_cron($execution_id) {
        $executor = new Workflow_Executor();
        $executor->execute($execution_id);
    }

    /**
     * Clean up old execution records
     *
     * @since    1.0.0
     */
    public function cleanup_old_executions() {
        $settings = get_option('wa_settings', array());
        $days = isset($settings['cleanup_after_days']) ? $settings['cleanup_after_days'] : 30;
        
        $execution_model = new Execution_Model();
        $execution_model->cleanup_old_executions($days);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Workflow_Automation_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}