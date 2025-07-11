<?php
/**
 * WordPress Post Node Class
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 */

/**
 * WordPress Post node for creating/updating posts
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/nodes/wordpress
 * @author     OpsGuide Team <support@opsguide.com>
 */
class WA_Wp_Post_Node extends WA_Abstract_Node {
    
    /**
     * Get the node type
     *
     * @since    1.0.0
     * @return   string
     */
    public function get_type() {
        return 'wp_post';
    }
    
    /**
     * Get node options
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_options() {
        return array(
            'name' => __('WordPress Post', 'workflow-automation'),
            'category' => 'wordpress',
            'description' => __('Create or update WordPress posts', 'workflow-automation'),
            'icon' => 'wa-icon-wp-post',
            'color' => '#21759B',
            'inputs' => array(
                array(
                    'name' => 'post_data',
                    'type' => 'object',
                    'required' => false
                )
            ),
            'outputs' => array(
                array(
                    'name' => 'post_id',
                    'type' => 'number'
                ),
                array(
                    'name' => 'post_url',
                    'type' => 'string'
                ),
                array(
                    'name' => 'post',
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
        // Get post types
        $post_types = get_post_types(array('public' => true), 'objects');
        $post_type_options = array();
        foreach ($post_types as $post_type) {
            $post_type_options[$post_type->name] = $post_type->label;
        }
        
        // Get post statuses
        $post_statuses = get_post_statuses();
        
        $fields = array(
            array(
                'key' => 'action',
                'label' => __('Action', 'workflow-automation'),
                'type' => 'select',
                'default' => 'create',
                'required' => true,
                'options' => array(
                    'create' => __('Create New Post', 'workflow-automation'),
                    'update' => __('Update Existing Post', 'workflow-automation'),
                    'create_or_update' => __('Create or Update', 'workflow-automation')
                ),
                'description' => __('Choose whether to create a new post or update an existing one', 'workflow-automation')
            ),
            array(
                'key' => 'post_id',
                'label' => __('Post ID', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{node_id.post_id}} or specific ID', 'workflow-automation'),
                'description' => __('Post ID to update (required for update action)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'action',
                    'operator' => 'in',
                    'value' => array('update', 'create_or_update')
                )
            ),
            array(
                'key' => 'post_type',
                'label' => __('Post Type', 'workflow-automation'),
                'type' => 'select',
                'default' => 'post',
                'options' => $post_type_options,
                'description' => __('Type of post to create', 'workflow-automation')
            ),
            array(
                'key' => 'post_title',
                'label' => __('Post Title', 'workflow-automation'),
                'type' => 'text',
                'required' => true,
                'placeholder' => __('{{trigger.title}} or custom title', 'workflow-automation'),
                'description' => __('Title of the post', 'workflow-automation')
            ),
            array(
                'key' => 'post_content',
                'label' => __('Post Content', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 10,
                'placeholder' => __('{{trigger.content}} or custom content...', 'workflow-automation'),
                'description' => __('Content of the post. Supports HTML and variables', 'workflow-automation')
            ),
            array(
                'key' => 'post_excerpt',
                'label' => __('Post Excerpt', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 3,
                'placeholder' => __('Optional excerpt...', 'workflow-automation'),
                'description' => __('Optional excerpt for the post', 'workflow-automation')
            ),
            array(
                'key' => 'post_status',
                'label' => __('Post Status', 'workflow-automation'),
                'type' => 'select',
                'default' => 'draft',
                'options' => $post_statuses,
                'description' => __('Status of the post', 'workflow-automation')
            ),
            array(
                'key' => 'post_author',
                'label' => __('Post Author', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('User ID or leave empty for current user', 'workflow-automation'),
                'description' => __('Author user ID (optional)', 'workflow-automation')
            ),
            array(
                'key' => 'post_category',
                'label' => __('Categories', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('Category IDs separated by commas', 'workflow-automation'),
                'description' => __('Category IDs (for posts only)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'post_type',
                    'operator' => '==',
                    'value' => 'post'
                )
            ),
            array(
                'key' => 'post_tags',
                'label' => __('Tags', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('Tags separated by commas', 'workflow-automation'),
                'description' => __('Post tags (for posts only)', 'workflow-automation'),
                'condition' => array(
                    'field' => 'post_type',
                    'operator' => '==',
                    'value' => 'post'
                )
            ),
            array(
                'key' => 'post_meta',
                'label' => __('Custom Fields (Meta)', 'workflow-automation'),
                'type' => 'textarea',
                'rows' => 5,
                'placeholder' => __('key1: value1' . "\n" . 'key2: {{variable}}', 'workflow-automation'),
                'description' => __('Custom fields in format "key: value" (one per line)', 'workflow-automation')
            ),
            array(
                'key' => 'featured_image',
                'label' => __('Featured Image URL', 'workflow-automation'),
                'type' => 'text',
                'placeholder' => __('{{trigger.image_url}} or image URL', 'workflow-automation'),
                'description' => __('URL of image to set as featured image', 'workflow-automation')
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
            $action = $this->get_setting('action', 'create');
            $post_id = null;
            
            // Get post ID for update actions
            if ($action === 'update' || $action === 'create_or_update') {
                $post_id_setting = $this->replace_variables($this->get_setting('post_id', ''), $context);
                if (!empty($post_id_setting)) {
                    $post_id = intval($post_id_setting);
                    
                    // Check if post exists for update action
                    if ($action === 'update' && !get_post($post_id)) {
                        throw new Exception(sprintf(__('Post with ID %d not found', 'workflow-automation'), $post_id));
                    }
                }
            }
            
            // Prepare post data
            $post_data = array(
                'post_type' => $this->get_setting('post_type', 'post'),
                'post_title' => $this->replace_variables($this->get_setting('post_title', ''), $context),
                'post_content' => $this->replace_variables($this->get_setting('post_content', ''), $context),
                'post_excerpt' => $this->replace_variables($this->get_setting('post_excerpt', ''), $context),
                'post_status' => $this->get_setting('post_status', 'draft')
            );
            
            // Set author
            $author = $this->replace_variables($this->get_setting('post_author', ''), $context);
            if (!empty($author)) {
                $post_data['post_author'] = intval($author);
            } else {
                $post_data['post_author'] = get_current_user_id();
            }
            
            // Update existing post
            if ($post_id && ($action === 'update' || ($action === 'create_or_update' && get_post($post_id)))) {
                $post_data['ID'] = $post_id;
                $result = wp_update_post($post_data, true);
                $is_update = true;
            } else {
                // Create new post
                $result = wp_insert_post($post_data, true);
                $is_update = false;
            }
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            $post_id = $result;
            
            // Handle categories (for posts only)
            if ($post_data['post_type'] === 'post') {
                $categories = $this->replace_variables($this->get_setting('post_category', ''), $context);
                if (!empty($categories)) {
                    $category_ids = array_map('intval', array_map('trim', explode(',', $categories)));
                    wp_set_post_categories($post_id, $category_ids);
                }
                
                // Handle tags
                $tags = $this->replace_variables($this->get_setting('post_tags', ''), $context);
                if (!empty($tags)) {
                    wp_set_post_tags($post_id, $tags);
                }
            }
            
            // Handle custom fields
            $meta_fields = $this->get_setting('post_meta', '');
            if (!empty($meta_fields)) {
                $lines = explode("\n", $meta_fields);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $parts = explode(':', $line, 2);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $value = $this->replace_variables(trim($parts[1]), $context);
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }
            
            // Handle featured image
            $featured_image_url = $this->replace_variables($this->get_setting('featured_image', ''), $context);
            if (!empty($featured_image_url)) {
                $this->set_featured_image($post_id, $featured_image_url);
            }
            
            // Get the created/updated post
            $post = get_post($post_id);
            
            $this->log(sprintf(
                'Post %s successfully. ID: %d, Title: %s',
                $is_update ? 'updated' : 'created',
                $post_id,
                $post->post_title
            ));
            
            return array(
                'success' => true,
                'post_id' => $post_id,
                'post_url' => get_permalink($post_id),
                'post' => array(
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_status' => $post->post_status,
                    'post_type' => $post->post_type,
                    'post_author' => $post->post_author,
                    'post_date' => $post->post_date,
                    'guid' => $post->guid
                ),
                'action' => $is_update ? 'updated' : 'created'
            );
            
        } catch (Exception $e) {
            $this->log('WordPress Post node error: ' . $e->getMessage(), 'error');
            
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
     * Set featured image from URL
     *
     * @since    1.0.0
     * @param    int       $post_id    The post ID
     * @param    string    $image_url  The image URL
     * @return   bool
     */
    private function set_featured_image($post_id, $image_url) {
        try {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            
            // Download image to temp location
            $tmp = download_url($image_url);
            
            if (is_wp_error($tmp)) {
                $this->log('Failed to download image: ' . $tmp->get_error_message(), 'warning');
                return false;
            }
            
            // Get file info
            $file_array = array(
                'name' => basename($image_url),
                'tmp_name' => $tmp
            );
            
            // Upload image
            $attachment_id = media_handle_sideload($file_array, $post_id);
            
            if (is_wp_error($attachment_id)) {
                @unlink($file_array['tmp_name']);
                $this->log('Failed to upload image: ' . $attachment_id->get_error_message(), 'warning');
                return false;
            }
            
            // Set as featured image
            set_post_thumbnail($post_id, $attachment_id);
            
            return true;
            
        } catch (Exception $e) {
            $this->log('Error setting featured image: ' . $e->getMessage(), 'warning');
            return false;
        }
    }
    
    /**
     * Validate settings
     *
     * @since    1.0.0
     * @return   bool|WP_Error
     */
    public function validate_settings() {
        $action = $this->get_setting('action', 'create');
        
        if (empty($this->get_setting('post_title'))) {
            return new WP_Error(
                'missing_title',
                __('Post title is required', 'workflow-automation')
            );
        }
        
        if ($action === 'update' && empty($this->get_setting('post_id'))) {
            return new WP_Error(
                'missing_post_id',
                __('Post ID is required for update action', 'workflow-automation')
            );
        }
        
        return true;
    }
}