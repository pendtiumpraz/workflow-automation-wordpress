<?php
/**
 * HTTP Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 */

/**
 * HTTP node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_HTTP_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'http';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('HTTP Request', 'workflow-automation'),
            'description' => __('Make HTTP requests to external APIs', 'workflow-automation'),
            'icon' => 'dashicons-admin-site-alt3',
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
        return array(
            array(
                'key' => 'method',
                'label' => __('Method', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'default' => 'GET',
                'options' => array(
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                    'PATCH' => 'PATCH',
                    'DELETE' => 'DELETE',
                    'HEAD' => 'HEAD',
                    'OPTIONS' => 'OPTIONS'
                ),
                'description' => __('HTTP request method', 'workflow-automation')
            ),
            array(
                'key' => 'url',
                'label' => __('URL', 'workflow-automation'),
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://api.example.com/endpoint',
                'description' => __('The URL to send the request to. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'headers',
                'label' => __('Headers', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => "Content-Type: application/json\nAuthorization: Bearer {{api_token}}\nX-Custom-Header: value",
                'description' => __('One header per line in format: Header-Name: value. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'auth_type',
                'label' => __('Authentication', 'workflow-automation'),
                'type' => 'select',
                'default' => 'none',
                'options' => array(
                    'none' => __('None', 'workflow-automation'),
                    'basic' => __('Basic Authentication', 'workflow-automation'),
                    'bearer' => __('Bearer Token', 'workflow-automation'),
                    'api_key' => __('API Key', 'workflow-automation'),
                    'custom' => __('Custom (use Headers)', 'workflow-automation')
                ),
                'description' => __('Authentication method', 'workflow-automation')
            ),
            array(
                'key' => 'auth_username',
                'label' => __('Username', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'username',
                'description' => __('Username for basic authentication', 'workflow-automation'),
                'condition' => array(
                    'field' => 'auth_type',
                    'operator' => '==',
                    'value' => 'basic'
                )
            ),
            array(
                'key' => 'auth_password',
                'label' => __('Password', 'workflow-automation'),
                'type' => 'password',
                'placeholder' => 'password',
                'description' => __('Password for basic authentication', 'workflow-automation'),
                'condition' => array(
                    'field' => 'auth_type',
                    'operator' => '==',
                    'value' => 'basic'
                )
            ),
            array(
                'key' => 'bearer_token',
                'label' => __('Bearer Token', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '{{api_token}}',
                'description' => __('Bearer token for authentication', 'workflow-automation'),
                'condition' => array(
                    'field' => 'auth_type',
                    'operator' => '==',
                    'value' => 'bearer'
                )
            ),
            array(
                'key' => 'api_key_header',
                'label' => __('API Key Header', 'workflow-automation'),
                'type' => 'text',
                'default' => 'X-API-Key',
                'placeholder' => 'X-API-Key',
                'description' => __('Header name for API key', 'workflow-automation'),
                'condition' => array(
                    'field' => 'auth_type',
                    'operator' => '==',
                    'value' => 'api_key'
                )
            ),
            array(
                'key' => 'api_key_value',
                'label' => __('API Key', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '{{api_key}}',
                'description' => __('API key value', 'workflow-automation'),
                'condition' => array(
                    'field' => 'auth_type',
                    'operator' => '==',
                    'value' => 'api_key'
                )
            ),
            array(
                'key' => 'body_type',
                'label' => __('Body Type', 'workflow-automation'),
                'type' => 'select',
                'default' => 'none',
                'options' => array(
                    'none' => __('None', 'workflow-automation'),
                    'json' => __('JSON', 'workflow-automation'),
                    'form' => __('Form Data', 'workflow-automation'),
                    'raw' => __('Raw', 'workflow-automation')
                ),
                'description' => __('Request body type', 'workflow-automation')
            ),
            array(
                'key' => 'body_json',
                'label' => __('JSON Body', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => '{"key": "value", "dynamic": "{{variable}}"}',
                'description' => __('JSON body content. Use {{variables}} for dynamic values.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'body_type',
                    'operator' => '==',
                    'value' => 'json'
                )
            ),
            array(
                'key' => 'body_form',
                'label' => __('Form Data', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => "field1=value1\nfield2={{variable}}\nfield3=value3",
                'description' => __('Form data, one field per line in format: name=value', 'workflow-automation'),
                'condition' => array(
                    'field' => 'body_type',
                    'operator' => '==',
                    'value' => 'form'
                )
            ),
            array(
                'key' => 'body_raw',
                'label' => __('Raw Body', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => 'Raw body content',
                'description' => __('Raw body content', 'workflow-automation'),
                'condition' => array(
                    'field' => 'body_type',
                    'operator' => '==',
                    'value' => 'raw'
                )
            ),
            array(
                'key' => 'timeout',
                'label' => __('Timeout (seconds)', 'workflow-automation'),
                'type' => 'number',
                'default' => 30,
                'min' => 1,
                'max' => 300,
                'description' => __('Request timeout in seconds', 'workflow-automation')
            ),
            array(
                'key' => 'follow_redirects',
                'label' => __('Follow Redirects', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Follow HTTP redirects', 'workflow-automation')
            ),
            array(
                'key' => 'verify_ssl',
                'label' => __('Verify SSL', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Verify SSL certificates', 'workflow-automation')
            ),
            array(
                'key' => 'response_format',
                'label' => __('Response Format', 'workflow-automation'),
                'type' => 'select',
                'default' => 'auto',
                'options' => array(
                    'auto' => __('Auto-detect', 'workflow-automation'),
                    'json' => __('JSON', 'workflow-automation'),
                    'xml' => __('XML', 'workflow-automation'),
                    'text' => __('Plain Text', 'workflow-automation')
                ),
                'description' => __('Expected response format', 'workflow-automation')
            ),
            array(
                'key' => 'error_on_status',
                'label' => __('Error on Non-2xx Status', 'workflow-automation'),
                'type' => 'checkbox',
                'default' => true,
                'description' => __('Treat non-2xx status codes as errors', 'workflow-automation')
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
        // Get settings
        $method = strtoupper($this->get_setting('method', 'GET'));
        $url = $this->replace_variables($this->get_setting('url', ''), $context);
        $timeout = intval($this->get_setting('timeout', 30));
        $follow_redirects = $this->get_setting('follow_redirects', true);
        $verify_ssl = $this->get_setting('verify_ssl', true);
        
        if (empty($url)) {
            throw new Exception('URL is required for HTTP request');
        }
        
        // Prepare request args
        $args = array(
            'method' => $method,
            'timeout' => $timeout,
            'redirection' => $follow_redirects ? 5 : 0,
            'sslverify' => $verify_ssl,
            'headers' => array()
        );
        
        // Parse headers
        $headers_raw = $this->get_setting('headers', '');
        if (!empty($headers_raw)) {
            $header_lines = explode("\n", $headers_raw);
            foreach ($header_lines as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, ':') !== false) {
                    list($name, $value) = explode(':', $line, 2);
                    $name = trim($name);
                    $value = $this->replace_variables(trim($value), $context);
                    $args['headers'][$name] = $value;
                }
            }
        }
        
        // Handle authentication
        $auth_type = $this->get_setting('auth_type', 'none');
        switch ($auth_type) {
            case 'basic':
                $username = $this->replace_variables($this->get_setting('auth_username', ''), $context);
                $password = $this->replace_variables($this->get_setting('auth_password', ''), $context);
                if (!empty($username) && !empty($password)) {
                    $args['headers']['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
                }
                break;
                
            case 'bearer':
                $token = $this->replace_variables($this->get_setting('bearer_token', ''), $context);
                if (!empty($token)) {
                    $args['headers']['Authorization'] = 'Bearer ' . $token;
                }
                break;
                
            case 'api_key':
                $header = $this->get_setting('api_key_header', 'X-API-Key');
                $value = $this->replace_variables($this->get_setting('api_key_value', ''), $context);
                if (!empty($header) && !empty($value)) {
                    $args['headers'][$header] = $value;
                }
                break;
        }
        
        // Handle body
        $body_type = $this->get_setting('body_type', 'none');
        if ($body_type !== 'none' && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            switch ($body_type) {
                case 'json':
                    $json_body = $this->replace_variables($this->get_setting('body_json', ''), $context);
                    if (!empty($json_body)) {
                        // Validate JSON
                        $decoded = json_decode($json_body);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('Invalid JSON body: ' . json_last_error_msg());
                        }
                        $args['body'] = $json_body;
                        $args['headers']['Content-Type'] = 'application/json';
                    }
                    break;
                    
                case 'form':
                    $form_data = array();
                    $form_raw = $this->get_setting('body_form', '');
                    if (!empty($form_raw)) {
                        $form_lines = explode("\n", $form_raw);
                        foreach ($form_lines as $line) {
                            $line = trim($line);
                            if (!empty($line) && strpos($line, '=') !== false) {
                                list($name, $value) = explode('=', $line, 2);
                                $name = trim($name);
                                $value = $this->replace_variables(trim($value), $context);
                                $form_data[$name] = $value;
                            }
                        }
                        $args['body'] = $form_data;
                    }
                    break;
                    
                case 'raw':
                    $raw_body = $this->replace_variables($this->get_setting('body_raw', ''), $context);
                    if (!empty($raw_body)) {
                        $args['body'] = $raw_body;
                    }
                    break;
            }
        }
        
        // Make the request
        $this->log(sprintf('Making %s request to %s', $method, $url));
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception('HTTP request failed: ' . $response->get_error_message());
        }
        
        // Get response data
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        
        // Check for error status codes
        $error_on_status = $this->get_setting('error_on_status', true);
        if ($error_on_status && ($status_code < 200 || $status_code >= 300)) {
            throw new Exception(sprintf(
                'HTTP request returned error status %d: %s',
                $status_code,
                substr($body, 0, 500)
            ));
        }
        
        // Parse response based on format
        $response_format = $this->get_setting('response_format', 'auto');
        $parsed_body = $body;
        
        if ($response_format === 'auto') {
            // Auto-detect based on Content-Type header
            $content_type = isset($headers['content-type']) ? $headers['content-type'] : '';
            if (strpos($content_type, 'application/json') !== false) {
                $response_format = 'json';
            } elseif (strpos($content_type, 'application/xml') !== false || strpos($content_type, 'text/xml') !== false) {
                $response_format = 'xml';
            }
        }
        
        switch ($response_format) {
            case 'json':
                $decoded = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $parsed_body = $decoded;
                } else {
                    $this->log('Failed to parse JSON response: ' . json_last_error_msg());
                }
                break;
                
            case 'xml':
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($body);
                if ($xml !== false) {
                    $parsed_body = json_decode(json_encode($xml), true);
                } else {
                    $errors = libxml_get_errors();
                    $this->log('Failed to parse XML response: ' . (!empty($errors) ? $errors[0]->message : 'Unknown error'));
                }
                break;
        }
        
        $this->log(sprintf('HTTP request completed with status %d', $status_code));
        
        // Return response data
        return array(
            'status_code' => $status_code,
            'headers' => $headers instanceof Requests_Utility_CaseInsensitiveDictionary ? $headers->getAll() : (array)$headers,
            'body' => $parsed_body,
            'raw_body' => $body,
            'url' => $url,
            'method' => $method,
            'request_time' => current_time('mysql')
        );
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $url = $this->get_setting('url', '');
        
        if (empty($url)) {
            return new WP_Error('missing_url', __('URL is required', 'workflow-automation'));
        }
        
        // Validate URL format if not a variable
        if (strpos($url, '{{') === false && !filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', __('Invalid URL format', 'workflow-automation'));
        }
        
        // Validate body JSON if specified
        $body_type = $this->get_setting('body_type', 'none');
        if ($body_type === 'json') {
            $json_body = $this->get_setting('body_json', '');
            if (!empty($json_body) && strpos($json_body, '{{') === false) {
                json_decode($json_body);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', __('Invalid JSON in body', 'workflow-automation'));
                }
            }
        }
        
        return true;
    }
}