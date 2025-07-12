# Workflow Automation WordPress Plugin

A powerful WordPress plugin that brings OpsGuide's workflow automation capabilities to WordPress. This plugin allows you to create visual workflows with various integrations including LINE, Google Sheets, LLMs, and more.

## 🚀 Current Progress: 90%

### ✅ Completed Features (90%)
- [x] Core plugin architecture and structure
- [x] Database schema and models
- [x] REST API endpoints
- [x] Node system (abstract class + implementations)
- [x] Workflow execution engine with error handling
- [x] Integration settings with AES-256-CBC encryption
- [x] Error handling and retry logic
- [x] Icon system for visual identification
- [x] Webhook handling system with security features
- [x] Modern UI/UX design for all admin pages
- [x] WordPress admin interface with fancy styling
- [x] AI nodes (OpenAI, Claude, Gemini)
- [x] WordPress-specific nodes (Post, User, Media)
- [x] Workflow templates system
- [x] Visual workflow builder with drag-and-drop
- [x] Node connection system with SVG paths
- [x] Import/Export functionality with n8n and Make.com compatibility
- [x] Node types:
  - [x] Webhook Start Node with authentication
  - [x] Email Node
  - [x] Slack Node
  - [x] HTTP Request Node
  - [x] Google Sheets Node
  - [x] LINE Messaging Node
  - [x] Filter Node
  - [x] Loop Node
  - [x] Transform Data Node
  - [x] Format Data Node
  - [x] Parse Data Node
  - [x] OpenAI GPT Node
  - [x] Claude AI Node
  - [x] Google Gemini Node
  - [x] WordPress Post Node
  - [x] WordPress User Node
  - [x] WordPress Media Node

### 🔄 In Progress (5%)
- [ ] Testing suite (20% complete)
- [ ] Documentation improvements (ongoing)

### ❌ To Do (5%)
- [ ] Microsoft 365 integration
- [ ] HubSpot integration
- [ ] Notion integration
- [ ] Telegram/WhatsApp nodes
- [ ] React-based workflow builder (future enhancement)
- [ ] Multi-language support
- [ ] Performance optimization
- [ ] Analytics dashboard
- [ ] Team collaboration features

## 📊 Current Architecture

```
workflow-automation/
├── workflow-automation.php          # Main plugin file
├── includes/
│   ├── class-workflow-automation.php     # Core plugin class
│   ├── class-workflow-automation-activator.php
│   ├── class-workflow-automation-deactivator.php
│   ├── class-workflow-automation-loader.php
│   ├── class-workflow-automation-i18n.php
│   ├── class-workflow-executor.php       # Workflow execution engine
│   ├── class-workflow-error-handler.php  # Error handling system
│   ├── class-node-icons.php             # Icon management
│   ├── admin/
│   │   ├── class-workflow-admin.php     # Admin functionality
│   │   └── views/
│   │       ├── workflows.php
│   │       ├── workflows-list.php       # Workflow list view
│   │       ├── workflow-builder.php     # Visual workflow builder
│   │       ├── workflow-new.php         # New workflow creation
│   │       ├── integrations.php         # Integration management
│   │       ├── executions-list.php      # Execution history
│   │       └── settings.php             # Plugin settings
│   ├── api/
│   │   ├── class-workflow-api.php       # Workflow REST API
│   │   ├── class-webhook-api.php        # Webhook REST API
│   │   ├── class-execution-api.php      # Execution REST API
│   │   ├── class-integration-api.php    # Integration REST API
│   │   └── class-node-api.php           # Node types REST API
│   ├── models/
│   │   ├── class-workflow-model.php
│   │   ├── class-node-model.php
│   │   ├── class-execution-model.php
│   │   ├── class-webhook-model.php
│   │   └── class-integration-settings-model.php
│   ├── class-webhook-handler.php        # Webhook processing
│   ├── class-workflow-templates.php     # Workflow templates
│   └── nodes/
│       ├── abstract-node.php            # Base node class
│       ├── webhook/
│       │   └── class-webhook-start-node.php
│       ├── integrations/
│       │   ├── class-email-node.php
│       │   ├── class-slack-node.php
│       │   ├── class-http-node.php
│       │   ├── class-google-sheets-node.php
│       │   └── class-line-node.php
│       ├── logic/
│       │   ├── class-filter-node.php
│       │   └── class-loop-node.php
│       ├── data/
│       │   ├── class-transform-node.php
│       │   ├── class-formatter-node.php
│       │   └── class-parser-node.php
│       ├── ai/                          # AI integration nodes
│       │   ├── class-openai-node.php
│       │   ├── class-claude-node.php
│       │   └── class-gemini-node.php
│       └── wordpress/                   # WordPress-specific nodes
│           ├── class-post-node.php
│           ├── class-user-node.php
│           └── class-media-node.php
├── admin/
│   ├── css/
│   │   └── workflow-icons.css
│   ├── js/
│   └── partials/
├── public/
│   ├── css/
│   └── js/
├── assets/
│   ├── css/
│   ├── js/
│   │   └── workflow-builder.js  # Workflow builder JavaScript
│   └── dist/                    # React build output
├── languages/                   # Translation files
└── tests/                      # PHPUnit tests

```

