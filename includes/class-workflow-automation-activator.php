<?php
/**
 * Fired during plugin activation
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Automation_Activator {

    /**
     * Activate the plugin.
     *
     * Create database tables and set default options.
     *
     * @since    1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::create_default_options();
        self::create_rewrite_rules();
        
        // Schedule cron events
        if (!wp_next_scheduled('wa_cleanup_old_executions')) {
            wp_schedule_event(time(), 'daily', 'wa_cleanup_old_executions');
        }
    }

    /**
     * Create database tables
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Workflows table
        $workflows_table = $wpdb->prefix . 'wa_workflows';
        $sql_workflows = "CREATE TABLE $workflows_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            flow_data LONGTEXT,
            status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
            created_by BIGINT UNSIGNED,
            created_at DATETIME,
            updated_at DATETIME,
            INDEX idx_status (status),
            INDEX idx_created_by (created_by)
        ) $charset_collate;";
        
        // Nodes registry table
        $nodes_table = $wpdb->prefix . 'wa_nodes';
        $sql_nodes = "CREATE TABLE $nodes_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            workflow_id BIGINT UNSIGNED NOT NULL,
            node_id VARCHAR(100) NOT NULL,
            node_type VARCHAR(50) NOT NULL,
            settings LONGTEXT,
            position_x INT,
            position_y INT,
            INDEX idx_workflow (workflow_id),
            UNIQUE KEY workflow_node (workflow_id, node_id)
        ) $charset_collate;";
        
        // Executions table
        $executions_table = $wpdb->prefix . 'wa_executions';
        $sql_executions = "CREATE TABLE $executions_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            workflow_id BIGINT UNSIGNED NOT NULL,
            status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
            trigger_type VARCHAR(50),
            trigger_data LONGTEXT,
            execution_data LONGTEXT,
            started_at DATETIME,
            completed_at DATETIME,
            error_message TEXT,
            INDEX idx_workflow_status (workflow_id, status),
            INDEX idx_started (started_at)
        ) $charset_collate;";
        
        // Webhook settings table
        $webhooks_table = $wpdb->prefix . 'wa_webhooks';
        $sql_webhooks = "CREATE TABLE $webhooks_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            workflow_id BIGINT UNSIGNED NOT NULL,
            node_id VARCHAR(100) NOT NULL,
            webhook_key VARCHAR(64) UNIQUE,
            settings LONGTEXT,
            created_at DATETIME,
            INDEX idx_workflow (workflow_id),
            INDEX idx_webhook_key (webhook_key)
        ) $charset_collate;";
        
        // Integration settings table
        $integrations_table = $wpdb->prefix . 'wa_integration_settings';
        $sql_integrations = "CREATE TABLE $integrations_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            integration_type VARCHAR(50) NOT NULL,
            organization_id BIGINT UNSIGNED,
            name VARCHAR(255) NOT NULL,
            settings LONGTEXT,
            is_active BOOLEAN DEFAULT true,
            created_by BIGINT UNSIGNED,
            created_at DATETIME,
            updated_at DATETIME,
            UNIQUE KEY integration_org (integration_type, organization_id, name),
            INDEX idx_type (integration_type),
            INDEX idx_org (organization_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_workflows);
        dbDelta($sql_nodes);
        dbDelta($sql_executions);
        dbDelta($sql_webhooks);
        dbDelta($sql_integrations);
        
        // Set default timestamps for MySQL 5.7+ compatibility
        $wpdb->query("ALTER TABLE $workflows_table 
                      MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                      MODIFY updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                      
        $wpdb->query("ALTER TABLE $webhooks_table 
                      MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
                      
        $wpdb->query("ALTER TABLE $integrations_table 
                      MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                      MODIFY updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    /**
     * Create default options
     *
     * @since    1.0.0
     */
    private static function create_default_options() {
        add_option('wa_version', WA_VERSION);
        add_option('wa_settings', array(
            'enable_debug' => false,
            'execution_timeout' => 300, // 5 minutes
            'max_execution_retries' => 3,
            'cleanup_after_days' => 30,
            'enable_webhook_logging' => true,
        ));
    }

    /**
     * Create rewrite rules for webhooks
     *
     * @since    1.0.0
     */
    private static function create_rewrite_rules() {
        // Load webhook handler to register its rewrite rules
        require_once WA_PLUGIN_DIR . 'includes/class-webhook-handler.php';
        $webhook_handler = new Webhook_Handler();
        $webhook_handler->add_rewrite_rules();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}