<?php
/**
 * Plugin Name: Workflow Automation
 * Plugin URI: https://github.com/wordpress-opsguide/workflow-automation
 * Description: A comprehensive workflow automation plugin for WordPress with visual builder and service integrations
 * Version: 1.0.0
 * Author: OpsGuide Team
 * Author URI: https://opsguide.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: workflow-automation
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Tested up to: 6.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WA_VERSION', '1.0.0');
define('WA_PLUGIN_FILE', __FILE__);
define('WA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WA_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-workflow-automation-activator.php
 */
function activate_workflow_automation() {
    require_once WA_PLUGIN_DIR . 'includes/class-workflow-automation-activator.php';
    Workflow_Automation_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-workflow-automation-deactivator.php
 */
function deactivate_workflow_automation() {
    require_once WA_PLUGIN_DIR . 'includes/class-workflow-automation-deactivator.php';
    Workflow_Automation_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_workflow_automation');
register_deactivation_hook(__FILE__, 'deactivate_workflow_automation');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require WA_PLUGIN_DIR . 'includes/class-workflow-automation.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_workflow_automation() {
    $plugin = new Workflow_Automation();
    $plugin->run();
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.4', '>=')) {
    run_workflow_automation();
    
    // Include emergency API - guaranteed to work
    include_once WA_PLUGIN_DIR . 'emergency-api.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Workflow Automation requires PHP 7.4 or higher. Your current PHP version is ', 'workflow-automation') . PHP_VERSION;
        echo '</p></div>';
    });
}