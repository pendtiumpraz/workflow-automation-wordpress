<?php
/**
 * Workflow Builder View
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/admin/views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$workflow_id = isset($_GET['workflow']) ? intval($_GET['workflow']) : 0;
$workflow_name = '';

if ($workflow_id) {
    $workflow_model = new Workflow_Model();
    $workflow = $workflow_model->get($workflow_id);
    if ($workflow) {
        $workflow_name = $workflow->name;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php 
        if ($workflow_id && $workflow_name) {
            echo esc_html(sprintf(__('Edit Workflow: %s', 'workflow-automation'), $workflow_name));
        } else {
            esc_html_e('New Workflow', 'workflow-automation');
        }
        ?>
    </h1>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation')); ?>" class="page-title-action">
        <?php esc_html_e('Back to Workflows', 'workflow-automation'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <div id="workflow-builder-root">
        <!-- React app will mount here -->
        <div class="workflow-builder-loading">
            <span class="spinner is-active"></span>
            <p><?php esc_html_e('Loading workflow builder...', 'workflow-automation'); ?></p>
        </div>
    </div>
    
    <div class="workflow-builder-toolbar">
        <div class="toolbar-left">
            <button type="button" id="wa-save-workflow" class="button button-primary">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('Save Workflow', 'workflow-automation'); ?>
            </button>
            
            <?php if ($workflow_id): ?>
            <button type="button" id="wa-test-workflow" class="button">
                <span class="dashicons dashicons-controls-play"></span>
                <?php esc_html_e('Test Run', 'workflow-automation'); ?>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="toolbar-right">
            <span class="save-status"></span>
        </div>
    </div>
</div>

<style>
.workflow-builder-loading {
    text-align: center;
    padding: 50px;
}

.workflow-builder-loading .spinner {
    float: none;
    margin: 0 auto 20px;
}

#workflow-builder-root {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin: 20px 0;
    position: relative;
    min-height: 600px;
}

.workflow-builder-toolbar {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-top: none;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.toolbar-left button {
    margin-right: 10px;
}

.save-status {
    color: #666;
    font-style: italic;
}

.save-status.success {
    color: #46b450;
}

.save-status.error {
    color: #dc3232;
}
</style>