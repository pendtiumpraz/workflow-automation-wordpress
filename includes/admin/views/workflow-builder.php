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

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get workflow ID from URL
$workflow_id = isset($_GET['workflow']) ? intval($_GET['workflow']) : 0;

if (!$workflow_id) {
    wp_die(__('Invalid workflow ID', 'workflow-automation'));
}

// Get workflow data
$workflow_model = new Workflow_Model();
$workflow = $workflow_model->get($workflow_id);

if (!$workflow) {
    wp_die(__('Workflow not found', 'workflow-automation'));
}

// Get workflow nodes and connections
$node_model = new Node_Model();
$nodes = $node_model->get_by_workflow($workflow_id);

// Get available node types
$available_nodes = array(
    'triggers' => array(
        'webhook_start' => array(
            'label' => __('Webhook Trigger', 'workflow-automation'),
            'icon' => 'dashicons-admin-links',
            'color' => '#4CAF50'
        ),
        'schedule_start' => array(
            'label' => __('Schedule Trigger', 'workflow-automation'),
            'icon' => 'dashicons-clock',
            'color' => '#2196F3'
        ),
        'manual_start' => array(
            'label' => __('Manual Trigger', 'workflow-automation'),
            'icon' => 'dashicons-admin-users',
            'color' => '#9C27B0'
        )
    ),
    'actions' => array(
        'email' => array(
            'label' => __('Send Email', 'workflow-automation'),
            'icon' => 'dashicons-email',
            'color' => '#FF9800'
        ),
        'slack' => array(
            'label' => __('Slack Message', 'workflow-automation'),
            'icon' => 'dashicons-format-status',
            'color' => '#4A154B'
        ),
        'line' => array(
            'label' => __('LINE Message', 'workflow-automation'),
            'icon' => 'dashicons-format-chat',
            'color' => '#00C300'
        ),
        'google_sheets' => array(
            'label' => __('Google Sheets', 'workflow-automation'),
            'icon' => 'dashicons-media-spreadsheet',
            'color' => '#0F9D58'
        ),
        'http' => array(
            'label' => __('HTTP Request', 'workflow-automation'),
            'icon' => 'dashicons-admin-site',
            'color' => '#607D8B'
        )
    ),
    'ai' => array(
        'openai' => array(
            'label' => __('OpenAI', 'workflow-automation'),
            'icon' => 'dashicons-admin-generic',
            'color' => '#00A67E'
        ),
        'claude' => array(
            'label' => __('Claude', 'workflow-automation'),
            'icon' => 'dashicons-admin-generic',
            'color' => '#8B6914'
        ),
        'gemini' => array(
            'label' => __('Google Gemini', 'workflow-automation'),
            'icon' => 'dashicons-star-filled',
            'color' => '#4285F4'
        )
    ),
    'wordpress' => array(
        'wp_post' => array(
            'label' => __('WordPress Post', 'workflow-automation'),
            'icon' => 'dashicons-admin-post',
            'color' => '#0073AA'
        ),
        'wp_user' => array(
            'label' => __('WordPress User', 'workflow-automation'),
            'icon' => 'dashicons-admin-users',
            'color' => '#0073AA'
        ),
        'wp_media' => array(
            'label' => __('WordPress Media', 'workflow-automation'),
            'icon' => 'dashicons-admin-media',
            'color' => '#0073AA'
        )
    ),
    'logic' => array(
        'filter' => array(
            'label' => __('Filter', 'workflow-automation'),
            'icon' => 'dashicons-filter',
            'color' => '#795548'
        ),
        'loop' => array(
            'label' => __('Loop', 'workflow-automation'),
            'icon' => 'dashicons-controls-repeat',
            'color' => '#3F51B5'
        ),
        'delay' => array(
            'label' => __('Delay', 'workflow-automation'),
            'icon' => 'dashicons-backup',
            'color' => '#009688'
        ),
        'code' => array(
            'label' => __('Custom Code', 'workflow-automation'),
            'icon' => 'dashicons-editor-code',
            'color' => '#E91E63'
        )
    )
);
?>

