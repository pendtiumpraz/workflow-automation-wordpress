# Workflow Automation Plugin Architecture

## System Architecture Overview

```mermaid
graph TB
    subgraph "WordPress Core"
        WP[WordPress Core]
        DB[(MySQL Database)]
        REST[REST API]
    end
    
    subgraph "Workflow Automation Plugin"
        subgraph "Core Layer"
            MAIN[Main Plugin File]
            LOADER[Plugin Loader]
            I18N[Internationalization]
            ACT[Activator/Deactivator]
        end
        
        subgraph "Data Layer"
            MODELS[Models]
            MODELS --> WM[Workflow Model]
            MODELS --> NM[Node Model]
            MODELS --> EM[Execution Model]
            MODELS --> WHM[Webhook Model]
            MODELS --> ISM[Integration Settings Model]
        end
        
        subgraph "Business Logic"
            ENGINE[Workflow Executor]
            ERROR[Error Handler]
            NODES[Node System]
            ICONS[Icon Manager]
        end
        
        subgraph "API Layer"
            WAPI[Workflow API]
            NAPI[Node API]
            EAPI[Execution API]
            WHAPI[Webhook API]
            IAPI[Integration API]
        end
        
        subgraph "Node Types"
            TRIGGER[Trigger Nodes]
            ACTION[Action Nodes]
            LOGIC[Logic Nodes]
            DATA[Data Nodes]
        end
        
        subgraph "UI Layer"
            ADMIN[Admin Interface]
            BUILDER[Workflow Builder]
            ICONS_UI[Visual Icons]
        end
    end
    
    subgraph "External Services"
        SLACK[Slack API]
        GOOGLE[Google APIs]
        LINE[LINE API]
        EMAIL[SMTP Server]
        HTTP[External APIs]
    end
    
    WP --> MAIN
    MAIN --> LOADER
    LOADER --> ENGINE
    ENGINE --> NODES
    NODES --> TRIGGER
    NODES --> ACTION
    NODES --> LOGIC
    NODES --> DATA
    
    MODELS --> DB
    API --> REST
    ADMIN --> BUILDER
    
    ACTION --> SLACK
    ACTION --> GOOGLE
    ACTION --> LINE
    ACTION --> EMAIL
    ACTION --> HTTP
```

## Data Flow Architecture

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Admin UI
    participant API as REST API
    participant EX as Workflow Executor
    participant N as Node
    participant EXT as External Service
    participant DB as Database
    
    U->>UI: Create/Edit Workflow
    UI->>API: Save Workflow
    API->>DB: Store Configuration
    
    Note over U,DB: Workflow Execution
    
    U->>API: Trigger Workflow
    API->>EX: Start Execution
    EX->>DB: Create Execution Record
    
    loop For Each Node
        EX->>N: Execute Node
        N->>EXT: Call External API
        EXT-->>N: Return Result
        N-->>EX: Return Output
        EX->>DB: Update Execution
    end
    
    EX-->>API: Execution Complete
    API-->>U: Return Result
