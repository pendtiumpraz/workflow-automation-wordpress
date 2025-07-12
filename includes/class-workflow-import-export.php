<?php
/**
 * Workflow Import/Export Handler
 *
 * @link       https://opsguide.com
 * @since      1.0.0
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 */

/**
 * Workflow Import/Export class
 *
 * Handles importing and exporting workflows in various formats
 *
 * @package    Workflow_Automation
 * @subpackage Workflow_Automation/includes
 * @author     OpsGuide Team <support@opsguide.com>
 */
class Workflow_Import_Export {

    /**
     * Node type mappings
     *
     * @var array
     */
    private $node_mappings = array(
        'n8n' => array(
            // Triggers
            'n8n-nodes-base.webhook' => 'webhook_start',
            'n8n-nodes-base.trigger' => 'manual_trigger',
            'n8n-nodes-base.schedule' => 'schedule_start',
            
            // Communication
            'n8n-nodes-base.httpRequest' => 'http',
            'n8n-nodes-base.slack' => 'slack',
            'n8n-nodes-base.emailSend' => 'email',
            'n8n-nodes-base.telegram' => 'telegram',
            'n8n-nodes-base.discord' => 'discord',
            'n8n-nodes-base.line' => 'line',
            
            // AI
            'n8n-nodes-base.openAi' => 'openai',
            '@n8n/n8n-nodes-langchain.openAi' => 'openai',
            
            // Data
            'n8n-nodes-base.googleSheets' => 'google_sheets',
            'n8n-nodes-base.googleDrive' => 'google_drive',
            'n8n-nodes-base.notion' => 'notion',
            'n8n-nodes-base.airtable' => 'airtable',
            'n8n-nodes-base.microsoftOneDrive' => 'onedrive',
            'n8n-nodes-base.microsoftExcel' => 'excel',
            
            // Logic
            'n8n-nodes-base.if' => 'filter',
            'n8n-nodes-base.switch' => 'switch',
            'n8n-nodes-base.splitInBatches' => 'loop',
            'n8n-nodes-base.merge' => 'merge',
            'n8n-nodes-base.wait' => 'delay',
            
            // Transform
            'n8n-nodes-base.code' => 'code',
            'n8n-nodes-base.function' => 'transform',
            'n8n-nodes-base.set' => 'set_variable',
            'n8n-nodes-base.dateTime' => 'date_time',
            
            // CRM
            'n8n-nodes-base.hubspot' => 'hubspot',
            'n8n-nodes-base.salesforce' => 'salesforce',
            'n8n-nodes-base.pipedrive' => 'pipedrive',
            
            // WordPress
            'n8n-nodes-base.wordpress' => 'wordpress'
        ),
        'make' => array(
            // Triggers
            'gateway:CustomWebHook' => 'webhook_start',
            'gateway:Scheduler' => 'schedule_start',
            
            // Communication
            'http:ActionSendRequest' => 'http',
            'slack:CreateMessage' => 'slack',
            'email:ActionSendEmail' => 'email',
            'telegram:SendMessage' => 'telegram',
            'discord:SendMessage' => 'discord',
            
            // AI
            'openai:CreateCompletion' => 'openai',
            'openai:CreateChatCompletion' => 'openai',
            
            // Data
            'google-sheets:ActionAddRow' => 'google_sheets',
            'google-drive:ActionUploadFile' => 'google_drive',
            'notion:CreateDatabaseItem' => 'notion',
            'airtable:CreateRecord' => 'airtable',
            'microsoft365-onedrive:ActionUploadFile' => 'onedrive',
            'microsoft365-excel:ActionAddRow' => 'excel',
            
            // Logic
            'builtin:BasicFeeder' => 'filter',
            'builtin:BasicRouter' => 'switch',
            'builtin:BasicIterator' => 'loop',
            'builtin:BasicAggregator' => 'merge',
            'builtin:Sleep' => 'delay',
            
            // Transform
            'tools:RunJavaScript' => 'code',
            'tools:SetVariable' => 'set_variable',
            'tools:SetMultipleVariables' => 'set_variable',
            'date:FormatDate' => 'date_time',
            
            // CRM
            'hubspot:CreateContact' => 'hubspot',
            'salesforce:CreateRecord' => 'salesforce',
            'pipedrive:CreateDeal' => 'pipedrive',
            
            // WordPress
            'wordpress:CreatePost' => 'wordpress'
        )
    );

