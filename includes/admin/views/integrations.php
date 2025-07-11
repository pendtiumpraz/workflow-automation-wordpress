<?php
/**
 * Integrations View
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

// Get available integration types
$integration_types = array(
    'communication' => array(
        'label' => __('Communication', 'workflow-automation'),
        'integrations' => array(
            'email' => array(
                'name' => __('Email (SMTP)', 'workflow-automation'),
                'description' => __('Send emails using SMTP server', 'workflow-automation'),
                'icon' => 'dashicons-email',
                'fields' => array('smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure', 'from_email', 'from_name')
            ),
            'slack' => array(
                'name' => __('Slack', 'workflow-automation'),
                'description' => __('Send messages to Slack channels', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'fields' => array('webhook_url', 'bot_token', 'default_channel')
            ),
            'line' => array(
                'name' => __('LINE', 'workflow-automation'),
                'description' => __('Send messages via LINE Messaging API', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'fields' => array('channel_access_token', 'channel_secret')
            ),
            'telegram' => array(
                'name' => __('Telegram', 'workflow-automation'),
                'description' => __('Send messages via Telegram Bot', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'fields' => array('bot_token', 'default_chat_id')
            ),
            'whatsapp' => array(
                'name' => __('WhatsApp Business', 'workflow-automation'),
                'description' => __('Send messages via WhatsApp Business API', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'fields' => array('phone_number_id', 'access_token', 'webhook_verify_token')
            )
        )
    ),
    'productivity' => array(
        'label' => __('Productivity', 'workflow-automation'),
        'integrations' => array(
            'google' => array(
                'name' => __('Google Services', 'workflow-automation'),
                'description' => __('Access Google Sheets, Drive, and more', 'workflow-automation'),
                'icon' => 'dashicons-media-spreadsheet',
                'fields' => array('client_id', 'client_secret', 'refresh_token', 'service_account_json')
            ),
            'microsoft' => array(
                'name' => __('Microsoft 365', 'workflow-automation'),
                'description' => __('Access Microsoft Office apps and services', 'workflow-automation'),
                'icon' => 'dashicons-admin-site-alt3',
                'fields' => array('tenant_id', 'client_id', 'client_secret', 'refresh_token')
            ),
            'notion' => array(
                'name' => __('Notion', 'workflow-automation'),
                'description' => __('Create and update Notion pages and databases', 'workflow-automation'),
                'icon' => 'dashicons-editor-table',
                'fields' => array('api_key', 'workspace_id')
            )
        )
    ),
    'ai' => array(
        'label' => __('AI & Machine Learning', 'workflow-automation'),
        'integrations' => array(
            'openai' => array(
                'name' => __('OpenAI', 'workflow-automation'),
                'description' => __('Use GPT models for text generation', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'fields' => array('api_key', 'organization_id')
            ),
            'claude' => array(
                'name' => __('Claude (Anthropic)', 'workflow-automation'),
                'description' => __('Use Claude AI for text generation', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'fields' => array('api_key')
            ),
            'gemini' => array(
                'name' => __('Google Gemini', 'workflow-automation'),
                'description' => __('Use Google Gemini AI models', 'workflow-automation'),
                'icon' => 'dashicons-star-filled',
                'fields' => array('api_key')
            )
        )
    ),
    'crm' => array(
        'label' => __('CRM & Marketing', 'workflow-automation'),
        'integrations' => array(
            'hubspot' => array(
                'name' => __('HubSpot', 'workflow-automation'),
                'description' => __('Sync contacts and deals with HubSpot', 'workflow-automation'),
                'icon' => 'dashicons-groups',
                'fields' => array('api_key', 'access_token')
            )
        )
    )
);

// Get existing integrations
$integration_model = new WA_Integration_Settings_Model();
$existing_integrations = array();
foreach ($integration_types as $category_key => $category) {
    foreach ($category['integrations'] as $type => $integration) {
        $existing = $integration_model->get_by_type($type);
        if (!empty($existing)) {
            $existing_integrations[$type] = $existing;
        }
    }
}
?>

<div class="wrap">
    <h1><?php _e('Integrations', 'workflow-automation'); ?></h1>
    <p><?php _e('Configure your external service integrations for use in workflows.', 'workflow-automation'); ?></p>
    
    <div class="wa-integrations-grid">
        <?php foreach ($integration_types as $category_key => $category) : ?>
            <div class="wa-integration-category">
                <h2><?php echo esc_html($category['label']); ?></h2>
                
                <div class="wa-integration-cards">
                    <?php foreach ($category['integrations'] as $type => $integration) : ?>
                        <?php
                        $configured = isset($existing_integrations[$type]) && !empty($existing_integrations[$type]);
                        $active_count = $configured ? count($existing_integrations[$type]) : 0;
                        ?>
                        <div class="wa-integration-card <?php echo $configured ? 'configured' : ''; ?>">
                            <div class="wa-integration-header">
                                <span class="dashicons <?php echo esc_attr($integration['icon']); ?>"></span>
                                <h3><?php echo esc_html($integration['name']); ?></h3>
                                <?php if ($configured) : ?>
                                    <span class="wa-integration-badge"><?php echo sprintf(_n('%d active', '%d active', $active_count, 'workflow-automation'), $active_count); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="wa-integration-description">
                                <?php echo esc_html($integration['description']); ?>
                            </p>
                            
                            <div class="wa-integration-actions">
                                <button type="button" 
                                        class="button wa-configure-integration"
                                        data-integration-type="<?php echo esc_attr($type); ?>"
                                        data-integration-name="<?php echo esc_attr($integration['name']); ?>">
                                    <?php echo $configured ? __('Manage', 'workflow-automation') : __('Configure', 'workflow-automation'); ?>
                                </button>
                                
                                <?php if ($configured) : ?>
                                    <button type="button" 
                                            class="button wa-add-integration"
                                            data-integration-type="<?php echo esc_attr($type); ?>"
                                            data-integration-name="<?php echo esc_attr($integration['name']); ?>">
                                        <?php _e('Add New', 'workflow-automation'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($configured && !empty($existing_integrations[$type])) : ?>
                                <div class="wa-integration-list">
                                    <?php foreach ($existing_integrations[$type] as $config) : ?>
                                        <div class="wa-integration-item">
                                            <span class="wa-integration-name"><?php echo esc_html($config->name); ?></span>
                                            <span class="wa-integration-status <?php echo $config->is_active ? 'active' : 'inactive'; ?>">
                                                <?php echo $config->is_active ? __('Active', 'workflow-automation') : __('Inactive', 'workflow-automation'); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Integration Configuration Modal -->
<div id="wa-integration-modal" class="wa-modal" style="display: none;">
    <div class="wa-modal-content">
        <div class="wa-modal-header">
            <h2 id="wa-modal-title"><?php _e('Configure Integration', 'workflow-automation'); ?></h2>
            <button type="button" class="wa-modal-close">&times;</button>
        </div>
        
        <div class="wa-modal-body">
            <form id="wa-integration-form">
                <input type="hidden" id="integration-type" name="integration_type">
                
                <div class="wa-form-group">
                    <label for="integration-name"><?php _e('Configuration Name', 'workflow-automation'); ?></label>
                    <input type="text" id="integration-name" name="name" class="regular-text" required>
                    <p class="description"><?php _e('A name to identify this configuration', 'workflow-automation'); ?></p>
                </div>
                
                <div id="wa-integration-fields">
                    <!-- Dynamic fields will be inserted here -->
                </div>
                
                <div class="wa-form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" checked>
                        <?php _e('Active', 'workflow-automation'); ?>
                    </label>
                </div>
            </form>
        </div>
        
        <div class="wa-modal-footer">
            <button type="button" class="button button-primary" id="wa-save-integration">
                <?php _e('Save Integration', 'workflow-automation'); ?>
            </button>
            <button type="button" class="button wa-modal-close">
                <?php _e('Cancel', 'workflow-automation'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.wa-integrations-grid {
    margin-top: 20px;
}

.wa-integration-category {
    margin-bottom: 40px;
}

.wa-integration-category h2 {
    font-size: 18px;
    margin-bottom: 15px;
}

.wa-integration-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.wa-integration-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    position: relative;
}

.wa-integration-card.configured {
    border-color: #46b450;
}

.wa-integration-header {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.wa-integration-header .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.wa-integration-header h3 {
    margin: 0;
    flex: 1;
}

.wa-integration-badge {
    background: #46b450;
    color: #fff;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.wa-integration-description {
    color: #555d66;
    margin: 10px 0;
}

.wa-integration-actions {
    margin-top: 15px;
}

.wa-integration-actions .button {
    margin-right: 10px;
}

.wa-integration-list {
    margin-top: 15px;
    border-top: 1px solid #e1e1e1;
    padding-top: 10px;
}

.wa-integration-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
}

.wa-integration-status {
    font-size: 12px;
    padding: 2px 6px;
    border-radius: 3px;
}

.wa-integration-status.active {
    background: #d4edda;
    color: #155724;
}

.wa-integration-status.inactive {
    background: #f8d7da;
    color: #721c24;
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
.wa-form-group input[type="password"],
.wa-form-group input[type="number"],
.wa-form-group textarea {
    width: 100%;
}

.wa-form-group .description {
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    var integrationFields = <?php echo json_encode($integration_types); ?>;
    
    // Configure integration button click
    $('.wa-configure-integration, .wa-add-integration').on('click', function() {
        var type = $(this).data('integration-type');
        var name = $(this).data('integration-name');
        
        $('#integration-type').val(type);
        $('#wa-modal-title').text(name);
        
        // Clear form
        $('#integration-name').val('');
        $('#wa-integration-fields').empty();
        
        // Add fields for this integration type
        var fields = getIntegrationFields(type);
        if (fields) {
            fields.forEach(function(field) {
                var fieldHtml = createFieldHtml(field);
                $('#wa-integration-fields').append(fieldHtml);
            });
        }
        
        $('#wa-integration-modal').show();
    });
    
    // Close modal
    $('.wa-modal-close').on('click', function() {
        $('#wa-integration-modal').hide();
    });
    
    // Save integration
    $('#wa-save-integration').on('click', function() {
        var $button = $(this);
        var formData = $('#wa-integration-form').serializeArray();
        var data = {};
        
        formData.forEach(function(field) {
            data[field.name] = field.value;
        });
        
        $button.prop('disabled', true).text('<?php esc_attr_e('Saving...', 'workflow-automation'); ?>');
        
        // Save via API
        $.ajax({
            url: wa_admin.api_url + '/integrations',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wa_admin.nonce);
            },
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert('<?php esc_attr_e('Failed to save integration. Please try again.', 'workflow-automation'); ?>');
                $button.prop('disabled', false).text('<?php esc_attr_e('Save Integration', 'workflow-automation'); ?>');
            }
        });
    });
    
    function getIntegrationFields(type) {
        for (var category in integrationFields) {
            if (integrationFields[category].integrations[type]) {
                return integrationFields[category].integrations[type].fields;
            }
        }
        return null;
    }
    
    function createFieldHtml(fieldName) {
        var fieldConfig = getFieldConfig(fieldName);
        var html = '<div class="wa-form-group">';
        html += '<label for="field-' + fieldName + '">' + fieldConfig.label + '</label>';
        
        if (fieldConfig.type === 'textarea') {
            html += '<textarea id="field-' + fieldName + '" name="settings[' + fieldName + ']" class="large-text" rows="5"></textarea>';
        } else if (fieldConfig.type === 'select') {
            html += '<select id="field-' + fieldName + '" name="settings[' + fieldName + ']" class="regular-text">';
            for (var value in fieldConfig.options) {
                html += '<option value="' + value + '">' + fieldConfig.options[value] + '</option>';
            }
            html += '</select>';
        } else {
            html += '<input type="' + fieldConfig.type + '" id="field-' + fieldName + '" name="settings[' + fieldName + ']" class="regular-text">';
        }
        
        if (fieldConfig.description) {
            html += '<p class="description">' + fieldConfig.description + '</p>';
        }
        
        html += '</div>';
        return html;
    }
    
    function getFieldConfig(fieldName) {
        var fieldConfigs = {
            // Email/SMTP
            'smtp_host': { label: '<?php esc_attr_e('SMTP Host', 'workflow-automation'); ?>', type: 'text', description: '<?php esc_attr_e('e.g., smtp.gmail.com', 'workflow-automation'); ?>' },
            'smtp_port': { label: '<?php esc_attr_e('SMTP Port', 'workflow-automation'); ?>', type: 'number', description: '<?php esc_attr_e('e.g., 587 for TLS, 465 for SSL', 'workflow-automation'); ?>' },
            'smtp_user': { label: '<?php esc_attr_e('SMTP Username', 'workflow-automation'); ?>', type: 'text' },
            'smtp_pass': { label: '<?php esc_attr_e('SMTP Password', 'workflow-automation'); ?>', type: 'password' },
            'smtp_secure': { 
                label: '<?php esc_attr_e('Encryption', 'workflow-automation'); ?>', 
                type: 'select',
                options: {
                    'tls': 'TLS',
                    'ssl': 'SSL',
                    'none': '<?php esc_attr_e('None', 'workflow-automation'); ?>'
                }
            },
            'from_email': { label: '<?php esc_attr_e('From Email', 'workflow-automation'); ?>', type: 'email' },
            'from_name': { label: '<?php esc_attr_e('From Name', 'workflow-automation'); ?>', type: 'text' },
            
            // API Keys
            'api_key': { label: '<?php esc_attr_e('API Key', 'workflow-automation'); ?>', type: 'password' },
            'access_token': { label: '<?php esc_attr_e('Access Token', 'workflow-automation'); ?>', type: 'password' },
            'bot_token': { label: '<?php esc_attr_e('Bot Token', 'workflow-automation'); ?>', type: 'password' },
            'channel_access_token': { label: '<?php esc_attr_e('Channel Access Token', 'workflow-automation'); ?>', type: 'password' },
            'channel_secret': { label: '<?php esc_attr_e('Channel Secret', 'workflow-automation'); ?>', type: 'password' },
            
            // OAuth
            'client_id': { label: '<?php esc_attr_e('Client ID', 'workflow-automation'); ?>', type: 'text' },
            'client_secret': { label: '<?php esc_attr_e('Client Secret', 'workflow-automation'); ?>', type: 'password' },
            'tenant_id': { label: '<?php esc_attr_e('Tenant ID', 'workflow-automation'); ?>', type: 'text' },
            'refresh_token': { label: '<?php esc_attr_e('Refresh Token', 'workflow-automation'); ?>', type: 'password' },
            
            // Others
            'webhook_url': { label: '<?php esc_attr_e('Webhook URL', 'workflow-automation'); ?>', type: 'text' },
            'default_channel': { label: '<?php esc_attr_e('Default Channel', 'workflow-automation'); ?>', type: 'text', description: '<?php esc_attr_e('e.g., #general', 'workflow-automation'); ?>' },
            'default_chat_id': { label: '<?php esc_attr_e('Default Chat ID', 'workflow-automation'); ?>', type: 'text' },
            'organization_id': { label: '<?php esc_attr_e('Organization ID', 'workflow-automation'); ?>', type: 'text' },
            'workspace_id': { label: '<?php esc_attr_e('Workspace ID', 'workflow-automation'); ?>', type: 'text' },
            'phone_number_id': { label: '<?php esc_attr_e('Phone Number ID', 'workflow-automation'); ?>', type: 'text' },
            'webhook_verify_token': { label: '<?php esc_attr_e('Webhook Verify Token', 'workflow-automation'); ?>', type: 'text' },
            'service_account_json': { 
                label: '<?php esc_attr_e('Service Account JSON', 'workflow-automation'); ?>', 
                type: 'textarea',
                description: '<?php esc_attr_e('Paste the entire service account JSON file contents', 'workflow-automation'); ?>'
            }
        };
        
        return fieldConfigs[fieldName] || { label: fieldName, type: 'text' };
    }
});
</script>