<?php
/**
 * Filter Logic Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/logic
 */

/**
 * Filter logic node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/logic
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Filter_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'filter';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Filter', 'workflow-automation'),
            'description' => __('Filter data based on conditions', 'workflow-automation'),
            'icon' => 'dashicons-filter',
            'category' => 'logic',
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
                'key' => 'filter_type',
                'label' => __('Filter Type', 'workflow-automation'),
                'type' => 'select',
                'default' => 'single',
                'required' => true,
                'options' => array(
                    'single' => __('Single Value', 'workflow-automation'),
                    'array' => __('Array/List', 'workflow-automation')
                ),
                'description' => __('Type of data to filter', 'workflow-automation')
            ),
            array(
                'key' => 'conditions',
                'label' => __('Conditions', 'workflow-automation'),
                'type' => 'conditions',
                'required' => true,
                'default' => array(
                    array(
                        'field' => '',
                        'operator' => '==',
                        'value' => '',
                        'data_type' => 'string'
                    )
                ),
                'operators' => array(
                    '==' => __('Equals', 'workflow-automation'),
                    '!=' => __('Not Equals', 'workflow-automation'),
                    '>' => __('Greater Than', 'workflow-automation'),
                    '<' => __('Less Than', 'workflow-automation'),
                    '>=' => __('Greater Than or Equal', 'workflow-automation'),
                    '<=' => __('Less Than or Equal', 'workflow-automation'),
                    'contains' => __('Contains', 'workflow-automation'),
                    'not_contains' => __('Does Not Contain', 'workflow-automation'),
                    'starts_with' => __('Starts With', 'workflow-automation'),
                    'ends_with' => __('Ends With', 'workflow-automation'),
                    'is_empty' => __('Is Empty', 'workflow-automation'),
                    'is_not_empty' => __('Is Not Empty', 'workflow-automation')
                ),
                'data_types' => array(
                    'string' => __('String', 'workflow-automation'),
                    'number' => __('Number', 'workflow-automation'),
                    'boolean' => __('Boolean', 'workflow-automation'),
                    'date' => __('Date', 'workflow-automation')
                ),
                'description' => __('Define filter conditions. Use {{variables}} in field names.', 'workflow-automation')
            ),
            array(
                'key' => 'condition_logic',
                'label' => __('Condition Logic', 'workflow-automation'),
                'type' => 'select',
                'default' => 'AND',
                'options' => array(
                    'AND' => __('All conditions must match (AND)', 'workflow-automation'),
                    'OR' => __('Any condition must match (OR)', 'workflow-automation')
                ),
                'description' => __('How to combine multiple conditions', 'workflow-automation')
            ),
            array(
                'key' => 'pass_output',
                'label' => __('Pass When True', 'workflow-automation'),
                'type' => 'select',
                'default' => 'data',
                'options' => array(
                    'data' => __('Pass the data', 'workflow-automation'),
                    'boolean' => __('Pass true/false', 'workflow-automation'),
                    'custom' => __('Pass custom value', 'workflow-automation')
                ),
                'description' => __('What to pass when filter matches', 'workflow-automation')
            ),
            array(
                'key' => 'pass_value',
                'label' => __('Custom Pass Value', 'workflow-automation'),
                'type' => 'textarea',
                'placeholder' => '{"status": "passed"}',
                'description' => __('Custom value to pass (JSON or text)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'pass_output',
                    'operator' => '==',
                    'value' => 'custom'
                )
            ),
            array(
                'key' => 'fail_behavior',
                'label' => __('Fail Behavior', 'workflow-automation'),
                'type' => 'select',
                'default' => 'stop',
                'options' => array(
                    'stop' => __('Stop workflow', 'workflow-automation'),
                    'continue' => __('Continue with null', 'workflow-automation'),
                    'custom' => __('Continue with custom value', 'workflow-automation')
                ),
                'description' => __('What to do when filter does not match', 'workflow-automation')
            ),
            array(
                'key' => 'fail_value',
                'label' => __('Custom Fail Value', 'workflow-automation'),
                'type' => 'textarea',
                'placeholder' => '{"status": "failed"}',
                'description' => __('Custom value for failed filter (JSON or text)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'fail_behavior',
                    'operator' => '==',
                    'value' => 'custom'
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
        $filter_type = $this->get_setting('filter_type', 'single');
        $conditions = $this->get_setting('conditions', array());
        $condition_logic = $this->get_setting('condition_logic', 'AND');
        
        if (empty($conditions)) {
            throw new Exception('No filter conditions defined');
        }
        
        // Determine what data to filter
        $data_to_filter = $previous_data;
        
        if ($filter_type === 'array') {
            // Filter array data
            if (!is_array($data_to_filter)) {
                $this->log('Data is not an array, cannot filter as array', 'warning');
                return $this->handle_fail($context);
            }
            
            $filtered_data = array();
            
            foreach ($data_to_filter as $item) {
                if ($this->evaluate_conditions($item, $conditions, $condition_logic, $context)) {
                    $filtered_data[] = $item;
                }
            }
            
            if (empty($filtered_data)) {
                return $this->handle_fail($context);
            }
            
            return $this->handle_pass($filtered_data, $context);
            
        } else {
            // Filter single value
            $matches = $this->evaluate_conditions($data_to_filter, $conditions, $condition_logic, $context);
            
            if ($matches) {
                return $this->handle_pass($data_to_filter, $context);
            } else {
                return $this->handle_fail($context);
            }
        }
    }

    /**
     * Evaluate conditions
     *
     * @since    1.0.0
     * @param    mixed     $data              The data to evaluate
     * @param    array     $conditions        The conditions
     * @param    string    $logic             AND or OR
     * @param    array     $context           The execution context
     * @return   bool
     */
    private function evaluate_conditions($data, $conditions, $logic, $context) {
        $results = array();
        
        foreach ($conditions as $condition) {
            $field = $this->replace_variables($condition['field'], $context);
            $operator = $condition['operator'];
            $value = $this->replace_variables($condition['value'], $context);
            $data_type = isset($condition['data_type']) ? $condition['data_type'] : 'string';
            
            // Get field value from data
            $field_value = $this->get_field_value($data, $field);
            
            // Convert data types
            $field_value = $this->convert_data_type($field_value, $data_type);
            $value = $this->convert_data_type($value, $data_type);
            
            // Evaluate condition
            $result = $this->evaluate_condition($field_value, $operator, $value);
            $results[] = $result;
            
            $this->log(sprintf(
                'Condition evaluated: %s %s %s = %s',
                $field,
                $operator,
                is_scalar($value) ? $value : json_encode($value),
                $result ? 'true' : 'false'
            ));
        }
        
        // Apply logic
        if ($logic === 'OR') {
            return in_array(true, $results, true);
        } else {
            return !in_array(false, $results, true);
        }
    }

    /**
     * Get field value from data
     *
     * @since    1.0.0
     * @param    mixed     $data     The data
     * @param    string    $field    The field path
     * @return   mixed
     */
    private function get_field_value($data, $field) {
        if ($field === '' || $field === '*') {
            return $data;
        }
        
        $parts = explode('.', $field);
        $current = $data;
        
        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } elseif (is_object($current) && isset($current->$part)) {
                $current = $current->$part;
            } else {
                return null;
            }
        }
        
        return $current;
    }

    /**
     * Convert data to specified type
     *
     * @since    1.0.0
     * @param    mixed     $value      The value
     * @param    string    $data_type  The data type
     * @return   mixed
     */
    private function convert_data_type($value, $data_type) {
        switch ($data_type) {
            case 'number':
                return is_numeric($value) ? floatval($value) : 0;
                
            case 'boolean':
                if (is_string($value)) {
                    return strtolower($value) === 'true' || $value === '1';
                }
                return (bool) $value;
                
            case 'date':
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp !== false ? $timestamp : 0;
                }
                return 0;
                
            default:
                return (string) $value;
        }
    }

    /**
     * Evaluate a single condition
     *
     * @since    1.0.0
     * @param    mixed     $field_value    The field value
     * @param    string    $operator       The operator
     * @param    mixed     $value          The comparison value
     * @return   bool
     */
    private function evaluate_condition($field_value, $operator, $value) {
        switch ($operator) {
            case '==':
                return $field_value == $value;
                
            case '!=':
                return $field_value != $value;
                
            case '>':
                return $field_value > $value;
                
            case '<':
                return $field_value < $value;
                
            case '>=':
                return $field_value >= $value;
                
            case '<=':
                return $field_value <= $value;
                
            case 'contains':
                return strpos((string) $field_value, (string) $value) !== false;
                
            case 'not_contains':
                return strpos((string) $field_value, (string) $value) === false;
                
            case 'starts_with':
                return strpos((string) $field_value, (string) $value) === 0;
                
            case 'ends_with':
                $length = strlen((string) $value);
                return $length === 0 || substr((string) $field_value, -$length) === (string) $value;
                
            case 'is_empty':
                return empty($field_value);
                
            case 'is_not_empty':
                return !empty($field_value);
                
            default:
                return false;
        }
    }

    /**
     * Handle successful filter
     *
     * @since    1.0.0
     * @param    mixed    $data       The filtered data
     * @param    array    $context    The execution context
     * @return   mixed
     */
    private function handle_pass($data, $context) {
        $pass_output = $this->get_setting('pass_output', 'data');
        
        switch ($pass_output) {
            case 'boolean':
                return true;
                
            case 'custom':
                $custom_value = $this->replace_variables($this->get_setting('pass_value', ''), $context);
                
                // Try to parse as JSON
                $json_value = json_decode($custom_value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json_value;
                }
                
                return $custom_value;
                
            default:
                return $data;
        }
    }

    /**
     * Handle failed filter
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   mixed
     */
    private function handle_fail($context) {
        $fail_behavior = $this->get_setting('fail_behavior', 'stop');
        
        switch ($fail_behavior) {
            case 'continue':
                return null;
                
            case 'custom':
                $custom_value = $this->replace_variables($this->get_setting('fail_value', ''), $context);
                
                // Try to parse as JSON
                $json_value = json_decode($custom_value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $json_value;
                }
                
                return $custom_value;
                
            default:
                throw new Exception('Filter condition not met');
        }
    }
}