    /**
     * Export workflow to JSON
     *
     * @param int    $workflow_id The workflow ID
     * @param string $format      Export format (native, n8n, make)
     * @return array|WP_Error
     */
    public function export_workflow($workflow_id, $format = 'native') {
        $workflow_model = new Workflow_Model();
        $workflow = $workflow_model->get($workflow_id);
        
        if (!$workflow) {
            return new WP_Error('not_found', __('Workflow not found', 'workflow-automation'));
        }
        
        // Get nodes
        $node_model = new Node_Model();
        $nodes = $node_model->get_by_workflow($workflow_id);
        
        // Parse flow data
        $flow_data = json_decode($workflow->flow_data, true);
        
        switch ($format) {
            case 'n8n':
                return $this->export_to_n8n($workflow, $nodes, $flow_data);
            case 'make':
                return $this->export_to_make($workflow, $nodes, $flow_data);
            default:
                return $this->export_native($workflow, $nodes, $flow_data);
        }
    }

    /**
     * Export to native format
     */
    private function export_native($workflow, $nodes, $flow_data) {
        $export = array(
            'version' => '1.0',
            'type' => 'workflow_automation',
            'workflow' => array(
                'name' => $workflow->name,
                'description' => $workflow->description,
                'status' => $workflow->status,
                'flow_data' => array(
                    'nodes' => array(),
                    'edges' => isset($flow_data['edges']) ? $flow_data['edges'] : array()
                )
            )
        );
        
        // Add nodes
        foreach ($nodes as $node) {
            $export['workflow']['flow_data']['nodes'][] = array(
                'node_id' => $node->node_id,
                'node_type' => $node->node_type,
                'settings' => json_decode($node->settings, true),
                'position_x' => $node->position_x,
                'position_y' => $node->position_y
            );
        }
        
        return $export;
    }

    /**
     * Export to n8n format
     */
    private function export_to_n8n($workflow, $nodes, $flow_data) {
        $export = array(
            'name' => $workflow->name,
            'nodes' => array(),
            'connections' => array(),
            'active' => $workflow->status === 'active',
            'settings' => array(),
            'id' => null
        );
        
        // Convert nodes
        $node_map = array();
        foreach ($nodes as $node) {
            $n8n_node = $this->convert_node_to_n8n($node);
            if ($n8n_node) {
                $export['nodes'][] = $n8n_node;
                $node_map[$node->node_id] = $n8n_node['name'];
            }
        }
        
        // Convert connections
        if (isset($flow_data['edges'])) {
            foreach ($flow_data['edges'] as $edge) {
                $source_name = isset($node_map[$edge['source']]) ? $node_map[$edge['source']] : '';
                $target_name = isset($node_map[$edge['target']]) ? $node_map[$edge['target']] : '';
                
                if ($source_name && $target_name) {
                    if (!isset($export['connections'][$source_name])) {
                        $export['connections'][$source_name] = array('main' => array());
                    }
                    $export['connections'][$source_name]['main'][] = array(
                        array(
                            'node' => $target_name,
                            'type' => 'main',
                            'index' => 0
                        )
                    );
                }
            }
        }
        
        return $export;
    }

    /**
     * Convert node to n8n format
     */
    private function convert_node_to_n8n($node) {
        $n8n_type = array_search($node->node_type, $this->node_mappings['n8n']);
        if (!$n8n_type) {
            $n8n_type = 'n8n-nodes-base.noOp'; // Default fallback
        }
        
        $settings = json_decode($node->settings, true);
        $parameters = $this->map_parameters_to_n8n($node->node_type, $settings);
        
        return array(
            'parameters' => $parameters,
            'id' => wp_generate_uuid4(),
            'name' => $this->generate_n8n_node_name($node->node_type),
            'type' => $n8n_type,
            'typeVersion' => 1,
            'position' => array($node->position_x, $node->position_y)
        );
    }

    /**
     * Map parameters to n8n format
     */
    private function map_parameters_to_n8n($node_type, $settings) {
        $parameters = array();
        
        switch ($node_type) {
            case 'webhook_start':
                $parameters['httpMethod'] = isset($settings['method']) ? $settings['method'] : 'POST';
                $parameters['path'] = isset($settings['webhook_key']) ? $settings['webhook_key'] : wp_generate_password(12, false);
                break;
                
            case 'http':
                $parameters['url'] = isset($settings['url']) ? $settings['url'] : '';
                $parameters['method'] = isset($settings['method']) ? $settings['method'] : 'GET';
                if (isset($settings['headers'])) {
                    $parameters['headerParametersUi'] = array('parameter' => $settings['headers']);
                }
                break;
                
            case 'openai':
                $parameters['resource'] = 'completion';
                $parameters['model'] = isset($settings['model']) ? $settings['model'] : 'gpt-3.5-turbo';
                $parameters['prompt'] = isset($settings['prompt']) ? $settings['prompt'] : '';
                break;
                
            case 'slack':
                $parameters['channel'] = isset($settings['channel']) ? $settings['channel'] : '';
                $parameters['text'] = isset($settings['message']) ? $settings['message'] : '';
                break;
                
            // Add more mappings as needed
        }
        
        return $parameters;
    }

