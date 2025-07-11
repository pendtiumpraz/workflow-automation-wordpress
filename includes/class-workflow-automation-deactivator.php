<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Automation_Deactivator {

    /**
     * Deactivate the plugin.
     *
     * Clear scheduled events and flush rewrite rules.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wa_cleanup_old_executions');
        wp_clear_scheduled_hook('wa_execute_workflow');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}