```

## Database Schema

```mermaid
erDiagram
    WORKFLOWS ||--o{ NODES : contains
    WORKFLOWS ||--o{ EXECUTIONS : has
    WORKFLOWS ||--o{ WEBHOOKS : triggers
    NODES ||--o{ NODE_CONNECTIONS : connects
    EXECUTIONS ||--o{ EXECUTION_LOGS : logs
    
    WORKFLOWS {
        bigint id PK
        string name
        text description
        json flow_data
        string status
        json settings
        datetime created_at
        datetime updated_at
    }
    
    NODES {
        bigint id PK
        bigint workflow_id FK
        string node_id
        string node_type
        json settings
        int position_x
        int position_y
        datetime created_at
        datetime updated_at
    }
    
    EXECUTIONS {
        bigint id PK
        bigint workflow_id FK
        string trigger_type
        json trigger_data
        string status
        json execution_data
        text error_message
        datetime started_at
        datetime completed_at
    }
    
    WEBHOOKS {
        bigint id PK
        bigint workflow_id FK
        string webhook_key
        string node_id
        boolean is_active
        datetime created_at
        datetime last_triggered
    }
    
    INTEGRATION_SETTINGS {
        bigint id PK
        string integration_type
        string name
        json settings_encrypted
        boolean is_active
        datetime created_at
        datetime updated_at
    }
```

## Node Architecture

```mermaid
classDiagram
    class AbstractNode {
        <<abstract>>
        #string id
        #array settings
        #string type
        +__construct(id, settings)
        +get_type() string
        +get_id() string
        +get_options() array
        +get_settings_fields() array
        +get_error_handling_fields() array
        +execute(context, previous_data) mixed
        +validate_settings() bool|WP_Error
        #replace_variables(text, context) string
        #get_setting(key, default) mixed
        #log(message, level) void
        #http_request(url, args) array
    }
    
    class WebhookStartNode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
    }
    
    class EmailNode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
        -send_smtp_email(to, subject, message, headers) bool
    }
    
    class HTTPNode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
    }
    
    class GoogleSheetsNode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
        -get_access_token(settings) string
        -read_data() array
        -write_data() array
    }
    
    class LINENode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
        -send_reply() array
        -send_push() array
        -send_broadcast() array
    }
    
    class TransformNode {
        +get_type() string
        +get_options() array
        +get_settings_fields() array
        +execute(context, previous_data) mixed
        -apply_transformation() mixed
    }
    
    AbstractNode <|-- WebhookStartNode
    AbstractNode <|-- EmailNode
    AbstractNode <|-- HTTPNode
    AbstractNode <|-- GoogleSheetsNode
    AbstractNode <|-- LINENode
    AbstractNode <|-- TransformNode
```

## Component Responsibilities

### Core Components

1. **Plugin Loader** (`class-workflow-automation-loader.php`)
   - Manages all WordPress hooks and filters
   - Coordinates plugin initialization

2. **Workflow Executor** (`class-workflow-executor.php`)
   - Executes workflows from start to finish
   - Manages execution context
   - Handles node connections and flow

3. **Error Handler** (`class-workflow-error-handler.php`)
   - Implements retry logic with backoff strategies
   - Classifies errors and determines retryability
   - Logs errors to database
   - Sends notifications on failures

### API Layer

1. **Workflow API** (`class-workflow-api.php`)
   - CRUD operations for workflows
   - Workflow execution triggers

2. **Node API** (`class-node-api.php`)
   - Provides node type information
   - Node validation endpoints

3. **Integration API** (`class-integration-api.php`)
   - Manages integration configurations
   - Tests integration connections

### Node System

1. **Abstract Node** (`abstract-node.php`)
   - Base class for all nodes
   - Common functionality (variable replacement, logging)
   - Error handling fields

2. **Node Types**
   - **Triggers**: Webhook Start
   - **Actions**: Email, Slack, HTTP, Google Sheets, LINE
   - **Logic**: Filter, Loop
   - **Data**: Transform, Format, Parse

### Security Features

1. **Data Encryption**
   - Sensitive integration settings encrypted using WordPress salts
   - AES-256-CBC encryption for API keys and tokens

2. **Permission Checks**
   - All API endpoints require `manage_options` capability
   - Nonce verification for forms

3. **Input Validation**
   - All user inputs sanitized
   - JSON validation for structured data

## Deployment Architecture

```
WordPress Installation
├── wp-content/
│   └── plugins/
│       └── workflow-automation/
│           ├── Plugin files
│           └── Assets
└── wp-admin/
    └── Workflow Automation Menu
```

## Performance Considerations

1. **Asynchronous Execution**
   - Workflows scheduled via WordPress cron
   - Non-blocking execution

2. **Database Optimization**
   - Indexed columns for fast lookups
   - Cleanup of old execution records

3. **Error Recovery**
   - Automatic retry with exponential backoff
   - Graceful degradation on failures

## Future Architecture Enhancements

1. **Scalability**
   - Queue system for high-volume workflows
   - Distributed execution support

2. **Monitoring**
   - Real-time execution tracking
   - Performance metrics dashboard

3. **Advanced Features**
   - Workflow versioning
   - A/B testing capabilities
   - Advanced analytics