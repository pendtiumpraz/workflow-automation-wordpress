<?php
/**
 * Claude Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 */

/**
 * Claude (Anthropic) integration node
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Claude_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'claude';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('Claude', 'workflow-automation'),
            'category' => 'ai',
            'description' => __('Generate text using Anthropic\'s Claude AI', 'workflow-automation'),
            'icon' => 'wa-icon-claude',
            'color' => '#D4A373',
            'inputs' => array(
                array(
                    'name' => 'prompt',
                    'type' => 'text',
                    'required' => true
                )
            ),
            'outputs' => array(
                array(
                    'name' => 'response',
                    'type' => 'text'
                ),
                array(
                    'name' => 'usage',
                    'type' => 'object'
                )
            )
        );
    }
    
    /**
     * Get settings fields
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_settings_fields() {
        // Get available integrations
        $integration_model = new WA_Integration_Settings_Model();
        $integrations = $integration_model->get_by_type('claude');
        
        $integration_options = array('' => __('Select Claude integration...', 'workflow-automation'));
        foreach ($integrations as $integration) {
            $integration_options[$integration->id] = $integration->name;
        }
        
        $fields = array(
            array(
                'key' => 'integration_id',
                'label' => __('Claude Integration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select the Claude integration to use', 'workflow-automation')
            ),
            array(
                'key' => 'model',
                'label' => __('Model', 'workflow-automation'),
                'type' => 'select',
                'default' => 'claude-3-sonnet-20240229',
                'options' => array(
                    'claude-3-opus-20240229' => 'Claude 3 Opus',
                    'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
                    'claude-3-haiku-20240307' => 'Claude 3 Haiku',
                    'claude-2.1' => 'Claude 2.1',
                    'claude-2.0' => 'Claude 2.0',
                    'claude-instant-1.2' => 'Claude Instant 1.2'
                ),
                'description' => __('Select the Claude model to use', 'workflow-automation')
            ),
            array(
                'key' => 'system_prompt',
                'label' => __('System Prompt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => __('You are a helpful assistant...', 'workflow-automation'),
                'description' => __('System message to set the behavior of Claude', 'workflow-automation')
            ),
            array(
                'key' => 'user_prompt',
                'label' => __('User Prompt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'required' => true,
                'placeholder' => __('{{trigger.message}} or custom prompt...', 'workflow-automation'),
                'description' => __('The prompt to send to Claude. You can use variables like {{node_id.field}}', 'workflow-automation')
            ),
            array(
                'key' => 'max_tokens',
                'label' => __('Max Tokens', 'workflow-automation'),
                'type' => 'number',
                'default' => 1000,
                'min' => 1,
                'max' => 4096,
                'description' => __('Maximum number of tokens to generate', 'workflow-automation')
            ),
            array(
                'key' => 'temperature',
                'label' => __('Temperature', 'workflow-automation'),
                'type' => 'number',
                'default' => 0.7,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'description' => __('Controls randomness (0-1). Lower values make output more focused', 'workflow-automation')
            ),
            array(
                'key' => 'stop_sequences',
                'label' => __('Stop Sequences', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 2,
                'placeholder' => __('One per line', 'workflow-automation'),
                'description' => __('Sequences where the API will stop generating (one per line)', 'workflow-automation')
            )
        );
        
        return array_merge($fields, $this->get_error_handling_fields());
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
        try {
            $integration_id = $this->get_setting('integration_id');
            if (empty($integration_id)) {
                throw new Exception(__('No Claude integration selected', 'workflow-automation'));
            }
            
            // Get integration settings
            $integration_model = new WA_Integration_Settings_Model();
            $integration = $integration_model->get($integration_id);
            
            if (!$integration || !$integration->is_active) {
                throw new Exception(__('Selected Claude integration is not active', 'workflow-automation'));
            }
            
            $settings = $integration->get_decrypted_settings();
            if (empty($settings['api_key'])) {
                throw new Exception(__('Claude API key not configured', 'workflow-automation'));
            }
            
            // Prepare the prompt
            $system_prompt = $this->replace_variables($this->get_setting('system_prompt', ''), $context);
            $user_prompt = $this->replace_variables($this->get_setting('user_prompt', ''), $context);
            
            // Build messages array
            $messages = array();
            if (!empty($system_prompt)) {
                $messages[] = array(
                    'role' => 'system',
                    'content' => $system_prompt
                );
            }
            $messages[] = array(
                'role' => 'user',
                'content' => $user_prompt
            );
            
            // Prepare stop sequences
            $stop_sequences = array();
            $stop_sequences_text = $this->get_setting('stop_sequences', '');
            if (!empty($stop_sequences_text)) {
                $stop_sequences = array_filter(array_map('trim', explode("\n", $stop_sequences_text)));
            }
            
            // Prepare API request
            $request_body = array(
                'model' => $this->get_setting('model', 'claude-3-sonnet-20240229'),
                'messages' => $messages,
                'max_tokens' => intval($this->get_setting('max_tokens', 1000)),
                'temperature' => floatval($this->get_setting('temperature', 0.7))
            );
            
            if (!empty($stop_sequences)) {
                $request_body['stop_sequences'] = $stop_sequences;
            }
            
            // Make API request
            $response = $this->http_request('https://api.anthropic.com/v1/messages', array(
                'method' => 'POST',
                'headers' => array(
                    'x-api-key' => $settings['api_key'],
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode($request_body)
            ));
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            if ($response['code'] !== 200) {
                $error_body = json_decode($response['body'], true);
                $error_message = isset($error_body['error']['message']) ? 
                    $error_body['error']['message'] : 
                    'Claude API error: ' . $response['code'];
                throw new Exception($error_message);
            }
            
            $result = json_decode($response['body'], true);
            
            if (!isset($result['content'][0]['text'])) {
                throw new Exception(__('Invalid response from Claude', 'workflow-automation'));
            }
            
            $content = $result['content'][0]['text'];
            
            $this->log('Claude completion successful. Input tokens: ' . 
                (isset($result['usage']['input_tokens']) ? $result['usage']['input_tokens'] : 'unknown') .
                ', Output tokens: ' . 
                (isset($result['usage']['output_tokens']) ? $result['usage']['output_tokens'] : 'unknown'));
            
            return array(
                'success' => true,
                'response' => $content,
                'usage' => isset($result['usage']) ? $result['usage'] : null,
                'model' => $result['model'],
                'stop_reason' => isset($result['stop_reason']) ? $result['stop_reason'] : null
            );
            
        } catch (Exception $e) {
            $this->log('Claude node error: ' . $e->getMessage(), 'error');
            
            // Handle error based on settings
            $error_handling = $this->get_setting('error_handling', 'stop');
            
            if ($error_handling === 'use_default') {
                $default_output = $this->get_setting('default_output', '{"success": false}');
                return json_decode($default_output, true);
            }
            
            throw $e;
        }
    }
    
    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        if (empty($this->get_setting('integration_id'))) {
            return new WP_Error(
                'missing_integration',
                __('Please select a Claude integration', 'workflow-automation')
            );
        }
        
        if (empty($this->get_setting('user_prompt'))) {
            return new WP_Error(
                'missing_prompt',
                __('User prompt is required', 'workflow-automation')
            );
        }
        
        return true;
    }
}