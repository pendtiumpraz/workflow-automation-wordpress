<?php
/**
 * Integration Manager
 * 
 * Manages active integrations and loads their settings into nodes
 */

class WA_Integration_Manager {
    
    private static $instance = null;
    private $active_integrations = array();
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_active_integrations();
    }
    
    /**
     * Load active integrations from WordPress options
     */
    private function load_active_integrations() {
        // Load from options table
        $integrations = get_option('wa_active_integrations', array());
        
        // Check common integration plugins/settings
        $this->check_email_integration();
        $this->check_slack_integration();
        $this->check_openai_integration();
        $this->check_line_integration();
        
        $this->active_integrations = array_merge($integrations, $this->active_integrations);
    }
    
    /**
     * Check if email integration is configured
     */
    private function check_email_integration() {
        // Always available since WordPress has built-in email
        $this->active_integrations['email'] = array(
            'name' => 'WordPress Email',
            'status' => 'active',
            'settings' => array(
                'from_email' => get_option('admin_email'),
                'from_name' => get_bloginfo('name'),
                'smtp_configured' => $this->is_smtp_configured()
            )
        );
    }
    
    /**
     * Check if SMTP is configured
     */
    private function is_smtp_configured() {
        // Check if function exists
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        // Check common SMTP plugins
        if (function_exists('is_plugin_active')) {
            if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php')) {
                return true;
            }
            if (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check Slack integration
     */
    private function check_slack_integration() {
        $slack_webhook = get_option('wa_slack_webhook_url');
        
        if (!empty($slack_webhook)) {
            $this->active_integrations['slack'] = array(
                'name' => 'Slack',
                'status' => 'active',
                'settings' => array(
                    'webhook_url' => $slack_webhook,
                    'default_channel' => get_option('wa_slack_default_channel', '#general'),
                    'bot_name' => get_option('wa_slack_bot_name', 'Workflow Bot')
                )
            );
        }
    }
    
    /**
     * Check OpenAI integration
     */
    private function check_openai_integration() {
        $openai_key = get_option('wa_openai_api_key');
        
        if (!empty($openai_key)) {
            $this->active_integrations['openai'] = array(
                'name' => 'OpenAI',
                'status' => 'active',
                'settings' => array(
                    'api_key' => $openai_key,
                    'default_model' => get_option('wa_openai_default_model', 'gpt-3.5-turbo'),
                    'max_tokens' => get_option('wa_openai_max_tokens', 500)
                )
            );
        }
    }
    
    /**
     * Check LINE integration
     */
    private function check_line_integration() {
        $line_token = get_option('wa_line_channel_access_token');
        
        if (!empty($line_token)) {
            $this->active_integrations['line'] = array(
                'name' => 'LINE',
                'status' => 'active',
                'settings' => array(
                    'channel_access_token' => $line_token,
                    'channel_secret' => get_option('wa_line_channel_secret')
                )
            );
        }
    }
    
    /**
     * Get active integrations
     */
    public function get_active_integrations() {
        return $this->active_integrations;
    }
    
    /**
     * Check if integration is active
     */
    public function is_integration_active($type) {
        return isset($this->active_integrations[$type]) && 
               $this->active_integrations[$type]['status'] === 'active';
    }
    
    /**
     * Get integration settings
     */
    public function get_integration_settings($type) {
        if ($this->is_integration_active($type)) {
            return $this->active_integrations[$type]['settings'];
        }
        return array();
    }
    
    /**
     * Get prefilled node data based on active integrations
     */
    public function get_node_defaults($node_type) {
        $defaults = array();
        
        switch ($node_type) {
            case 'email':
                if ($this->is_integration_active('email')) {
                    $settings = $this->get_integration_settings('email');
                    $defaults = array(
                        'from_email' => $settings['from_email'],
                        'from_name' => $settings['from_name']
                    );
                }
                break;
                
            case 'slack':
                if ($this->is_integration_active('slack')) {
                    $settings = $this->get_integration_settings('slack');
                    $defaults = array(
                        'webhook_url' => $settings['webhook_url'],
                        'channel' => $settings['default_channel'],
                        'username' => $settings['bot_name']
                    );
                }
                break;
                
            case 'openai':
                if ($this->is_integration_active('openai')) {
                    $settings = $this->get_integration_settings('openai');
                    $defaults = array(
                        'api_key' => $settings['api_key'],
                        'model' => $settings['default_model'],
                        'max_tokens' => $settings['max_tokens']
                    );
                }
                break;
                
            case 'line':
                if ($this->is_integration_active('line')) {
                    $settings = $this->get_integration_settings('line');
                    $defaults = array(
                        'channel_access_token' => $settings['channel_access_token']
                    );
                }
                break;
        }
        
        return $defaults;
    }
    
    /**
     * Save integration settings
     */
    public function save_integration_settings($type, $settings) {
        switch ($type) {
            case 'slack':
                update_option('wa_slack_webhook_url', $settings['webhook_url'] ?? '');
                update_option('wa_slack_default_channel', $settings['default_channel'] ?? '#general');
                update_option('wa_slack_bot_name', $settings['bot_name'] ?? 'Workflow Bot');
                break;
                
            case 'openai':
                update_option('wa_openai_api_key', $settings['api_key'] ?? '');
                update_option('wa_openai_default_model', $settings['default_model'] ?? 'gpt-3.5-turbo');
                update_option('wa_openai_max_tokens', $settings['max_tokens'] ?? 500);
                break;
                
            case 'line':
                update_option('wa_line_channel_access_token', $settings['channel_access_token'] ?? '');
                update_option('wa_line_channel_secret', $settings['channel_secret'] ?? '');
                break;
        }
        
        // Reload integrations
        $this->load_active_integrations();
    }
    
    /**
     * Get integration status for display
     */
    public function get_integration_status_display() {
        $status = array();
        
        foreach ($this->active_integrations as $type => $integration) {
            $status[] = array(
                'type' => $type,
                'name' => $integration['name'],
                'status' => $integration['status'],
                'icon' => $this->get_integration_icon($type)
            );
        }
        
        return $status;
    }
    
    /**
     * Get integration icon
     */
    private function get_integration_icon($type) {
        $icons = array(
            'email' => '📧',
            'slack' => '💬',
            'openai' => '🤖',
            'line' => '💚'
        );
        
        return $icons[$type] ?? '🔧';
    }
}