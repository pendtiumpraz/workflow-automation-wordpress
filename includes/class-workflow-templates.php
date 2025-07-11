<?php
/**
 * Workflow Templates
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Workflow Templates class.
 *
 * Provides pre-built workflow templates for common automation scenarios.
 *
 * @since      1.0.0
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Workflow_Templates {

    /**
     * Get all available workflow templates
     *
     * @since    1.0.0
     * @return   array
     */
    public static function get_templates() {
        $templates = array(
            'webhook-to-email' => array(
                'name' => __('Webhook to Email', 'workflow-automation'),
                'description' => __('Receive webhook data and send it via email', 'workflow-automation'),
                'category' => 'communication',
                'icon' => 'dashicons-email',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'webhook_start',
                        'label' => 'Webhook Trigger',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'method' => 'POST',
                            'auth_type' => 'none'
                        )
                    ),
                    array(
                        'id' => 'email_1',
                        'type' => 'email',
                        'label' => 'Send Email',
                        'position' => array('x' => 350, 'y' => 100),
                        'data' => array(
                            'to_email' => '{{admin_email}}',
                            'subject' => 'Webhook Received',
                            'message' => 'Webhook data:\n\n{{webhook.body}}'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'email_1'
                    )
                )
            ),
            
            'webhook-to-slack' => array(
                'name' => __('Webhook to Slack', 'workflow-automation'),
                'description' => __('Forward webhook data to a Slack channel', 'workflow-automation'),
                'category' => 'communication',
                'icon' => 'dashicons-format-status',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'webhook_start',
                        'label' => 'Webhook Trigger',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'method' => 'POST',
                            'auth_type' => 'none'
                        )
                    ),
                    array(
                        'id' => 'slack_1',
                        'type' => 'slack',
                        'label' => 'Send to Slack',
                        'position' => array('x' => 350, 'y' => 100),
                        'data' => array(
                            'channel' => '#general',
                            'message' => 'New webhook received:\n```{{webhook.body}}```'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'slack_1'
                    )
                )
            ),
            
            'form-to-spreadsheet' => array(
                'name' => __('Form to Google Sheets', 'workflow-automation'),
                'description' => __('Save form submissions to Google Sheets', 'workflow-automation'),
                'category' => 'productivity',
                'icon' => 'dashicons-media-spreadsheet',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'webhook_start',
                        'label' => 'Form Submission',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'method' => 'POST',
                            'auth_type' => 'none'
                        )
                    ),
                    array(
                        'id' => 'sheets_1',
                        'type' => 'google_sheets',
                        'label' => 'Add to Sheet',
                        'position' => array('x' => 350, 'y' => 100),
                        'data' => array(
                            'action' => 'append',
                            'sheet_name' => 'Sheet1'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'sheets_1'
                    )
                )
            ),
            
            'line-chatbot-ai' => array(
                'name' => __('LINE Chatbot with AI', 'workflow-automation'),
                'description' => __('AI-powered LINE chatbot using OpenAI', 'workflow-automation'),
                'category' => 'ai',
                'icon' => 'dashicons-format-chat',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'webhook_start',
                        'label' => 'LINE Webhook',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'method' => 'POST',
                            'auth_type' => 'signature'
                        )
                    ),
                    array(
                        'id' => 'filter_1',
                        'type' => 'filter',
                        'label' => 'Check Message Type',
                        'position' => array('x' => 300, 'y' => 100),
                        'data' => array(
                            'field' => '{{webhook.events[0].type}}',
                            'operator' => 'equals',
                            'value' => 'message'
                        )
                    ),
                    array(
                        'id' => 'ai_1',
                        'type' => 'openai',
                        'label' => 'Generate Response',
                        'position' => array('x' => 500, 'y' => 100),
                        'data' => array(
                            'model' => 'gpt-3.5-turbo',
                            'system_prompt' => 'You are a helpful assistant responding to LINE messages.',
                            'user_prompt' => '{{webhook.events[0].message.text}}',
                            'temperature' => 0.7
                        )
                    ),
                    array(
                        'id' => 'line_reply',
                        'type' => 'line',
                        'label' => 'Reply to User',
                        'position' => array('x' => 700, 'y' => 100),
                        'data' => array(
                            'reply_token' => '{{webhook.events[0].replyToken}}',
                            'message_type' => 'text',
                            'message' => '{{ai_1.response}}'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'filter_1'
                    ),
                    array(
                        'source' => 'filter_1',
                        'target' => 'ai_1'
                    ),
                    array(
                        'source' => 'ai_1',
                        'target' => 'line_reply'
                    )
                )
            ),
            
            'wordpress-content-ai' => array(
                'name' => __('AI Content Generator', 'workflow-automation'),
                'description' => __('Generate and publish WordPress content using AI', 'workflow-automation'),
                'category' => 'wordpress',
                'icon' => 'dashicons-admin-post',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'schedule_start',
                        'label' => 'Daily Schedule',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'schedule' => 'daily',
                            'time' => '09:00'
                        )
                    ),
                    array(
                        'id' => 'ai_1',
                        'type' => 'openai',
                        'label' => 'Generate Content',
                        'position' => array('x' => 300, 'y' => 100),
                        'data' => array(
                            'model' => 'gpt-4',
                            'system_prompt' => 'You are a content writer creating blog posts.',
                            'user_prompt' => 'Write a blog post about {{topic}}',
                            'temperature' => 0.8
                        )
                    ),
                    array(
                        'id' => 'wp_post',
                        'type' => 'wp_post',
                        'label' => 'Create Post',
                        'position' => array('x' => 500, 'y' => 100),
                        'data' => array(
                            'action' => 'create',
                            'post_title' => '{{ai_1.title}}',
                            'post_content' => '{{ai_1.content}}',
                            'post_status' => 'draft'
                        )
                    ),
                    array(
                        'id' => 'email_notify',
                        'type' => 'email',
                        'label' => 'Notify Admin',
                        'position' => array('x' => 700, 'y' => 100),
                        'data' => array(
                            'to_email' => '{{admin_email}}',
                            'subject' => 'New AI Content Created',
                            'message' => 'A new post has been created: {{wp_post.post_url}}'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'ai_1'
                    ),
                    array(
                        'source' => 'ai_1',
                        'target' => 'wp_post'
                    ),
                    array(
                        'source' => 'wp_post',
                        'target' => 'email_notify'
                    )
                )
            ),
            
            'customer-support-automation' => array(
                'name' => __('Customer Support Automation', 'workflow-automation'),
                'description' => __('Automated customer support with AI and ticket creation', 'workflow-automation'),
                'category' => 'business',
                'icon' => 'dashicons-groups',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'webhook_start',
                        'label' => 'Support Request',
                        'position' => array('x' => 50, 'y' => 100),
                        'data' => array(
                            'method' => 'POST',
                            'auth_type' => 'token'
                        )
                    ),
                    array(
                        'id' => 'ai_classify',
                        'type' => 'openai',
                        'label' => 'Classify Request',
                        'position' => array('x' => 250, 'y' => 100),
                        'data' => array(
                            'model' => 'gpt-3.5-turbo',
                            'system_prompt' => 'Classify the customer request as: urgent, normal, or low priority. Also suggest a department: technical, billing, or general.',
                            'user_prompt' => '{{webhook.message}}',
                            'temperature' => 0.3
                        )
                    ),
                    array(
                        'id' => 'filter_urgent',
                        'type' => 'filter',
                        'label' => 'Check if Urgent',
                        'position' => array('x' => 450, 'y' => 50),
                        'data' => array(
                            'field' => '{{ai_classify.priority}}',
                            'operator' => 'equals',
                            'value' => 'urgent'
                        )
                    ),
                    array(
                        'id' => 'slack_urgent',
                        'type' => 'slack',
                        'label' => 'Alert Team',
                        'position' => array('x' => 650, 'y' => 50),
                        'data' => array(
                            'channel' => '#urgent-support',
                            'message' => '🚨 Urgent support request from {{webhook.customer_email}}:\n{{webhook.message}}'
                        )
                    ),
                    array(
                        'id' => 'ai_response',
                        'type' => 'claude',
                        'label' => 'Generate Response',
                        'position' => array('x' => 450, 'y' => 150),
                        'data' => array(
                            'model' => 'claude-3-haiku',
                            'system_prompt' => 'You are a helpful customer support agent. Provide a professional and helpful response.',
                            'user_prompt' => 'Customer request: {{webhook.message}}\nDepartment: {{ai_classify.department}}',
                            'temperature' => 0.5
                        )
                    ),
                    array(
                        'id' => 'email_response',
                        'type' => 'email',
                        'label' => 'Send Response',
                        'position' => array('x' => 650, 'y' => 150),
                        'data' => array(
                            'to_email' => '{{webhook.customer_email}}',
                            'subject' => 'Re: {{webhook.subject}}',
                            'message' => '{{ai_response.response}}\n\n---\nTicket #{{ticket_number}}'
                        )
                    ),
                    array(
                        'id' => 'log_sheets',
                        'type' => 'google_sheets',
                        'label' => 'Log to Sheet',
                        'position' => array('x' => 850, 'y' => 100),
                        'data' => array(
                            'action' => 'append',
                            'sheet_name' => 'Support Tickets'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'ai_classify'
                    ),
                    array(
                        'source' => 'ai_classify',
                        'target' => 'filter_urgent'
                    ),
                    array(
                        'source' => 'filter_urgent',
                        'target' => 'slack_urgent',
                        'condition' => 'true'
                    ),
                    array(
                        'source' => 'ai_classify',
                        'target' => 'ai_response'
                    ),
                    array(
                        'source' => 'ai_response',
                        'target' => 'email_response'
                    ),
                    array(
                        'source' => 'email_response',
                        'target' => 'log_sheets'
                    )
                )
            ),
            
            'social-media-automation' => array(
                'name' => __('Social Media Automation', 'workflow-automation'),
                'description' => __('Auto-post WordPress content to social media', 'workflow-automation'),
                'category' => 'marketing',
                'icon' => 'dashicons-share',
                'nodes' => array(
                    array(
                        'id' => 'trigger_1',
                        'type' => 'wp_post',
                        'label' => 'New Post Published',
                        'position' => array('x' => 100, 'y' => 100),
                        'data' => array(
                            'action' => 'publish',
                            'post_type' => 'post'
                        )
                    ),
                    array(
                        'id' => 'ai_summary',
                        'type' => 'openai',
                        'label' => 'Create Summary',
                        'position' => array('x' => 300, 'y' => 100),
                        'data' => array(
                            'model' => 'gpt-3.5-turbo',
                            'system_prompt' => 'Create a short, engaging social media post (max 280 characters) from this blog post.',
                            'user_prompt' => 'Title: {{trigger_1.post_title}}\nContent: {{trigger_1.post_excerpt}}',
                            'temperature' => 0.7
                        )
                    ),
                    array(
                        'id' => 'delay_1',
                        'type' => 'delay',
                        'label' => 'Wait 5 minutes',
                        'position' => array('x' => 500, 'y' => 100),
                        'data' => array(
                            'delay' => 300,
                            'unit' => 'seconds'
                        )
                    ),
                    array(
                        'id' => 'http_twitter',
                        'type' => 'http',
                        'label' => 'Post to Twitter',
                        'position' => array('x' => 700, 'y' => 50),
                        'data' => array(
                            'method' => 'POST',
                            'url' => 'https://api.twitter.com/2/tweets',
                            'headers' => array(
                                'Authorization' => 'Bearer {{twitter_token}}'
                            ),
                            'body' => array(
                                'text' => '{{ai_summary.response}} {{trigger_1.post_url}}'
                            )
                        )
                    ),
                    array(
                        'id' => 'slack_notify',
                        'type' => 'slack',
                        'label' => 'Notify Marketing',
                        'position' => array('x' => 700, 'y' => 150),
                        'data' => array(
                            'channel' => '#marketing',
                            'message' => '✅ New post shared on social media:\n{{trigger_1.post_title}}\n{{trigger_1.post_url}}'
                        )
                    )
                ),
                'connections' => array(
                    array(
                        'source' => 'trigger_1',
                        'target' => 'ai_summary'
                    ),
                    array(
                        'source' => 'ai_summary',
                        'target' => 'delay_1'
                    ),
                    array(
                        'source' => 'delay_1',
                        'target' => 'http_twitter'
                    ),
                    array(
                        'source' => 'delay_1',
                        'target' => 'slack_notify'
                    )
                )
            )
        );
        
        return apply_filters('wa_workflow_templates', $templates);
    }
    
    /**
     * Get template by ID
     *
     * @since    1.0.0
     * @param    string    $template_id    Template ID
     * @return   array|null
     */
    public static function get_template($template_id) {
        $templates = self::get_templates();
        return isset($templates[$template_id]) ? $templates[$template_id] : null;
    }
    
    /**
     * Get templates by category
     *
     * @since    1.0.0
     * @param    string    $category    Category name
     * @return   array
     */
    public static function get_templates_by_category($category) {
        $templates = self::get_templates();
        $filtered = array();
        
        foreach ($templates as $id => $template) {
            if ($template['category'] === $category) {
                $template['id'] = $id;
                $filtered[] = $template;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get all template categories
     *
     * @since    1.0.0
     * @return   array
     */
    public static function get_categories() {
        return array(
            'communication' => __('Communication', 'workflow-automation'),
            'productivity' => __('Productivity', 'workflow-automation'),
            'ai' => __('AI & Automation', 'workflow-automation'),
            'wordpress' => __('WordPress', 'workflow-automation'),
            'business' => __('Business', 'workflow-automation'),
            'marketing' => __('Marketing', 'workflow-automation')
        );
    }
    
    /**
     * Create workflow from template
     *
     * @since    1.0.0
     * @param    string    $template_id    Template ID
     * @param    string    $name           Workflow name
     * @param    int       $user_id        User ID
     * @return   int|false                 Workflow ID or false on failure
     */
    public static function create_from_template($template_id, $name, $user_id = null) {
        $template = self::get_template($template_id);
        if (!$template) {
            return false;
        }
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Create workflow
        $workflow_model = new Workflow_Model();
        $workflow_data = array(
            'name' => $name,
            'description' => sprintf(__('Created from template: %s', 'workflow-automation'), $template['name']),
            'status' => 'inactive',
            'created_by' => $user_id
        );
        
        $workflow_id = $workflow_model->create($workflow_data);
        if (!$workflow_id) {
            return false;
        }
        
        // Create nodes
        $node_model = new Node_Model();
        $node_id_map = array();
        
        foreach ($template['nodes'] as $node) {
            $node_data = array(
                'workflow_id' => $workflow_id,
                'type' => $node['type'],
                'name' => $node['label'],
                'position_x' => $node['position']['x'],
                'position_y' => $node['position']['y'],
                'config' => json_encode($node['data'])
            );
            
            $new_node_id = $node_model->create($node_data);
            if ($new_node_id) {
                $node_id_map[$node['id']] = $new_node_id;
            }
        }
        
        // Create connections
        if (!empty($template['connections'])) {
            foreach ($template['connections'] as $connection) {
                if (isset($node_id_map[$connection['source']]) && isset($node_id_map[$connection['target']])) {
                    $connection_data = array(
                        'workflow_id' => $workflow_id,
                        'source_node_id' => $node_id_map[$connection['source']],
                        'target_node_id' => $node_id_map[$connection['target']],
                        'condition' => isset($connection['condition']) ? $connection['condition'] : null
                    );
                    
                    $node_model->create_connection($connection_data);
                }
            }
        }
        
        return $workflow_id;
    }
}