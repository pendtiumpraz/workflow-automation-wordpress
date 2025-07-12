<?php
/**
 * REST API Test Page
 * 
 * Place this file in your WordPress root directory and access it directly
 * to test if the Workflow Automation REST API is working
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and has permission
if (!current_user_can('manage_options')) {
    die('You need to be logged in as an administrator to view this page.');
}

// Get the REST API URL
$rest_url = get_rest_url();
$wa_api_url = $rest_url . 'wa/v1/';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Workflow Automation API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        pre {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #005a87;
        }
    </style>
</head>
<body>
    <h1>Workflow Automation REST API Test</h1>
    
    <div class="test-section">
        <h2>API Information</h2>
        <p><strong>REST URL:</strong> <?php echo esc_html($rest_url); ?></p>
        <p><strong>WA API URL:</strong> <?php echo esc_html($wa_api_url); ?></p>
        <p><strong>Current User:</strong> <?php echo wp_get_current_user()->user_login; ?></p>
        <p><strong>Nonce:</strong> <span id="nonce"><?php echo wp_create_nonce('wp_rest'); ?></span></p>
    </div>
    
    <div class="test-section">
        <h2>Registered REST Routes</h2>
        <?php
        $routes = rest_get_server()->get_routes();
        $wa_routes = array_filter($routes, function($route) {
            return strpos($route, '/wa/v1') !== false;
        }, ARRAY_FILTER_USE_KEY);
        
        if (empty($wa_routes)) {
            echo '<p class="error">No Workflow Automation routes found!</p>';
        } else {
            echo '<ul>';
            foreach ($wa_routes as $route => $handlers) {
                echo '<li>' . esc_html($route) . '</li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>API Tests</h2>
        <button onclick="testAPI('/nodes/types')">Test /nodes/types</button>
        <button onclick="testAPI('/nodes/types/email/schema')">Test /nodes/types/email/schema</button>
        <button onclick="testAPI('/nodes/categories')">Test /nodes/categories</button>
        <div id="test-results"></div>
    </div>
    
    <div class="test-section">
        <h2>Node Types Check</h2>
        <?php
        // Try to load the workflow executor
        $plugin_dir = plugin_dir_path(__FILE__);
        
        if (file_exists($plugin_dir . 'includes/class-workflow-executor.php')) {
            require_once $plugin_dir . 'includes/class-workflow-executor.php';
            $executor = new Workflow_Executor();
            $node_types = $executor->get_available_node_types();
            
            echo '<p>Found ' . count($node_types) . ' node types:</p>';
            echo '<ul>';
            foreach ($node_types as $type => $class) {
                echo '<li><strong>' . esc_html($type) . '</strong>: ' . esc_html($class) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="error">Could not load Workflow Executor class.</p>';
        }
        ?>
    </div>
    
    <script>
    function testAPI(endpoint) {
        const baseUrl = '<?php echo esc_js($wa_api_url); ?>';
        const nonce = document.getElementById('nonce').textContent;
        const resultsDiv = document.getElementById('test-results');
        
        resultsDiv.innerHTML = '<p>Testing ' + endpoint + '...</p>';
        
        fetch(baseUrl + endpoint.replace(/^\//, ''), {
            method: 'GET',
            headers: {
                'X-WP-Nonce': nonce,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            resultsDiv.innerHTML += '<p>Status: ' + response.status + ' ' + response.statusText + '</p>';
            resultsDiv.innerHTML += '<p>Content-Type: ' + contentType + '</p>';
            
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        })
        .then(data => {
            resultsDiv.innerHTML += '<h3>Response:</h3>';
            resultsDiv.innerHTML += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            resultsDiv.innerHTML += '<p class="error">Error: ' + error.message + '</p>';
        });
    }
    </script>
</body>
</html>