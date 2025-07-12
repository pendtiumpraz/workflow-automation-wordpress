# Workflow JSON Format Documentation

## Current Workflow Automation JSON Structure

```json
{
  "name": "My Workflow",
  "status": "active",
  "flow_data": {
    "nodes": [
      {
        "node_id": "node_1234567890_abc",
        "node_type": "webhook_start",
        "settings": "{\"webhook_key\":\"abc123\",\"method\":\"POST\"}",
        "position_x": 100,
        "position_y": 200
      },
      {
        "node_id": "node_1234567891_def",
        "node_type": "openai",
        "settings": "{\"prompt\":\"Process this data\",\"model\":\"gpt-3.5-turbo\"}",
        "position_x": 400,
        "position_y": 200
      }
    ],
    "edges": [
      {
        "source": "node_1234567890_abc",
        "target": "node_1234567891_def"
      }
    ]
  }
}
```

## n8n JSON Format

```json
{
  "name": "My workflow",
  "nodes": [
    {
      "parameters": {
        "httpMethod": "POST",
        "path": "webhook-path"
      },
      "id": "uuid-here",
      "name": "Webhook",
      "type": "n8n-nodes-base.webhook",
      "typeVersion": 1,
      "position": [250, 300]
    },
    {
      "parameters": {
        "resource": "completion",
        "model": "gpt-3.5-turbo",
        "prompt": "={{ $json[\"text\"] }}"
      },
      "id": "uuid-here-2",
      "name": "OpenAI",
      "type": "n8n-nodes-base.openAi",
      "typeVersion": 1,
      "position": [450, 300]
    }
  ],
  "connections": {
    "Webhook": {
      "main": [
        [
          {
            "node": "OpenAI",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  }
}
```

## Make.com (Integromat) JSON Format

```json
{
  "name": "My scenario",
  "flow": [
    {
      "id": 1,
      "module": "gateway:CustomWebHook",
      "version": 1,
      "parameters": {
        "hook": 123456,
        "maxResults": 1
      },
      "mapper": {},
      "metadata": {
        "designer": {
          "x": 0,
          "y": 0
        }
      }
    },
    {
      "id": 2,
      "module": "openai:CreateCompletion",
      "version": 1,
      "parameters": {
        "apiKey": "connection:openai"
      },
      "mapper": {
        "model": "gpt-3.5-turbo",
        "prompt": "{{1.text}}"
      },
      "metadata": {
        "designer": {
          "x": 300,
          "y": 0
        }
      }
    }
  ]
}
```

## Mapping Strategy

### Node Type Mappings

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

### Parameter Mappings

Each platform has different parameter structures that need to be mapped:

1. **Variables/Expressions**:
   - Our format: `{{node_id.field}}`
   - n8n: `={{ $node["NodeName"].json["field"] }}`
   - Make.com: `{{1.field}}` (using module ID)

2. **Connections**:
   - Our format: Simple source->target edges
   - n8n: Nested connection object with main/error outputs
   - Make.com: Sequential flow array

3. **Positions**:
   - Our format: `position_x`, `position_y`
   - n8n: `position: [x, y]`
   - Make.com: `metadata.designer.x/y`