    /**
     * Export to Make.com format
     */
    private function export_to_make($workflow, $nodes, $flow_data) {
        $export = array(
            'name' => $workflow->name,
            'flow' => array(),
            'metadata' => array(
                'version' => 1,
                'scenario' => array(
                    'autoCommit' => true,
                    'sequential' => true,
                    'confidential' => false
                )
            )
        );
        
        // Convert nodes to Make.com modules
        $module_id = 1;
        $node_to_module_map = array();
        
        foreach ($nodes as $node) {
            $make_module = $this->convert_node_to_make($node, $module_id);
            if ($make_module) {
                $export['flow'][] = $make_module;
                $node_to_module_map[$node->node_id] = $module_id;
                $module_id++;
            }
        }
        
        return $export;
    }

    /**
     * Convert node to Make.com format
     */
    private function convert_node_to_make($node, $module_id) {
        $make_type = array_search($node->node_type, $this->node_mappings['make']);
        if (!$make_type) {
            return null;
        }
        
        $settings = json_decode($node->settings, true);
        
        return array(
            'id' => $module_id,
            'module' => $make_type,
            'version' => 1,
            'parameters' => $this->map_parameters_to_make($node->node_type, $settings),
            'mapper' => $this->get_make_mapper($node->node_type, $settings),
            'metadata' => array(
                'designer' => array(
                    'x' => $node->position_x,
                    'y' => $node->position_y
                )
            )
        );
    }

    /**
     * Import workflow from JSON
     *
     * @param array  $data   Import data
     * @param string $format Import format
     * @return int|WP_Error Workflow ID or error
     */
    public function import_workflow($data, $format = 'native') {
        switch ($format) {
            case 'n8n':
                return $this->import_from_n8n($data);
            case 'make':
                return $this->import_from_make($data);
            default:
                return $this->import_native($data);
        }
    }

    /**
     * Import from n8n format
     */
    private function import_from_n8n($data) {
        if (!isset($data['nodes']) || !is_array($data['nodes'])) {
            return new WP_Error('invalid_format', __('Invalid n8n format', 'workflow-automation'));
        }
        
        // Create workflow
        $workflow_model = new Workflow_Model();
        $workflow_id = $workflow_model->create(array(
            'name' => isset($data['name']) ? $data['name'] : __('Imported from n8n', 'workflow-automation'),
            'description' => __('Imported from n8n', 'workflow-automation'),
            'status' => isset($data['active']) && $data['active'] ? 'active' : 'draft',
            'created_by' => get_current_user_id()
        ));
        
        if (!$workflow_id) {
            return new WP_Error('create_failed', __('Failed to create workflow', 'workflow-automation'));
        }
        
        // Import nodes
        $node_model = new Node_Model();
        $node_id_map = array();
        
        foreach ($data['nodes'] as $n8n_node) {
            $our_node = $this->convert_node_from_n8n($n8n_node);
            if ($our_node) {
                $node_id = 'node_' . time() . '_' . wp_generate_password(8, false);
                $node_id_map[$n8n_node['name']] = $node_id;
                
                $node_model->create(array(
                    'workflow_id' => $workflow_id,
                    'node_id' => $node_id,
                    'node_type' => $our_node['type'],
                    'settings' => json_encode($our_node['settings']),
                    'position_x' => $our_node['position'][0],
                    'position_y' => $our_node['position'][1]
                ));
            }
        }
        
        // Import connections
        $edges = array();
        if (isset($data['connections'])) {
            foreach ($data['connections'] as $source_name => $outputs) {
                if (isset($outputs['main']) && isset($node_id_map[$source_name])) {
                    foreach ($outputs['main'] as $output_connections) {
                        foreach ($output_connections as $connection) {
                            if (isset($node_id_map[$connection['node']])) {
                                $edges[] = array(
                                    'source' => $node_id_map[$source_name],
                                    'target' => $node_id_map[$connection['node']]
                                );
                            }
                        }
                    }
                }
            }
        }
        
        // Update workflow with edges
        $workflow_model->update($workflow_id, array(
            'flow_data' => json_encode(array('edges' => $edges))
        ));
        
        return $workflow_id;
    }

