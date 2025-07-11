<?php
/**
 * Workflow Error Handler
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Workflow Error Handler class
 *
 * Handles error management and retry logic for workflow execution
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Error_Handler {

    /**
     * Error types
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $error_types    Error type definitions
     */
    private $error_types = array(
        'node_error' => array(
            'level' => 'error',
            'retryable' => true,
            'max_retries' => 3
        ),
        'connection_error' => array(
            'level' => 'error',
            'retryable' => true,
            'max_retries' => 5
        ),
        'timeout_error' => array(
            'level' => 'error',
            'retryable' => true,
            'max_retries' => 2
        ),
        'validation_error' => array(
            'level' => 'warning',
            'retryable' => false,
            'max_retries' => 0
        ),
        'permission_error' => array(
            'level' => 'error',
            'retryable' => false,
            'max_retries' => 0
        ),
        'rate_limit_error' => array(
            'level' => 'warning',
            'retryable' => true,
            'max_retries' => 10,
            'backoff' => 'exponential'
        ),
        'authentication_error' => array(
            'level' => 'error',
            'retryable' => true,
            'max_retries' => 1
        ),
        'data_error' => array(
            'level' => 'error',
            'retryable' => false,
            'max_retries' => 0
        ),
        'system_error' => array(
            'level' => 'critical',
            'retryable' => true,
            'max_retries' => 2
        )
    );

    /**
     * Error log
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $error_log    Log of errors
     */
    private $error_log = array();

    /**
     * Get error handler instance
     *
     * @since    1.0.0
     * @return   Workflow_Error_Handler
     */
    public static function get_instance() {
        static $instance = null;
        
        if (null === $instance) {
            $instance = new self();
        }
        
        return $instance;
    }

    /**
     * Handle error with retry logic
     *
     * @since    1.0.0
     * @param    callable    $callback        The function to execute
     * @param    array       $args            Arguments for the callback
     * @param    array       $options         Retry options
     * @return   mixed
     * @throws   Exception
     */
    public function handle_with_retry($callback, $args = array(), $options = array()) {
        $defaults = array(
            'max_retries' => 3,
            'retry_delay' => 1000, // milliseconds
            'backoff_type' => 'linear', // linear, exponential, fibonacci
            'backoff_multiplier' => 2,
            'max_delay' => 30000, // 30 seconds
            'error_type' => 'node_error',
            'context' => array()
        );
        
        $options = wp_parse_args($options, $defaults);
        
        $attempt = 0;
        $last_error = null;
        
        while ($attempt <= $options['max_retries']) {
            try {
                // Execute callback
                $result = call_user_func_array($callback, $args);
                
                // Success - log if this was a retry
                if ($attempt > 0) {
                    $this->log_recovery($options['context'], $attempt);
                }
                
                return $result;
                
            } catch (Exception $e) {
                $last_error = $e;
                
                // Log the error
                $this->log_error($e, $options['context'], $attempt);
                
                // Check if error is retryable
                if (!$this->is_retryable($e, $options['error_type'])) {
                    throw $e;
                }
                
                // Check if we've exceeded max retries
                if ($attempt >= $options['max_retries']) {
                    throw new Exception(
                        sprintf(
                            'Max retries (%d) exceeded. Last error: %s',
                            $options['max_retries'],
                            $e->getMessage()
                        ),
                        0,
                        $e
                    );
                }
                
                // Calculate delay
                $delay = $this->calculate_delay(
                    $attempt,
                    $options['retry_delay'],
                    $options['backoff_type'],
                    $options['backoff_multiplier'],
                    $options['max_delay']
                );
                
                // Apply delay
                if ($delay > 0) {
                    usleep($delay * 1000); // Convert to microseconds
                }
                
                $attempt++;
            }
        }
        
        // Should never reach here
        throw $last_error ?: new Exception('Unknown error in retry handler');
    }

    /**
     * Execute node with error handling
     *
     * @since    1.0.0
     * @param    object    $node              The node instance
     * @param    array     $context           Execution context
     * @param    mixed     $previous_data     Previous node output
     * @param    array     $error_config      Error handling configuration
     * @return   mixed
     */
    public function execute_node_safely($node, $context, $previous_data, $error_config = array()) {
        $defaults = array(
            'on_error' => 'stop', // stop, continue, use_default, retry
            'default_value' => null,
            'max_retries' => 3,
            'retry_delay' => 1000,
            'ignore_errors' => array(),
            'transform_error' => null // Callback to transform error into output
        );
        
        $config = wp_parse_args($error_config, $defaults);
        
        try {
            // Execute with retry if configured
            if ($config['on_error'] === 'retry') {
                return $this->handle_with_retry(
                    array($node, 'execute'),
                    array($context, $previous_data),
                    array(
                        'max_retries' => $config['max_retries'],
                        'retry_delay' => $config['retry_delay'],
                        'context' => array(
                            'node_id' => $node->get_id(),
                            'node_type' => $node->get_type()
                        )
                    )
                );
            } else {
                // Execute without retry
                return $node->execute($context, $previous_data);
            }
            
        } catch (Exception $e) {
            // Check if error should be ignored
            foreach ($config['ignore_errors'] as $pattern) {
                if (preg_match($pattern, $e->getMessage())) {
                    return $config['default_value'];
                }
            }
            
            // Handle based on error strategy
            switch ($config['on_error']) {
                case 'stop':
                    throw $e;
                    
                case 'continue':
                    $this->log_error($e, array(
                        'node_id' => $node->get_id(),
                        'action' => 'continue'
                    ));
                    return null;
                    
                case 'use_default':
                    $this->log_error($e, array(
                        'node_id' => $node->get_id(),
                        'action' => 'use_default'
                    ));
                    return $config['default_value'];
                    
                case 'transform':
                    if (is_callable($config['transform_error'])) {
                        return call_user_func($config['transform_error'], $e, $context, $previous_data);
                    }
                    return array('error' => $e->getMessage());
                    
                default:
                    throw $e;
            }
        }
    }

    /**
     * Check if error is retryable
     *
     * @since    1.0.0
     * @param    Exception    $error         The error
     * @param    string       $error_type    Error type
     * @return   bool
     */
    private function is_retryable($error, $error_type = null) {
        // Check for specific non-retryable conditions
        $non_retryable_patterns = array(
            '/invalid\s+api\s+key/i',
            '/authentication\s+failed/i',
            '/permission\s+denied/i',
            '/invalid\s+configuration/i',
            '/malformed\s+request/i'
        );
        
        foreach ($non_retryable_patterns as $pattern) {
            if (preg_match($pattern, $error->getMessage())) {
                return false;
            }
        }
        
        // Check for retryable conditions
        $retryable_patterns = array(
            '/timeout/i',
            '/connection\s+(refused|reset|aborted)/i',
            '/too\s+many\s+requests/i',
            '/rate\s+limit/i',
            '/temporary\s+failure/i',
            '/service\s+unavailable/i',
            '/gateway\s+timeout/i'
        );
        
        foreach ($retryable_patterns as $pattern) {
            if (preg_match($pattern, $error->getMessage())) {
                return true;
            }
        }
        
        // Check error type configuration
        if ($error_type && isset($this->error_types[$error_type])) {
            return $this->error_types[$error_type]['retryable'];
        }
        
        // Default to not retryable
        return false;
    }

    /**
     * Calculate retry delay
     *
     * @since    1.0.0
     * @param    int       $attempt         Attempt number (0-based)
     * @param    int       $base_delay      Base delay in milliseconds
     * @param    string    $backoff_type    Backoff type
     * @param    int       $multiplier      Backoff multiplier
     * @param    int       $max_delay       Maximum delay
     * @return   int
     */
    private function calculate_delay($attempt, $base_delay, $backoff_type, $multiplier, $max_delay) {
        $delay = $base_delay;
        
        switch ($backoff_type) {
            case 'exponential':
                $delay = $base_delay * pow($multiplier, $attempt);
                break;
                
            case 'linear':
                $delay = $base_delay * ($attempt + 1);
                break;
                
            case 'fibonacci':
                $delay = $this->fibonacci_delay($attempt, $base_delay);
                break;
                
            case 'random':
                $max = $base_delay * ($attempt + 1) * $multiplier;
                $delay = rand($base_delay, $max);
                break;
        }
        
        // Add jitter (10% randomness)
        $jitter = $delay * 0.1;
        $delay = $delay + rand(-$jitter, $jitter);
        
        // Apply max delay cap
        return min($delay, $max_delay);
    }

    /**
     * Calculate Fibonacci delay
     *
     * @since    1.0.0
     * @param    int    $n              Sequence number
     * @param    int    $base_delay     Base delay
     * @return   int
     */
    private function fibonacci_delay($n, $base_delay) {
        if ($n <= 0) return $base_delay;
        if ($n == 1) return $base_delay * 2;
        
        $a = $base_delay;
        $b = $base_delay * 2;
        
        for ($i = 2; $i <= $n; $i++) {
            $temp = $a + $b;
            $a = $b;
            $b = $temp;
        }
        
        return $b;
    }

    /**
     * Log error
     *
     * @since    1.0.0
     * @param    Exception    $error      The error
     * @param    array        $context    Error context
     * @param    int          $attempt    Attempt number
     */
    private function log_error($error, $context = array(), $attempt = 0) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
            'attempt' => $attempt
        );
        
        $this->error_log[] = $log_entry;
        
        // Also log to WordPress error log
        error_log(sprintf(
            '[Workflow Automation] Error (attempt %d): %s in %s:%d',
            $attempt,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        ));
        
        // Store in database if execution ID is available
        if (isset($context['execution_id'])) {
            $this->store_error_in_db($context['execution_id'], $log_entry);
        }
    }

    /**
     * Log successful recovery
     *
     * @since    1.0.0
     * @param    array    $context    Recovery context
     * @param    int      $attempts   Number of attempts
     */
    private function log_recovery($context, $attempts) {
        error_log(sprintf(
            '[Workflow Automation] Recovered after %d retry attempts. Context: %s',
            $attempts,
            json_encode($context)
        ));
    }

    /**
     * Store error in database
     *
     * @since    1.0.0
     * @param    int      $execution_id    Execution ID
     * @param    array    $error_data      Error data
     */
    private function store_error_in_db($execution_id, $error_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wa_execution_errors';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            // Create table
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                execution_id BIGINT UNSIGNED NOT NULL,
                error_time DATETIME NOT NULL,
                error_message TEXT NOT NULL,
                error_details LONGTEXT,
                attempt_number INT DEFAULT 0,
                recovered BOOLEAN DEFAULT false,
                INDEX idx_execution (execution_id),
                INDEX idx_time (error_time)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        
        // Insert error record
        $wpdb->insert(
            $table_name,
            array(
                'execution_id' => $execution_id,
                'error_time' => $error_data['timestamp'],
                'error_message' => $error_data['message'],
                'error_details' => json_encode($error_data),
                'attempt_number' => $error_data['attempt']
            ),
            array('%d', '%s', '%s', '%s', '%d')
        );
    }

    /**
     * Get error log
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_error_log() {
        return $this->error_log;
    }

    /**
     * Clear error log
     *
     * @since    1.0.0
     */
    public function clear_error_log() {
        $this->error_log = array();
    }

    /**
     * Get execution errors from database
     *
     * @since    1.0.0
     * @param    int      $execution_id    Execution ID
     * @param    array    $args            Query arguments
     * @return   array
     */
    public function get_execution_errors($execution_id, $args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 100,
            'offset' => 0,
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $table_name = $wpdb->prefix . 'wa_execution_errors';
        
        $sql = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE execution_id = %d ORDER BY error_time {$args['order']} LIMIT %d OFFSET %d",
            $execution_id,
            $args['limit'],
            $args['offset']
        );
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as &$result) {
            if (!empty($result->error_details)) {
                $result->error_details = json_decode($result->error_details, true);
            }
        }
        
        return $results;
    }

    /**
     * Handle workflow execution errors
     *
     * @since    1.0.0
     * @param    int         $execution_id    Execution ID
     * @param    Exception   $error           The error
     * @param    array       $context         Error context
     * @return   array                        Error response
     */
    public function handle_workflow_error($execution_id, $error, $context = array()) {
        // Determine error type
        $error_type = $this->determine_error_type($error);
        
        // Get workflow settings
        $execution_model = new Execution_Model();
        $execution = $execution_model->get($execution_id);
        
        if ($execution) {
            $workflow_model = new Workflow_Model();
            $workflow = $workflow_model->get($execution->workflow_id);
            
            // Check workflow error settings
            $error_settings = isset($workflow->settings['error_handling']) ? $workflow->settings['error_handling'] : array();
            
            // Apply workflow-specific error handling
            if (isset($error_settings['on_error'])) {
                switch ($error_settings['on_error']) {
                    case 'retry_workflow':
                        // Schedule workflow retry
                        wp_schedule_single_event(
                            time() + ($error_settings['retry_delay'] ?? 300),
                            'wa_retry_workflow',
                            array($execution_id)
                        );
                        break;
                        
                    case 'notify':
                        // Send error notification
                        $this->send_error_notification($execution_id, $error, $context);
                        break;
                        
                    case 'fallback':
                        // Execute fallback workflow
                        if (!empty($error_settings['fallback_workflow_id'])) {
                            $this->execute_fallback_workflow(
                                $error_settings['fallback_workflow_id'],
                                $execution,
                                $error
                            );
                        }
                        break;
                }
            }
        }
        
        return array(
            'error_type' => $error_type,
            'message' => $error->getMessage(),
            'retryable' => $this->is_retryable($error, $error_type),
            'context' => $context
        );
    }

    /**
     * Determine error type from exception
     *
     * @since    1.0.0
     * @param    Exception    $error    The error
     * @return   string
     */
    private function determine_error_type($error) {
        $message = strtolower($error->getMessage());
        
        if (strpos($message, 'timeout') !== false) {
            return 'timeout_error';
        } elseif (strpos($message, 'connection') !== false) {
            return 'connection_error';
        } elseif (strpos($message, 'rate limit') !== false) {
            return 'rate_limit_error';
        } elseif (strpos($message, 'authentication') !== false || strpos($message, 'unauthorized') !== false) {
            return 'authentication_error';
        } elseif (strpos($message, 'permission') !== false || strpos($message, 'forbidden') !== false) {
            return 'permission_error';
        } elseif (strpos($message, 'validation') !== false || strpos($message, 'invalid') !== false) {
            return 'validation_error';
        } elseif (strpos($message, 'data') !== false || strpos($message, 'parse') !== false) {
            return 'data_error';
        } else {
            return 'node_error';
        }
    }

    /**
     * Send error notification
     *
     * @since    1.0.0
     * @param    int         $execution_id    Execution ID
     * @param    Exception   $error           The error
     * @param    array       $context         Error context
     */
    private function send_error_notification($execution_id, $error, $context) {
        // Get admin email
        $admin_email = get_option('admin_email');
        
        $subject = sprintf(
            '[%s] Workflow Execution Error',
            get_bloginfo('name')
        );
        
        $message = sprintf(
            "A workflow execution has failed.\n\n" .
            "Execution ID: %d\n" .
            "Error: %s\n" .
            "Context: %s\n\n" .
            "Please check the workflow automation logs for more details.",
            $execution_id,
            $error->getMessage(),
            json_encode($context, JSON_PRETTY_PRINT)
        );
        
        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Execute fallback workflow
     *
     * @since    1.0.0
     * @param    int      $workflow_id    Fallback workflow ID
     * @param    object   $execution      Original execution
     * @param    Exception $error         The error
     */
    private function execute_fallback_workflow($workflow_id, $execution, $error) {
        $execution_model = new Execution_Model();
        
        // Create new execution for fallback workflow
        $fallback_data = array(
            'workflow_id' => $workflow_id,
            'trigger_type' => 'error_fallback',
            'trigger_data' => array(
                'original_execution_id' => $execution->id,
                'original_workflow_id' => $execution->workflow_id,
                'error_message' => $error->getMessage(),
                'error_details' => array(
                    'code' => $error->getCode(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine()
                )
            ),
            'status' => 'pending'
        );
        
        $fallback_execution_id = $execution_model->create($fallback_data);
        
        if ($fallback_execution_id) {
            // Schedule immediate execution
            wp_schedule_single_event(time(), 'wa_execute_workflow', array($fallback_execution_id));
        }
    }
}