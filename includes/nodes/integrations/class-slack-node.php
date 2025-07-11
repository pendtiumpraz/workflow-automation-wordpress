<?php
/**
 * Slack Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 */

/**
 * Slack node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Slack_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'slack';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Slack Message', 'workflow-automation'),
            'description' => __('Send a message to Slack', 'workflow-automation'),
            'icon' => 'dashicons-format-status',
            'category' => 'actions',
            'can_be_start' => false
        );
    }

    /**
     * Get settings fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_settings_fields() {
        // Get available Slack integrations
        $integration_model = new Integration_Settings_Model();
        $slack_integrations = $integration_model->get_by_type('slack');
        
        $integration_options = array(
            '' => __('-- Select Slack Configuration --', 'workflow-automation')
        );
        
        foreach ($slack_integrations as $integration) {
            if ($integration->is_active) {
                $integration_options[$integration->id] = $integration->name;
            }
        }
        
        $fields = array();
        
        // If we have integrations, show selector
        if (count($integration_options) > 1) {
            $fields[] = array(
                'key' => 'integration_id',
                'label' => __('Slack Configuration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select a Slack configuration to use', 'workflow-automation')
            );
            
            $fields[] = array(
                'key' => 'use_custom_webhook',
                'label' => __('Use Custom Webhook', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Override the configuration webhook with a custom one', 'workflow-automation')
            );
        }
        
        // Always show webhook URL field (required if no integrations or custom webhook)
        $fields[] = array(
            'key' => 'webhook_url',
            'label' => __('Webhook URL', 'workflow-automation'),
            'type' => 'text',
            'required' => count($integration_options) <= 1, // Required if no integrations
            'placeholder' => 'https://hooks.slack.com/services/...',
            'description' => __('Slack incoming webhook URL', 'workflow-automation'),
            'condition' => count($integration_options) > 1 ? array(
                'field' => 'use_custom_webhook',
                'operator' => '==',
                'value' => true
            ) : null
        );
        
        // Add remaining fields
        $fields = array_merge($fields, array(
            array(
                'key' => 'channel',
                'label' => __('Channel', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '#general',
                'description' => __('Channel to post to (optional, uses webhook default if empty)', 'workflow-automation')
            ),
            array(
                'key' => 'username',
                'label' => __('Username', 'workflow-automation'),
                'type' => 'text',
                'default' => 'Workflow Bot',
                'description' => __('Bot username', 'workflow-automation')
            ),
            array(
                'key' => 'icon_emoji',
                'label' => __('Icon Emoji', 'workflow-automation'),
                'type' => 'text',
                'default' => ':robot_face:',
                'placeholder' => ':robot_face:',
                'description' => __('Bot icon emoji', 'workflow-automation')
            ),
            array(
                'key' => 'text',
                'label' => __('Message Text', 'workflow-automation'),
                'type' => 'textarea',
                'required' => true,
                'rows' => 5,
                'placeholder' => __('Your message here...', 'workflow-automation'),
                'description' => __('Message text. Use {{variables}} for dynamic values. Supports Slack markdown.', 'workflow-automation')
            ),
            array(
                'key' => 'use_blocks',
                'label' => __('Use Block Kit', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Use Slack Block Kit for rich formatting', 'workflow-automation')
            ),
            array(
                'key' => 'blocks',
                'label' => __('Blocks (JSON)', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => '[{"type": "section", "text": {"type": "mrkdwn", "text": "Hello"}}]',
                'description' => __('Slack Block Kit blocks in JSON format', 'workflow-automation'),
                'condition' => array(
                    'field' => 'use_blocks',
                    'operator' => '==',
                    'value' => true
                )
            ),
            array(
                'key' => 'link_names',
                'label' => __('Link Names', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Find and link channel names and usernames', 'workflow-automation')
            ),
            array(
                'key' => 'unfurl_links',
                'label' => __('Unfurl Links', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Enable unfurling of primarily text-based content', 'workflow-automation')
            ),
            array(
                'key' => 'unfurl_media',
                'label' => __('Unfurl Media', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Enable unfurling of media content', 'workflow-automation')
            )
        ));
        
        return $fields;
    }

    /**
     * Execute the node
     *
     * @since    1.0.0
     * @param    array    $context         The execution context
     * @param    mixed    $previous_data   Data from previous node
     * @return   mixed
     */
    public function execute($context, $previous_data) {
        // Get webhook URL from integration or direct input
        $webhook_url = '';
        $integration_id = $this->get_setting('integration_id', '');
        $use_custom_webhook = $this->get_setting('use_custom_webhook', false);
        
        if (!empty($integration_id) && !$use_custom_webhook) {
            // Use webhook from integration settings
            $integration_model = new Integration_Settings_Model();
            $integration = $integration_model->get($integration_id);
            
            if (!$integration || !$integration->is_active) {
                throw new Exception('Selected Slack integration is not available or inactive');
            }
            
            // Decrypt settings to get webhook URL
            $settings = $integration_model->decrypt_settings($integration->settings);
            
            if (empty($settings['webhook_url'])) {
                throw new Exception('Slack integration does not have a webhook URL configured');
            }
            
            $webhook_url = $settings['webhook_url'];
            
            // Use default channel if not specified in node
            $channel = $this->get_setting('channel', '');
            if (empty($channel) && !empty($settings['default_channel'])) {
                $this->settings['channel'] = $settings['default_channel'];
            }
        } else {
            // Use custom webhook URL
            $webhook_url = $this->replace_variables($this->get_setting('webhook_url', ''), $context);
        }
        
        if (empty($webhook_url)) {
            throw new Exception('Slack webhook URL is required');
        }
        
        // Build payload
        $payload = array();
        
        // Channel
        $channel = $this->replace_variables($this->get_setting('channel', ''), $context);
        if (!empty($channel)) {
            $payload['channel'] = $channel;
        }
        
        // Username
        $username = $this->replace_variables($this->get_setting('username', 'Workflow Bot'), $context);
        if (!empty($username)) {
            $payload['username'] = $username;
        }
        
        // Icon
        $icon_emoji = $this->replace_variables($this->get_setting('icon_emoji', ':robot_face:'), $context);
        if (!empty($icon_emoji)) {
            $payload['icon_emoji'] = $icon_emoji;
        }
        
        // Message content
        $use_blocks = $this->get_setting('use_blocks', false);
        
        if ($use_blocks) {
            // Use Block Kit
            $blocks_json = $this->replace_variables($this->get_setting('blocks', ''), $context);
            
            if (empty($blocks_json)) {
                throw new Exception('Blocks JSON is required when using Block Kit');
            }
            
            $blocks = json_decode($blocks_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in blocks: ' . json_last_error_msg());
            }
            
            $payload['blocks'] = $blocks;
            
            // Add text as fallback
            $text = $this->replace_variables($this->get_setting('text', ''), $context);
            if (!empty($text)) {
                $payload['text'] = $text;
            }
        } else {
            // Use simple text
            $text = $this->replace_variables($this->get_setting('text', ''), $context);
            
            if (empty($text)) {
                throw new Exception('Message text is required');
            }
            
            $payload['text'] = $text;
        }
        
        // Additional options
        $payload['link_names'] = $this->get_setting('link_names', true);
        $payload['unfurl_links'] = $this->get_setting('unfurl_links', true);
        $payload['unfurl_media'] = $this->get_setting('unfurl_media', true);
        
        // Send to Slack
        $response = $this->http_request($webhook_url, array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($payload)
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Failed to send Slack message: ' . $response->get_error_message());
        }
        
        if ($response['code'] !== 200) {
            throw new Exception(sprintf(
                'Slack API error: %s (HTTP %d)',
                $response['body'],
                $response['code']
            ));
        }
        
        $this->log('Slack message sent successfully');
        
        // Return output
        return array(
            'sent' => true,
            'channel' => $channel,
            'response' => $response['body'],
            'sent_at' => current_time('mysql')
        );
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $webhook_url = $this->get_setting('webhook_url', '');
        $text = $this->get_setting('text', '');
        $use_blocks = $this->get_setting('use_blocks', false);
        $blocks = $this->get_setting('blocks', '');
        
        if (empty($webhook_url)) {
            return new WP_Error('missing_webhook', __('Slack webhook URL is required', 'workflow-automation'));
        }
        
        // Validate URL format if not a variable
        if (strpos($webhook_url, '{{') === false && !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_webhook', __('Invalid webhook URL', 'workflow-automation'));
        }
        
        if ($use_blocks) {
            if (empty($blocks)) {
                return new WP_Error('missing_blocks', __('Blocks JSON is required when using Block Kit', 'workflow-automation'));
            }
            
            // Validate JSON if not a variable
            if (strpos($blocks, '{{') === false) {
                json_decode($blocks);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', __('Invalid JSON in blocks', 'workflow-automation'));
                }
            }
        } else {
            if (empty($text)) {
                return new WP_Error('missing_text', __('Message text is required', 'workflow-automation'));
            }
        }
        
        return true;
    }
}