<div class="wa-admin-wrap">
    <div class="wa-admin-header">
        <div class="wa-admin-header-content">
            <div>
                <h1 class="wa-admin-title">
                    <a href="<?php echo admin_url('admin.php?page=workflow-automation'); ?>" class="wa-logo" style="background: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem;">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                    </a>
                    <span id="wa-workflow-name"><?php echo esc_html($workflow->name); ?></span>
                    <button type="button" class="wa-edit-name" style="background: rgba(255, 255, 255, 0.2); color: white; border: none; padding: 0.5rem; border-radius: var(--wa-border-radius); cursor: pointer; margin-left: 1rem;" title="<?php esc_attr_e('Edit name', 'workflow-automation'); ?>">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                </h1>
                <p class="wa-admin-subtitle"><?php _e('Visual workflow builder with drag-and-drop interface', 'workflow-automation'); ?></p>
            </div>
            
            <div class="wa-admin-actions">
                <div class="wa-save-status" style="display: flex; align-items: center; gap: 0.5rem; color: rgba(255, 255, 255, 0.8); margin-right: 1rem;">
                    <span class="wa-save-indicator" style="width: 8px; height: 8px; border-radius: 50%; background: #46b450;"></span>
                    <span class="wa-save-message"><?php _e('All changes saved', 'workflow-automation'); ?></span>
                </div>
                
                <button type="button" id="wa-save-workflow" class="wa-btn wa-btn-success">
                    <span class="dashicons dashicons-yes"></span>
                    <?php _e('Save', 'workflow-automation'); ?>
                </button>
                
                <button type="button" id="wa-test-workflow" class="wa-btn wa-btn-outline" style="color: white; border-color: rgba(255, 255, 255, 0.3);">
                    <span class="dashicons dashicons-controls-play"></span>
                    <?php _e('Test', 'workflow-automation'); ?>
                </button>
                
                <div class="wa-workflow-status" style="display: flex; align-items: center; gap: 0.75rem; color: white;">
                    <label class="wa-switch">
                        <input type="checkbox" id="wa-workflow-active" <?php checked($workflow->status, 'active'); ?>>
                        <span class="wa-slider"></span>
                    </label>
                    <span class="wa-status-label">
                        <?php echo $workflow->status === 'active' ? __('Active', 'workflow-automation') : __('Inactive', 'workflow-automation'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wrap wa-workflow-builder-wrap" style="margin: 0; height: calc(100vh - 160px);">
        <div class="wa-builder-container" style="height: 100%;">
        <div class="wa-builder-sidebar">
            <h2><?php _e('Nodes', 'workflow-automation'); ?></h2>
            
            <div class="wa-node-search">
                <input type="text" id="wa-node-search" placeholder="<?php esc_attr_e('Search nodes...', 'workflow-automation'); ?>">
            </div>
            
            <div class="wa-node-categories">
                <?php foreach ($available_nodes as $category => $nodes) : ?>
                    <div class="wa-node-category">
                        <h3 class="wa-category-title" data-category="<?php echo esc_attr($category); ?>">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                            <?php echo esc_html(ucfirst($category)); ?>
                        </h3>
                        <div class="wa-category-nodes">
                            <?php foreach ($nodes as $type => $node) : ?>
                                <div class="wa-draggable-node" 
                                     data-node-type="<?php echo esc_attr($type); ?>"
                                     data-node-label="<?php echo esc_attr($node['label']); ?>"
                                     data-node-icon="<?php echo esc_attr($node['icon']); ?>"
                                     data-node-color="<?php echo esc_attr($node['color']); ?>">
                                    <span class="dashicons <?php echo esc_attr($node['icon']); ?>"></span>
                                    <?php echo esc_html($node['label']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="wa-builder-canvas">
            <div id="wa-workflow-canvas">
                <!-- React Flow will be mounted here -->
            </div>
            
            <div class="wa-canvas-controls">
                <button type="button" class="wa-control-btn" id="wa-zoom-in" title="<?php esc_attr_e('Zoom In', 'workflow-automation'); ?>">
                    <span class="dashicons dashicons-plus"></span>
                </button>
                <button type="button" class="wa-control-btn" id="wa-zoom-out" title="<?php esc_attr_e('Zoom Out', 'workflow-automation'); ?>">
                    <span class="dashicons dashicons-minus"></span>
                </button>
                <button type="button" class="wa-control-btn" id="wa-fit-view" title="<?php esc_attr_e('Fit View', 'workflow-automation'); ?>">
                    <span class="dashicons dashicons-editor-expand"></span>
                </button>
                <button type="button" class="wa-control-btn" id="wa-center-view" title="<?php esc_attr_e('Center View', 'workflow-automation'); ?>">
                    <span class="dashicons dashicons-screenoptions"></span>
                </button>
            </div>
        </div>
        
        <div class="wa-builder-properties">
            <h2><?php _e('Properties', 'workflow-automation'); ?></h2>
            <div id="wa-properties-content">
                <div class="wa-empty-properties">
                    <p><?php _e('Select a node to view its properties', 'workflow-automation'); ?></p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Node Configuration Modal -->
<div id="wa-node-config-modal" class="wa-modal" style="display: none;">
    <div class="wa-modal-content wa-modal-large">
        <div class="wa-modal-header">
            <h2 id="wa-node-config-title"><?php _e('Configure Node', 'workflow-automation'); ?></h2>
            <button type="button" class="wa-modal-close">&times;</button>
        </div>
        
        <div class="wa-modal-body">
            <form id="wa-node-config-form">
                <div id="wa-node-config-fields">
                    <!-- Dynamic fields will be loaded here -->
                </div>
            </form>
        </div>
        
        <div class="wa-modal-footer">
            <button type="button" class="button button-primary" id="wa-save-node-config">
                <?php _e('Save Configuration', 'workflow-automation'); ?>
            </button>
            <button type="button" class="button wa-modal-close">
                <?php _e('Cancel', 'workflow-automation'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Test Workflow Modal -->
<div id="wa-test-modal" class="wa-modal" style="display: none;">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h2><?php _e('Test Workflow', 'workflow-automation'); ?></h2>
            <button type="button" class="wa-modal-close">&times;</button>
        </div>
        
        <div class="wa-modal-body">
            <form id="wa-test-form">
                <div class="wa-form-group">
                    <label for="test-trigger-type"><?php _e('Trigger Type', 'workflow-automation'); ?></label>
                    <select id="test-trigger-type" name="trigger_type" class="regular-text">
                        <option value="manual"><?php _e('Manual', 'workflow-automation'); ?></option>
                        <option value="webhook"><?php _e('Webhook', 'workflow-automation'); ?></option>
                    </select>
                </div>
                
                <div class="wa-form-group" id="test-data-group">
                    <label for="test-data"><?php _e('Test Data (JSON)', 'workflow-automation'); ?></label>
                    <textarea id="test-data" name="test_data" class="large-text" rows="10">{
  "example": "data"
}</textarea>
                    <p class="description"><?php _e('Enter test data in JSON format', 'workflow-automation'); ?></p>
                </div>
            </form>
        </div>
        
        <div class="wa-modal-footer">
            <button type="button" class="button button-primary" id="wa-run-test">
                <?php _e('Run Test', 'workflow-automation'); ?>
            </button>
            <button type="button" class="button wa-modal-close">
                <?php _e('Cancel', 'workflow-automation'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Builder Layout */
.wa-workflow-builder-wrap {
    margin: -10px -20px 0 -20px;
    height: calc(100vh - 32px);
    display: flex;
    flex-direction: column;
}

.wa-builder-header {
    background: #fff;
    border-bottom: 1px solid #e1e1e1;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wa-builder-title {
    display: flex;
    align-items: center;
    margin: 0;
    font-size: 20px;
}

.wa-builder-title .dashicons {
    margin-right: 10px;
    text-decoration: none;
    color: #555;
}

.wa-edit-name {
    margin-left: 10px;
    cursor: pointer;
    color: #0073aa;
    background: none;
    border: none;
    padding: 0;
}

.wa-builder-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.wa-save-status {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
}

.wa-save-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #46b450;
}

.wa-save-indicator.saving {
    background: #f56e28;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

/* Workflow Status Switch */
.wa-workflow-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.wa-switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}

.wa-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.wa-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 20px;
}

.wa-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .wa-slider {
    background-color: #46b450;
}

input:checked + .wa-slider:before {
    transform: translateX(20px);
}

/* Builder Container */
.wa-builder-container {
    flex: 1;
    display: flex;
    overflow: hidden;
}

/* Sidebar */
.wa-builder-sidebar {
    width: 250px;
    background: #f1f1f1;
    border-right: 1px solid #e1e1e1;
    padding: 20px;
    overflow-y: auto;
}

.wa-builder-sidebar h2 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

.wa-node-search {
    margin-bottom: 20px;
}

.wa-node-search input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.wa-node-category {
    margin-bottom: 10px;
}

.wa-category-title {
    margin: 0;
    padding: 8px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
}

.wa-category-title:hover {
    background: #f8f8f8;
}

.wa-category-title .dashicons {
    margin-right: 5px;
    transition: transform 0.2s;
}

.wa-category-title.expanded .dashicons {
    transform: rotate(90deg);
}

.wa-category-nodes {
    margin-top: 5px;
}

.wa-draggable-node {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 5px;
    cursor: move;
    transition: all 0.2s;
}

.wa-draggable-node:hover {
    background: #f0f0f1;
    transform: translateX(5px);
}

.wa-draggable-node .dashicons {
    margin-right: 8px;
}

/* Canvas */
.wa-builder-canvas {
    flex: 1;
    position: relative;
    background: #fafafa;
}

#wa-workflow-canvas {
    width: 100%;
    height: 100%;
}

.wa-canvas-controls {
    position: absolute;
    bottom: 20px;
    right: 20px;
    display: flex;
    gap: 5px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.wa-control-btn {
    width: 30px;
    height: 30px;
    border: none;
    background: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
}

.wa-control-btn:hover {
    background: #f0f0f1;
}

/* Properties Panel */
.wa-builder-properties {
    width: 300px;
    background: #fff;
    border-left: 1px solid #e1e1e1;
    padding: 20px;
    overflow-y: auto;
}

.wa-builder-properties h2 {
    margin: 0 0 15px 0;
    font-size: 16px;
}

.wa-empty-properties {
    text-align: center;
    color: #666;
    padding: 40px 0;
}

/* Node Properties */
.wa-node-properties {
    font-size: 14px;
}

.wa-property-group {
    margin-bottom: 20px;
}

.wa-property-group h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    font-weight: 600;
}

.wa-property-row {
    margin-bottom: 10px;
}

.wa-property-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.wa-property-row input,
.wa-property-row select,
.wa-property-row textarea {
    width: 100%;
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
    max-width: 800px;
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

.wa-modal-footer .button {
    margin-left: 10px;
}

.wa-form-group {
    margin-bottom: 20px;
}

.wa-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.wa-form-group input[type="text"],
.wa-form-group input[type="number"],
.wa-form-group select,
.wa-form-group textarea {
    width: 100%;
}

.wa-form-group .description {
    margin-top: 5px;
}
</style>

<script>
// Initialize workflow builder data
var waWorkflowData = {
    id: <?php echo $workflow_id; ?>,
    name: '<?php echo esc_js($workflow->name); ?>',
    nodes: <?php echo json_encode($nodes); ?>,
    availableNodes: <?php echo json_encode($available_nodes); ?>
};
</script>