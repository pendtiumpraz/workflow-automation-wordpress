<?php
/**
 * OpenAI Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 */

/**
 * OpenAI integration node
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_OpenAI_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'openai';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('OpenAI', 'workflow-automation'),
            'category' => 'ai',
            'description' => __('Generate text using OpenAI\'s GPT models', 'workflow-automation'),
            'icon' => 'wa-icon-openai',
            'color' => '#00A67E',
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
        $integrations = $integration_model->get_by_type('openai');
        
        $integration_options = array('' => __('Select OpenAI integration...', 'workflow-automation'));
        foreach ($integrations as $integration) {
            $integration_options[$integration->id] = $integration->name;
        }
        
        $fields = array(
            array(
                'key' => 'integration_id',
                'label' => __('OpenAI Integration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select the OpenAI integration to use', 'workflow-automation')
            ),
            array(
                'key' => 'model',
                'label' => __('Model', 'workflow-automation'),
                'type' => 'select',
                'default' => 'gpt-3.5-turbo',
                'options' => array(
                    'gpt-4-turbo-preview' => 'GPT-4 Turbo',
                    'gpt-4' => 'GPT-4',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    'gpt-3.5-turbo-16k' => 'GPT-3.5 Turbo 16k'
                ),
                'description' => __('Select the OpenAI model to use', 'workflow-automation')
            ),
            array(
                'key' => 'system_prompt',
                'label' => __('System Prompt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => __('You are a helpful assistant...', 'workflow-automation'),
                'description' => __('System message to set the behavior of the assistant', 'workflow-automation')
            ),
            array(
                'key' => 'user_prompt',
                'label' => __('User Prompt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'required' => true,
                'placeholder' => __('{{trigger.message}} or custom prompt...', 'workflow-automation'),
                'description' => __('The prompt to send to OpenAI. You can use variables like {{node_id.field}}', 'workflow-automation')
            ),
            array(
                'key' => 'temperature',
                'label' => __('Temperature', 'workflow-automation'),
                'type' => 'number',
                'default' => 0.7,
                'min' => 0,
                'max' => 2,
                'step' => 0.1,
                'description' => __('Controls randomness (0-2). Lower values make output more focused', 'workflow-automation')
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
                'key' => 'response_format',
                'label' => __('Response Format', 'workflow-automation'),
                'type' => 'select',
                'default' => 'text',
                'options' => array(
                    'text' => __('Text', 'workflow-automation'),
                    'json_object' => __('JSON Object', 'workflow-automation')
                ),
                'description' => __('Format of the response', 'workflow-automation')
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
                throw new Exception(__('No OpenAI integration selected', 'workflow-automation'));
            }
            
            // Get integration settings
            $integration_model = new WA_Integration_Settings_Model();
            $integration = $integration_model->get($integration_id);
            
            if (!$integration || !$integration->is_active) {
                throw new Exception(__('Selected OpenAI integration is not active', 'workflow-automation'));
            }
            
            $settings = $integration->get_decrypted_settings();
            if (empty($settings['api_key'])) {
                throw new Exception(__('OpenAI API key not configured', 'workflow-automation'));
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
            
            // Prepare API request
            $request_body = array(
                'model' => $this->get_setting('model', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'temperature' => floatval($this->get_setting('temperature', 0.7)),
                'max_tokens' => intval($this->get_setting('max_tokens', 1000))
            );
            
            if ($this->get_setting('response_format') === 'json_object') {
                $request_body['response_format'] = array('type' => 'json_object');
            }
            
            // Make API request
            $response = $this->http_request('https://api.openai.com/v1/chat/completions', array(
                'method' => 'POST',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $settings['api_key'],
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
                    'OpenAI API error: ' . $response['code'];
                throw new Exception($error_message);
            }
            
            $result = json_decode($response['body'], true);
            
            if (!isset($result['choices'][0]['message']['content'])) {
                throw new Exception(__('Invalid response from OpenAI', 'workflow-automation'));
            }
            
            $content = $result['choices'][0]['message']['content'];
            
            // If JSON format requested, try to parse it
            if ($this->get_setting('response_format') === 'json_object') {
                $json_content = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $content = $json_content;
                }
            }
            
            $this->log('OpenAI completion successful. Tokens used: ' . 
                (isset($result['usage']['total_tokens']) ? $result['usage']['total_tokens'] : 'unknown'));
            
            return array(
                'success' => true,
                'response' => $content,
                'usage' => isset($result['usage']) ? $result['usage'] : null,
                'model' => $result['model'],
                'finish_reason' => isset($result['choices'][0]['finish_reason']) ? 
                    $result['choices'][0]['finish_reason'] : null
            );
            
        } catch (Exception $e) {
            $this->log('OpenAI node error: ' . $e->getMessage(), 'error');
            
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
                __('Please select an OpenAI integration', 'workflow-automation')
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