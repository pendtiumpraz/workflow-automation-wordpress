<?php
/**
 * Transform Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 */

/**
 * Transform node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/data
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Transform_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'transform';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Transform Data', 'workflow-automation'),
            'description' => __('Transform and manipulate data using various operations', 'workflow-automation'),
            'icon' => 'dashicons-randomize',
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
                'key' => 'transformations',
                'label' => __('Transformations', 'workflow-automation'),
                'type' => 'repeater',
                'description' => __('Add one or more transformations to apply to the data', 'workflow-automation'),
                'fields' => array(
                    array(
                        'key' => 'type',
                        'label' => __('Transformation Type', 'workflow-automation'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'set' => __('Set Value', 'workflow-automation'),
                            'copy' => __('Copy Value', 'workflow-automation'),
                            'move' => __('Move Value', 'workflow-automation'),
                            'delete' => __('Delete Value', 'workflow-automation'),
                            'rename' => __('Rename Key', 'workflow-automation'),
                            'merge' => __('Merge Objects', 'workflow-automation'),
                            'split' => __('Split String', 'workflow-automation'),
                            'join' => __('Join Array', 'workflow-automation'),
                            'extract' => __('Extract Data', 'workflow-automation'),
                            'calculate' => __('Calculate', 'workflow-automation'),
                            'format' => __('Format Value', 'workflow-automation'),
                            'convert' => __('Convert Type', 'workflow-automation'),
                            'filter_array' => __('Filter Array', 'workflow-automation'),
                            'map_array' => __('Map Array', 'workflow-automation'),
                            'reduce_array' => __('Reduce Array', 'workflow-automation'),
                            'custom' => __('Custom JavaScript', 'workflow-automation')
                        )
                    ),
                    array(
                        'key' => 'source',
                        'label' => __('Source Path', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'data.items[0].name or {{variable}}',
                        'description' => __('Path to source data using dot notation', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => 'in',
                            'value' => array('copy', 'move', 'rename', 'extract', 'format', 'convert')
                        )
                    ),
                    array(
                        'key' => 'target',
                        'label' => __('Target Path', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'result.name',
                        'description' => __('Path where to store the result', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => 'in',
                            'value' => array('set', 'copy', 'move', 'rename', 'extract', 'calculate', 'format', 'convert')
                        )
                    ),
                    array(
                        'key' => 'value',
                        'label' => __('Value', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'Static value or {{variable}}',
                        'description' => __('The value to set', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'set'
                        )
                    ),
                    array(
                        'key' => 'delete_path',
                        'label' => __('Path to Delete', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'data.unwanted_field',
                        'description' => __('Path to the field to delete', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'delete'
                        )
                    ),
                    array(
                        'key' => 'merge_sources',
                        'label' => __('Objects to Merge', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 3,
                        'placeholder' => "data.object1\ndata.object2\n{{additional_data}}",
                        'description' => __('List of object paths to merge, one per line', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'merge'
                        )
                    ),
                    array(
                        'key' => 'split_delimiter',
                        'label' => __('Delimiter', 'workflow-automation'),
                        'type' => 'text',
                        'default' => ',',
                        'description' => __('Delimiter to split by', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'split'
                        )
                    ),
                    array(
                        'key' => 'join_delimiter',
                        'label' => __('Delimiter', 'workflow-automation'),
                        'type' => 'text',
                        'default' => ', ',
                        'description' => __('Delimiter to join with', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'join'
                        )
                    ),
                    array(
                        'key' => 'extract_pattern',
                        'label' => __('Extract Pattern', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'regex pattern or JSONPath',
                        'description' => __('Regular expression or JSONPath to extract data', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'extract'
                        )
                    ),
                    array(
                        'key' => 'calculation',
                        'label' => __('Calculation', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'add' => __('Add (+)', 'workflow-automation'),
                            'subtract' => __('Subtract (-)', 'workflow-automation'),
                            'multiply' => __('Multiply (*)', 'workflow-automation'),
                            'divide' => __('Divide (/)', 'workflow-automation'),
                            'modulo' => __('Modulo (%)', 'workflow-automation'),
                            'power' => __('Power (^)', 'workflow-automation'),
                            'round' => __('Round', 'workflow-automation'),
                            'floor' => __('Floor', 'workflow-automation'),
                            'ceil' => __('Ceil', 'workflow-automation'),
                            'abs' => __('Absolute', 'workflow-automation'),
                            'min' => __('Minimum', 'workflow-automation'),
                            'max' => __('Maximum', 'workflow-automation'),
                            'average' => __('Average', 'workflow-automation'),
                            'sum' => __('Sum', 'workflow-automation')
                        ),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'calculate'
                        )
                    ),
                    array(
                        'key' => 'operands',
                        'label' => __('Operands', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 2,
                        'placeholder' => "data.price\ndata.quantity",
                        'description' => __('Values to calculate with, one per line', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'calculate'
                        )
                    ),
                    array(
                        'key' => 'format_type',
                        'label' => __('Format Type', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'date' => __('Date/Time', 'workflow-automation'),
                            'number' => __('Number', 'workflow-automation'),
                            'currency' => __('Currency', 'workflow-automation'),
                            'percent' => __('Percentage', 'workflow-automation'),
                            'uppercase' => __('Uppercase', 'workflow-automation'),
                            'lowercase' => __('Lowercase', 'workflow-automation'),
                            'capitalize' => __('Capitalize', 'workflow-automation'),
                            'trim' => __('Trim Whitespace', 'workflow-automation'),
                            'slug' => __('URL Slug', 'workflow-automation'),
                            'base64_encode' => __('Base64 Encode', 'workflow-automation'),
                            'base64_decode' => __('Base64 Decode', 'workflow-automation'),
                            'url_encode' => __('URL Encode', 'workflow-automation'),
                            'url_decode' => __('URL Decode', 'workflow-automation'),
                            'html_encode' => __('HTML Encode', 'workflow-automation'),
                            'html_decode' => __('HTML Decode', 'workflow-automation'),
                            'json_encode' => __('JSON Encode', 'workflow-automation'),
                            'json_decode' => __('JSON Decode', 'workflow-automation')
                        ),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'format'
                        )
                    ),
                    array(
                        'key' => 'format_pattern',
                        'label' => __('Format Pattern', 'workflow-automation'),
                        'type' => 'text',
                        'placeholder' => 'Y-m-d H:i:s',
                        'description' => __('Format pattern (for date/number formatting)', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'format_type',
                            'operator' => 'in',
                            'value' => array('date', 'number')
                        )
                    ),
                    array(
                        'key' => 'convert_to',
                        'label' => __('Convert To', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'string' => __('String', 'workflow-automation'),
                            'number' => __('Number', 'workflow-automation'),
                            'integer' => __('Integer', 'workflow-automation'),
                            'float' => __('Float', 'workflow-automation'),
                            'boolean' => __('Boolean', 'workflow-automation'),
                            'array' => __('Array', 'workflow-automation'),
                            'object' => __('Object', 'workflow-automation')
                        ),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'convert'
                        )
                    ),
                    array(
                        'key' => 'array_operation',
                        'label' => __('Array Operation', 'workflow-automation'),
                        'type' => 'select',
                        'options' => array(
                            'filter_empty' => __('Remove Empty Values', 'workflow-automation'),
                            'filter_duplicates' => __('Remove Duplicates', 'workflow-automation'),
                            'filter_condition' => __('Filter by Condition', 'workflow-automation'),
                            'map_property' => __('Extract Property', 'workflow-automation'),
                            'map_template' => __('Map to Template', 'workflow-automation'),
                            'reduce_sum' => __('Sum Values', 'workflow-automation'),
                            'reduce_count' => __('Count Items', 'workflow-automation'),
                            'reduce_group' => __('Group By', 'workflow-automation')
                        ),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => 'in',
                            'value' => array('filter_array', 'map_array', 'reduce_array')
                        )
                    ),
                    array(
                        'key' => 'custom_code',
                        'label' => __('JavaScript Code', 'workflow-automation'),
                        'type' => 'textarea',
                        'rows' => 10,
                        'placeholder' => '// Available variables: data, context, utils\n// Return the transformed value\n\nreturn data.map(item => ({\n    id: item.id,\n    name: item.name.toUpperCase()\n}));',
                        'description' => __('Custom JavaScript transformation. Must return a value.', 'workflow-automation'),
                        'condition' => array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'custom'
                        )
                    )
                )
            ),
            array(
                'key' => 'output_mode',
                'label' => __('Output Mode', 'workflow-automation'),
                'type' => 'select',
                'default' => 'merge',
                'options' => array(
                    'merge' => __('Merge with Input', 'workflow-automation'),
                    'replace' => __('Replace Input', 'workflow-automation'),
                    'new' => __('New Object', 'workflow-automation')
                ),
                'description' => __('How to handle the transformation output', 'workflow-automation')
            ),
            array(
                'key' => 'error_handling',
                'label' => __('Error Handling', 'workflow-automation'),
                'type' => 'select',
                'default' => 'stop',
                'options' => array(
                    'stop' => __('Stop on Error', 'workflow-automation'),
                    'skip' => __('Skip Failed Transformations', 'workflow-automation'),
                    'default' => __('Use Default Value', 'workflow-automation')
                ),
                'description' => __('How to handle transformation errors', 'workflow-automation')
            ),
            array(
                'key' => 'default_value',
                'label' => __('Default Value', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'null',
                'description' => __('Default value to use on error', 'workflow-automation'),
                'condition' => array(
                    'field' => 'error_handling',
                    'operator' => '==',
                    'value' => 'default'
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
        $transformations = $this->get_setting('transformations', array());
        $output_mode = $this->get_setting('output_mode', 'merge');
        $error_handling = $this->get_setting('error_handling', 'stop');
        $default_value = $this->get_setting('default_value', null);
        
        // Prepare working data
        $data = $previous_data;
        $result = $output_mode === 'new' ? array() : $data;
        
        $this->log('Starting data transformation with ' . count($transformations) . ' operations');
        
        // Apply each transformation
        foreach ($transformations as $index => $transformation) {
            try {
                $transformed = $this->apply_transformation($transformation, $data, $context);
                
                if ($output_mode === 'replace') {
                    $result = $transformed;
                    $data = $transformed; // Update data for next transformation
                } elseif ($output_mode === 'new') {
                    $result = array_merge($result, $transformed);
                } else { // merge
                    if (is_array($result) && is_array($transformed)) {
                        $result = array_merge($result, $transformed);
                    } else {
                        $result = $transformed;
                    }
                    $data = $result; // Update data for next transformation
                }
                
                $this->log(sprintf('Transformation %d (%s) completed successfully', $index + 1, $transformation['type']));
                
            } catch (Exception $e) {
                $this->log(sprintf('Transformation %d failed: %s', $index + 1, $e->getMessage()));
                
                switch ($error_handling) {
                    case 'stop':
                        throw $e;
                        
                    case 'skip':
                        continue;
                        
                    case 'default':
                        if (isset($transformation['target'])) {
                            $this->set_value_by_path($result, $transformation['target'], $default_value);
                        }
                        break;
                }
            }
        }
        
        return $result;
    }

    /**
     * Apply a single transformation
     *
     * @since    1.0.0
     * @param    array    $transformation    The transformation config
     * @param    mixed    $data              The data to transform
     * @param    array    $context           The execution context
     * @return   mixed
     */
    private function apply_transformation($transformation, $data, $context) {
        $type = $transformation['type'] ?? '';
        
        switch ($type) {
            case 'set':
                return $this->transform_set($transformation, $data, $context);
                
            case 'copy':
                return $this->transform_copy($transformation, $data, $context);
                
            case 'move':
                return $this->transform_move($transformation, $data, $context);
                
            case 'delete':
                return $this->transform_delete($transformation, $data, $context);
                
            case 'rename':
                return $this->transform_rename($transformation, $data, $context);
                
            case 'merge':
                return $this->transform_merge($transformation, $data, $context);
                
            case 'split':
                return $this->transform_split($transformation, $data, $context);
                
            case 'join':
                return $this->transform_join($transformation, $data, $context);
                
            case 'extract':
                return $this->transform_extract($transformation, $data, $context);
                
            case 'calculate':
                return $this->transform_calculate($transformation, $data, $context);
                
            case 'format':
                return $this->transform_format($transformation, $data, $context);
                
            case 'convert':
                return $this->transform_convert($transformation, $data, $context);
                
            case 'filter_array':
                return $this->transform_filter_array($transformation, $data, $context);
                
            case 'map_array':
                return $this->transform_map_array($transformation, $data, $context);
                
            case 'reduce_array':
                return $this->transform_reduce_array($transformation, $data, $context);
                
            case 'custom':
                return $this->transform_custom($transformation, $data, $context);
                
            default:
                throw new Exception('Unknown transformation type: ' . $type);
        }
    }

    /**
     * Set transformation
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_set($config, $data, $context) {
        $target = $this->replace_variables($config['target'] ?? '', $context);
        $value = $this->replace_variables($config['value'] ?? '', $context);
        
        $result = is_array($data) ? $data : array();
        $this->set_value_by_path($result, $target, $value);
        
        return $result;
    }

    /**
     * Copy transformation
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_copy($config, $data, $context) {
        $source = $this->replace_variables($config['source'] ?? '', $context);
        $target = $this->replace_variables($config['target'] ?? '', $context);
        
        $value = $this->get_value_by_path($data, $source);
        $result = is_array($data) ? $data : array();
        $this->set_value_by_path($result, $target, $value);
        
        return $result;
    }

    /**
     * Move transformation
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_move($config, $data, $context) {
        $source = $this->replace_variables($config['source'] ?? '', $context);
        $target = $this->replace_variables($config['target'] ?? '', $context);
        
        $value = $this->get_value_by_path($data, $source);
        $result = is_array($data) ? $data : array();
        
        // Set to new location
        $this->set_value_by_path($result, $target, $value);
        
        // Delete from old location
        $this->delete_value_by_path($result, $source);
        
        return $result;
    }

    /**
     * Delete transformation
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_delete($config, $data, $context) {
        $path = $this->replace_variables($config['delete_path'] ?? '', $context);
        
        $result = is_array($data) ? $data : array();
        $this->delete_value_by_path($result, $path);
        
        return $result;
    }

    /**
     * Format transformation
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_format($config, $data, $context) {
        $source = $this->replace_variables($config['source'] ?? '', $context);
        $target = $this->replace_variables($config['target'] ?? '', $context);
        $format_type = $config['format_type'] ?? '';
        $format_pattern = $this->replace_variables($config['format_pattern'] ?? '', $context);
        
        $value = $this->get_value_by_path($data, $source);
        $formatted = $value;
        
        switch ($format_type) {
            case 'date':
                if (!empty($value)) {
                    $timestamp = is_numeric($value) ? $value : strtotime($value);
                    $formatted = date($format_pattern ?: 'Y-m-d H:i:s', $timestamp);
                }
                break;
                
            case 'number':
                if (is_numeric($value)) {
                    $decimals = 2;
                    if (preg_match('/\.(\d+)/', $format_pattern, $matches)) {
                        $decimals = strlen($matches[1]);
                    }
                    $formatted = number_format($value, $decimals);
                }
                break;
                
            case 'currency':
                if (is_numeric($value)) {
                    $formatted = '$' . number_format($value, 2);
                }
                break;
                
            case 'percent':
                if (is_numeric($value)) {
                    $formatted = number_format($value * 100, 2) . '%';
                }
                break;
                
            case 'uppercase':
                $formatted = strtoupper($value);
                break;
                
            case 'lowercase':
                $formatted = strtolower($value);
                break;
                
            case 'capitalize':
                $formatted = ucwords(strtolower($value));
                break;
                
            case 'trim':
                $formatted = trim($value);
                break;
                
            case 'slug':
                $formatted = sanitize_title($value);
                break;
                
            case 'base64_encode':
                $formatted = base64_encode($value);
                break;
                
            case 'base64_decode':
                $formatted = base64_decode($value);
                break;
                
            case 'url_encode':
                $formatted = urlencode($value);
                break;
                
            case 'url_decode':
                $formatted = urldecode($value);
                break;
                
            case 'html_encode':
                $formatted = htmlspecialchars($value);
                break;
                
            case 'html_decode':
                $formatted = html_entity_decode($value);
                break;
                
            case 'json_encode':
                $formatted = json_encode($value);
                break;
                
            case 'json_decode':
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $formatted = $decoded;
                }
                break;
        }
        
        $result = is_array($data) ? $data : array();
        $this->set_value_by_path($result, $target, $formatted);
        
        return $result;
    }

    /**
     * Custom transformation using JavaScript
     *
     * @since    1.0.0
     * @param    array    $config    Transformation config
     * @param    mixed    $data      Input data
     * @param    array    $context   Execution context
     * @return   mixed
     */
    private function transform_custom($config, $data, $context) {
        $code = $config['custom_code'] ?? '';
        
        if (empty($code)) {
            throw new Exception('Custom code is required for custom transformation');
        }
        
        // Note: In a real implementation, this would need a JavaScript engine
        // For WordPress, we'll provide a limited set of PHP-based operations
        // that simulate common JavaScript transformations
        
        throw new Exception('Custom JavaScript transformations are not yet implemented');
    }

    /**
     * Get value by path
     *
     * @since    1.0.0
     * @param    mixed     $data    The data
     * @param    string    $path    The path (dot notation)
     * @return   mixed
     */
    private function get_value_by_path($data, $path) {
        if (empty($path)) {
            return $data;
        }
        
        $parts = explode('.', $path);
        $current = $data;
        
        foreach ($parts as $part) {
            // Handle array indices
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
     * Set value by path
     *
     * @since    1.0.0
     * @param    array     &$data   The data (passed by reference)
     * @param    string    $path    The path (dot notation)
     * @param    mixed     $value   The value to set
     */
    private function set_value_by_path(&$data, $path, $value) {
        if (empty($path)) {
            $data = $value;
            return;
        }
        
        $parts = explode('.', $path);
        $current = &$data;
        
        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            $is_last = ($i === count($parts) - 1);
            
            // Handle array indices
            if (preg_match('/^(.+)\[(\d+)\]$/', $part, $matches)) {
                $key = $matches[1];
                $index = intval($matches[2]);
                
                if (!isset($current[$key])) {
                    $current[$key] = array();
                }
                
                if ($is_last) {
                    $current[$key][$index] = $value;
                } else {
                    if (!isset($current[$key][$index])) {
                        $current[$key][$index] = array();
                    }
                    $current = &$current[$key][$index];
                }
            } else {
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
    }

    /**
     * Delete value by path
     *
     * @since    1.0.0
     * @param    array     &$data   The data (passed by reference)
     * @param    string    $path    The path (dot notation)
     */
    private function delete_value_by_path(&$data, $path) {
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
                return; // Path doesn't exist
            }
        }
        
        $last_part = $parts[count($parts) - 1];
        
        // Handle array indices
        if (preg_match('/^(.+)\[(\d+)\]$/', $last_part, $matches)) {
            $key = $matches[1];
            $index = intval($matches[2]);
            
            if (isset($current[$key]) && is_array($current[$key])) {
                unset($current[$key][$index]);
                // Re-index array if numeric
                if (array_keys($current[$key]) === range(0, count($current[$key]) - 1)) {
                    $current[$key] = array_values($current[$key]);
                }
            }
        } else {
            unset($current[$last_part]);
        }
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $transformations = $this->get_setting('transformations', array());
        
        if (empty($transformations)) {
            return new WP_Error('missing_transformations', __('At least one transformation is required', 'workflow-automation'));
        }
        
        foreach ($transformations as $index => $transformation) {
            $type = $transformation['type'] ?? '';
            
            if (empty($type)) {
                return new WP_Error('missing_type', sprintf(__('Transformation %d: Type is required', 'workflow-automation'), $index + 1));
            }
            
            // Validate based on type
            switch ($type) {
                case 'set':
                    if (empty($transformation['target']) || empty($transformation['value'])) {
                        return new WP_Error('missing_params', sprintf(__('Transformation %d: Target and value are required for set operation', 'workflow-automation'), $index + 1));
                    }
                    break;
                    
                case 'copy':
                case 'move':
                    if (empty($transformation['source']) || empty($transformation['target'])) {
                        return new WP_Error('missing_params', sprintf(__('Transformation %d: Source and target are required for %s operation', 'workflow-automation'), $index + 1, $type));
                    }
                    break;
                    
                case 'custom':
                    if (empty($transformation['custom_code'])) {
                        return new WP_Error('missing_code', sprintf(__('Transformation %d: Custom code is required', 'workflow-automation'), $index + 1));
                    }
                    break;
            }
        }
        
        return true;
    }
}