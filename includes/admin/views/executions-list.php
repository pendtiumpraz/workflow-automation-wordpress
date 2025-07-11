<?php
/**
 * Executions List View
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

// Get filter parameters
$workflow_id = isset($_GET['workflow_id']) ? intval($_GET['workflow_id']) : 0;
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$per_page = 20;

// Get executions
$execution_model = new Execution_Model();
$filters = array();
if ($workflow_id) {
    $filters['workflow_id'] = $workflow_id;
}
if ($status) {
    $filters['status'] = $status;
}

// Get total count
global $wpdb;
$table_name = $wpdb->prefix . 'wa_executions';
$where_sql = '1=1';
if ($workflow_id) {
    $where_sql .= $wpdb->prepare(' AND workflow_id = %d', $workflow_id);
}
if ($status) {
    $where_sql .= $wpdb->prepare(' AND status = %s', $status);
}
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}");

// Get executions for current page
$offset = ($page - 1) * $per_page;
$sql = $wpdb->prepare(
    "SELECT e.*, w.name as workflow_name 
     FROM {$table_name} e
     LEFT JOIN {$wpdb->prefix}wa_workflows w ON e.workflow_id = w.id
     WHERE {$where_sql}
     ORDER BY e.created_at DESC
     LIMIT %d OFFSET %d",
    $per_page,
    $offset
);
$executions = $wpdb->get_results($sql);

// Get all workflows for filter dropdown
$workflow_model = new Workflow_Model();
$workflows = $workflow_model->get_all();
?>

<div class="wrap">
    <h1><?php _e('Workflow Executions', 'workflow-automation'); ?></h1>
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="workflow-automation-executions">
                
                <select name="workflow_id" id="filter-workflow">
                    <option value=""><?php _e('All Workflows', 'workflow-automation'); ?></option>
                    <?php foreach ($workflows as $workflow) : ?>
                        <option value="<?php echo esc_attr($workflow->id); ?>" 
                                <?php selected($workflow_id, $workflow->id); ?>>
                            <?php echo esc_html($workflow->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" id="filter-status">
                    <option value=""><?php _e('All Statuses', 'workflow-automation'); ?></option>
                    <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'workflow-automation'); ?></option>
                    <option value="running" <?php selected($status, 'running'); ?>><?php _e('Running', 'workflow-automation'); ?></option>
                    <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('Completed', 'workflow-automation'); ?></option>
                    <option value="failed" <?php selected($status, 'failed'); ?>><?php _e('Failed', 'workflow-automation'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'workflow-automation'); ?>">
            </form>
        </div>
        
        <div class="tablenav-pages">
            <?php
            $total_pages = ceil($total_items / $per_page);
            $pagination_args = array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $page
            );
            
            echo paginate_links($pagination_args);
            ?>
        </div>
    </div>
    
    <?php if (empty($executions)) : ?>
        <div class="wa-empty-state">
            <h2><?php _e('No executions found', 'workflow-automation'); ?></h2>
            <p><?php _e('Workflow executions will appear here when workflows are triggered.', 'workflow-automation'); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php _e('ID', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-workflow"><?php _e('Workflow', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-trigger"><?php _e('Trigger', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-duration"><?php _e('Duration', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-started"><?php _e('Started', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'workflow-automation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($executions as $execution) : ?>
                    <?php
                    // Calculate duration
                    $duration = '';
                    if ($execution->started_at && $execution->completed_at) {
                        $start = strtotime($execution->started_at);
                        $end = strtotime($execution->completed_at);
                        $diff = $end - $start;
                        if ($diff < 60) {
                            $duration = sprintf(_n('%d second', '%d seconds', $diff, 'workflow-automation'), $diff);
                        } else {
                            $minutes = floor($diff / 60);
                            $seconds = $diff % 60;
                            $duration = sprintf(_n('%d minute', '%d minutes', $minutes, 'workflow-automation'), $minutes);
                            if ($seconds > 0) {
                                $duration .= ' ' . sprintf(_n('%d second', '%d seconds', $seconds, 'workflow-automation'), $seconds);
                            }
                        }
                    } elseif ($execution->started_at && $execution->status === 'running') {
                        $duration = __('Running...', 'workflow-automation');
                    }
                    
                    // Parse trigger data
                    $trigger_data = json_decode($execution->trigger_data, true);
                    $trigger_type = $execution->trigger_type ?: __('Manual', 'workflow-automation');
                    ?>
                    <tr>
                        <td class="column-id">
                            <strong>#<?php echo esc_html($execution->id); ?></strong>
                        </td>
                        <td class="column-workflow">
                            <a href="<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow=' . $execution->workflow_id); ?>">
                                <?php echo esc_html($execution->workflow_name); ?>
                            </a>
                        </td>
                        <td class="column-trigger">
                            <?php echo esc_html(ucfirst($trigger_type)); ?>
                            <?php if ($trigger_type === 'webhook' && isset($trigger_data['webhook_key'])) : ?>
                                <br><small><?php echo esc_html(substr($trigger_data['webhook_key'], 0, 8)) . '...'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="column-status">
                            <span class="wa-execution-status wa-status-<?php echo esc_attr($execution->status); ?>">
                                <?php echo esc_html(ucfirst($execution->status)); ?>
                            </span>
                            <?php if ($execution->status === 'failed' && !empty($execution->error_message)) : ?>
                                <span class="dashicons dashicons-info wa-error-info" 
                                      title="<?php echo esc_attr($execution->error_message); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-duration">
                            <?php echo esc_html($duration ?: '-'); ?>
                        </td>
                        <td class="column-started">
                            <?php if ($execution->started_at) : ?>
                                <?php echo human_time_diff(strtotime($execution->started_at), current_time('timestamp')); ?> ago
                                <br><small><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($execution->started_at)); ?></small>
                            <?php else : ?>
                                <span class="description"><?php _e('Not started', 'workflow-automation'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small wa-view-execution" 
                                    data-execution-id="<?php echo esc_attr($execution->id); ?>"
                                    title="<?php esc_attr_e('View details', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            <?php if ($execution->status === 'failed' || $execution->status === 'completed') : ?>
                                <button type="button" class="button button-small wa-retry-execution" 
                                        data-execution-id="<?php echo esc_attr($execution->id); ?>"
                                        data-workflow-id="<?php echo esc_attr($execution->workflow_id); ?>"
                                        title="<?php esc_attr_e('Retry execution', 'workflow-automation'); ?>">
                                    <span class="dashicons dashicons-controls-repeat"></span>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <!-- Bottom pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php echo paginate_links($pagination_args); ?>
        </div>
    </div>
</div>

<!-- Execution Details Modal -->
<div id="wa-execution-modal" class="wa-modal" style="display: none;">
    <div class="wa-modal-content wa-modal-large">
        <div class="wa-modal-header">
            <h2><?php _e('Execution Details', 'workflow-automation'); ?></h2>
            <button type="button" class="wa-modal-close">&times;</button>
        </div>
        
        <div class="wa-modal-body">
            <div id="wa-execution-details">
                <!-- Details will be loaded here -->
            </div>
        </div>
        
        <div class="wa-modal-footer">
            <button type="button" class="button wa-modal-close">
                <?php _e('Close', 'workflow-automation'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.wa-empty-state {
    margin: 50px auto;
    text-align: center;
    max-width: 500px;
}

.wa-empty-state h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.wa-empty-state p {
    font-size: 16px;
    color: #666;
}

.wa-execution-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.wa-status-pending {
    background: #f0f0f1;
    color: #50575e;
}

.wa-status-running {
    background: #e1f3fc;
    color: #0073aa;
}

.wa-status-completed {
    background: #d4edda;
    color: #155724;
}

.wa-status-failed {
    background: #f8d7da;
    color: #721c24;
}

.wa-error-info {
    color: #dc3232;
    cursor: help;
    margin-left: 5px;
}

.column-id {
    width: 60px;
}

.column-trigger,
.column-status,
.column-duration {
    width: 100px;
}

.column-started {
    width: 150px;
}

.column-actions {
    width: 100px;
}

.column-actions .button {
    margin-right: 5px;
}

.column-actions .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    margin-top: 2px;
}

/* Modal Styles */
.wa-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wa-modal-content {
    background: #fff;
    border-radius: 4px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: auto;
}

