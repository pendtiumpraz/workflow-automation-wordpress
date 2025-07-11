<?php
/**
 * Formatter Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 */

/**
 * Formatter node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Formatter_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'formatter';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Format Data', 'workflow-automation'),
            'description' => __('Format data for output or display', 'workflow-automation'),
            'icon' => 'dashicons-editor-code',
            'category' => 'data',
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
                'key' => 'format_type',
                'label' => __('Format Type', 'workflow-automation'),
                'type' => 'select',
                'required' => true,
                'default' => 'template',
                'options' => array(
                    'template' => __('Text Template', 'workflow-automation'),
                    'json' => __('JSON', 'workflow-automation'),
                    'xml' => __('XML', 'workflow-automation'),
                    'csv' => __('CSV', 'workflow-automation'),
                    'table' => __('HTML Table', 'workflow-automation'),
                    'markdown' => __('Markdown', 'workflow-automation'),
                    'yaml' => __('YAML', 'workflow-automation'),
                    'url_params' => __('URL Parameters', 'workflow-automation'),
                    'form_data' => __('Form Data', 'workflow-automation')
                ),
                'description' => __('Output format type', 'workflow-automation')
            ),
            array(
                'key' => 'template',
                'label' => __('Template', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => "Hello {{name}},\n\nYour order #{{order_id}} has been {{status}}.\n\nItems:\n{{#each items}}\n- {{this.name}} ({{this.quantity}} x {{this.price}})\n{{/each}}\n\nTotal: {{total}}",
                'description' => __('Template with {{variables}} and {{#each}} loops', 'workflow-automation'),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'template'
                )
            ),
            array(
                'key' => 'json_options',
                'label' => __('JSON Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'pretty_print',
                        'label' => __('Pretty Print', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Format JSON with indentation', 'workflow-automation')
                    ),
                    array(
                        'key' => 'escape_unicode',
                        'label' => __('Escape Unicode', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Escape non-ASCII characters', 'workflow-automation')
                    ),
                    array(
                        'key' => 'include_fields',
                        'label' => __('Include Fields', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "id\nname\nstatus",
                        'description' => __('Only include these fields (one per line)', 'workflow-automation')
                    ),
                    array(
                        'key' => 'exclude_fields',
                        'label' => __('Exclude Fields', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "password\nsecret_key",
                        'description' => __('Exclude these fields (one per line)', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'json'
                )
            ),
            array(
                'key' => 'xml_options',
                'label' => __('XML Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'root_element',
                        'label' => __('Root Element', 'workflow-automation'),
                        'type' => 'text',
                        'default' => 'root',
                        'description' => __('XML root element name', 'workflow-automation')
                    ),
                    array(
                        'key' => 'item_element',
                        'label' => __('Item Element', 'workflow-automation'),
                        'type' => 'text',
                        'default' => 'item',
                        'description' => __('Element name for array items', 'workflow-automation')
                    ),
                    array(
                        'key' => 'attributes',
                        'label' => __('Use Attributes', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => false,
                        'description' => __('Convert fields to XML attributes where possible', 'workflow-automation')
                    ),
                    array(
                        'key' => 'cdata_fields',
                        'label' => __('CDATA Fields', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "description\ncontent",
                        'description' => __('Wrap these fields in CDATA (one per line)', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'xml'
                )
            ),
            array(
                'key' => 'csv_options',
                'label' => __('CSV Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'delimiter',
                        'label' => __('Delimiter', 'workflow-automation'),
                        'type' => 'text',
                        'default' => ',',
                        'description' => __('Field delimiter', 'workflow-automation')
                    ),
                    array(
                        'key' => 'enclosure',
                        'label' => __('Enclosure', 'workflow-automation'),
                        'type' => 'text',
                        'default' => '"',
                        'description' => __('Field enclosure character', 'workflow-automation')
                    ),
                    array(
                        'key' => 'include_headers',
                        'label' => __('Include Headers', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Include column headers in first row', 'workflow-automation')
                    ),
                    array(
                        'key' => 'columns',
                        'label' => __('Columns', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 5,
                        'placeholder' => "id:ID\nname:Full Name\nemail:Email Address\nstatus:Status",
                        'description' => __('Column mapping (field:header) one per line', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'csv'
                )
            ),
            array(
                'key' => 'table_options',
                'label' => __('Table Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'table_class',
                        'label' => __('Table CSS Class', 'workflow-automation'),
                        'type' => 'text',
                        'default' => 'wa-formatted-table',
                        'description' => __('CSS class for the table', 'workflow-automation')
                    ),
                    array(
                        'key' => 'include_styles',
                        'label' => __('Include Inline Styles', 'workflow-automation'),
                        'type' => 'checkbox',
                        'default' => true,
                        'description' => __('Add basic inline CSS styles', 'workflow-automation')
                    ),
                    array(
                        'key' => 'columns',
                        'label' => __('Columns', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 5,
                        'placeholder' => "id:ID:center\nname:Full Name:left\nemail:Email:left\nstatus:Status:center",
                        'description' => __('Column mapping (field:header:align) one per line', 'workflow-automation')
                    ),
                    array(
                        'key' => 'empty_cell',
                        'label' => __('Empty Cell Text', 'workflow-automation'),
                        'type' => 'text',
                        'default' => '-',
                        'description' => __('Text to show in empty cells', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'table'
                )
            ),
            array(
                'key' => 'markdown_options',
                'label' => __('Markdown Options', 'workflow-automation'),
                'type' => 'group',
                'fields' => array(
                    array(
                        'key' => 'table_style',
                        'label' => __('Table Style', 'workflow-automation'),
                        'type' => 'select',
                        'default' => 'github',
                        'options' => array(
                            'github' => __('GitHub Flavored', 'workflow-automation'),
                            'simple' => __('Simple', 'workflow-automation'),
                            'grid' => __('Grid', 'workflow-automation')
                        ),
                        'description' => __('Markdown table style', 'workflow-automation')
                    ),
                    array(
                        'key' => 'template',
                        'label' => __('Markdown Template', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 10,
                        'placeholder' => "# {{title}}\n\n## Summary\n{{summary}}\n\n## Items\n{{#each items}}\n### {{this.name}}\n- **ID**: {{this.id}}\n- **Status**: {{this.status}}\n{{/each}}",
                        'description' => __('Markdown template with variables', 'workflow-automation')
                    )
                ),
                'condition' => array(
                    'field' => 'format_type',
                    'operator' => '==',
                    'value' => 'markdown'
                )
            ),
            array(
                'key' => 'data_source',
                'label' => __('Data Source', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'Leave empty to use all data',
                'description' => __('Path to data to format (e.g., data.items)', 'workflow-automation')
            ),
            array(
                'key' => 'output_path',
                'label' => __('Output Path', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'formatted_output',
                'description' => __('Where to store the formatted output', 'workflow-automation')
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
        $format_type = $this->get_setting('format_type', 'template');
        $data_source = $this->get_setting('data_source', '');
        $output_path = $this->get_setting('output_path', 'formatted_output');
        
        // Get source data
        $source_data = $previous_data;
        if (!empty($data_source)) {
            $source_data = $this->get_value_by_path($previous_data, $data_source);
        }
        
        // Format based on type
        $formatted = '';
        switch ($format_type) {
            case 'template':
                $formatted = $this->format_template($source_data, $context);
                break;
                
            case 'json':
                $formatted = $this->format_json($source_data, $context);
                break;
                
            case 'xml':
                $formatted = $this->format_xml($source_data, $context);
                break;
                
            case 'csv':
                $formatted = $this->format_csv($source_data, $context);
                break;
                
            case 'table':
                $formatted = $this->format_table($source_data, $context);
                break;
                
            case 'markdown':
                $formatted = $this->format_markdown($source_data, $context);
                break;
                
            case 'yaml':
                $formatted = $this->format_yaml($source_data, $context);
                break;
                
            case 'url_params':
                $formatted = $this->format_url_params($source_data, $context);
                break;
                
            case 'form_data':
                $formatted = $this->format_form_data($source_data, $context);
                break;
        }
        
        $this->log(sprintf('Formatted data as %s (%d characters)', $format_type, strlen($formatted)));
        
        // Prepare output
        $result = is_array($previous_data) ? $previous_data : array();
        
        if (!empty($output_path)) {
            $this->set_value_by_path($result, $output_path, $formatted);
        } else {
            $result['formatted_output'] = $formatted;
        }
        
        // Add metadata
        $result['_format_metadata'] = array(
            'type' => $format_type,
            'length' => strlen($formatted),
            'timestamp' => current_time('mysql')
        );
        
        return $result;
    }

    /**
     * Format as template
     *
     * @since    1.0.0
     * @param    mixed    $data      Data to format
     * @param    array    $context   Execution context
     * @return   string
     */
    private function format_template($data, $context) {
        $template = $this->get_setting('template', '');
        
        if (empty($template)) {
            return '';
        }
        
        // Simple template engine
        $output = $template;
        
        // Replace simple variables {{variable}}
        $output = preg_replace_callback('/\{\{([^}]+)\}\}/', function($matches) use ($data, $context) {
            $path = trim($matches[1]);
            
            // Check context variables first
            if (strpos($path, 'context.') === 0) {
                $value = $this->get_value_by_path($context, substr($path, 8));
            } else {
                $value = $this->get_value_by_path($data, $path);
            }
            
            return is_scalar($value) ? $value : json_encode($value);
        }, $output);
        
        // Handle each loops {{#each items}}...{{/each}}
        $output = preg_replace_callback('/\{\{#each\s+([^}]+)\}\}(.*?)\{\{\/each\}\}/s', function($matches) use ($data) {
            $array_path = trim($matches[1]);
            $loop_template = $matches[2];
            
            $array_data = $this->get_value_by_path($data, $array_path);
            
            if (!is_array($array_data)) {
                return '';
            }
            
            $loop_output = '';
            foreach ($array_data as $index => $item) {
                $item_output = $loop_template;
                
                // Replace {{this.property}}
                $item_output = preg_replace_callback('/\{\{this\.([^}]+)\}\}/', function($m) use ($item) {
                    $value = $this->get_value_by_path($item, $m[1]);
                    return is_scalar($value) ? $value : json_encode($value);
                }, $item_output);
                
                // Replace {{@index}}
                $item_output = str_replace('{{@index}}', $index, $item_output);
                
                $loop_output .= $item_output;
            }
            
            return $loop_output;
        }, $output);
        
        return $output;
    }

    /**
     * Format as JSON
     *
     * @since    1.0.0
     * @param    mixed    $data      Data to format
     * @param    array    $context   Execution context
     * @return   string
     */
    private function format_json($data, $context) {
        $options = $this->get_setting('json_options', array());
        $pretty_print = $options['pretty_print'] ?? true;
        $escape_unicode = $options['escape_unicode'] ?? false;
        $include_fields = array_filter(array_map('trim', explode("\n", $options['include_fields'] ?? '')));
        $exclude_fields = array_filter(array_map('trim', explode("\n", $options['exclude_fields'] ?? '')));
        
        // Filter data if needed
        if (!empty($include_fields) || !empty($exclude_fields)) {
            $data = $this->filter_fields($data, $include_fields, $exclude_fields);
        }
        
        // Prepare JSON options
        $json_options = 0;
        if ($pretty_print) {
            $json_options |= JSON_PRETTY_PRINT;
        }
        if (!$escape_unicode) {
            $json_options |= JSON_UNESCAPED_UNICODE;
        }
        $json_options |= JSON_UNESCAPED_SLASHES;
        
        return json_encode($data, $json_options);
    }

    /**
     * Format as CSV
     *
     * @since    1.0.0
     * @param    mixed    $data      Data to format
     * @param    array    $context   Execution context
     * @return   string
     */
    private function format_csv($data, $context) {
        $options = $this->get_setting('csv_options', array());
        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';
        $include_headers = $options['include_headers'] ?? true;
        $columns_config = array_filter(array_map('trim', explode("\n", $options['columns'] ?? '')));
        
        // Ensure data is array
        if (!is_array($data)) {
            $data = array($data);
        }
        
        // If single object, wrap in array
        if (!isset($data[0])) {
            $data = array($data);
        }
        
        // Parse column configuration
        $columns = array();
        $headers = array();
        
        if (!empty($columns_config)) {
            foreach ($columns_config as $config) {
                $parts = explode(':', $config, 2);
                $field = trim($parts[0]);
                $header = isset($parts[1]) ? trim($parts[1]) : $field;
                $columns[$field] = $header;
                $headers[] = $header;
            }
        } else {
            // Auto-detect columns from first item
            if (!empty($data[0]) && is_array($data[0])) {
                foreach ($data[0] as $key => $value) {
                    if (is_scalar($value)) {
                        $columns[$key] = $key;
                        $headers[] = $key;
                    }
                }
            }
        }
        
        // Create CSV
        $output = '';
        $temp = fopen('php://temp', 'r+');
        
        // Write headers
        if ($include_headers && !empty($headers)) {
            fputcsv($temp, $headers, $delimiter, $enclosure);
        }
        
        // Write data
        foreach ($data as $row) {
            $csv_row = array();
            foreach ($columns as $field => $header) {
                $value = $this->get_value_by_path($row, $field);
                $csv_row[] = is_scalar($value) ? $value : json_encode($value);
            }
            fputcsv($temp, $csv_row, $delimiter, $enclosure);
        }
        
        // Get CSV string
        rewind($temp);
        $output = stream_get_contents($temp);
        fclose($temp);
        
        return $output;
    }

    /**
     * Filter fields
     *
     * @since    1.0.0
     * @param    mixed    $data             Data to filter
     * @param    array    $include_fields   Fields to include
     * @param    array    $exclude_fields   Fields to exclude
     * @return   mixed
     */
    private function filter_fields($data, $include_fields, $exclude_fields) {
        if (!is_array($data)) {
            return $data;
        }
        
        // Handle array of items
        if (isset($data[0])) {
            return array_map(function($item) use ($include_fields, $exclude_fields) {
                return $this->filter_fields($item, $include_fields, $exclude_fields);
            }, $data);
        }
        
        // Filter single object
        $filtered = array();
        
        if (!empty($include_fields)) {
            // Include only specified fields
            foreach ($include_fields as $field) {
                $value = $this->get_value_by_path($data, $field);
                if ($value !== null) {
                    $this->set_value_by_path($filtered, $field, $value);
                }
            }
        } else {
            // Start with all fields
            $filtered = $data;
            
            // Exclude specified fields
            foreach ($exclude_fields as $field) {
                $this->delete_value_by_path($filtered, $field);
            }
        }
        
        return $filtered;
    }

    /**
     * Get value by path (helper method)
     *
     * @since    1.0.0
     * @param    mixed     $data    The data
     * @param    string    $path    The path
     * @return   mixed
     */
    private function get_value_by_path($data, $path) {
        // Same implementation as in transform node
        if (empty($path)) {
            return $data;
        }
        
        $parts = explode('.', $path);
        $current = $data;
        
        foreach ($parts as $part) {
            if (preg_match('/^(.+)\[(\d+)\]$/', $part, $matches)) {
                $key = $matches[1];
                $index = intval($matches[2]);
                
                if (is_array($current) && isset($current[$key]) && is_array($current[$key])) {
                    $current = isset($current[$key][$index]) ? $current[$key][$index] : null;
                } else {
                    return null;
                }
            } else {
                if (is_array($current) && isset($current[$part])) {
                    $current = $current[$part];
                } elseif (is_object($current) && isset($current->$part)) {
                    $current = $current->$part;
                } else {
                    return null;
                }
            }
        }
        
        return $current;
    }

    /**
     * Set value by path (helper method)
     *
     * @since    1.0.0
     * @param    array     &$data   The data
     * @param    string    $path    The path
     * @param    mixed     $value   The value
     */
    private function set_value_by_path(&$data, $path, $value) {
        // Same implementation as in transform node
        if (empty($path)) {
            $data = $value;
            return;
        }
        
        $parts = explode('.', $path);
        $current = &$data;
        
        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            $is_last = ($i === count($parts) - 1);
            
            if ($is_last) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part])) {
                    $current[$part] = array();
                }
                $current = &$current[$part];
            }
        }
    }

    /**
     * Delete value by path (helper method)
     *
     * @since    1.0.0
     * @param    array     &$data   The data
     * @param    string    $path    The path
     */
    private function delete_value_by_path(&$data, $path) {
        // Same implementation as in transform node
        if (empty($path)) {
            return;
        }
        
        $parts = explode('.', $path);
        $current = &$data;
        
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $part = $parts[$i];
            
            if (is_array($current) && isset($current[$part])) {
                $current = &$current[$part];
            } else {
                return;
            }
        }
        
        $last_part = $parts[count($parts) - 1];
        unset($current[$last_part]);
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $format_type = $this->get_setting('format_type', '');
        
        if (empty($format_type)) {
            return new WP_Error('missing_format_type', __('Format type is required', 'workflow-automation'));
        }
        
        // Validate based on format type
        switch ($format_type) {
            case 'template':
                $template = $this->get_setting('template', '');
                if (empty($template)) {
                    return new WP_Error('missing_template', __('Template is required for template formatting', 'workflow-automation'));
                }
                break;
                
            case 'markdown':
                $options = $this->get_setting('markdown_options', array());
                if (empty($options['template'])) {
                    return new WP_Error('missing_template', __('Markdown template is required', 'workflow-automation'));
                }
                break;
        }
        
        return true;
    }
}