<?php
/**
 * Settings View
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/admin/views
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Handle form submission
if (isset($_POST['wa_save_settings']) && wp_verify_nonce($_POST['wa_settings_nonce'], 'wa_save_settings')) {
    $settings = array(
        'enable_debug' => isset($_POST['enable_debug']) ? 1 : 0,
        'enable_webhook_logging' => isset($_POST['enable_webhook_logging']) ? 1 : 0,
        'cleanup_after_days' => intval($_POST['cleanup_after_days']),
        'max_execution_time' => intval($_POST['max_execution_time']),
        'default_retry_count' => intval($_POST['default_retry_count']),
        'enable_notifications' => isset($_POST['enable_notifications']) ? 1 : 0,
        'notification_email' => sanitize_email($_POST['notification_email']),
        'enable_auto_save' => isset($_POST['enable_auto_save']) ? 1 : 0,
        'auto_save_interval' => intval($_POST['auto_save_interval'])
    );
    
    update_option('wa_settings', $settings);
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'workflow-automation') . '</p></div>';
}

// Get current settings
$settings = get_option('wa_settings', array(
    'enable_debug' => 0,
    'enable_webhook_logging' => 0,
    'cleanup_after_days' => 30,
    'max_execution_time' => 300,
    'default_retry_count' => 3,
    'enable_notifications' => 0,
    'notification_email' => get_option('admin_email'),
    'enable_auto_save' => 1,
    'auto_save_interval' => 2
));
?>

<div class="wrap">
    <h1><?php _e('Workflow Automation Settings', 'workflow-automation'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wa_save_settings', 'wa_settings_nonce'); ?>
        
        <h2 class="title"><?php _e('General Settings', 'workflow-automation'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Debug Mode', 'workflow-automation'); ?></th>
                    <td>
                        <label for="enable-debug">
                            <input type="checkbox" name="enable_debug" id="enable-debug" value="1" 
                                   <?php checked($settings['enable_debug'], 1); ?>>
                            <?php _e('Enable debug logging', 'workflow-automation'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Logs detailed information about workflow execution to the error log.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Webhook Logging', 'workflow-automation'); ?></th>
                    <td>
                        <label for="enable-webhook-logging">
                            <input type="checkbox" name="enable_webhook_logging" id="enable-webhook-logging" value="1" 
                                   <?php checked($settings['enable_webhook_logging'], 1); ?>>
                            <?php _e('Log webhook requests', 'workflow-automation'); ?>
                        </label>
                        <p class="description">
                            <?php _e('Logs incoming webhook requests for debugging purposes.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="cleanup-after-days"><?php _e('Cleanup Old Executions', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cleanup_after_days" id="cleanup-after-days" 
                               value="<?php echo esc_attr($settings['cleanup_after_days']); ?>" 
                               min="1" max="365" class="small-text">
                        <?php _e('days', 'workflow-automation'); ?>
                        <p class="description">
                            <?php _e('Automatically delete execution records older than this many days.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2 class="title"><?php _e('Execution Settings', 'workflow-automation'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="max-execution-time"><?php _e('Max Execution Time', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_execution_time" id="max-execution-time" 
                               value="<?php echo esc_attr($settings['max_execution_time']); ?>" 
                               min="30" max="900" class="small-text">
                        <?php _e('seconds', 'workflow-automation'); ?>
                        <p class="description">
                            <?php _e('Maximum time allowed for a single workflow execution.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default-retry-count"><?php _e('Default Retry Count', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="default_retry_count" id="default-retry-count" 
                               value="<?php echo esc_attr($settings['default_retry_count']); ?>" 
                               min="0" max="10" class="small-text">
                        <p class="description">
                            <?php _e('Default number of retries for failed nodes when retry is enabled.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2 class="title"><?php _e('Notification Settings', 'workflow-automation'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Email Notifications', 'workflow-automation'); ?></th>
                    <td>
                        <label for="enable-notifications">
                            <input type="checkbox" name="enable_notifications" id="enable-notifications" value="1" 
                                   <?php checked($settings['enable_notifications'], 1); ?>>
                            <?php _e('Send email notifications for workflow failures', 'workflow-automation'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="notification-email"><?php _e('Notification Email', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="notification_email" id="notification-email" 
                               value="<?php echo esc_attr($settings['notification_email']); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php _e('Email address to receive workflow failure notifications.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <h2 class="title"><?php _e('Editor Settings', 'workflow-automation'); ?></h2>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><?php _e('Auto-save', 'workflow-automation'); ?></th>
                    <td>
                        <label for="enable-auto-save">
                            <input type="checkbox" name="enable_auto_save" id="enable-auto-save" value="1" 
                                   <?php checked($settings['enable_auto_save'], 1); ?>>
                            <?php _e('Enable auto-save in workflow builder', 'workflow-automation'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="auto-save-interval"><?php _e('Auto-save Interval', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="auto_save_interval" id="auto-save-interval" 
                               value="<?php echo esc_attr($settings['auto_save_interval']); ?>" 
                               min="1" max="10" class="small-text">
                        <?php _e('seconds', 'workflow-automation'); ?>
                        <p class="description">
                            <?php _e('How often to auto-save workflow changes.', 'workflow-automation'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" name="wa_save_settings" class="button button-primary">
                <?php _e('Save Settings', 'workflow-automation'); ?>
            </button>
        </p>
    </form>
    
    <hr>
    
    <h2><?php _e('System Information', 'workflow-automation'); ?></h2>
    
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><?php _e('Plugin Version', 'workflow-automation'); ?></th>
                <td><?php echo WA_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('PHP Version', 'workflow-automation'); ?></th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('WordPress Version', 'workflow-automation'); ?></th>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Active Theme', 'workflow-automation'); ?></th>
                <td><?php echo wp_get_theme()->get('Name'); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('PHP Max Execution Time', 'workflow-automation'); ?></th>
                <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
            </tr>
            <tr>
                <th scope="row"><?php _e('PHP Memory Limit', 'workflow-automation'); ?></th>
                <td><?php echo ini_get('memory_limit'); ?></td>
            </tr>
        </tbody>
    </table>
    
    <hr>
    
    <h2><?php _e('Database Tables', 'workflow-automation'); ?></h2>
    
    <?php
    global $wpdb;
    $tables = array(
        'wa_workflows' => __('Workflows', 'workflow-automation'),
        'wa_nodes' => __('Nodes', 'workflow-automation'),
        'wa_node_connections' => __('Node Connections', 'workflow-automation'),
        'wa_executions' => __('Executions', 'workflow-automation'),
        'wa_execution_logs' => __('Execution Logs', 'workflow-automation'),
        'wa_webhooks' => __('Webhooks', 'workflow-automation'),
        'wa_integration_settings' => __('Integration Settings', 'workflow-automation')
    );
    ?>
    
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php _e('Table', 'workflow-automation'); ?></th>
                <th><?php _e('Status', 'workflow-automation'); ?></th>
                <th><?php _e('Records', 'workflow-automation'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $table => $label) : ?>
                <?php
                $table_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
                $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
                ?>
                <tr>
                    <td><?php echo esc_html($label); ?> (<?php echo esc_html($table_name); ?>)</td>
                    <td>
                        <?php if ($exists) : ?>
                            <span style="color: green;">✓ <?php _e('Exists', 'workflow-automation'); ?></span>
                        <?php else : ?>
                            <span style="color: red;">✗ <?php _e('Missing', 'workflow-automation'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo number_format_i18n($count); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>