.wa-modal-large {
    max-width: 900px;
}

.wa-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e1e1e1;
}

.wa-modal-header h2 {
    margin: 0;
}

.wa-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
}

.wa-modal-body {
    padding: 20px;
}

.wa-modal-footer {
    padding: 20px;
    border-top: 1px solid #e1e1e1;
    text-align: right;
}

/* Execution details */
.wa-execution-info {
    margin-bottom: 20px;
}

.wa-execution-info h3 {
    margin-top: 20px;
    margin-bottom: 10px;
}

.wa-execution-info table {
    width: 100%;
    border-collapse: collapse;
}

.wa-execution-info th {
    text-align: left;
    padding: 8px;
    background: #f1f1f1;
    font-weight: 600;
}

.wa-execution-info td {
    padding: 8px;
    border-bottom: 1px solid #e1e1e1;
}

.wa-execution-logs {
    background: #f1f1f1;
    padding: 10px;
    border-radius: 3px;
    max-height: 300px;
    overflow-y: auto;
}

.wa-log-entry {
    margin-bottom: 5px;
    font-family: monospace;
    font-size: 12px;
}

.wa-log-info {
    color: #0073aa;
}

.wa-log-warning {
    color: #f56e28;
}

.wa-log-error {
    color: #dc3232;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View execution details
    $('.wa-view-execution').on('click', function() {
        var executionId = $(this).data('execution-id');
        
        $('#wa-execution-details').html('<p><?php esc_attr_e('Loading...', 'workflow-automation'); ?></p>');
        $('#wa-execution-modal').show();
        
        // Load execution details via API
        $.ajax({
            url: wa_admin.api_url + '/executions/' + executionId,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wa_admin.nonce);
            },
            success: function(response) {
                displayExecutionDetails(response);
            },
            error: function() {
                $('#wa-execution-details').html('<p class="error"><?php esc_attr_e('Failed to load execution details.', 'workflow-automation'); ?></p>');
            }
        });
    });
    
    // Close modal
    $('.wa-modal-close').on('click', function() {
        $('#wa-execution-modal').hide();
    });
    
    // Retry execution
    $('.wa-retry-execution').on('click', function() {
        if (!confirm('<?php esc_attr_e('Are you sure you want to retry this execution?', 'workflow-automation'); ?>')) {
            return;
        }
        
        var $button = $(this);
        var workflowId = $button.data('workflow-id');
        var executionId = $button.data('execution-id');
        
        $button.prop('disabled', true);
        
        // Get original trigger data and create new execution
        $.ajax({
            url: wa_admin.api_url + '/executions/' + executionId,
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wa_admin.nonce);
            },
            success: function(execution) {
                // Create new execution with same trigger data
                $.ajax({
                    url: wa_admin.api_url + '/workflows/' + workflowId + '/execute',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', wa_admin.nonce);
                    },
                    data: JSON.stringify({
                        trigger_type: execution.trigger_type,
                        trigger_data: execution.trigger_data
                    }),
                    contentType: 'application/json',
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        alert('<?php esc_attr_e('Failed to retry execution.', 'workflow-automation'); ?>');
                        $button.prop('disabled', false);
                    }
                });
            }
        });
    });
    
    function displayExecutionDetails(execution) {
        var html = '<div class="wa-execution-info">';
        
        // Basic info
        html += '<h3><?php esc_attr_e('Execution Information', 'workflow-automation'); ?></h3>';
        html += '<table>';
        html += '<tr><th><?php esc_attr_e('ID', 'workflow-automation'); ?></th><td>#' + execution.id + '</td></tr>';
        html += '<tr><th><?php esc_attr_e('Workflow', 'workflow-automation'); ?></th><td>' + execution.workflow_name + '</td></tr>';
        html += '<tr><th><?php esc_attr_e('Status', 'workflow-automation'); ?></th><td><span class="wa-execution-status wa-status-' + execution.status + '">' + execution.status + '</span></td></tr>';
        html += '<tr><th><?php esc_attr_e('Trigger Type', 'workflow-automation'); ?></th><td>' + (execution.trigger_type || 'manual') + '</td></tr>';
        html += '<tr><th><?php esc_attr_e('Created', 'workflow-automation'); ?></th><td>' + execution.created_at + '</td></tr>';
        html += '<tr><th><?php esc_attr_e('Started', 'workflow-automation'); ?></th><td>' + (execution.started_at || '-') + '</td></tr>';
        html += '<tr><th><?php esc_attr_e('Completed', 'workflow-automation'); ?></th><td>' + (execution.completed_at || '-') + '</td></tr>';
        html += '</table>';
        
        // Error message
        if (execution.error_message) {
            html += '<h3><?php esc_attr_e('Error Message', 'workflow-automation'); ?></h3>';
            html += '<div class="wa-execution-logs"><pre>' + escapeHtml(execution.error_message) + '</pre></div>';
        }
        
        // Trigger data
        if (execution.trigger_data) {
            html += '<h3><?php esc_attr_e('Trigger Data', 'workflow-automation'); ?></h3>';
            html += '<div class="wa-execution-logs"><pre>' + JSON.stringify(execution.trigger_data, null, 2) + '</pre></div>';
        }
        
        // Execution data
        if (execution.execution_data) {
            html += '<h3><?php esc_attr_e('Execution Data', 'workflow-automation'); ?></h3>';
            html += '<div class="wa-execution-logs"><pre>' + JSON.stringify(execution.execution_data, null, 2) + '</pre></div>';
        }
        
        // Logs
        if (execution.logs && execution.logs.length > 0) {
            html += '<h3><?php esc_attr_e('Execution Logs', 'workflow-automation'); ?></h3>';
            html += '<div class="wa-execution-logs">';
            execution.logs.forEach(function(log) {
                html += '<div class="wa-log-entry wa-log-' + log.level + '">';
                html += '[' + log.timestamp + '] [' + log.node_id + '] ' + escapeHtml(log.message);
                html += '</div>';
            });
            html += '</div>';
        }
        
        html += '</div>';
        
        $('#wa-execution-details').html(html);
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>