## 🏗️ Complete Architecture (Target)

```
workflow-automation/
├── Core System (✅ Complete)
│   ├── Plugin initialization
│   ├── Database management
│   ├── Model layer (CRUD operations)
│   ├── REST API layer
│   └── Error handling system
│
├── Node System (90% Complete)
│   ├── Abstract node framework ✅
│   ├── Trigger nodes ✅
│   ├── Action nodes (80%)
│   ├── Logic nodes ✅
│   ├── Data transformation nodes ✅
│   ├── AI nodes 🔄
│   └── WordPress nodes 🔄
│
├── Integration Layer (75% Complete)
│   ├── Integration settings ✅
│   ├── Credential encryption ✅
│   ├── OAuth handling 🔄
│   ├── Webhook processing ✅
│   └── API connectors (partial)
│
├── Workflow Engine (✅ Complete)
│   ├── Execution runtime
│   ├── Context management
│   ├── Variable interpolation
│   ├── Error recovery
│   └── Retry mechanisms
│
├── User Interface (40% Complete)
│   ├── Admin menu integration ✅
│   ├── Workflow list view ✅
│   ├── Visual workflow builder 🔄
│   ├── Node configuration UI 🔄
│   └── Execution monitoring 🔄
│
└── Advanced Features (5% Complete)
    ├── Workflow templates 🔄
    ├── Version control ❌
    ├── Team collaboration ❌
    ├── Analytics dashboard ❌
    └── Performance optimization ❌
```

## 🔧 Installation

1. Clone this repository to your WordPress plugins directory:
```bash
cd wp-content/plugins/
git clone https://github.com/pendtiumpraz/workflow-automation-wordpress.git workflow-automation
```

2. Activate the plugin through the WordPress admin panel

3. Configure integrations in Workflow Automation > Integrations

## 📦 Import/Export Workflows

The plugin supports importing and exporting workflows in multiple formats:

