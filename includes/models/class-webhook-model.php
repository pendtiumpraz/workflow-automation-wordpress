<?php
/**
 * Webhook Model
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 */

/**
 * Webhook Model class
 *
 * Handles CRUD operations for webhooks
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes/models
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Webhook_Model {

    /**
     * The table name
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $table_name    The database table name
     */
    private $table_name;

    /**
     * Initialize the model
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wa_webhooks';
    }

    /**
     * Get webhook by key
     *
     * @since    1.0.0
     * @param    string    $key    The webhook key
     * @return   object|null
     */
    public function get_by_key($key) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE webhook_key = %s",
            $key
        );
        
        $result = $wpdb->get_row($sql);
        
        if ($result && $result->settings) {
            $result->settings = json_decode($result->settings, true);
        }
        
        return $result;
    }

    /**
     * Create a webhook
     *
     * @since    1.0.0
     * @param    int      $workflow_id    The workflow ID
     * @param    string   $node_id        The node ID
     * @param    array    $settings       Webhook settings
     * @return   array|false    Webhook data or false on failure
     */
    public function create_webhook($workflow_id, $node_id, $settings = array()) {
        global $wpdb;
        
        // Generate unique webhook key
        $webhook_key = $this->generate_webhook_key();
        
        $data = array(
            'workflow_id' => $workflow_id,
            'node_id' => $node_id,
            'webhook_key' => $webhook_key,
            'settings' => json_encode($settings),
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert(
            $this->table_name,
            $data,
            array('%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            return array(
                'id' => $wpdb->insert_id,
                'webhook_key' => $webhook_key,
                'webhook_url' => $this->get_webhook_url($webhook_key)
            );
        }
        
        return false;
    }

    /**
     * Update webhook settings
     *
     * @since    1.0.0
     * @param    int      $id         The webhook ID
     * @param    array    $settings   Webhook settings
     * @return   bool
     */
    public function update_settings($id, $settings) {
        global $wpdb;
        
        $data = array(
            'settings' => json_encode($settings)
        );
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Delete webhooks by workflow ID
     *
     * @since    1.0.0
     * @param    int    $workflow_id    The workflow ID
     * @return   bool
     */
    public function delete_by_workflow($workflow_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('workflow_id' => $workflow_id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Generate a unique webhook key
     *
     * @since    1.0.0
     * @return   string
     */
    private function generate_webhook_key() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get webhook URL from key
     *
     * @since    1.0.0
     * @param    string    $key    The webhook key
     * @return   string
     */
    public function get_webhook_url($key) {
        require_once WA_PLUGIN_DIR . 'includes/class-webhook-handler.php';
        return Webhook_Handler::get_webhook_url($key);
    }
    
    /**
     * Get webhook by ID
     *
     * @since    1.0.0
     * @param    int    $id    The webhook ID
     * @return   object|null
     */
    public function get($id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $id
        );
        
        $result = $wpdb->get_row($sql);
        
        if ($result && $result->settings) {
            $result->settings = json_decode($result->settings, true);
        }
        
        return $result;
    }
    
    /**
     * Update webhook
     *
     * @since    1.0.0
     * @param    int      $id     The webhook ID
     * @param    array    $data   Data to update
     * @return   bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        $formats = array();
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'workflow_id':
                case 'is_active':
                    $formats[] = '%d';
                    break;
                default:
                    $formats[] = '%s';
                    break;
            }
        }
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $id),
            $formats,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete webhook
     *
     * @since    1.0.0
     * @param    int    $id    The webhook ID
     * @return   bool
     */
    public function delete($id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
}