<?php
/**
 * Integrations Management View
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

// Get integration model
$integration_model = new Integration_Settings_Model();
$integration_types = $integration_model->get_integration_types();

// Handle form submission
if (isset($_POST['wa_save_integration']) && wp_verify_nonce($_POST['wa_integration_nonce'], 'wa_save_integration')) {
    $integration_type = sanitize_text_field($_POST['integration_type']);
    $integration_id = isset($_POST['integration_id']) ? intval($_POST['integration_id']) : 0;
    
    $settings = array();
    if (isset($integration_types[$integration_type])) {
        foreach ($integration_types[$integration_type]['fields'] as $field) {
            if (isset($_POST[$field['key']])) {
                $settings[$field['key']] = sanitize_text_field($_POST[$field['key']]);
            }
        }
    }
    
    $data = array(
        'integration_type' => $integration_type,
        'name' => sanitize_text_field($_POST['integration_name']),
        'settings' => $settings,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );
    
    if ($integration_id) {
        $result = $integration_model->update($integration_id, $data);
        $message = __('Integration updated successfully.', 'workflow-automation');
    } else {
        $result = $integration_model->create($data);
        $message = __('Integration added successfully.', 'workflow-automation');
    }
    
    if ($result) {
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html__('Failed to save integration.', 'workflow-automation') . '</p></div>';
    }
}

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['integration']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_integration')) {
    $integration_id = intval($_GET['integration']);
    if ($integration_model->delete($integration_id)) {
        echo '<div class="notice notice-success"><p>' . esc_html__('Integration deleted successfully.', 'workflow-automation') . '</p></div>';
    }
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Integrations', 'workflow-automation'); ?></h1>
    
    <?php if ($current_tab === 'list'): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&tab=add')); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'workflow-automation'); ?>
        </a>
    <?php else: ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations')); ?>" class="page-title-action">
            <?php esc_html_e('Back to List', 'workflow-automation'); ?>
        </a>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <?php if ($current_tab === 'list'): ?>
        <!-- Integration List -->
        <div class="integrations-grid" style="margin-top: 20px;">
            <?php foreach ($integration_types as $type => $config): ?>
                <?php
                $integrations = $integration_model->get_by_type($type);
                ?>
                <div class="integration-card" style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin-bottom: 20px;">
                    <h3>
                        <span class="<?php echo esc_attr($config['icon']); ?>"></span>
                        <?php echo esc_html($config['label']); ?>
                    </h3>
                    
                    <?php if (empty($integrations)): ?>
                        <p style="color: #666;"><?php esc_html_e('No configurations added yet.', 'workflow-automation'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&tab=add&type=' . $type)); ?>" class="button">
                            <?php esc_html_e('Add Configuration', 'workflow-automation'); ?>
                        </a>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Name', 'workflow-automation'); ?></th>
                                    <th><?php esc_html_e('Status', 'workflow-automation'); ?></th>
                                    <th><?php esc_html_e('Actions', 'workflow-automation'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($integrations as $integration): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($integration->name); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($integration->is_active): ?>
                                                <span style="color: #46b450;">● <?php esc_html_e('Active', 'workflow-automation'); ?></span>
                                            <?php else: ?>
                                                <span style="color: #dc3232;">● <?php esc_html_e('Inactive', 'workflow-automation'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&tab=edit&integration=' . $integration->id)); ?>">
                                                <?php esc_html_e('Edit', 'workflow-automation'); ?>
                                            </a>
                                            |
                                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=workflow-automation-integrations&action=delete&integration=' . $integration->id), 'delete_integration')); ?>" 
                                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this integration?', 'workflow-automation'); ?>');" 
                                               style="color: #dc3232;">
                                                <?php esc_html_e('Delete', 'workflow-automation'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="margin-top: 10px;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&tab=add&type=' . $type)); ?>" class="button">
                                <?php esc_html_e('Add Another', 'workflow-automation'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php elseif ($current_tab === 'add' || $current_tab === 'edit'): ?>
        <!-- Add/Edit Form -->
        <?php
        $integration = null;
        $selected_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
        
        if ($current_tab === 'edit' && isset($_GET['integration'])) {
            $integration_id = intval($_GET['integration']);
            $integration = $integration_model->get($integration_id);
            if ($integration) {
                $selected_type = $integration->integration_type;
                // Decrypt settings for display
                $integration->settings = $integration_model->decrypt_settings($integration->settings);
            }
        }
        ?>
        
        <form method="post" action="">
            <?php wp_nonce_field('wa_save_integration', 'wa_integration_nonce'); ?>
            
            <?php if ($integration): ?>
                <input type="hidden" name="integration_id" value="<?php echo esc_attr($integration->id); ?>">
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="integration_type"><?php esc_html_e('Integration Type', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <?php if ($integration): ?>
                            <input type="hidden" name="integration_type" value="<?php echo esc_attr($selected_type); ?>">
                            <strong>
                                <span class="<?php echo esc_attr($integration_types[$selected_type]['icon']); ?>"></span>
                                <?php echo esc_html($integration_types[$selected_type]['label']); ?>
                            </strong>
                        <?php else: ?>
                            <select name="integration_type" id="integration_type" required onchange="window.location.href='<?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&tab=add&type=')); ?>' + this.value;">
                                <option value=""><?php esc_html_e('Select Integration Type', 'workflow-automation'); ?></option>
                                <?php foreach ($integration_types as $type => $config): ?>
                                    <option value="<?php echo esc_attr($type); ?>" <?php selected($selected_type, $type); ?>>
                                        <?php echo esc_html($config['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="integration_name"><?php esc_html_e('Configuration Name', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="integration_name" id="integration_name" class="regular-text" required
                               value="<?php echo $integration ? esc_attr($integration->name) : ''; ?>"
                               placeholder="<?php esc_attr_e('e.g., Main Workspace', 'workflow-automation'); ?>">
                        <p class="description"><?php esc_html_e('A name to identify this configuration', 'workflow-automation'); ?></p>
                    </td>
                </tr>
                
                <?php if ($selected_type && isset($integration_types[$selected_type])): ?>
                    <?php foreach ($integration_types[$selected_type]['fields'] as $field): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr($field['key']); ?>">
                                    <?php echo esc_html($field['label']); ?>
                                    <?php if (!empty($field['required'])): ?>
                                        <span style="color: #dc3232;">*</span>
                                    <?php endif; ?>
                                </label>
                            </th>
                            <td>
                                <?php
                                $field_value = '';
                                if ($integration && isset($integration->settings[$field['key']])) {
                                    $field_value = $integration->settings[$field['key']];
                                } elseif (isset($field['default'])) {
                                    $field_value = $field['default'];
                                }
                                
                                switch ($field['type']):
                                    case 'select':
                                        ?>
                                        <select name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>" 
                                                <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                            <?php foreach ($field['options'] as $value => $label): ?>
                                                <option value="<?php echo esc_attr($value); ?>" <?php selected($field_value, $value); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php
                                        break;
                                        
                                    case 'textarea':
                                        ?>
                                        <textarea name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>"
                                                  class="large-text" rows="4"
                                                  <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                                  <?php echo isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>><?php echo esc_textarea($field_value); ?></textarea>
                                        <?php
                                        break;
                                        
                                    case 'password':
                                        ?>
                                        <input type="password" name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>"
                                               class="regular-text" value="<?php echo esc_attr($field_value); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                               <?php echo isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'number':
                                        ?>
                                        <input type="number" name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>"
                                               class="small-text" value="<?php echo esc_attr($field_value); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                               <?php echo isset($field['min']) ? 'min="' . esc_attr($field['min']) . '"' : ''; ?>
                                               <?php echo isset($field['max']) ? 'max="' . esc_attr($field['max']) . '"' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'email':
                                        ?>
                                        <input type="email" name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>"
                                               class="regular-text" value="<?php echo esc_attr($field_value); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                               <?php echo isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>>
                                        <?php
                                        break;
                                        
                                    case 'hidden':
                                        ?>
                                        <input type="hidden" name="<?php echo esc_attr($field['key']); ?>" value="<?php echo esc_attr($field_value); ?>">
                                        <em><?php esc_html_e('(Managed automatically)', 'workflow-automation'); ?></em>
                                        <?php
                                        break;
                                        
                                    default:
                                        ?>
                                        <input type="text" name="<?php echo esc_attr($field['key']); ?>" id="<?php echo esc_attr($field['key']); ?>"
                                               class="regular-text" value="<?php echo esc_attr($field_value); ?>"
                                               <?php echo !empty($field['required']) ? 'required' : ''; ?>
                                               <?php echo isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>>
                                        <?php
                                        break;
                                endswitch;
                                
                                if (!empty($field['description'])):
                                    ?>
                                    <p class="description"><?php echo esc_html($field['description']); ?></p>
                                    <?php
                                endif;
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <tr>
                    <th scope="row">
                        <label for="is_active"><?php esc_html_e('Status', 'workflow-automation'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   <?php checked($integration ? $integration->is_active : true); ?>>
                            <?php esc_html_e('Active', 'workflow-automation'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Inactive integrations cannot be used in workflows', 'workflow-automation'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="wa_save_integration" class="button button-primary" 
                       value="<?php echo $integration ? esc_attr__('Update Integration', 'workflow-automation') : esc_attr__('Add Integration', 'workflow-automation'); ?>">
            </p>
        </form>
        
        <?php if ($selected_type === 'google' && !$integration): ?>
            <div class="notice notice-info">
                <p><?php esc_html_e('For Google services, you will need to:', 'workflow-automation'); ?></p>
                <ol>
                    <li><?php esc_html_e('Create a project in Google Cloud Console', 'workflow-automation'); ?></li>
                    <li><?php esc_html_e('Enable the required APIs (Gmail, Drive, Sheets, etc.)', 'workflow-automation'); ?></li>
                    <li><?php esc_html_e('Create OAuth 2.0 credentials', 'workflow-automation'); ?></li>
                    <li><?php esc_html_e('Add redirect URI:', 'workflow-automation'); ?> <code><?php echo esc_url(admin_url('admin.php?page=workflow-automation-integrations&oauth=google')); ?></code></li>
                </ol>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>