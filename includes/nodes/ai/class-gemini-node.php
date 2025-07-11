<?php
/**
 * Gemini Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 */

/**
 * Google Gemini integration node
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/ai
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Gemini_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'gemini';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('Google Gemini', 'workflow-automation'),
            'category' => 'ai',
            'description' => __('Generate text using Google\'s Gemini AI', 'workflow-automation'),
            'icon' => 'wa-icon-gemini',
            'color' => '#4285F4',
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
                    'name' => 'candidates',
                    'type' => 'array'
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
        $integrations = $integration_model->get_by_type('gemini');
        
        $integration_options = array('' => __('Select Gemini integration...', 'workflow-automation'));
        foreach ($integrations as $integration) {
            $integration_options[$integration->id] = $integration->name;
        }
        
        $fields = array(
            array(
                'key' => 'integration_id',
                'label' => __('Gemini Integration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select the Gemini integration to use', 'workflow-automation')
            ),
            array(
                'key' => 'model',
                'label' => __('Model', 'workflow-automation'),
                'type' => 'select',
                'default' => 'gemini-pro',
                'options' => array(
                    'gemini-pro' => 'Gemini Pro',
                    'gemini-pro-vision' => 'Gemini Pro Vision'
                ),
                'description' => __('Select the Gemini model to use', 'workflow-automation')
            ),
            array(
                'key' => 'prompt',
                'label' => __('Prompt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'required' => true,
                'placeholder' => __('{{trigger.message}} or custom prompt...', 'workflow-automation'),
                'description' => __('The prompt to send to Gemini. You can use variables like {{node_id.field}}', 'workflow-automation')
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
                'key' => 'max_output_tokens',
                'label' => __('Max Output Tokens', 'workflow-automation'),
                'type' => 'number',
                'default' => 1000,
                'min' => 1,
                'max' => 2048,
                'description' => __('Maximum number of tokens to generate', 'workflow-automation')
            ),
            array(
                'key' => 'top_p',
                'label' => __('Top P', 'workflow-automation'),
                'type' => 'number',
                'default' => 0.95,
                'min' => 0,
                'max' => 1,
                'step' => 0.01,
                'description' => __('Nucleus sampling parameter', 'workflow-automation')
            ),
            array(
                'key' => 'top_k',
                'label' => __('Top K', 'workflow-automation'),
                'type' => 'number',
                'default' => 40,
                'min' => 1,
                'max' => 100,
                'description' => __('Top-k sampling parameter', 'workflow-automation')
            ),
            array(
                'key' => 'stop_sequences',
                'label' => __('Stop Sequences', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 2,
                'placeholder' => __('One per line', 'workflow-automation'),
                'description' => __('Sequences where the API will stop generating (one per line)', 'workflow-automation')
            ),
            array(
                'key' => 'safety_settings',
                'label' => __('Safety Settings', 'workflow-automation'),
                'type' => 'select',
                'default' => 'default',
                'options' => array(
                    'default' => __('Default', 'workflow-automation'),
                    'none' => __('Block None', 'workflow-automation'),
                    'few' => __('Block Few', 'workflow-automation'),
                    'some' => __('Block Some', 'workflow-automation'),
                    'most' => __('Block Most', 'workflow-automation')
                ),
                'description' => __('Content safety filtering level', 'workflow-automation')
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
                throw new Exception(__('No Gemini integration selected', 'workflow-automation'));
            }
            
            // Get integration settings
            $integration_model = new WA_Integration_Settings_Model();
            $integration = $integration_model->get($integration_id);
            
            if (!$integration || !$integration->is_active) {
                throw new Exception(__('Selected Gemini integration is not active', 'workflow-automation'));
            }
            
            $settings = $integration->get_decrypted_settings();
            if (empty($settings['api_key'])) {
                throw new Exception(__('Gemini API key not configured', 'workflow-automation'));
            }
            
            // Prepare the prompt
            $prompt = $this->replace_variables($this->get_setting('prompt', ''), $context);
            
            // Prepare stop sequences
            $stop_sequences = array();
            $stop_sequences_text = $this->get_setting('stop_sequences', '');
            if (!empty($stop_sequences_text)) {
                $stop_sequences = array_filter(array_map('trim', explode("\n", $stop_sequences_text)));
            }
            
            // Prepare generation config
            $generation_config = array(
                'temperature' => floatval($this->get_setting('temperature', 0.7)),
                'topP' => floatval($this->get_setting('top_p', 0.95)),
                'topK' => intval($this->get_setting('top_k', 40)),
                'maxOutputTokens' => intval($this->get_setting('max_output_tokens', 1000))
            );
            
            if (!empty($stop_sequences)) {
                $generation_config['stopSequences'] = $stop_sequences;
            }
            
            // Prepare safety settings
            $safety_level_map = array(
                'none' => 'BLOCK_NONE',
                'few' => 'BLOCK_ONLY_HIGH',
                'some' => 'BLOCK_MEDIUM_AND_ABOVE',
                'most' => 'BLOCK_LOW_AND_ABOVE'
            );
            
            $safety_setting = $this->get_setting('safety_settings', 'default');
            $safety_settings = array();
            
            if ($safety_setting !== 'default') {
                $harm_categories = array(
                    'HARM_CATEGORY_HARASSMENT',
                    'HARM_CATEGORY_HATE_SPEECH',
                    'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'HARM_CATEGORY_DANGEROUS_CONTENT'
                );
                
                foreach ($harm_categories as $category) {
                    $safety_settings[] = array(
                        'category' => $category,
                        'threshold' => $safety_level_map[$safety_setting]
                    );
                }
            }
            
            // Prepare API request
            $request_body = array(
                'contents' => array(
                    array(
                        'parts' => array(
                            array('text' => $prompt)
                        )
                    )
                ),
                'generationConfig' => $generation_config
            );
            
            if (!empty($safety_settings)) {
                $request_body['safetySettings'] = $safety_settings;
            }
            
            $model = $this->get_setting('model', 'gemini-pro');
            $api_url = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                $model,
                $settings['api_key']
            );
            
            // Make API request
            $response = $this->http_request($api_url, array(
                'method' => 'POST',
                'headers' => array(
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
                    'Gemini API error: ' . $response['code'];
                throw new Exception($error_message);
            }
            
            $result = json_decode($response['body'], true);
            
            if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                throw new Exception(__('Invalid response from Gemini', 'workflow-automation'));
            }
            
            $content = $result['candidates'][0]['content']['parts'][0]['text'];
            
            $this->log('Gemini completion successful');
            
            return array(
                'success' => true,
                'response' => $content,
                'candidates' => $result['candidates'],
                'prompt_feedback' => isset($result['promptFeedback']) ? $result['promptFeedback'] : null
            );
            
        } catch (Exception $e) {
            $this->log('Gemini node error: ' . $e->getMessage(), 'error');
            
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
                __('Please select a Gemini integration', 'workflow-automation')
            );
        }
        
        if (empty($this->get_setting('prompt'))) {
            return new WP_Error(
                'missing_prompt',
                __('Prompt is required', 'workflow-automation')
            );
        }
        
        return true;
    }
}