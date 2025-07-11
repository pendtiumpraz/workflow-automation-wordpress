<?php
/**
 * Email Node
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 */

/**
 * Email node class
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/integrations
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Email_Node extends WA_Abstract_Node {

    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'email';
    }

    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'label' => __('Send Email', 'workflow-automation'),
            'description' => __('Send an email notification', 'workflow-automation'),
            'icon' => 'dashicons-email',
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
                'key' => 'to',
                'label' => __('To', 'workflow-automation'),
                'type' => 'text',
                'required' => true,
                'placeholder' => 'email@example.com',
                'description' => __('Recipient email address. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'cc',
                'label' => __('CC', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'cc@example.com',
                'description' => __('CC email addresses (comma separated)', 'workflow-automation')
            ),
            array(
                'key' => 'bcc',
                'label' => __('BCC', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => 'bcc@example.com',
                'description' => __('BCC email addresses (comma separated)', 'workflow-automation')
            ),
            array(
                'key' => 'from_name',
                'label' => __('From Name', 'workflow-automation'),
                'type' => 'text',
                'default' => get_bloginfo('name'),
                'description' => __('Sender name', 'workflow-automation')
            ),
            array(
                'key' => 'from_email',
                'label' => __('From Email', 'workflow-automation'),
                'type' => 'text',
                'default' => get_option('admin_email'),
                'description' => __('Sender email address', 'workflow-automation')
            ),
            array(
                'key' => 'subject',
                'label' => __('Subject', 'workflow-automation'),
                'type' => 'text',
                'required' => true,
                'placeholder' => __('Email subject', 'workflow-automation'),
                'description' => __('Email subject. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'body',
                'label' => __('Body', 'workflow-automation'),
                'type' => 'textarea',
                'required' => true,
                'rows' => 10,
                'placeholder' => __('Email body content...', 'workflow-automation'),
                'description' => __('Email body. Use {{variables}} for dynamic values.', 'workflow-automation')
            ),
            array(
                'key' => 'content_type',
                'label' => __('Content Type', 'workflow-automation'),
                'type' => 'select',
                'default' => 'text/plain',
                'options' => array(
                    'text/plain' => __('Plain Text', 'workflow-automation'),
                    'text/html' => __('HTML', 'workflow-automation')
                ),
                'description' => __('Email content type', 'workflow-automation')
            ),
            array(
                'key' => 'attachments',
                'label' => __('Attachments', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => '/path/to/file.pdf',
                'description' => __('File paths for attachments (comma separated)', 'workflow-automation')
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
        // Get settings with variable replacement
        $to = $this->replace_variables($this->get_setting('to', ''), $context);
        $subject = $this->replace_variables($this->get_setting('subject', ''), $context);
        $body = $this->replace_variables($this->get_setting('body', ''), $context);
        
        // Validate required fields
        if (empty($to)) {
            throw new Exception('Email recipient (To) is required');
        }
        
        if (empty($subject)) {
            throw new Exception('Email subject is required');
        }
        
        if (empty($body)) {
            throw new Exception('Email body is required');
        }
        
        // Validate email address
        if (!is_email($to)) {
            throw new Exception('Invalid email address: ' . $to);
        }
        
        // Prepare headers
        $headers = array();
        
        // From
        $from_name = $this->replace_variables($this->get_setting('from_name', get_bloginfo('name')), $context);
        $from_email = $this->replace_variables($this->get_setting('from_email', get_option('admin_email')), $context);
        
        if (!empty($from_name) && !empty($from_email)) {
            $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);
        } elseif (!empty($from_email)) {
            $headers[] = 'From: ' . $from_email;
        }
        
        // CC
        $cc = $this->replace_variables($this->get_setting('cc', ''), $context);
        if (!empty($cc)) {
            $cc_addresses = array_map('trim', explode(',', $cc));
            foreach ($cc_addresses as $cc_address) {
                if (is_email($cc_address)) {
                    $headers[] = 'Cc: ' . $cc_address;
                }
            }
        }
        
        // BCC
        $bcc = $this->replace_variables($this->get_setting('bcc', ''), $context);
        if (!empty($bcc)) {
            $bcc_addresses = array_map('trim', explode(',', $bcc));
            foreach ($bcc_addresses as $bcc_address) {
                if (is_email($bcc_address)) {
                    $headers[] = 'Bcc: ' . $bcc_address;
                }
            }
        }
        
        // Content Type
        $content_type = $this->get_setting('content_type', 'text/plain');
        $headers[] = 'Content-Type: ' . $content_type;
        
        // Attachments
        $attachments = array();
        $attachment_paths = $this->replace_variables($this->get_setting('attachments', ''), $context);
        if (!empty($attachment_paths)) {
            $paths = array_map('trim', explode(',', $attachment_paths));
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    $attachments[] = $path;
                } else {
                    $this->log('Attachment file not found: ' . $path, 'warning');
                }
            }
        }
        
        // Send email
        $sent = wp_mail($to, $subject, $body, $headers, $attachments);
        
        if (!$sent) {
            throw new Exception('Failed to send email');
        }
        
        $this->log('Email sent successfully to: ' . $to);
        
        // Return output
        return array(
            'sent' => true,
            'to' => $to,
            'subject' => $subject,
            'sent_at' => current_time('mysql')
        );
    }

    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $to = $this->get_setting('to', '');
        $subject = $this->get_setting('subject', '');
        $body = $this->get_setting('body', '');
        
        if (empty($to)) {
            return new WP_Error('missing_to', __('Email recipient (To) is required', 'workflow-automation'));
        }
        
        if (empty($subject)) {
            return new WP_Error('missing_subject', __('Email subject is required', 'workflow-automation'));
        }
        
        if (empty($body)) {
            return new WP_Error('missing_body', __('Email body is required', 'workflow-automation'));
        }
        
        // Check if it's a variable or valid email
        if (!strpos($to, '{{') && !is_email($to)) {
            return new WP_Error('invalid_email', __('Invalid email address', 'workflow-automation'));
        }
        
        return true;
    }
}