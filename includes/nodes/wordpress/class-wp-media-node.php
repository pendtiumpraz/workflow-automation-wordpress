<?php
/**
 * WordPress Media Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 */

/**
 * WordPress Media node for handling media files
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Wp_Media_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'wp_media';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('WordPress Media', 'workflow-automation'),
            'category' => 'wordpress',
            'description' => __('Upload, manage and manipulate media files', 'workflow-automation'),
            'icon' => 'wa-icon-wp-media',
            'color' => '#21759B',
            'inputs' => array(
                array(
                    'name' => 'media_data',
                    'type' => 'object',
                    'required' => false
                )
            ),
            'outputs' => array(
                array(
                    'name' => 'attachment_id',
                    'type' => 'number'
                ),
                array(
                    'name' => 'attachment_url',
                    'type' => 'string'
                ),
                array(
                    'name' => 'attachment',
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
        $fields = array(
            array(
                'key' => 'action',
                'label' => __('Action', 'workflow-automation'),
                'type' => 'select',
                'default' => 'upload',
                'required' => true,
                'options' => array(
                    'upload' => __('Upload from URL', 'workflow-automation'),
                    'upload_base64' => __('Upload from Base64', 'workflow-automation'),
                    'get' => __('Get Media Info', 'workflow-automation'),
                    'update' => __('Update Media', 'workflow-automation'),
                    'delete' => __('Delete Media', 'workflow-automation'),
                    'generate_sizes' => __('Regenerate Image Sizes', 'workflow-automation')
                ),
                'description' => __('Choose the action to perform', 'workflow-automation')
            ),
            array(
                'key' => 'media_url',
                'label' => __('Media URL', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.image_url}} or media URL', 'workflow-automation'),
                'description' => __('URL of the media file to upload', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'upload'
                )
            ),
            array(
                'key' => 'base64_data',
                'label' => __('Base64 Data', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => __('{{trigger.base64_image}} or base64 string', 'workflow-automation'),
                'description' => __('Base64 encoded media data', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => '==',
                    'value' => 'upload_base64'
                )
            ),
            array(
                'key' => 'attachment_id',
                'label' => __('Attachment ID', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{node_id.attachment_id}} or ID', 'workflow-automation'),
                'description' => __('ID of the attachment', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('get', 'update', 'delete', 'generate_sizes')
                )
            ),
            array(
                'key' => 'filename',
                'label' => __('Filename', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.filename}} or custom-name.jpg', 'workflow-automation'),
                'description' => __('Filename for the uploaded file', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64')
                )
            ),
            array(
                'key' => 'title',
                'label' => __('Title', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.title}} or media title', 'workflow-automation'),
                'description' => __('Title for the media item', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'caption',
                'label' => __('Caption', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 2,
                'placeholder' => __('{{trigger.caption}} or media caption', 'workflow-automation'),
                'description' => __('Caption for the media item', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'description',
                'label' => __('Description', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => __('{{trigger.description}} or media description', 'workflow-automation'),
                'description' => __('Description for the media item', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'alt_text',
                'label' => __('Alt Text', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.alt_text}} or alternative text', 'workflow-automation'),
                'description' => __('Alternative text for images', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'post_id',
                'label' => __('Attach to Post', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{node_id.post_id}} or post ID', 'workflow-automation'),
                'description' => __('Post ID to attach the media to', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'media_meta',
                'label' => __('Media Meta', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => __('meta_key1: value1' . "\n" . 'meta_key2: {{variable}}', 'workflow-automation'),
                'description' => __('Media meta in format "key: value" (one per line)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'update')
                )
            ),
            array(
                'key' => 'image_sizes',
                'label' => __('Generate Sizes', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('thumbnail, medium, large or leave empty for all', 'workflow-automation'),
                'description' => __('Comma-separated list of image sizes to generate', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('upload', 'upload_base64', 'generate_sizes')
                )
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
            $action = $this->get_setting('action', 'upload');
            
            switch ($action) {
                case 'upload':
                    return $this->upload_from_url($context);
                    
                case 'upload_base64':
                    return $this->upload_from_base64($context);
                    
                case 'get':
                    return $this->get_media($context);
                    
                case 'update':
                    return $this->update_media($context);
                    
                case 'delete':
                    return $this->delete_media($context);
                    
                case 'generate_sizes':
                    return $this->generate_sizes($context);
                    
                default:
                    throw new Exception(__('Invalid action specified', 'workflow-automation'));
            }
            
        } catch (Exception $e) {
            $this->log('WordPress Media node error: ' . $e->getMessage(), 'error');
            
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
     * Upload media from URL
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function upload_from_url($context) {
        $media_url = $this->replace_variables($this->get_setting('media_url', ''), $context);
        
        if (empty($media_url)) {
            throw new Exception(__('Media URL is required', 'workflow-automation'));
        }
        
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Download file to temp location
        $tmp = download_url($media_url);
        
        if (is_wp_error($tmp)) {
            throw new Exception($tmp->get_error_message());
        }
        
        // Get filename
        $filename = $this->replace_variables($this->get_setting('filename', ''), $context);
        if (empty($filename)) {
            $filename = basename($media_url);
        }
        
        // Prepare file array
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $tmp
        );
        
        // Get post ID to attach to
        $post_id = $this->replace_variables($this->get_setting('post_id', ''), $context);
        $post_id = !empty($post_id) ? intval($post_id) : 0;
        
        // Upload file
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);
            throw new Exception($attachment_id->get_error_message());
        }
        
        // Update attachment metadata
        $this->update_attachment_metadata($attachment_id, $context);
        
        // Generate additional sizes if needed
        $this->handle_image_sizes($attachment_id, $context);
        
        $attachment = get_post($attachment_id);
        $attachment_url = wp_get_attachment_url($attachment_id);
        
        $this->log(sprintf('Media uploaded successfully. ID: %d, URL: %s', $attachment_id, $attachment_url));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => $attachment_url,
            'attachment' => $this->format_attachment_data($attachment),
            'action' => 'uploaded'
        );
    }
    
    /**
     * Upload media from base64
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function upload_from_base64($context) {
        $base64_data = $this->replace_variables($this->get_setting('base64_data', ''), $context);
        
        if (empty($base64_data)) {
            throw new Exception(__('Base64 data is required', 'workflow-automation'));
        }
        
        // Remove data URI prefix if present
        $base64_data = preg_replace('/^data:image\/\w+;base64,/', '', $base64_data);
        
        // Decode base64
        $decoded = base64_decode($base64_data);
        
        if ($decoded === false) {
            throw new Exception(__('Invalid base64 data', 'workflow-automation'));
        }
        
        // Get filename
        $filename = $this->replace_variables($this->get_setting('filename', ''), $context);
        if (empty($filename)) {
            $filename = 'upload-' . time() . '.jpg';
        }
        
        // Create temp file
        $upload_dir = wp_upload_dir();
        $tmp_name = wp_tempnam($filename, $upload_dir['temp']);
        
        if (!$tmp_name) {
            throw new Exception(__('Failed to create temporary file', 'workflow-automation'));
        }
        
        // Write decoded data to temp file
        if (!file_put_contents($tmp_name, $decoded)) {
            @unlink($tmp_name);
            throw new Exception(__('Failed to write temporary file', 'workflow-automation'));
        }
        
        // Prepare file array
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $tmp_name,
            'type' => $this->get_mime_type_from_filename($filename),
            'error' => 0,
            'size' => filesize($tmp_name)
        );
        
        // Get post ID to attach to
        $post_id = $this->replace_variables($this->get_setting('post_id', ''), $context);
        $post_id = !empty($post_id) ? intval($post_id) : 0;
        
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Upload file
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        if (is_wp_error($attachment_id)) {
            @unlink($tmp_name);
            throw new Exception($attachment_id->get_error_message());
        }
        
        // Update attachment metadata
        $this->update_attachment_metadata($attachment_id, $context);
        
        // Generate additional sizes if needed
        $this->handle_image_sizes($attachment_id, $context);
        
        $attachment = get_post($attachment_id);
        $attachment_url = wp_get_attachment_url($attachment_id);
        
        $this->log(sprintf('Media uploaded from base64 successfully. ID: %d, URL: %s', $attachment_id, $attachment_url));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => $attachment_url,
            'attachment' => $this->format_attachment_data($attachment),
            'action' => 'uploaded_base64'
        );
    }
    
    /**
     * Get media information
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function get_media($context) {
        $attachment_id = $this->get_attachment_id($context);
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            throw new Exception(__('Invalid attachment ID', 'workflow-automation'));
        }
        
        $this->log(sprintf('Media retrieved successfully. ID: %d', $attachment_id));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => wp_get_attachment_url($attachment_id),
            'attachment' => $this->format_attachment_data($attachment),
            'action' => 'retrieved'
        );
    }
    
    /**
     * Update media
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function update_media($context) {
        $attachment_id = $this->get_attachment_id($context);
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            throw new Exception(__('Invalid attachment ID', 'workflow-automation'));
        }
        
        // Update attachment metadata
        $this->update_attachment_metadata($attachment_id, $context);
        
        // Update post parent if provided
        $post_id = $this->replace_variables($this->get_setting('post_id', ''), $context);
        if (!empty($post_id)) {
            wp_update_post(array(
                'ID' => $attachment_id,
                'post_parent' => intval($post_id)
            ));
        }
        
        $attachment = get_post($attachment_id);
        
        $this->log(sprintf('Media updated successfully. ID: %d', $attachment_id));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => wp_get_attachment_url($attachment_id),
            'attachment' => $this->format_attachment_data($attachment),
            'action' => 'updated'
        );
    }
    
    /**
     * Delete media
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function delete_media($context) {
        $attachment_id = $this->get_attachment_id($context);
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            throw new Exception(__('Invalid attachment ID', 'workflow-automation'));
        }
        
        $attachment_url = wp_get_attachment_url($attachment_id);
        
        if (!wp_delete_attachment($attachment_id, true)) {
            throw new Exception(__('Failed to delete attachment', 'workflow-automation'));
        }
        
        $this->log(sprintf('Media deleted successfully. ID: %d', $attachment_id));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => $attachment_url,
            'action' => 'deleted'
        );
    }
    
    /**
     * Generate image sizes
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   array
     */
    private function generate_sizes($context) {
        $attachment_id = $this->get_attachment_id($context);
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            throw new Exception(__('Invalid attachment ID', 'workflow-automation'));
        }
        
        if (!wp_attachment_is_image($attachment_id)) {
            throw new Exception(__('Attachment is not an image', 'workflow-automation'));
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $fullsizepath = get_attached_file($attachment_id);
        $metadata = wp_generate_attachment_metadata($attachment_id, $fullsizepath);
        
        if (!empty($metadata) && !is_wp_error($metadata)) {
            wp_update_attachment_metadata($attachment_id, $metadata);
        }
        
        $this->log(sprintf('Image sizes regenerated successfully. ID: %d', $attachment_id));
        
        return array(
            'success' => true,
            'attachment_id' => $attachment_id,
            'attachment_url' => wp_get_attachment_url($attachment_id),
            'attachment' => $this->format_attachment_data($attachment),
            'sizes' => isset($metadata['sizes']) ? array_keys($metadata['sizes']) : array(),
            'action' => 'sizes_generated'
        );
    }
    
    /**
     * Get attachment ID from settings
     *
     * @since    1.0.0
     * @param    array    $context    The execution context
     * @return   int
     */
    private function get_attachment_id($context) {
        $attachment_id = $this->replace_variables($this->get_setting('attachment_id', ''), $context);
        
        if (empty($attachment_id)) {
            throw new Exception(__('Attachment ID is required', 'workflow-automation'));
        }
        
        return intval($attachment_id);
    }
    
    /**
     * Update attachment metadata
     *
     * @since    1.0.0
     * @param    int      $attachment_id    The attachment ID
     * @param    array    $context          The execution context
     */
    private function update_attachment_metadata($attachment_id, $context) {
        $update_data = array('ID' => $attachment_id);
        
        // Title
        $title = $this->replace_variables($this->get_setting('title', ''), $context);
        if (!empty($title)) {
            $update_data['post_title'] = $title;
        }
        
        // Caption
        $caption = $this->replace_variables($this->get_setting('caption', ''), $context);
        if (!empty($caption)) {
            $update_data['post_excerpt'] = $caption;
        }
        
        // Description
        $description = $this->replace_variables($this->get_setting('description', ''), $context);
        if (!empty($description)) {
            $update_data['post_content'] = $description;
        }
        
        // Update post data if needed
        if (count($update_data) > 1) {
            wp_update_post($update_data);
        }
        
        // Alt text
        $alt_text = $this->replace_variables($this->get_setting('alt_text', ''), $context);
        if (!empty($alt_text)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
        
        // Custom meta
        $this->handle_media_meta($attachment_id, $context);
    }
    
    /**
     * Handle media meta fields
     *
     * @since    1.0.0
     * @param    int      $attachment_id    The attachment ID
     * @param    array    $context          The execution context
     */
    private function handle_media_meta($attachment_id, $context) {
        $meta_fields = $this->get_setting('media_meta', '');
        if (empty($meta_fields)) {
            return;
        }
        
        $lines = explode("\n", $meta_fields);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = $this->replace_variables(trim($parts[1]), $context);
                update_post_meta($attachment_id, $key, $value);
            }
        }
    }
    
    /**
     * Handle image size generation
     *
     * @since    1.0.0
     * @param    int      $attachment_id    The attachment ID
     * @param    array    $context          The execution context
     */
    private function handle_image_sizes($attachment_id, $context) {
        if (!wp_attachment_is_image($attachment_id)) {
            return;
        }
        
        $sizes = $this->replace_variables($this->get_setting('image_sizes', ''), $context);
        if (empty($sizes)) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $fullsizepath = get_attached_file($attachment_id);
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        if ($sizes === 'all') {
            $metadata = wp_generate_attachment_metadata($attachment_id, $fullsizepath);
        } else {
            $size_array = array_map('trim', explode(',', $sizes));
            foreach ($size_array as $size) {
                if (isset($metadata['sizes'][$size])) {
                    continue;
                }
                
                $resized = image_make_intermediate_size($fullsizepath, 
                    get_option($size . '_size_w'), 
                    get_option($size . '_size_h'), 
                    get_option($size . '_crop')
                );
                
                if ($resized) {
                    $metadata['sizes'][$size] = $resized;
                }
            }
        }
        
        wp_update_attachment_metadata($attachment_id, $metadata);
    }
    
    /**
     * Get MIME type from filename
     *
     * @since    1.0.0
     * @param    string    $filename    The filename
     * @return   string
     */
    private function get_mime_type_from_filename($filename) {
        $mime_types = wp_get_mime_types();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        foreach ($mime_types as $exts => $mime) {
            if (preg_match('/' . $extension . '/i', $exts)) {
                return $mime;
            }
        }
        
        return 'application/octet-stream';
    }
    
    /**
     * Format attachment data for output
     *
     * @since    1.0.0
     * @param    WP_Post    $attachment    The attachment post
     * @return   array
     */
    private function format_attachment_data($attachment) {
        $metadata = wp_get_attachment_metadata($attachment->ID);
        
        $data = array(
            'ID' => $attachment->ID,
            'title' => $attachment->post_title,
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'mime_type' => $attachment->post_mime_type,
            'url' => wp_get_attachment_url($attachment->ID),
            'file' => get_attached_file($attachment->ID),
            'width' => isset($metadata['width']) ? $metadata['width'] : null,
            'height' => isset($metadata['height']) ? $metadata['height'] : null,
            'filesize' => isset($metadata['filesize']) ? $metadata['filesize'] : filesize(get_attached_file($attachment->ID)),
            'sizes' => array()
        );
        
        // Add available sizes
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                $data['sizes'][$size] = array(
                    'url' => wp_get_attachment_image_url($attachment->ID, $size),
                    'width' => $size_data['width'],
                    'height' => $size_data['height']
                );
            }
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
        $action = $this->get_setting('action', 'upload');
        
        if ($action === 'upload' && empty($this->get_setting('media_url'))) {
            return new WP_Error(
                'missing_media_url',
                __('Media URL is required for upload action', 'workflow-automation')
            );
        }
        
        if ($action === 'upload_base64' && empty($this->get_setting('base64_data'))) {
            return new WP_Error(
                'missing_base64_data',
                __('Base64 data is required for base64 upload action', 'workflow-automation')
            );
        }
        
        if (in_array($action, array('get', 'update', 'delete', 'generate_sizes'))) {
            if (empty($this->get_setting('attachment_id'))) {
                return new WP_Error(
                    'missing_attachment_id',
                    __('Attachment ID is required', 'workflow-automation')
                );
            }
        }
        
        return true;
    }
}