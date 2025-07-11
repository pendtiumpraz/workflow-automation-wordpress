<?php
/**
 * Integration Settings Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Integration Settings Model class
 *
 * Handles CRUD operations for integration settings
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Integration_Settings_Model {

    /**
     * The table name
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name
     */
    private $table_name;

    /**
     * Initialize the model
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wa_integration_settings';
    }

    /**
     * Get integration settings by type
     *
     * @since    1.0.0
     * @param    string    $integration_type    The integration type
     * @param    int       $organization_id     The organization ID (optional)
     * @return   array
     */
    public function get_by_type($integration_type, $organization_id = null) {
        global $wpdb;
        
        $where = array('integration_type = %s', 'is_active = 1');
        $where_values = array($integration_type);
        
        if ($organization_id !== null) {
            $where[] = 'organization_id = %d';
            $where_values[] = $organization_id;
        } else {
            // For single-site WordPress, use NULL organization
            $where[] = 'organization_id IS NULL';
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY name ASC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $result) {
            if ($result->settings) {
                $result->settings = json_decode($result->settings, true);
            }
        }
        
        return $results;
    }

    /**
     * Get single integration setting
     *
     * @since    1.0.0
     * @param    int    $id    The setting ID
     * @return   object|null
     */
    public function get($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($sql);
        
        if ($result && $result->settings) {
            $result->settings = json_decode($result->settings, true);
        }
        
        return $result;
    }

    /**
     * Get integration by name
     *
     * @since    1.0.0
     * @param    string    $integration_type    The integration type
     * @param    string    $name                The setting name
     * @param    int       $organization_id     The organization ID (optional)
     * @return   object|null
     */
    public function get_by_name($integration_type, $name, $organization_id = null) {
        global $wpdb;
        
        if ($organization_id !== null) {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE integration_type = %s AND name = %s AND organization_id = %d",
                $integration_type,
                $name,
                $organization_id
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE integration_type = %s AND name = %s AND organization_id IS NULL",
                $integration_type,
                $name
            );
        }
        
        $result = $wpdb->get_row($sql);
        
        if ($result && $result->settings) {
            $result->settings = json_decode($result->settings, true);
        }
        
        return $result;
    }

    /**
     * Create integration settings
     *
     * @since    1.0.0
     * @param    array    $data    Integration data
     * @return   int|false    The integration ID or false on failure
     */
    public function create($data) {
        global $wpdb;
        
        $defaults = array(
            'integration_type' => '',
            'organization_id' => null,
            'name' => '',
            'settings' => array(),
            'is_active' => true,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Encode settings as JSON
        if (is_array($data['settings'])) {
            // Encrypt sensitive data before storing
            $data['settings'] = $this->encrypt_settings($data['settings']);
            $data['settings'] = json_encode($data['settings']);
        }
        
        $result = $wpdb->insert($this->table_name, $data);
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Update integration settings
     *
     * @since    1.0.0
     * @param    int      $id      The integration ID
     * @param    array    $data    Integration data
     * @return   bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        // Always update the updated_at timestamp
        $data['updated_at'] = current_time('mysql');
        
        // Encode settings as JSON if present
        if (isset($data['settings']) && is_array($data['settings'])) {
            // Encrypt sensitive data before storing
            $data['settings'] = $this->encrypt_settings($data['settings']);
            $data['settings'] = json_encode($data['settings']);
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id)
        );
        
        return $result !== false;
    }

    /**
     * Delete integration settings
     *
     * @since    1.0.0
     * @param    int    $id    The integration ID
     * @return   bool
     */
    public function delete($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get available integration types
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_integration_types() {
        return array(
            'slack' => array(
                'label' => __('Slack', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'fields' => array(
                    array(
                        'key' => 'workspace_name',
                        'label' => __('Workspace Name', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('Name to identify this Slack workspace', 'workflow-automation')
                    ),
                    array(
                        'key' => 'webhook_url',
                        'label' => __('Incoming Webhook URL', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'https://hooks.slack.com/services/...',
                        'description' => __('Get this from your Slack app settings', 'workflow-automation')
                    ),
                    array(
                        'key' => 'bot_token',
                        'label' => __('Bot User OAuth Token', 'workflow-automation'),
                        'type' => 'password',
                        'placeholder' => 'xoxb-...',
                        'description' => __('Optional: For advanced features like channel listing', 'workflow-automation')
                    ),
                    array(
                        'key' => 'default_channel',
                        'label' => __('Default Channel', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => '#general',
                        'description' => __('Default channel for messages', 'workflow-automation')
                    )
                )
            ),
            'email' => array(
                'label' => __('Email (SMTP)', 'workflow-automation'),
                'icon' => 'dashicons-email',
                'fields' => array(
                    array(
                        'key' => 'smtp_host',
                        'label' => __('SMTP Host', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'smtp.gmail.com',
                        'description' => __('SMTP server hostname', 'workflow-automation')
                    ),
                    array(
                        'key' => 'smtp_port',
                        'label' => __('SMTP Port', 'workflow-automation'),
                        'type' => 'number',
                        'required' => true,
                        'default' => 587,
                        'description' => __('Usually 587 for TLS, 465 for SSL', 'workflow-automation')
                    ),
                    array(
                        'key' => 'smtp_secure',
                        'label' => __('Encryption', 'workflow-automation'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'tls' => 'TLS',
                            'ssl' => 'SSL',
                            'none' => __('None', 'workflow-automation')
                        ),
                        'default' => 'tls'
                    ),
                    array(
                        'key' => 'smtp_username',
                        'label' => __('SMTP Username', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('Your email address or username', 'workflow-automation')
                    ),
                    array(
                        'key' => 'smtp_password',
                        'label' => __('SMTP Password', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('Your email password or app password', 'workflow-automation')
                    ),
                    array(
                        'key' => 'from_email',
                        'label' => __('From Email', 'workflow-automation'),
                        'type' => 'email',
                        'required' => true,
                        'description' => __('Default sender email address', 'workflow-automation')
                    ),
                    array(
                        'key' => 'from_name',
                        'label' => __('From Name', 'workflow-automation'),
                        'type' => 'text',
                        'description' => __('Default sender name', 'workflow-automation')
                    )
                )
            ),
            'openai' => array(
                'label' => __('OpenAI', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'fields' => array(
                    array(
                        'key' => 'api_key',
                        'label' => __('API Key', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => 'sk-...',
                        'description' => __('Your OpenAI API key', 'workflow-automation')
                    ),
                    array(
                        'key' => 'organization_id',
                        'label' => __('Organization ID', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'org-...',
                        'description' => __('Optional: Your OpenAI organization ID', 'workflow-automation')
                    ),
                    array(
                        'key' => 'default_model',
                        'label' => __('Default Model', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'gpt-4' => 'GPT-4',
                            'gpt-4-turbo-preview' => 'GPT-4 Turbo',
                            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                            'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16K'
                        ),
                        'default' => 'gpt-3.5-turbo'
                    )
                )
            ),
            'google' => array(
                'label' => __('Google Services', 'workflow-automation'),
                'icon' => 'dashicons-google',
                'fields' => array(
                    array(
                        'key' => 'auth_type',
                        'label' => __('Authentication Type', 'workflow-automation'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'oauth' => __('OAuth 2.0 (for user data)', 'workflow-automation'),
                            'service_account' => __('Service Account (for automation)', 'workflow-automation')
                        ),
                        'default' => 'service_account',
                        'description' => __('Service Account recommended for automation workflows', 'workflow-automation')
                    ),
                    array(
                        'key' => 'client_id',
                        'label' => __('Client ID', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('OAuth 2.0 Client ID', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'auth_type',
                            'operator' => '==',
                            'value' => 'oauth'
                        )
                    ),
                    array(
                        'key' => 'client_secret',
                        'label' => __('Client Secret', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('OAuth 2.0 Client Secret', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'auth_type',
                            'operator' => '==',
                            'value' => 'oauth'
                        )
                    ),
                    array(
                        'key' => 'service_account_json',
                        'label' => __('Service Account JSON', 'workflow-automation'),
                        'type' => 'textarea',
                        'required' => true,
                        'rows' => 10,
                        'placeholder' => '{"type": "service_account", "project_id": "...", ...}',
                        'description' => __('Paste the entire JSON key file content from Google Cloud Console', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'auth_type',
                            'operator' => '==',
                            'value' => 'service_account'
                        )
                    ),
                    array(
                        'key' => 'scopes',
                        'label' => __('API Scopes', 'workflow-automation'),
                        'type' => 'text',
                        'default' => 'https://www.googleapis.com/auth/spreadsheets,https://www.googleapis.com/auth/drive',
                        'description' => __('Comma-separated list of Google API scopes', 'workflow-automation')
                    )
                )
            ),
            'line' => array(
                'label' => __('LINE Official Account', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'fields' => array(
                    array(
                        'key' => 'channel_id',
                        'label' => __('Channel ID', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('LINE Channel ID from LINE Developers Console', 'workflow-automation')
                    ),
                    array(
                        'key' => 'channel_secret',
                        'label' => __('Channel Secret', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('LINE Channel Secret for webhook signature verification', 'workflow-automation')
                    ),
                    array(
                        'key' => 'channel_access_token',
                        'label' => __('Channel Access Token', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => 'Long-lived access token',
                        'description' => __('Channel access token from Messaging API settings', 'workflow-automation')
                    ),
                    array(
                        'key' => 'webhook_url',
                        'label' => __('Webhook URL (Read Only)', 'workflow-automation'),
                        'type' => 'text',
                        'readonly' => true,
                        'placeholder' => 'Will be generated automatically',
                        'description' => __('Copy this URL to LINE Developers Console webhook settings', 'workflow-automation')
                    ),
                    array(
                        'key' => 'enable_webhook_redelivery',
                        'label' => __('Enable Webhook Redelivery', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Redeliver webhooks if bot server fails to receive', 'workflow-automation')
                    ),
                    array(
                        'key' => 'official_account_name',
                        'label' => __('Official Account Name', 'workflow-automation'),
                        'type' => 'text',
                        'description' => __('Display name for identification', 'workflow-automation')
                    )
                )
            ),
            'claude' => array(
                'label' => __('Claude (Anthropic)', 'workflow-automation'),
                'icon' => 'dashicons-admin-generic',
                'fields' => array(
                    array(
                        'key' => 'api_key',
                        'label' => __('API Key', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => 'sk-ant-api03-...',
                        'description' => __('Your Anthropic API key', 'workflow-automation')
                    ),
                    array(
                        'key' => 'default_model',
                        'label' => __('Default Model', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'claude-3-opus-20240229' => 'Claude 3 Opus',
                            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                            'claude-3-haiku-20240307' => 'Claude 3 Haiku',
                            'claude-2.1' => 'Claude 2.1',
                            'claude-2.0' => 'Claude 2.0'
                        ),
                        'default' => 'claude-3-sonnet-20240229'
                    ),
                    array(
                        'key' => 'max_tokens',
                        'label' => __('Max Tokens', 'workflow-automation'),
                        'type' => 'number',
                        'default' => 1000,
                        'min' => 1,
                        'max' => 100000,
                        'description' => __('Maximum tokens in response', 'workflow-automation')
                    )
                )
            ),
            'gemini' => array(
                'label' => __('Google Gemini', 'workflow-automation'),
                'icon' => 'dashicons-star-filled',
                'fields' => array(
                    array(
                        'key' => 'api_key',
                        'label' => __('API Key', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('Your Google AI API key for Gemini', 'workflow-automation')
                    ),
                    array(
                        'key' => 'default_model',
                        'label' => __('Default Model', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'gemini-pro' => 'Gemini Pro',
                            'gemini-pro-vision' => 'Gemini Pro Vision'
                        ),
                        'default' => 'gemini-pro'
                    )
                )
            ),
            'microsoft' => array(
                'label' => __('Microsoft 365', 'workflow-automation'),
                'icon' => 'dashicons-admin-site-alt3',
                'fields' => array(
                    array(
                        'key' => 'tenant_id',
                        'label' => __('Tenant ID', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('Azure AD Tenant ID', 'workflow-automation')
                    ),
                    array(
                        'key' => 'client_id',
                        'label' => __('Application (Client) ID', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('Azure AD App Registration Client ID', 'workflow-automation')
                    ),
                    array(
                        'key' => 'client_secret',
                        'label' => __('Client Secret', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('Azure AD App Registration Secret', 'workflow-automation')
                    ),
                    array(
                        'key' => 'redirect_uri',
                        'label' => __('Redirect URI', 'workflow-automation'),
                        'type' => 'text',
                        'readonly' => true,
                        'default' => admin_url('admin.php?page=workflow-automation-integrations&oauth=microsoft'),
                        'description' => __('Add this to Azure AD App Registration', 'workflow-automation')
                    )
                )
            ),
            'notion' => array(
                'label' => __('Notion', 'workflow-automation'),
                'icon' => 'dashicons-editor-table',
                'fields' => array(
                    array(
                        'key' => 'api_key',
                        'label' => __('Integration Token', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => 'secret_...',
                        'description' => __('Internal Integration Token from Notion', 'workflow-automation')
                    ),
                    array(
                        'key' => 'version',
                        'label' => __('API Version', 'workflow-automation'),
                        'type' => 'text',
                        'default' => '2022-06-28',
                        'description' => __('Notion API version', 'workflow-automation')
                    )
                )
            ),
            'hubspot' => array(
                'label' => __('HubSpot', 'workflow-automation'),
                'icon' => 'dashicons-groups',
                'fields' => array(
                    array(
                        'key' => 'api_key',
                        'label' => __('Private App Access Token', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('Access token from HubSpot private app', 'workflow-automation')
                    ),
                    array(
                        'key' => 'portal_id',
                        'label' => __('Portal ID', 'workflow-automation'),
                        'type' => 'text',
                        'description' => __('Your HubSpot portal ID', 'workflow-automation')
                    )
                )
            ),
            'telegram' => array(
                'label' => __('Telegram Bot', 'workflow-automation'),
                'icon' => 'dashicons-format-status',
                'fields' => array(
                    array(
                        'key' => 'bot_token',
                        'label' => __('Bot Token', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11',
                        'description' => __('Bot token from @BotFather', 'workflow-automation')
                    ),
                    array(
                        'key' => 'bot_username',
                        'label' => __('Bot Username', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => '@YourBotName',
                        'description' => __('Bot username for reference', 'workflow-automation')
                    )
                )
            ),
            'whatsapp' => array(
                'label' => __('WhatsApp Business', 'workflow-automation'),
                'icon' => 'dashicons-format-chat',
                'fields' => array(
                    array(
                        'key' => 'phone_number_id',
                        'label' => __('Phone Number ID', 'workflow-automation'),
                        'type' => 'text',
                        'required' => true,
                        'description' => __('WhatsApp Business phone number ID', 'workflow-automation')
                    ),
                    array(
                        'key' => 'access_token',
                        'label' => __('Access Token', 'workflow-automation'),
                        'type' => 'password',
                        'required' => true,
                        'description' => __('WhatsApp Business API access token', 'workflow-automation')
                    ),
                    array(
                        'key' => 'webhook_verify_token',
                        'label' => __('Webhook Verify Token', 'workflow-automation'),
                        'type' => 'text',
                        'default' => wp_generate_password(32, false),
                        'description' => __('Token for webhook verification', 'workflow-automation')
                    )
                )
            )
        );
    }

    /**
     * Encrypt sensitive settings
     *
     * @since    1.0.0
     * @param    array    $settings    The settings to encrypt
     * @return   array
     */
    private function encrypt_settings($settings) {
        $sensitive_fields = array('password', 'api_key', 'secret', 'token', 'webhook_url', 'smtp_password', 'client_secret', 'refresh_token', 'access_token', 'bot_token');
        
        foreach ($settings as $key => $value) {
            // Check if field name contains sensitive keywords
            foreach ($sensitive_fields as $field) {
                if (stripos($key, $field) !== false && !empty($value)) {
                    // Encrypt the value
                    $settings[$key] = $this->encrypt_value($value);
                    break;
                }
            }
        }
        
        return $settings;
    }

    /**
     * Decrypt sensitive settings
     *
     * @since    1.0.0
     * @param    array    $settings    The settings to decrypt
     * @return   array
     */
    public function decrypt_settings($settings) {
        $sensitive_fields = array('password', 'api_key', 'secret', 'token', 'webhook_url', 'smtp_password', 'client_secret', 'refresh_token', 'access_token', 'bot_token');
        
        foreach ($settings as $key => $value) {
            // Check if field name contains sensitive keywords
            foreach ($sensitive_fields as $field) {
                if (stripos($key, $field) !== false && !empty($value)) {
                    // Decrypt the value
                    $settings[$key] = $this->decrypt_value($value);
                    break;
                }
            }
        }
        
        return $settings;
    }

    /**
     * Encrypt a value
     *
     * @since    1.0.0
     * @param    string    $value    The value to encrypt
     * @return   string
     */
    private function encrypt_value($value) {
        // Use WordPress salt for encryption
        $key = wp_salt('auth');
        $method = 'AES-256-CBC';
        
        if (function_exists('openssl_encrypt')) {
            $iv_length = openssl_cipher_iv_length($method);
            $iv = openssl_random_pseudo_bytes($iv_length);
            $encrypted = openssl_encrypt($value, $method, $key, 0, $iv);
            
            // Return base64 encoded string with IV
            return base64_encode($encrypted . '::' . $iv);
        }
        
        // Fallback to base64 if OpenSSL not available
        return base64_encode($value);
    }

    /**
     * Decrypt a value
     *
     * @since    1.0.0
     * @param    string    $value    The value to decrypt
     * @return   string
     */
    private function decrypt_value($value) {
        // Use WordPress salt for decryption
        $key = wp_salt('auth');
        $method = 'AES-256-CBC';
        
        if (function_exists('openssl_decrypt')) {
            $data = base64_decode($value);
            if (strpos($data, '::') !== false) {
                list($encrypted, $iv) = explode('::', $data, 2);
                return openssl_decrypt($encrypted, $method, $key, 0, $iv);
            }
        }
        
        // Fallback to base64 decode
        return base64_decode($value);
    }
}