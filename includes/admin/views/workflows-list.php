<?php
/**
 * Workflows List View - Modern Design
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

// Get workflows
$workflow_model = new Workflow_Model();
$workflows = $workflow_model->get_all();

// Get statistics
$execution_model = new Execution_Model();
$total_workflows = count($workflows);
$active_workflows = count(array_filter($workflows, function($w) { return $w->status === 'active'; }));
$total_executions = 0;
$failed_executions = 0;

foreach ($workflows as $workflow) {
    $stats = $execution_model->get_workflow_stats($workflow->id);
    $total_executions += $stats->total_executions;
    $failed_executions += $stats->failed_executions;
}
?>

<div class="wa-admin-wrap">
    <!-- Modern Header -->
    <div class="wa-admin-header">
        <div class="wa-admin-header-content">
            <div>
                <h1 class="wa-admin-title">
                    <span class="wa-logo">⚡</span>
                    <?php _e('Workflow Automation', 'workflow-automation'); ?>
                </h1>
                <p class="wa-admin-subtitle"><?php _e('Automate your tasks with intelligent workflows', 'workflow-automation'); ?></p>
            </div>
            <div class="wa-admin-actions">
                <a href="<?php echo admin_url('admin.php?page=workflow-automation-new'); ?>" class="wa-btn wa-btn-primary wa-btn-lg">
                    <span class="dashicons dashicons-plus"></span>
                    <?php _e('Create Workflow', 'workflow-automation'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="wa-container">
        <!-- Statistics Cards -->
        <div class="wa-stats-grid">
            <div class="wa-stat-card">
                <div class="wa-stat-number"><?php echo number_format_i18n($total_workflows); ?></div>
                <div class="wa-stat-label"><?php _e('Total Workflows', 'workflow-automation'); ?></div>
                <span class="wa-stat-icon dashicons dashicons-randomize"></span>
            </div>
            <div class="wa-stat-card success">
                <div class="wa-stat-number"><?php echo number_format_i18n($active_workflows); ?></div>
                <div class="wa-stat-label"><?php _e('Active Workflows', 'workflow-automation'); ?></div>
                <span class="wa-stat-icon dashicons dashicons-yes"></span>
            </div>
            <div class="wa-stat-card">
                <div class="wa-stat-number"><?php echo number_format_i18n($total_executions); ?></div>
                <div class="wa-stat-label"><?php _e('Total Executions', 'workflow-automation'); ?></div>
                <span class="wa-stat-icon dashicons dashicons-controls-play"></span>
            </div>
            <div class="wa-stat-card <?php echo $failed_executions > 0 ? 'danger' : ''; ?>">
                <div class="wa-stat-number"><?php echo number_format_i18n($failed_executions); ?></div>
                <div class="wa-stat-label"><?php _e('Failed Executions', 'workflow-automation'); ?></div>
                <span class="wa-stat-icon dashicons dashicons-warning"></span>
            </div>
        </div>

        <?php if (empty($workflows)) : ?>
            <!-- Empty State -->
            <div class="wa-card">
                <div class="wa-card-body" style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; color: var(--wa-gray-300); margin-bottom: 2rem;">
                        <span class="dashicons dashicons-randomize"></span>
                    </div>
                    <h2 style="color: var(--wa-gray-700); margin-bottom: 1rem;"><?php _e('No workflows yet', 'workflow-automation'); ?></h2>
                    <p style="color: var(--wa-gray-600); margin-bottom: 2rem; font-size: 1.1rem;"><?php _e('Create your first workflow to start automating tasks and boost your productivity.', 'workflow-automation'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=workflow-automation-new'); ?>" class="wa-btn wa-btn-primary wa-btn-lg">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Create Your First Workflow', 'workflow-automation'); ?>
                    </a>
                </div>
            </div>
        <?php else : ?>
            <!-- Workflows Grid -->
            <div class="wa-grid wa-grid-2">
                <?php foreach ($workflows as $workflow) : ?>
                    <?php
                    // Get execution stats
                    $stats = $execution_model->get_workflow_stats($workflow->id);
                    ?>
                    <div class="wa-card wa-fade-in">
                        <div class="wa-card-header">
                            <h3 class="wa-card-title">
                                <span class="dashicons dashicons-randomize"></span>
                                <a href="<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow=' . $workflow->id); ?>" 
                                   style="text-decoration: none; color: inherit;">
                                    <?php echo esc_html($workflow->name); ?>
                                </a>
                            </h3>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <?php if ($workflow->status === 'active') : ?>
                                    <span class="wa-badge wa-badge-success">
                                        <span class="dashicons dashicons-yes-alt" style="font-size: 12px; width: 12px; height: 12px;"></span>
                                        <?php _e('Active', 'workflow-automation'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="wa-badge wa-badge-gray">
                                        <span class="dashicons dashicons-pause" style="font-size: 12px; width: 12px; height: 12px;"></span>
                                        <?php _e('Inactive', 'workflow-automation'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="wa-card-body">
                            <?php if (!empty($workflow->description)) : ?>
                                <p style="color: var(--wa-gray-600); margin-bottom: 1.5rem;"><?php echo esc_html($workflow->description); ?></p>
                            <?php endif; ?>
                            
                            <div class="wa-grid wa-grid-3" style="gap: 1rem;">
                                <div style="text-align: center;">
                                    <div style="font-size: 1.5rem; font-weight: 600; color: var(--wa-gray-900);">
                                        <?php echo number_format_i18n($stats->total_executions); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--wa-gray-600);">
                                        <?php _e('Executions', 'workflow-automation'); ?>
                                    </div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 1.5rem; font-weight: 600; color: <?php echo $stats->failed_executions > 0 ? 'var(--wa-danger)' : 'var(--wa-success)'; ?>;">
                                        <?php echo number_format_i18n($stats->failed_executions); ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--wa-gray-600);">
                                        <?php _e('Failed', 'workflow-automation'); ?>
                                    </div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 1.5rem; font-weight: 600; color: var(--wa-gray-900);">
                                        <?php echo $stats->success_rate; ?>%
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--wa-gray-600);">
                                        <?php _e('Success', 'workflow-automation'); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--wa-gray-500);">
                                <div>
                                    <span class="dashicons dashicons-clock" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                    <?php if ($stats->last_execution) : ?>
                                        <?php echo human_time_diff(strtotime($stats->last_execution), current_time('timestamp')); ?> ago
                                    <?php else : ?>
                                        <?php _e('Never run', 'workflow-automation'); ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="dashicons dashicons-calendar-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                    <?php echo date_i18n('M j, Y', strtotime($workflow->created_at)); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wa-card-footer">
                            <div class="wa-flex wa-gap-2">
                                <a href="<?php echo admin_url('admin.php?page=workflow-automation-builder&workflow=' . $workflow->id); ?>" 
                                   class="wa-btn wa-btn-primary wa-btn-sm">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php _e('Edit', 'workflow-automation'); ?>
                                </a>
                                <button type="button" class="wa-btn wa-btn-secondary wa-btn-sm wa-duplicate-workflow" 
                                        data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                        title="<?php esc_attr_e('Duplicate workflow', 'workflow-automation'); ?>">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                                <button type="button" class="wa-btn wa-btn-<?php echo $workflow->status === 'active' ? 'warning' : 'success'; ?> wa-btn-sm wa-toggle-workflow" 
                                        data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                        data-current-status="<?php echo esc_attr($workflow->status); ?>"
                                        title="<?php echo $workflow->status === 'active' ? 
                                            esc_attr__('Deactivate workflow', 'workflow-automation') : 
                                            esc_attr__('Activate workflow', 'workflow-automation'); ?>">
                                    <span class="dashicons dashicons-<?php echo $workflow->status === 'active' ? 'pause' : 'controls-play'; ?>"></span>
                                </button>
                            </div>
                            <button type="button" class="wa-btn wa-btn-danger wa-btn-sm wa-delete-workflow" 
                                    data-workflow-id="<?php echo esc_attr($workflow->id); ?>"
                                    title="<?php esc_attr_e('Delete workflow', 'workflow-automation'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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

<script>
jQuery(document).ready(function($) {
    // Add click handlers for workflow actions
    $('.wa-duplicate-workflow').on('click', function() {
        var workflowId = $(this).data('workflow-id');
        // TODO: Implement duplicate workflow functionality
        console.log('Duplicate workflow:', workflowId);
    });
    
    $('.wa-toggle-workflow').on('click', function() {
        var workflowId = $(this).data('workflow-id');
        var currentStatus = $(this).data('current-status');
        var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        // TODO: Implement toggle workflow status
        console.log('Toggle workflow:', workflowId, 'from', currentStatus, 'to', newStatus);
    });
    
    $('.wa-delete-workflow').on('click', function() {
        var workflowId = $(this).data('workflow-id');
        
        if (confirm('Are you sure you want to delete this workflow? This action cannot be undone.')) {
            // TODO: Implement delete workflow functionality
            console.log('Delete workflow:', workflowId);
        }
    });
});
</script>