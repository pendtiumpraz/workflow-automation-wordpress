<?php
/**
 * Google Sheets Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 */

/**
 * Google Sheets node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Google_Sheets_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'google_sheets';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Google Sheets', 'workflow-automation'),
            'description' => __('Read from and write to Google Sheets', 'workflow-automation'),
            'icon' => 'dashicons-media-spreadsheet',
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
        // Get available Google integrations
        $integration_model = new Integration_Settings_Model();
        $google_integrations = $integration_model->get_by_type('google');
        
        $integration_options = array(
            '' => __('-- Select Google Configuration --', 'workflow-automation')
        );
        
        foreach ($google_integrations as $integration) {
            if ($integration->is_active) {
                $integration_options[$integration->id] = $integration->name;
            }
        }
        
        return array(
            array(
                'key' => 'integration_id',
                'label' => __('Google Configuration', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'options' => $integration_options,
                'description' => __('Select a Google configuration to use', 'workflow-automation')
            ),
            array(
                'key' => 'action',
                'label' => __('Action', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'default' => 'read',
                'options' => array(
                    'read' => __('Read Data', 'workflow-automation'),
                    'write' => __('Write Data', 'workflow-automation'),
                    'append' => __('Append Row', 'workflow-automation'),
                    'update' => __('Update Cell/Range', 'workflow-automation'),
                    'clear' => __('Clear Range', 'workflow-automation'),
                    'create_sheet' => __('Create Sheet', 'workflow-automation')
                ),
                'description' => __('What to do with the spreadsheet', 'workflow-automation')
            ),
            array(
                'key' => 'spreadsheet_id',
                'label' => __('Spreadsheet ID', 'workflow-automation'),
                'type' => 'text',
                'required' => true,
                'placeholder' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms',
                'description' => __('The ID from the spreadsheet URL. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'sheet_name',
                'label' => __('Sheet Name', 'workflow-automation'),
                'type' => 'text',
                'default' => 'Sheet1',
                'placeholder' => 'Sheet1',
                'description' => __('Name of the sheet/tab to work with', 'workflow-automation')
            ),
            array(
                'key' => 'range',
                'label' => __('Range', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'A1:C10',
                'description' => __('Cell range (e.g., A1:C10, A:A, 1:1). Use {{variables}} for dynamic values.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('read', 'update', 'clear')
                )
            ),
            array(
                'key' => 'read_options',
                'label' => __('Read Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'first_row_as_header',
                        'label' => __('First Row as Header', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Use first row as column headers', 'workflow-automation')
                    ),
                    array(
                        'key' => 'value_render_option',
                        'label' => __('Value Render Option', 'workflow-automation'),
                        'type' => 'select',
                        'default' => 'FORMATTED_VALUE',
                        'options' => array(
                            'FORMATTED_VALUE' => __('Formatted Value', 'workflow-automation'),
                            'UNFORMATTED_VALUE' => __('Unformatted Value', 'workflow-automation'),
                            'FORMULA' => __('Formula', 'workflow-automation')
                        ),
                        'description' => __('How values should be rendered', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'read'
                )
            ),
            array(
                'key' => 'data',
                'label' => __('Data to Write', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => '[["Name", "Email", "Status"], ["John Doe", "john@example.com", "Active"]]',
                'description' => __('Data in JSON array format. For append, use single row: ["value1", "value2"]. Use {{variables}} for dynamic values.', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('write', 'append', 'update')
                )
            ),
            array(
                'key' => 'write_options',
                'label' => __('Write Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'value_input_option',
                        'label' => __('Value Input Option', 'workflow-automation'),
                        'type' => 'select',
                        'default' => 'USER_ENTERED',
                        'options' => array(
                            'USER_ENTERED' => __('User Entered (parse formulas)', 'workflow-automation'),
                            'RAW' => __('Raw (store as-is)', 'workflow-automation')
                        ),
                        'description' => __('How input data should be interpreted', 'workflow-automation')
                    ),
                    array(
                        'key' => 'insert_data_option',
                        'label' => __('Insert Data Option', 'workflow-automation'),
                        'type' => 'select',
                        'default' => 'INSERT_ROWS',
                        'options' => array(
                            'INSERT_ROWS' => __('Insert Rows', 'workflow-automation'),
                            'OVERWRITE' => __('Overwrite', 'workflow-automation')
                        ),
                        'description' => __('How to handle existing data when appending', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('write', 'append', 'update')
                )
            ),
            array(
                'key' => 'new_sheet_title',
                'label' => __('New Sheet Title', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'New Sheet',
                'description' => __('Title for the new sheet', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'create_sheet'
                )
            ),
            array(
                'key' => 'sheet_properties',
                'label' => __('Sheet Properties', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'row_count',
                        'label' => __('Row Count', 'workflow-automation'),
                        'type' => 'number',
                        'default' => 1000,
                        'min' => 1,
                        'description' => __('Number of rows in new sheet', 'workflow-automation')
                    ),
                    array(
                        'key' => 'column_count',
                        'label' => __('Column Count', 'workflow-automation'),
                        'type' => 'number',
                        'default' => 26,
                        'min' => 1,
                        'description' => __('Number of columns in new sheet', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'create_sheet'
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
            throw new Exception('Google configuration is required');
        }
        
        $integration_model = new Integration_Settings_Model();
        $integration = $integration_model->get($integration_id);
        
        if (!$integration || !$integration->is_active) {
            throw new Exception('Selected Google integration is not available or inactive');
        }
        
        // Decrypt settings
        $settings = $integration_model->decrypt_settings($integration->settings);
        
        // Get action and common parameters
        $action = $this->get_setting('action', 'read');
        $spreadsheet_id = $this->replace_variables($this->get_setting('spreadsheet_id', ''), $context);
        $sheet_name = $this->replace_variables($this->get_setting('sheet_name', 'Sheet1'), $context);
        
        if (empty($spreadsheet_id)) {
            throw new Exception('Spreadsheet ID is required');
        }
        
        // Initialize Google Sheets API client
        $access_token = $this->get_access_token($settings);
        
        // Execute based on action
        switch ($action) {
            case 'read':
                return $this->read_data($access_token, $spreadsheet_id, $sheet_name, $context);
                
            case 'write':
                return $this->write_data($access_token, $spreadsheet_id, $sheet_name, $context);
                
            case 'append':
                return $this->append_row($access_token, $spreadsheet_id, $sheet_name, $context);
                
            case 'update':
                return $this->update_range($access_token, $spreadsheet_id, $sheet_name, $context);
                
            case 'clear':
                return $this->clear_range($access_token, $spreadsheet_id, $sheet_name, $context);
                
            case 'create_sheet':
                return $this->create_sheet($access_token, $spreadsheet_id, $context);
                
            default:
                throw new Exception('Invalid action: ' . $action);
        }
    }

    /**
     * Get access token from settings
     *
     * @since    1.0.0
     * @param    array    $settings    Integration settings
     * @return   string
     */
    private function get_access_token($settings) {
        if ($settings['auth_type'] === 'service_account') {
            return $this->get_service_account_token($settings);
        } else {
            // OAuth flow - check if token needs refresh
            if (empty($settings['access_token'])) {
                throw new Exception('Google Sheets integration is not authenticated');
            }
            
            // TODO: Implement token refresh logic
            return $settings['access_token'];
        }
    }

    /**
     * Get service account access token
     *
     * @since    1.0.0
     * @param    array    $settings    Integration settings
     * @return   string
     */
    private function get_service_account_token($settings) {
        if (empty($settings['service_account_json'])) {
            throw new Exception('Service account JSON is missing');
        }
        
        $service_account = json_decode($settings['service_account_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid service account JSON');
        }
        
        // Create JWT for service account authentication
        $now = time();
        $header = array(
            'typ' => 'JWT',
            'alg' => 'RS256'
        );
        
        $claim = array(
            'iss' => $service_account['client_email'],
            'scope' => $settings['scopes'] ?: 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        );
        
        // Encode JWT
        $header_encoded = $this->base64url_encode(json_encode($header));
        $claim_encoded = $this->base64url_encode(json_encode($claim));
        
        $signature_input = $header_encoded . '.' . $claim_encoded;
        
        // Sign with private key
        $private_key = openssl_pkey_get_private($service_account['private_key']);
        if (!$private_key) {
            throw new Exception('Invalid service account private key');
        }
        
        openssl_sign($signature_input, $signature, $private_key, OPENSSL_ALGO_SHA256);
        $signature_encoded = $this->base64url_encode($signature);
        
        $jwt = $signature_input . '.' . $signature_encoded;
        
        // Exchange JWT for access token
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Failed to get access token: ' . $response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            throw new Exception('Google auth error: ' . $body['error_description']);
        }
        
        return $body['access_token'];
    }

    /**
     * Base64 URL encode
     *
     * @since    1.0.0
     * @param    string    $data    Data to encode
     * @return   string
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Read data from spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    string    $sheet_name      Sheet name
     * @param    array     $context         Execution context
     * @return   array
     */
    private function read_data($access_token, $spreadsheet_id, $sheet_name, $context) {
        $range = $this->replace_variables($this->get_setting('range', ''), $context);
        if (empty($range)) {
            $range = $sheet_name;
        } else {
            $range = $sheet_name . '!' . $range;
        }
        
        $read_options = $this->get_setting('read_options', array());
        $value_render = isset($read_options['value_render_option']) ? $read_options['value_render_option'] : 'FORMATTED_VALUE';
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?valueRenderOption=%s',
            $spreadsheet_id,
            urlencode($range),
            $value_render
        );
        
        $response = $this->google_api_request($url, $access_token);
        
        $data = $response['values'] ?? array();
        
        // Process data based on options
        if (!empty($read_options['first_row_as_header']) && count($data) > 1) {
            $headers = array_shift($data);
            $result = array();
            
            foreach ($data as $row) {
                $row_data = array();
                foreach ($headers as $index => $header) {
                    $row_data[$header] = isset($row[$index]) ? $row[$index] : '';
                }
                $result[] = $row_data;
            }
            
            return array(
                'data' => $result,
                'headers' => $headers,
                'row_count' => count($result),
                'range' => $range
            );
        }
        
        return array(
            'data' => $data,
            'row_count' => count($data),
            'range' => $range
        );
    }

    /**
     * Write data to spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    string    $sheet_name      Sheet name
     * @param    array     $context         Execution context
     * @return   array
     */
    private function write_data($access_token, $spreadsheet_id, $sheet_name, $context) {
        $range = $this->replace_variables($this->get_setting('range', ''), $context);
        if (empty($range)) {
            $range = $sheet_name;
        } else {
            $range = $sheet_name . '!' . $range;
        }
        
        $data = $this->replace_variables($this->get_setting('data', ''), $context);
        if (empty($data)) {
            throw new Exception('Data is required for write operation');
        }
        
        // Parse data
        $values = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        $write_options = $this->get_setting('write_options', array());
        $value_input = isset($write_options['value_input_option']) ? $write_options['value_input_option'] : 'USER_ENTERED';
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?valueInputOption=%s',
            $spreadsheet_id,
            urlencode($range),
            $value_input
        );
        
        $body = array(
            'range' => $range,
            'values' => $values
        );
        
        $response = $this->google_api_request($url, $access_token, 'PUT', $body);
        
        return array(
            'updated_cells' => $response['updatedCells'] ?? 0,
            'updated_rows' => $response['updatedRows'] ?? 0,
            'updated_columns' => $response['updatedColumns'] ?? 0,
            'updated_range' => $response['updatedRange'] ?? $range
        );
    }

    /**
     * Append row to spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    string    $sheet_name      Sheet name
     * @param    array     $context         Execution context
     * @return   array
     */
    private function append_row($access_token, $spreadsheet_id, $sheet_name, $context) {
        $data = $this->replace_variables($this->get_setting('data', ''), $context);
        if (empty($data)) {
            throw new Exception('Data is required for append operation');
        }
        
        // Parse data
        $values = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        // Ensure data is in 2D array format
        if (!empty($values) && !is_array($values[0])) {
            $values = array($values);
        }
        
        $write_options = $this->get_setting('write_options', array());
        $value_input = isset($write_options['value_input_option']) ? $write_options['value_input_option'] : 'USER_ENTERED';
        $insert_option = isset($write_options['insert_data_option']) ? $write_options['insert_data_option'] : 'INSERT_ROWS';
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=%s&insertDataOption=%s',
            $spreadsheet_id,
            urlencode($sheet_name),
            $value_input,
            $insert_option
        );
        
        $body = array(
            'values' => $values
        );
        
        $response = $this->google_api_request($url, $access_token, 'POST', $body);
        
        return array(
            'appended_rows' => $response['updates']['updatedRows'] ?? 0,
            'appended_range' => $response['updates']['updatedRange'] ?? '',
            'table_range' => $response['tableRange'] ?? ''
        );
    }

    /**
     * Update range in spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    string    $sheet_name      Sheet name
     * @param    array     $context         Execution context
     * @return   array
     */
    private function update_range($access_token, $spreadsheet_id, $sheet_name, $context) {
        return $this->write_data($access_token, $spreadsheet_id, $sheet_name, $context);
    }

    /**
     * Clear range in spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    string    $sheet_name      Sheet name
     * @param    array     $context         Execution context
     * @return   array
     */
    private function clear_range($access_token, $spreadsheet_id, $sheet_name, $context) {
        $range = $this->replace_variables($this->get_setting('range', ''), $context);
        if (empty($range)) {
            throw new Exception('Range is required for clear operation');
        }
        
        $range = $sheet_name . '!' . $range;
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:clear',
            $spreadsheet_id,
            urlencode($range)
        );
        
        $response = $this->google_api_request($url, $access_token, 'POST');
        
        return array(
            'cleared_range' => $response['clearedRange'] ?? $range,
            'success' => true
        );
    }

    /**
     * Create new sheet in spreadsheet
     *
     * @since    1.0.0
     * @param    string    $access_token    Access token
     * @param    string    $spreadsheet_id  Spreadsheet ID
     * @param    array     $context         Execution context
     * @return   array
     */
    private function create_sheet($access_token, $spreadsheet_id, $context) {
        $title = $this->replace_variables($this->get_setting('new_sheet_title', ''), $context);
        if (empty($title)) {
            throw new Exception('Sheet title is required');
        }
        
        $sheet_properties = $this->get_setting('sheet_properties', array());
        $row_count = isset($sheet_properties['row_count']) ? intval($sheet_properties['row_count']) : 1000;
        $column_count = isset($sheet_properties['column_count']) ? intval($sheet_properties['column_count']) : 26;
        
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s:batchUpdate',
            $spreadsheet_id
        );
        
        $body = array(
            'requests' => array(
                array(
                    'addSheet' => array(
                        'properties' => array(
                            'title' => $title,
                            'gridProperties' => array(
                                'rowCount' => $row_count,
                                'columnCount' => $column_count
                            )
                        )
                    )
                )
            )
        );
        
        $response = $this->google_api_request($url, $access_token, 'POST', $body);
        
        $sheet_id = $response['replies'][0]['addSheet']['properties']['sheetId'] ?? null;
        
        return array(
            'sheet_id' => $sheet_id,
            'sheet_title' => $title,
            'row_count' => $row_count,
            'column_count' => $column_count,
            'success' => !empty($sheet_id)
        );
    }

    /**
     * Make Google API request
     *
     * @since    1.0.0
     * @param    string    $url             API URL
     * @param    string    $access_token    Access token
     * @param    string    $method          HTTP method
     * @param    array     $body            Request body
     * @return   array
     */
    private function google_api_request($url, $access_token, $method = 'GET', $body = null) {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            )
        );
        
        if ($body !== null) {
            $args['body'] = json_encode($body);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            throw new Exception('Google Sheets API error: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $data = json_decode($response_body, true);
        
        if ($status_code >= 400) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error';
            throw new Exception(sprintf('Google Sheets API error (HTTP %d): %s', $status_code, $error_message));
        }
        
        return $data;
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $integration_id = $this->get_setting('integration_id', '');
        $spreadsheet_id = $this->get_setting('spreadsheet_id', '');
        $action = $this->get_setting('action', '');
        
        if (empty($integration_id)) {
            return new WP_Error('missing_integration', __('Google configuration is required', 'workflow-automation'));
        }
        
        if (empty($spreadsheet_id)) {
            return new WP_Error('missing_spreadsheet', __('Spreadsheet ID is required', 'workflow-automation'));
        }
        
        // Validate data for write operations
        if (in_array($action, array('write', 'append', 'update'))) {
            $data = $this->get_setting('data', '');
            if (empty($data)) {
                return new WP_Error('missing_data', __('Data is required for write operations', 'workflow-automation'));
            }
            
            // Validate JSON if not a variable
            if (strpos($data, '{{') === false) {
                json_decode($data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return new WP_Error('invalid_json', __('Invalid JSON in data field', 'workflow-automation'));
                }
            }
        }
        
        return true;
    }
}