<?php
/**
 * Workflows List View
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
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Workflows', 'workflow-automation'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=workflow-automation-new'); ?>" class="page-title-action">
        <?php _e('Add New', 'workflow-automation'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php
    // Get workflows
    $workflow_model = new Workflow_Model();
    $workflows = $workflow_model->get_all();
    ?>
    
    <?php if (empty($workflows)) : ?>
        <div class="wa-empty-state">
            <h2><?php _e('No workflows yet', 'workflow-automation'); ?></h2>
            <p><?php _e('Create your first workflow to start automating tasks.', 'workflow-automation'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=workflow-automation-new'); ?>" class="button button-primary">
                <?php _e('Create Workflow', 'workflow-automation'); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-name"><?php _e('Name', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-executions"><?php _e('Executions', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-last-run"><?php _e('Last Run', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Created', 'workflow-automation'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'workflow-automation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workflows as $workflow) : ?>
                    <?php
                    // Get execution stats
                    $execution_model = new Execution_Model();
                    $stats = $execution_model->get_workflow_stats($workflow->id);
                    ?>
                    <tr>
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow=' . $workflow->id); ?>">
                                    <?php echo esc_html($workflow->name); ?>
                                </a>
                            </strong>
                            <?php if (!empty($workflow->description)) : ?>
                                <p class="description"><?php echo esc_html($workflow->description); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="column-status">
                            <?php if ($workflow->status === 'active') : ?>
                                <span class="wa-status wa-status-active"><?php _e('Active', 'workflow-automation'); ?></span>
                            <?php else : ?>
                                <span class="wa-status wa-status-inactive"><?php _e('Inactive', 'workflow-automation'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-executions">
                            <?php echo number_format_i18n($stats->total_executions); ?>
                            <?php if ($stats->failed_executions > 0) : ?>
                                <span class="wa-failed-count" title="<?php esc_attr_e('Failed executions', 'workflow-automation'); ?>">
                                    (<?php echo number_format_i18n($stats->failed_executions); ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="column-last-run">
                            <?php if ($stats->last_execution) : ?>
                                <?php echo human_time_diff(strtotime($stats->last_execution), current_time('timestamp')); ?> ago
                            <?php else : ?>
                                <span class="description"><?php _e('Never', 'workflow-automation'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-date">
                            <?php echo date_i18n(get_option('date_format'), strtotime($workflow->created_at)); ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow=' . $workflow->id); ?>" 
                               class="button button-small" title="<?php esc_attr_e('Edit workflow', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <button type="button" class="button button-small wa-duplicate-workflow" 
                                    data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                    title="<?php esc_attr_e('Duplicate workflow', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-admin-page"></span>
                            </button>
                            <button type="button" class="button button-small wa-toggle-workflow" 
                                    data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                    data-current-status="<?php echo esc_attr($workflow->status); ?>"
                                    title="<?php echo $workflow->status === 'active' ? 
                                        esc_attr__('Deactivate workflow', 'workflow-automation') : 
                                        esc_attr__('Activate workflow', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-<?php echo $workflow->status === 'active' ? 'pause' : 'controls-play'; ?>"></span>
                            </button>
                            <button type="button" class="button button-small wa-delete-workflow" 
                                    data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                    title="<?php esc_attr_e('Delete workflow', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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
    margin-bottom: 20px;
}

.wa-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.wa-status-active {
    background: #d4edda;
    color: #155724;
}

.wa-status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.wa-failed-count {
    color: #dc3545;
    font-size: 12px;
}

.column-status,
.column-executions,
.column-last-run {
    width: 100px;
}

.column-date {
    width: 120px;
}

.column-actions {
    width: 150px;
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
</style>