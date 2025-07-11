<?php
/**
 * LINE Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 */

/**
 * LINE node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_LINE_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'line';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('LINE Message', 'workflow-automation'),
            'description' => __('Send messages via LINE Messaging API', 'workflow-automation'),
            'icon' => 'dashicons-format-chat',
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
        // Get available LINE integrations
        $integration_model = new Integration_Settings_Model();
        $line_integrations = $integration_model->get_by_type('line');
        
        $integration_options = array(
            '' => __('-- Select LINE Configuration --', 'workflow-automation')
        );
        
        foreach ($line_integrations as $integration) {
            if ($integration->is_active) {
                $integration_options[$integration->id] = $integration->name;
            }
        }
        
        return array(
            array(
                'key' => 'integration_id',
                'label' => __('LINE Configuration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select a LINE Official Account configuration', 'workflow-automation')
            ),
            array(
                'key' => 'message_type',
                'label' => __('Message Type', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'default' => 'reply',
                'options' => array(
                    'reply' => __('Reply Message', 'workflow-automation'),
                    'push' => __('Push Message', 'workflow-automation'),
                    'multicast' => __('Multicast Message', 'workflow-automation'),
                    'broadcast' => __('Broadcast Message', 'workflow-automation')
                ),
                'description' => __('Type of message to send', 'workflow-automation')
            ),
            array(
                'key' => 'reply_token',
                'label' => __('Reply Token', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '{{line_reply_token}}',
                'description' => __('Reply token from webhook event. Use {{line_reply_token}} from webhook trigger.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'message_type',
                    'operator' => '==',
                    'value' => 'reply'
                )
            ),
            array(
                'key' => 'to',
                'label' => __('Recipient', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '{{line_user_id}}',
                'description' => __('User ID, group ID, or room ID. Use {{line_user_id}} from webhook.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'message_type',
                    'operator' => '==',
                    'value' => 'push'
                )
            ),
            array(
                'key' => 'to_list',
                'label' => __('Recipients', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => "U1234567890abcdef\nU2345678901bcdefg\n{{user_ids}}",
                'description' => __('List of user IDs, one per line. Max 500 recipients.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'message_type',
                    'operator' => '==',
                    'value' => 'multicast'
                )
            ),
            array(
                'key' => 'messages',
                'label' => __('Messages', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'type_1',
                        'label' => __('Message 1 Type', 'workflow-automation'),
                        'type' => 'select',
                        'default' => 'text',
                        'options' => array(
                            'text' => __('Text', 'workflow-automation'),
                            'sticker' => __('Sticker', 'workflow-automation'),
                            'image' => __('Image', 'workflow-automation'),
                            'video' => __('Video', 'workflow-automation'),
                            'audio' => __('Audio', 'workflow-automation'),
                            'location' => __('Location', 'workflow-automation'),
                            'template' => __('Template', 'workflow-automation'),
                            'flex' => __('Flex Message', 'workflow-automation')
                        )
                    ),
                    array(
                        'key' => 'text_1',
                        'label' => __('Message Text', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => __('Your message here. Use {{variables}} for dynamic content.', 'workflow-automation'),
                        'description' => __('Text message content (max 5000 characters)', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type_1',
                            'operator' => '==',
                            'value' => 'text'
                        )
                    ),
                    array(
                        'key' => 'emojis_1',
                        'label' => __('LINE Emojis', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => '{"index": 14, "productId": "5ac1bfd5040ab15980c9b435", "emojiId": "001"}',
                        'description' => __('JSON array of emoji objects for text message', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type_1',
                            'operator' => '==',
                            'value' => 'text'
                        )
                    )
                )
            ),
            array(
                'key' => 'quick_reply',
                'label' => __('Quick Reply', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Add quick reply buttons to the message', 'workflow-automation')
            ),
            array(
                'key' => 'quick_reply_items',
                'label' => __('Quick Reply Items', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => '[
  {
    "type": "action",
    "action": {
      "type": "message",
      "label": "Yes",
      "text": "Yes"
    }
  },
  {
    "type": "action",
    "action": {
      "type": "message",
      "label": "No",
      "text": "No"
    }
  }
]',
                'description' => __('JSON array of quick reply items (max 13 items)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'quick_reply',
                    'operator' => '==',
                    'value' => true
                )
            ),
            array(
                'key' => 'notification_disabled',
                'label' => __('Disable Notification', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => false,
                'description' => __('Send message without push notification', 'workflow-automation')
            ),
            array(
                'key' => 'custom_aggregation_units',
                'label' => __('Custom Aggregation Units', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => '["promotion_a", "promotion_b"]',
                'description' => __('Name of aggregation unit (JSON array). Max 1 for push/multicast, max 3 for broadcast.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'message_type',
                    'operator' => 'in',
                    'value' => array('push', 'multicast', 'broadcast')
                )
            )
        );
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
        // Get integration settings
        $integration_id = $this->get_setting('integration_id', '');
        if (empty($integration_id)) {
            throw new Exception('LINE configuration is required');
        }
        
        $integration_model = new Integration_Settings_Model();
        $integration = $integration_model->get($integration_id);
        
        if (!$integration || !$integration->is_active) {
            throw new Exception('Selected LINE integration is not available or inactive');
        }
        
        // Decrypt settings
        $settings = $integration_model->decrypt_settings($integration->settings);
        
        if (empty($settings['channel_access_token'])) {
            throw new Exception('LINE channel access token is not configured');
        }
        
        $access_token = $settings['channel_access_token'];
        
        // Get message type
        $message_type = $this->get_setting('message_type', 'reply');
        
        // Build messages array
        $messages = $this->build_messages($context);
        
        if (empty($messages)) {
            throw new Exception('At least one message is required');
        }
        
        // Add quick reply if enabled
        if ($this->get_setting('quick_reply', false)) {
            $quick_reply_items = $this->replace_variables($this->get_setting('quick_reply_items', ''), $context);
            if (!empty($quick_reply_items)) {
                $items = json_decode($quick_reply_items, true);
                if (json_last_error() === JSON_ERROR_NONE && !empty($items)) {
                    // Add quick reply to the last message
                    $messages[count($messages) - 1]['quickReply'] = array(
                        'items' => $items
                    );
                }
            }
        }
        
        // Execute based on message type
        switch ($message_type) {
            case 'reply':
                return $this->send_reply($access_token, $messages, $context);
                
            case 'push':
                return $this->send_push($access_token, $messages, $context);
                
            case 'multicast':
                return $this->send_multicast($access_token, $messages, $context);
                
            case 'broadcast':
                return $this->send_broadcast($access_token, $messages, $context);
                
            default:
                throw new Exception('Invalid message type: ' . $message_type);
        }
    }

    /**
     * Build messages array
     *
     * @since    1.0.0
     * @param    array    $context    Execution context
     * @return   array
     */
    private function build_messages($context) {
        $messages = array();
        
        // Support up to 5 messages (LINE limit)
        for ($i = 1; $i <= 5; $i++) {
            $type_key = 'type_' . $i;
            $type = $this->get_setting($type_key, '');
            
            if (empty($type) && $i > 1) {
                break; // No more messages
            }
            
            if ($i === 1 && empty($type)) {
                $type = 'text'; // Default first message to text
            }
            
            $message = null;
            
            switch ($type) {
                case 'text':
                    $text = $this->replace_variables($this->get_setting('text_' . $i, ''), $context);
                    if (!empty($text)) {
                        $message = array(
                            'type' => 'text',
                            'text' => substr($text, 0, 5000) // Max 5000 chars
                        );
                        
                        // Add emojis if specified
                        $emojis_json = $this->replace_variables($this->get_setting('emojis_' . $i, ''), $context);
                        if (!empty($emojis_json)) {
                            $emojis = json_decode($emojis_json, true);
                            if (json_last_error() === JSON_ERROR_NONE && !empty($emojis)) {
                                $message['emojis'] = is_array($emojis[0]) ? $emojis : array($emojis);
                            }
                        }
                    }
                    break;
                    
                case 'sticker':
                    $package_id = $this->replace_variables($this->get_setting('sticker_package_' . $i, ''), $context);
                    $sticker_id = $this->replace_variables($this->get_setting('sticker_id_' . $i, ''), $context);
                    if (!empty($package_id) && !empty($sticker_id)) {
                        $message = array(
                            'type' => 'sticker',
                            'packageId' => $package_id,
                            'stickerId' => $sticker_id
                        );
                    }
                    break;
                    
                case 'image':
                    $original_url = $this->replace_variables($this->get_setting('image_original_' . $i, ''), $context);
                    $preview_url = $this->replace_variables($this->get_setting('image_preview_' . $i, ''), $context);
                    if (!empty($original_url)) {
                        $message = array(
                            'type' => 'image',
                            'originalContentUrl' => $original_url,
                            'previewImageUrl' => $preview_url ?: $original_url
                        );
                    }
                    break;
                    
                case 'flex':
                    $flex_json = $this->replace_variables($this->get_setting('flex_' . $i, ''), $context);
                    if (!empty($flex_json)) {
                        $flex_content = json_decode($flex_json, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $message = array(
                                'type' => 'flex',
                                'altText' => $this->replace_variables($this->get_setting('flex_alt_' . $i, 'Flex Message'), $context),
                                'contents' => $flex_content
                            );
                        }
                    }
                    break;
            }
            
            if ($message !== null) {
                $messages[] = $message;
            }
        }
        
        return $messages;
    }

    /**
     * Send reply message
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    array     $messages        Messages to send
     * @param    array     $context         Execution context
     * @return   array
     */
    private function send_reply($access_token, $messages, $context) {
        $reply_token = $this->replace_variables($this->get_setting('reply_token', ''), $context);
        
        if (empty($reply_token)) {
            throw new Exception('Reply token is required for reply messages');
        }
        
        $body = array(
            'replyToken' => $reply_token,
            'messages' => $messages
        );
        
        // Add notification option
        if ($this->get_setting('notification_disabled', false)) {
            $body['notificationDisabled'] = true;
        }
        
        $response = $this->line_api_request(
            'https://api.line.me/v2/bot/message/reply',
            $access_token,
            $body
        );
        
        return array(
            'type' => 'reply',
            'message_count' => count($messages),
            'reply_token' => $reply_token,
            'sent_at' => current_time('mysql'),
            'success' => true
        );
    }

    /**
     * Send push message
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    array     $messages        Messages to send
     * @param    array     $context         Execution context
     * @return   array
     */
    private function send_push($access_token, $messages, $context) {
        $to = $this->replace_variables($this->get_setting('to', ''), $context);
        
        if (empty($to)) {
            throw new Exception('Recipient ID is required for push messages');
        }
        
        $body = array(
            'to' => $to,
            'messages' => $messages
        );
        
        // Add optional parameters
        if ($this->get_setting('notification_disabled', false)) {
            $body['notificationDisabled'] = true;
        }
        
        $custom_units = $this->replace_variables($this->get_setting('custom_aggregation_units', ''), $context);
        if (!empty($custom_units)) {
            $units = json_decode($custom_units, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($units)) {
                $body['customAggregationUnits'] = array_slice($units, 0, 1); // Max 1 for push
            }
        }
        
        $response = $this->line_api_request(
            'https://api.line.me/v2/bot/message/push',
            $access_token,
            $body
        );
        
        return array(
            'type' => 'push',
            'message_count' => count($messages),
            'recipient' => $to,
            'message_id' => isset($response['sentMessages'][0]['id']) ? $response['sentMessages'][0]['id'] : null,
            'sent_at' => current_time('mysql'),
            'success' => true
        );
    }

    /**
     * Send multicast message
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    array     $messages        Messages to send
     * @param    array     $context         Execution context
     * @return   array
     */
    private function send_multicast($access_token, $messages, $context) {
        $to_list_raw = $this->replace_variables($this->get_setting('to_list', ''), $context);
        
        if (empty($to_list_raw)) {
            throw new Exception('Recipients list is required for multicast messages');
        }
        
        // Parse recipient list
        $to_list = array_filter(array_map('trim', explode("\n", $to_list_raw)));
        
        if (empty($to_list)) {
            throw new Exception('At least one recipient is required for multicast messages');
        }
        
        if (count($to_list) > 500) {
            throw new Exception('Maximum 500 recipients allowed for multicast messages');
        }
        
        $body = array(
            'to' => $to_list,
            'messages' => $messages
        );
        
        // Add optional parameters
        if ($this->get_setting('notification_disabled', false)) {
            $body['notificationDisabled'] = true;
        }
        
        $custom_units = $this->replace_variables($this->get_setting('custom_aggregation_units', ''), $context);
        if (!empty($custom_units)) {
            $units = json_decode($custom_units, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($units)) {
                $body['customAggregationUnits'] = array_slice($units, 0, 1); // Max 1 for multicast
            }
        }
        
        $response = $this->line_api_request(
            'https://api.line.me/v2/bot/message/multicast',
            $access_token,
            $body
        );
        
        return array(
            'type' => 'multicast',
            'message_count' => count($messages),
            'recipient_count' => count($to_list),
            'message_id' => isset($response['sentMessages'][0]['id']) ? $response['sentMessages'][0]['id'] : null,
            'sent_at' => current_time('mysql'),
            'success' => true
        );
    }

    /**
     * Send broadcast message
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    array     $messages        Messages to send
     * @param    array     $context         Execution context
     * @return   array
     */
    private function send_broadcast($access_token, $messages, $context) {
        $body = array(
            'messages' => $messages
        );
        
        // Add optional parameters
        if ($this->get_setting('notification_disabled', false)) {
            $body['notificationDisabled'] = true;
        }
        
        $custom_units = $this->replace_variables($this->get_setting('custom_aggregation_units', ''), $context);
        if (!empty($custom_units)) {
            $units = json_decode($custom_units, true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($units)) {
                $body['customAggregationUnits'] = array_slice($units, 0, 3); // Max 3 for broadcast
            }
        }
        
        $response = $this->line_api_request(
            'https://api.line.me/v2/bot/message/broadcast',
            $access_token,
            $body
        );
        
        return array(
            'type' => 'broadcast',
            'message_count' => count($messages),
            'message_id' => isset($response['sentMessages'][0]['id']) ? $response['sentMessages'][0]['id'] : null,
            'sent_at' => current_time('mysql'),
            'success' => true
        );
    }

    /**
     * Make LINE API request
     *
     * @since    1.0.0
     * @param    string    $url             API URL
     * @param    string    $access_token    Access token
     * @param    array     $body            Request body
     * @return   array
     */
    private function line_api_request($url, $access_token, $body) {
        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('LINE API error: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($status_code >= 400) {
            $error_data = json_decode($response_body, true);
            $error_message = isset($error_data['message']) ? $error_data['message'] : 'Unknown error';
            
            throw new Exception(sprintf(
                'LINE API error (HTTP %d): %s',
                $status_code,
                $error_message
            ));
        }
        
        $this->log('LINE message sent successfully');
        
        // LINE API returns empty response on success for most endpoints
        if (empty($response_body)) {
            return array();
        }
        
        return json_decode($response_body, true);
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $integration_id = $this->get_setting('integration_id', '');
        $message_type = $this->get_setting('message_type', '');
        
        if (empty($integration_id)) {
            return new WP_Error('missing_integration', __('LINE configuration is required', 'workflow-automation'));
        }
        
        // Validate based on message type
        switch ($message_type) {
            case 'reply':
                if (empty($this->get_setting('reply_token', ''))) {
                    return new WP_Error('missing_reply_token', __('Reply token is required for reply messages', 'workflow-automation'));
                }
                break;
                
            case 'push':
                if (empty($this->get_setting('to', ''))) {
                    return new WP_Error('missing_recipient', __('Recipient ID is required for push messages', 'workflow-automation'));
                }
                break;
                
            case 'multicast':
                if (empty($this->get_setting('to_list', ''))) {
                    return new WP_Error('missing_recipients', __('Recipients list is required for multicast messages', 'workflow-automation'));
                }
                break;
        }
        
        // Validate at least one message
        $has_message = false;
        for ($i = 1; $i <= 5; $i++) {
            $type = $this->get_setting('type_' . $i, '');
            if (!empty($type)) {
                $has_message = true;
                break;
            }
        }
        
        if (!$has_message && empty($this->get_setting('text_1', ''))) {
            return new WP_Error('missing_message', __('At least one message is required', 'workflow-automation'));
        }
        
        return true;
    }
}