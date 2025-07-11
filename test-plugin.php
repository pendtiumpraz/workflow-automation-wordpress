<?php
/**
 * Test script for Workflow Automation plugin
 * 
 * This script tests basic functionality of the plugin
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if plugin is active
if (!is_plugin_active('workflow-automation/workflow-automation.php')) {
    echo "Plugin is not active. Please activate the Workflow Automation plugin first.\n";
    exit;
}

echo "=== Workflow Automation Plugin Test ===\n\n";

// Test 1: Check if tables exist
echo "1. Checking database tables...\n";
global $wpdb;
$tables = array(
    'wa_workflows',
    'wa_nodes', 
    'wa_executions',
    'wa_webhooks',
    'wa_integration_settings'
);

foreach ($tables as $table) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    echo "   - $table_name: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// Test 2: Check if models can be loaded
echo "\n2. Testing model classes...\n";
try {
    $workflow_model = new Workflow_Model();
    echo "   - Workflow_Model: ✓ LOADED\n";
    
    $node_model = new Node_Model();
    echo "   - Node_Model: ✓ LOADED\n";
    
    $execution_model = new Execution_Model();
    echo "   - Execution_Model: ✓ LOADED\n";
} catch (Exception $e) {
    echo "   - Error loading models: " . $e->getMessage() . "\n";
}

// Test 3: Create a test workflow
echo "\n3. Creating test workflow...\n";
try {
    $workflow_data = array(
        'name' => 'Test Workflow ' . time(),
        'description' => 'This is a test workflow',
        'status' => 'inactive'
    );
    
    $workflow_id = $workflow_model->create($workflow_data);
    if ($workflow_id) {
        echo "   - Workflow created with ID: $workflow_id\n";
        
        // Test retrieving the workflow
        $workflow = $workflow_model->get($workflow_id);
        if ($workflow) {
            echo "   - Workflow retrieved successfully\n";
            echo "     Name: " . $workflow->name . "\n";
            echo "     Status: " . $workflow->status . "\n";
        }
    } else {
        echo "   - Failed to create workflow\n";
    }
} catch (Exception $e) {
    echo "   - Error creating workflow: " . $e->getMessage() . "\n";
}

// Test 4: Check REST API endpoints
echo "\n4. Checking REST API endpoints...\n";
$rest_server = rest_get_server();
$namespaces = $rest_server->get_namespaces();
if (in_array('wa/v1', $namespaces)) {
    echo "   - REST API namespace 'wa/v1': ✓ REGISTERED\n";
    
    // Get routes
    $routes = $rest_server->get_routes();
    $wa_routes = array_filter(array_keys($routes), function($route) {
        return strpos($route, '/wa/v1/') === 0;
    });
    
    echo "   - Found " . count($wa_routes) . " API routes:\n";
    foreach (array_slice($wa_routes, 0, 5) as $route) {
        echo "     * $route\n";
    }
    if (count($wa_routes) > 5) {
        echo "     ... and " . (count($wa_routes) - 5) . " more\n";
    }
} else {
    echo "   - REST API namespace 'wa/v1': ✗ NOT FOUND\n";
}

// Test 5: Check admin menu
echo "\n5. Checking admin menu...\n";
global $menu, $submenu;
$found_menu = false;
foreach ($menu as $menu_item) {
    if (isset($menu_item[2]) && $menu_item[2] === 'workflow-automation') {
        $found_menu = true;
        echo "   - Main menu: ✓ FOUND\n";
        break;
    }
}
if (!$found_menu) {
    echo "   - Main menu: ✗ NOT FOUND\n";
}

// Test 6: Check available node types
echo "\n6. Checking available node types...\n";
if (function_exists('wa_get_available_nodes')) {
    $nodes = wa_get_available_nodes();
    echo "   - Node categories: " . count($nodes) . "\n";
    foreach ($nodes as $category => $category_nodes) {
        echo "     * $category: " . count($category_nodes) . " nodes\n";
    }
} else {
    echo "   - Function wa_get_available_nodes: ✗ NOT FOUND\n";
}

echo "\n=== Test Complete ===\n";