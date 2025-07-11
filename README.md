# Workflow Automation WordPress Plugin

A powerful WordPress plugin that brings OpsGuide's workflow automation capabilities to WordPress. This plugin allows you to create visual workflows with various integrations including LINE, Google Sheets, LLMs, and more.

## 🚀 Current Progress: 75%

### ✅ Completed Features (75%)
- [x] Core plugin architecture and structure
- [x] Database schema and models
- [x] REST API endpoints
- [x] Node system (abstract class + implementations)
- [x] Workflow execution engine
- [x] Integration settings with encryption
- [x] Error handling and retry logic
- [x] Icon system for visual identification
- [x] Webhook handling system with security features
- [x] Node types:
  - [x] Webhook Start Node
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

### 🔄 In Progress (15%)
- [ ] React-based workflow builder UI (40% complete)
- [ ] WordPress admin interface (60% complete)
- [ ] AI nodes (OpenAI/Claude/Gemini) (20% complete)
- [ ] WordPress-specific nodes (10% complete)
- [ ] Workflow templates system (15% complete)
- [ ] Testing suite (10% complete)

### ❌ To Do (10%)
- [ ] Microsoft 365 integration
- [ ] HubSpot integration
- [ ] Notion integration
- [ ] Telegram/WhatsApp nodes
- [ ] Import/Export functionality
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

### 1.0.0 (Current Development - 75% Complete)
- Initial plugin architecture
- Core node system
- Basic integrations (Slack, Email, Google Sheets, LINE)
- Error handling system
- Webhook handling with security
- Admin interface improvements
- Visual workflow builder (in progress)
- AI nodes foundation (OpenAI, Claude, Gemini)
- WordPress-specific nodes foundation

---

**Note**: This plugin is currently under active development. Features and APIs may change.