### Supported Formats
- **Native Format**: Our own JSON format for full feature compatibility
- **n8n**: Import/export workflows from [n8n.io](https://n8n.io)
- **Make.com**: Import/export scenarios from [Make.com](https://make.com) (formerly Integromat)

### How to Export
1. Go to **Workflow Automation > Workflows**
2. Click the **Download** button on any workflow
3. Select your desired format (Native, n8n, or Make.com)
4. The workflow will be downloaded as a JSON file

### How to Import
1. Go to **Workflow Automation > Workflows**
2. Click the **Import** button at the top
3. Select the format of your file
4. Upload the JSON file or paste the JSON content
5. Click **Import Workflow**

### Node Type Mappings
The plugin automatically converts between different platforms' node types:

| Our Type | n8n Type | Make.com Module |
|----------|----------|-----------------|
| webhook_start | n8n-nodes-base.webhook | gateway:CustomWebHook |
| http | n8n-nodes-base.httpRequest | http:ActionSendRequest |
| openai | n8n-nodes-base.openAi | openai:CreateCompletion |
| slack | n8n-nodes-base.slack | slack:CreateMessage |
| email | n8n-nodes-base.emailSend | email:ActionSendEmail |
| google_sheets | n8n-nodes-base.googleSheets | google-sheets:ActionAddRow |
| filter | n8n-nodes-base.if | builtin:BasicFeeder |
| loop | n8n-nodes-base.splitInBatches | builtin:BasicIterator |

### Example Import Files
Test import files are available in the `test-imports/` directory:
- `n8n-example.json` - Example n8n workflow
- `make-example.json` - Example Make.com scenario

## 🛠️ Development Setup

### Requirements
- PHP 7.4 or higher (compatible with PHP 8.2)
- WordPress 5.8 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 14+ (for building React components)

### Building React Components
```bash
cd workflow-automation
npm install
npm run build
```

### Running Tests
```bash
composer install
./vendor/bin/phpunit
```

## 📦 Available Node Types

### Triggers
- **Webhook Start**: Trigger workflows via webhooks

### Actions
- **Email**: Send emails via SMTP
- **Slack**: Send messages to Slack channels
- **HTTP Request**: Make HTTP API calls
- **Google Sheets**: Read/write spreadsheet data
- **LINE**: Send LINE messages (reply/push/broadcast)

### Logic
- **Filter**: Filter data based on conditions
- **Loop**: Iterate over arrays

### Data
- **Transform**: Manipulate data structure
- **Format**: Format data (JSON, CSV, XML, etc.)
- **Parse**: Parse structured data

## 🔌 Supported Integrations

- ✅ Slack
- ✅ Email (SMTP)
- ✅ Google Services (Sheets, Drive)
- ✅ LINE Official Account
- 🔄 OpenAI
- 🔄 Claude (Anthropic)
- 🔄 Google Gemini
- ❌ Microsoft 365
- ❌ Notion
- ❌ HubSpot
- ❌ Telegram
- ❌ WhatsApp Business

## 🚦 API Endpoints

- `GET /wp-json/wa/v1/workflows` - List workflows
- `POST /wp-json/wa/v1/workflows` - Create workflow
- `GET /wp-json/wa/v1/workflows/{id}` - Get workflow
- `PUT /wp-json/wa/v1/workflows/{id}` - Update workflow
- `DELETE /wp-json/wa/v1/workflows/{id}` - Delete workflow
- `POST /wp-json/wa/v1/workflows/{id}/execute` - Execute workflow
- `GET /wp-json/wa/v1/nodes/types` - Get available node types
- `GET /wp-json/wa/v1/integrations` - List integrations
- `POST /wp-json/wa/v1/integrations/test` - Test integration

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 👥 Credits

- **Author**: PendtiumPraz
- **Email**: pendtiumpraz@gmail.com
- **Based on**: OpsGuide Automation Platform

## 🔄 Version History

### 1.0.0 (Current Development - 85% Complete)
- Initial plugin architecture
- Core node system with 16+ node types
- Full integrations (Slack, Email, Google Sheets, LINE)
- Complete error handling and retry system
- Webhook handling with authentication and security
- Modern admin interface with professional UI/UX design
- Visual workflow builder (jQuery-based, React version in progress)
- Complete AI nodes (OpenAI, Claude, Gemini)
- Complete WordPress-specific nodes (Post, User, Media)
- Workflow templates system
- Webhook endpoints with rewrite rules
- Execution monitoring and history

---

**Note**: This plugin is currently under active development. Features and APIs may change.