    /**
     * Convert node from n8n format
     */
    private function convert_node_from_n8n($n8n_node) {
        $our_type = isset($this->node_mappings['n8n'][$n8n_node['type']]) 
            ? $this->node_mappings['n8n'][$n8n_node['type']] 
            : 'code'; // Default fallback
        
        $settings = $this->map_parameters_from_n8n($our_type, $n8n_node['parameters']);
        
        return array(
            'type' => $our_type,
            'settings' => $settings,
            'position' => isset($n8n_node['position']) ? $n8n_node['position'] : array(100, 100)
        );
    }

    /**
     * Generate n8n node name
     */
    private function generate_n8n_node_name($type) {
        $names = array(
            'webhook_start' => 'Webhook',
            'http' => 'HTTP Request',
            'openai' => 'OpenAI',
            'slack' => 'Slack',
            'email' => 'Send Email',
            'google_sheets' => 'Google Sheets',
            'filter' => 'IF',
            'loop' => 'Split In Batches'
        );
        
        return isset($names[$type]) ? $names[$type] : ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Map parameters from n8n format
     */
    private function map_parameters_from_n8n($node_type, $parameters) {
        $settings = array();
        
        switch ($node_type) {
            case 'webhook_start':
                $settings['method'] = isset($parameters['httpMethod']) ? $parameters['httpMethod'] : 'POST';
                $settings['webhook_key'] = isset($parameters['path']) ? $parameters['path'] : '';
                break;
                
            case 'http':
                $settings['url'] = isset($parameters['url']) ? $parameters['url'] : '';
                $settings['method'] = isset($parameters['method']) ? $parameters['method'] : 'GET';
                break;
                
            case 'openai':
                $settings['model'] = isset($parameters['model']) ? $parameters['model'] : 'gpt-3.5-turbo';
                $settings['prompt'] = isset($parameters['prompt']) ? $parameters['prompt'] : '';
                break;
                
            case 'slack':
                $settings['channel'] = isset($parameters['channel']) ? $parameters['channel'] : '';
                $settings['message'] = isset($parameters['text']) ? $parameters['text'] : '';
                break;
        }
        
        return $settings;
    }

    /**
     * Get Make.com mapper
     */
    private function get_make_mapper($node_type, $settings) {
        $mapper = array();
        
        switch ($node_type) {
            case 'openai':
                $mapper['model'] = isset($settings['model']) ? $settings['model'] : 'gpt-3.5-turbo';
                $mapper['prompt'] = isset($settings['prompt']) ? $settings['prompt'] : '';
                break;
                
            case 'slack':
                $mapper['channel'] = isset($settings['channel']) ? $settings['channel'] : '';
                $mapper['text'] = isset($settings['message']) ? $settings['message'] : '';
                break;
        }
        
        return $mapper;
    }

    /**
     * Map parameters to Make.com format
     */
    private function map_parameters_to_make($node_type, $settings) {
        $parameters = array();
        
        switch ($node_type) {
            case 'webhook_start':
                $parameters['hook'] = wp_rand(100000, 999999);
                $parameters['maxResults'] = 1;
                break;
                
            case 'openai':
                $parameters['apiKey'] = 'connection:openai';
                break;
                
            case 'slack':
                $parameters['connection'] = 'connection:slack';
                break;
        }
        
        return $parameters;
    }

    /**
     * Import from Make.com format
     */
    private function import_from_make($data) {
        if (!isset($data['flow']) || !is_array($data['flow'])) {
            return new WP_Error('invalid_format', __('Invalid Make.com format', 'workflow-automation'));
        }
        
        // Create workflow
        $workflow_model = new Workflow_Model();
        $workflow_id = $workflow_model->create(array(
            'name' => isset($data['name']) ? $data['name'] : __('Imported from Make.com', 'workflow-automation'),
            'description' => __('Imported from Make.com', 'workflow-automation'),
            'status' => 'draft',
            'created_by' => get_current_user_id()
        ));
        
        if (!$workflow_id) {
            return new WP_Error('create_failed', __('Failed to create workflow', 'workflow-automation'));
        }
        
        // Import modules as nodes
        $node_model = new Node_Model();
        $node_id_map = array();
        $edges = array();
        
        foreach ($data['flow'] as $index => $module) {
            $our_node = $this->convert_node_from_make($module);
            if ($our_node) {
                $node_id = 'node_' . time() . '_' . wp_generate_password(8, false);
                $node_id_map[$module['id']] = $node_id;
                
                $node_model->create(array(
                    'workflow_id' => $workflow_id,
                    'node_id' => $node_id,
                    'node_type' => $our_node['type'],
                    'settings' => json_encode($our_node['settings']),
                    'position_x' => $our_node['position'][0],
                    'position_y' => $our_node['position'][1]
                ));
                
                // Make.com uses sequential flow, so connect to previous
                if ($index > 0) {
                    $prev_module_id = $data['flow'][$index - 1]['id'];
                    if (isset($node_id_map[$prev_module_id])) {
                        $edges[] = array(
                            'source' => $node_id_map[$prev_module_id],
                            'target' => $node_id
                        );
                    }
                }
            }
        }
        
        // Update workflow with edges
        $workflow_model->update($workflow_id, array(
            'flow_data' => json_encode(array('edges' => $edges))
        ));
        
        return $workflow_id;
    }

    /**
     * Convert node from Make.com format
     */
    private function convert_node_from_make($module) {
        $our_type = isset($this->node_mappings['make'][$module['module']]) 
            ? $this->node_mappings['make'][$module['module']] 
            : 'code'; // Default fallback
        
        $settings = $this->map_parameters_from_make($our_type, $module);
        
        $position = array(100, 100);
        if (isset($module['metadata']['designer'])) {
            $position = array(
                $module['metadata']['designer']['x'],
                $module['metadata']['designer']['y']
            );
        }
        
        return array(
            'type' => $our_type,
            'settings' => $settings,
            'position' => $position
        );
    }

    /**
     * Map parameters from Make.com format
     */
    private function map_parameters_from_make($node_type, $module) {
        $settings = array();
        
        // Combine parameters and mapper
        $all_params = array();
        if (isset($module['parameters'])) {
            $all_params = array_merge($all_params, $module['parameters']);
        }
        if (isset($module['mapper'])) {
            $all_params = array_merge($all_params, $module['mapper']);
        }
        
        switch ($node_type) {
            case 'webhook_start':
                $settings['webhook_key'] = 'make_hook_' . (isset($all_params['hook']) ? $all_params['hook'] : wp_generate_password(12, false));
                break;
                
            case 'http':
                $settings['url'] = isset($all_params['url']) ? $all_params['url'] : '';
                $settings['method'] = isset($all_params['method']) ? $all_params['method'] : 'GET';
                break;
                
            case 'openai':
                $settings['model'] = isset($all_params['model']) ? $all_params['model'] : 'gpt-3.5-turbo';
                $settings['prompt'] = isset($all_params['prompt']) ? $all_params['prompt'] : '';
                break;
                
            case 'slack':
                $settings['channel'] = isset($all_params['channel']) ? $all_params['channel'] : '';
                $settings['message'] = isset($all_params['text']) ? $all_params['text'] : '';
                break;
        }
        
        return $settings;
    }

    /**
     * Import from native format
     */
    private function import_native($data) {
        if (!isset($data['workflow']) || !is_array($data['workflow'])) {
            return new WP_Error('invalid_format', __('Invalid native format', 'workflow-automation'));
        }
        
        $workflow_data = $data['workflow'];
        
        // Create workflow
        $workflow_model = new Workflow_Model();
        $workflow_id = $workflow_model->create(array(
            'name' => isset($workflow_data['name']) ? $workflow_data['name'] : __('Imported workflow', 'workflow-automation'),
            'description' => isset($workflow_data['description']) ? $workflow_data['description'] : '',
            'status' => isset($workflow_data['status']) ? $workflow_data['status'] : 'draft',
            'created_by' => get_current_user_id()
        ));
        
        if (!$workflow_id) {
            return new WP_Error('create_failed', __('Failed to create workflow', 'workflow-automation'));
        }
        
        // Import nodes
        if (isset($workflow_data['flow_data']['nodes'])) {
            $node_model = new Node_Model();
            foreach ($workflow_data['flow_data']['nodes'] as $node) {
                $node_model->create(array(
                    'workflow_id' => $workflow_id,
                    'node_id' => $node['node_id'],
                    'node_type' => $node['node_type'],
                    'settings' => is_string($node['settings']) ? $node['settings'] : json_encode($node['settings']),
                    'position_x' => isset($node['position_x']) ? $node['position_x'] : 100,
                    'position_y' => isset($node['position_y']) ? $node['position_y'] : 100
                ));
            }
        }
        
        // Import edges
        if (isset($workflow_data['flow_data']['edges'])) {
            $workflow_model->update($workflow_id, array(
                'flow_data' => json_encode(array('edges' => $workflow_data['flow_data']['edges']))
            ));
        }
        
        return $workflow_id;
    }
}