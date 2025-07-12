# Workflow Automation Plugin - Usage Guide

## 🚀 Getting Started

### 1. Installation
- Upload the plugin to your WordPress plugins directory
- Activate the plugin from WordPress admin panel
- The plugin will create necessary database tables automatically

### 2. Setting Up Integrations

Before creating workflows, you need to configure your integrations:

1. Go to **Workflow Automation > Integrations**
2. Click on any integration card (e.g., Slack, Email, Google Sheets)
3. Fill in the required credentials:
   - **Email/SMTP**: SMTP server details
   - **Slack**: Bot token or webhook URL
   - **Google Services**: Service account JSON
   - **LINE**: Channel access token and secret
   - **OpenAI/Claude/Gemini**: API keys
4. Click **Save Integration**

### 3. Creating Your First Workflow

#### Step 1: Create a New Workflow
1. Go to **Workflow Automation > Workflows**
2. Click **Create New Workflow**
3. Enter a workflow name and description
4. Click **Create Workflow**

#### Step 2: Build Your Workflow
1. You'll be taken to the visual workflow builder
2. **Adding Nodes**:
   - Find nodes in the left sidebar (organized by category)
   - Drag any node from the sidebar to the canvas
   - Nodes will appear where you drop them
3. **Configuring Nodes**:
   - Click on a node to select it
   - Double-click a node to configure its settings
   - Fill in the required fields and save
4. **Connecting Nodes**:
   - Click and hold on the **output port** (right side circle) of a node
   - Drag to the **input port** (left side circle) of another node
   - Release to create a connection
   - The connection will appear as a curved line
   - Click on a connection line to delete it

#### Step 3: Activate Your Workflow
1. Use the toggle switch in the top-right corner
2. Click **Save** to save your changes
3. The workflow is now active and ready to run

### 4. Workflow Examples

#### Example 1: Webhook to Slack
1. Add a **Webhook Trigger** node
2. Add a **Slack Message** node
3. Connect them together
4. Configure the Slack node with your channel and message template
5. Save and activate the workflow
6. Copy the webhook URL from the trigger node
7. Send a POST request to test

#### Example 2: Google Sheets to Email
1. Add a **Google Sheets** node (configured to read data)
2. Add a **Filter** node (to filter specific rows)
3. Add an **Email** node
4. Connect them in sequence
5. Configure each node with appropriate settings
6. Save and activate

#### Example 3: LINE Bot with AI
1. Add a **Webhook Trigger** node (LINE webhook)
2. Add a **OpenAI/Claude** node for AI processing
3. Add a **LINE Reply** node
4. Connect them together
5. Configure AI prompt and LINE reply settings
6. Set the webhook URL in LINE Developers Console

### 5. Testing Workflows

1. Click the **Test** button in the workflow builder
2. Choose trigger type (Manual or Webhook)
3. Provide test data in JSON format
4. Click **Run Test**
5. Check the Executions page for results

### 6. Monitoring Executions

Go to **Workflow Automation > Executions** to:
- View all workflow executions
- Check execution status (completed/failed)
- See execution duration
- View detailed logs and error messages
- Retry failed executions

### 7. Webhook URLs

For webhook-triggered workflows:
- The webhook URL format is: `https://yoursite.com/workflow-webhook/{key}`
- For LINE webhooks: `https://yoursite.com/line-webhook/{key}`
- The key is automatically generated when you add a webhook trigger node
- You can find the full URL in the node configuration

### 8. Troubleshooting

#### Integration not saving?
- Check that all required fields are filled
- Verify your credentials are correct
- Check browser console for errors

#### Nodes not dragging to canvas?
- Ensure JavaScript is enabled
- Try refreshing the page
- Check browser console for jQuery UI errors

#### Workflow not executing?
- Verify the workflow is activated (toggle switch ON)
- Check integration credentials are valid
- Review execution logs for error details

#### Webhook not working?
- Ensure permalink settings are not "Plain"
- Try flushing permalinks (Settings > Permalinks > Save)
- Check the webhook URL is correct
- Verify any security tokens match

### 9. Best Practices

1. **Name workflows descriptively** - Use names that clearly indicate what the workflow does
2. **Test before activating** - Always test workflows with sample data first
3. **Use filters wisely** - Add filter nodes to prevent unnecessary processing
4. **Monitor executions** - Regularly check execution logs for failures
5. **Secure your webhooks** - Use security tokens for webhook authentication
6. **Document complex workflows** - Add descriptions to help others understand

### 10. Advanced Features

#### Using Variables
- Access previous node outputs using `{{node_id.field}}`
- Use in text fields for dynamic content

#### Error Handling
- Configure retry settings in node properties
- Set up error notification workflows

#### Conditional Logic
- Use Filter nodes for IF/THEN logic
- Chain multiple filters for complex conditions

---

For more help, visit the [GitHub repository](https://github.com/pendtiumpraz/workflow-automation-wordpress) or create an issue.