# Nexus Platform API Documentation

## Overview

The Nexus Platform provides a comprehensive REST API for managing cognitive digital twins, agents, workflows, contacts, memory, and AI model orchestration. All API endpoints are prefixed with `/api/v1` and require authentication via Laravel Sanctum tokens.

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

### Sanctum Token Authentication

The API uses Laravel Sanctum for token-based authentication. Include the token in the `Authorization` header:

```http
Authorization: Bearer {token}
```

### Login

```http
POST /api/v1/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "user": { /* user object */ },
  "token": "sanctum-token-string"
}
```

### Register

```http
POST /api/v1/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Verify Token

```http
POST /api/v1/verify-token
```

**Headers:**
```http
Authorization: Bearer {token}
```

**Response:**
```json
{
  "valid": true,
  "user": { /* user object */ }
}
```

### Logout

```http
POST /api/v1/logout
```

**Headers:**
```http
Authorization: Bearer {token}
```

### Refresh Token

```http
POST /api/v1/refresh-token
```

**Headers:**
```http
Authorization: Bearer {token}
```

## Health Check

```http
GET /api/v1/health
```

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2024-01-01T00:00:00.000000Z",
  "app": "Nexus Platform"
}
```

## Standard Response Format

All API responses follow a consistent structure:

```json
{
  "success": true,
  "data": { /* response data */ },
  "message": "Operation completed successfully",
  "errors": null
}
```

## Error Responses

```json
{
  "success": false,
  "data": null,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Pagination

List endpoints support pagination via query parameters:

```http
GET /api/v1/contacts?page=1&per_page=20
```

**Response includes:**
```json
{
  "data": [ /* items */ ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 100
}
```

---

## Contacts Hub

### List Contacts

```http
GET /api/v1/contacts
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | int | Page number |
| `per_page` | int | Items per page |
| `search` | string | Search term |
| `type` | string | Filter by contact type |
| `is_active` | bool | Filter by active status |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "uuid-string",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "type": "client",
      "title": "CEO",
      "company": "Acme Corp",
      "avatar_url": "https://...",
      "metadata": {},
      "attributes": {},
      "is_active": true,
      "last_seen_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

### Create Contact

```http
POST /api/v1/contacts
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "type": "client",
  "title": "CEO",
  "company": "Acme Corp",
  "metadata": {"key": "value"},
  "attributes": {"custom": "data"}
}
```

### Get Contact

```http
GET /api/v1/contacts/{id}
```

### Update Contact

```http
PUT /api/v1/contacts/{id}
```

### Delete Contact

```http
DELETE /api/v1/contacts/{id}
```

### Import Contacts

```http
POST /api/v1/contacts/import
```

**Request Body:**
```json
{
  "contacts": [
    { /* contact objects */ }
  ]
}
```

### Export Contacts

```http
GET /api/v1/contacts/export
```

Returns CSV file download.

### Get Contact Memory

```http
GET /api/v1/contacts/{id}/memory
```

Returns all memories associated with a contact.

### Get Contact Rules

```http
GET /api/v1/contacts/{id}/rules
```

Returns automation rules for a contact.

### Get Contact Analytics

```http
GET /api/v1/contacts/{id}/analytics
```

Returns analytics data for a contact.

---

## Conversations Hub

### List Conversations

```http
GET /api/v1/conversations
```

### Create Conversation

```http
POST /api/v1/conversations
```

**Request Body:**
```json
{
  "contact_id": 1,
  "channel": "whatsapp",
  "subject": "Support Request"
}
```

### Get Conversation

```http
GET /api/v1/conversations/{id}
```

### Update Conversation

```http
PUT /api/v1/conversations/{id}
```

### Delete Conversation

```http
DELETE /api/v1/conversations/{id}
```

### Get Messages

```http
GET /api/v1/conversations/{id}/messages
```

Returns all messages in a conversation.

### Send Message

```http
POST /api/v1/conversations/{id}/send-message
```

**Request Body:**
```json
{
  "content": "Hello, how can I help?",
  "type": "text"
}
```

---

## Agents Hub

### List Agents

```http
GET /api/v1/agents
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Support Agent",
      "key": "support-agent",
      "description": "Handles support requests",
      "type": "specialized",
      "provider": "openai",
      "status": "idle",
      "settings": {},
      "metadata": {},
      "is_active": true,
      "last_executed_at": null,
      "execution_count": 0,
      "success_count": 0,
      "error_count": 0,
      "type_label": "Specialized Agent",
      "status_label": "Idle",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Create Agent

```http
POST /api/v1/agents
```

**Request Body:**
```json
{
  "name": "Support Agent",
  "key": "support-agent",
  "description": "Handles support requests",
  "type": "specialized",
  "provider": "openai",
  "settings": {"model": "gpt-4"},
  "metadata": {}
}
```

### Get Agent

```http
GET /api/v1/agents/{id}
```

### Update Agent

```http
PUT /api/v1/agents/{id}
```

### Delete Agent

```http
DELETE /api/v1/agents/{id}
```

### Execute Agent

```http
POST /api/v1/agents/{id}/execute
```

**Request Body:**
```json
{
  "input": "Process this request",
  "context": {"key": "value"}
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "result": "Agent execution result",
    "execution_time": 1.23
  }
}
```

### Get Agent Status

```http
GET /api/v1/agents/{id}/status
```

**Response:**
```json
{
  "status": "idle",
  "last_executed_at": null,
  "execution_count": 0,
  "success_rate": 0.0
}
```

---

## Workflows Hub

### List Workflows

```http
GET /api/v1/workflows
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Onboarding Workflow",
      "key": "onboarding",
      "description": "New user onboarding",
      "steps": [
        {"order": 1, "name": "Welcome", "status": "pending"}
      ],
      "trigger_type": "manual",
      "trigger_config": {},
      "status": "draft",
      "settings": {},
      "metadata": {},
      "is_active": true,
      "progress": 0,
      "total_steps": 1,
      "completed_steps": 0,
      "status_label": "Draft",
      "trigger_type_label": "Manual",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Create Workflow

```http
POST /api/v1/workflows
```

**Request Body:**
```json
{
  "name": "Onboarding Workflow",
  "key": "onboarding",
  "description": "New user onboarding",
  "steps": [
    {"order": 1, "name": "Welcome", "agent_id": 1}
  ],
  "trigger_type": "manual",
  "trigger_config": {},
  "settings": {},
  "metadata": {}
}
```

### Get Workflow

```http
GET /api/v1/workflows/{id}
```

### Update Workflow

```http
PUT /api/v1/workflows/{id}
```

### Delete Workflow

```http
DELETE /api/v1/workflows/{id}
```

### Execute Workflow

```http
POST /api/v1/workflows/{id}/execute
```

**Request Body:**
```json
{
  "input": {"contact_id": 1},
  "context": {}
}
```

### Get Workflow Progress

```http
GET /api/v1/workflows/{id}/progress
```

**Response:**
```json
{
  "progress": 50,
  "total_steps": 4,
  "completed_steps": 2,
  "current_step": {"order": 2, "name": "Process"}
}
```

### Get Workflow Templates

```http
GET /api/v1/workflows/templates
```

---

## Tasks Hub

### List Tasks

```http
GET /api/v1/tasks
```

### Create Task

```http
POST /api/v1/tasks
```

**Request Body:**
```json
{
  "agent_id": 1,
  "workflow_id": 1,
  "type": "processing",
  "priority": "high",
  "payload": {"data": "value"}
}
```

### Get Task

```http
GET /api/v1/tasks/{id}
```

### Update Task

```http
PUT /api/v1/tasks/{id}
```

### Delete Task

```http
DELETE /api/v1/tasks/{id}
```

### Cancel Task

```http
POST /api/v1/tasks/{id}/cancel
```

### Pause Task

```http
POST /api/v1/tasks/{id}/pause
```

### Resume Task

```http
POST /api/v1/tasks/{id}/resume
```

### Get Task Stats

```http
GET /api/v1/tasks/stats
```

**Response:**
```json
{
  "total": 100,
  "pending": 20,
  "running": 10,
  "completed": 60,
  "failed": 10
}
```

### Get Active Tasks

```http
GET /api/v1/tasks/active
```

### Get Queue Stats

```http
GET /api/v1/tasks/queue-stats
```

### Get Routing Stats

```http
GET /api/v1/tasks/routing-stats
```

---

## Memory Hub

### List Memories

```http
GET /api/v1/memories
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `contact_id` | int | Filter by contact |
| `type` | string | Filter by memory type |
| `source` | string | Filter by source |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "contact_id": 1,
      "conversation_id": null,
      "source": "conversation",
      "type": "semantic",
      "title": "Important fact",
      "content": "The contact prefers email communication",
      "vector": null,
      "metadata": {"confidence": 0.95},
      "tags": ["preference", "communication"],
      "expires_at": null,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Create Memory

```http
POST /api/v1/memories
```

**Request Body:**
```json
{
  "contact_id": 1,
  "conversation_id": null,
  "source": "manual",
  "type": "semantic",
  "title": "Important fact",
  "content": "The contact prefers email communication",
  "metadata": {"confidence": 0.95},
  "tags": ["preference", "communication"],
  "expires_at": "2025-12-31T00:00:00.000000Z"
}
```

### Get Memory

```http
GET /api/v1/memories/{id}
```

### Update Memory

```http
PUT /api/v1/memories/{id}
```

### Delete Memory

```http
DELETE /api/v1/memories/{id}
```

### Search Memories

```http
GET /api/v1/memories/search?q=preference&contact_id=1
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query |
| `contact_id` | int | Filter by contact |
| `type` | string | Filter by memory type |

### Index Memory

```http
POST /api/v1/memories/{id}/index
```

Indexes the memory for vector search.

---

## AI Models Hub

### List AI Models

```http
GET /api/v1/ai-models
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "GPT-4",
      "provider": "openai",
      "model_id": "gpt-4",
      "description": "Most capable OpenAI model",
      "max_tokens": 8192,
      "cost_per_1k_input": 0.03,
      "cost_per_1k_output": 0.06,
      "capabilities": ["chat", "completion"],
      "is_active": true,
      "priority": 1,
      "rate_limit": 100,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Create AI Model

```http
POST /api/v1/ai-models
```

**Request Body:**
```json
{
  "name": "GPT-4",
  "provider": "openai",
  "model_id": "gpt-4",
  "description": "Most capable OpenAI model",
  "max_tokens": 8192,
  "cost_per_1k_input": 0.03,
  "cost_per_1k_output": 0.06,
  "capabilities": ["chat", "completion"],
  "is_active": true,
  "priority": 1,
  "rate_limit": 100
}
```

### Get AI Model

```http
GET /api/v1/ai-models/{id}
```

### Update AI Model

```http
PUT /api/v1/ai-models/{id}
```

### Delete AI Model

```http
DELETE /api/v1/ai-models/{id}
```

### Test AI Model

```http
POST /api/v1/ai-models/{id}/test
```

**Request Body:**
```json
{
  "prompt": "Hello, world!"
}
```

### Execute AI Model

```http
POST /api/v1/ai-models/execute
```

**Request Body:**
```json
{
  "model_id": "gpt-4",
  "prompt": "Hello, world!",
  "max_tokens": 100
}
```

### Execute with Fallback

```http
POST /api/v1/ai-models/execute-with-fallback
```

**Request Body:**
```json
{
  "prompt": "Hello, world!",
  "fallback_models": ["gpt-3.5-turbo", "claude-2"]
}
```

### Select Model

```http
POST /api/v1/ai-models/select
```

**Request Body:**
```json
{
  "criteria": "cost",
  "task_type": "chat"
}
```

### Optimize Cost

```http
POST /api/v1/ai-models/optimize-cost
```

**Request Body:**
```json
{
  "usage_data": {"input_tokens": 1000, "output_tokens": 500}
}
```

### Route by Quality

```http
POST /api/v1/ai-models/route-quality
```

**Request Body:**
```json
{
  "prompt": "Complex reasoning task"
}
```

### Route by Speed

```http
POST /api/v1/ai-models/route-speed
```

**Request Body:**
```json
{
  "prompt": "Quick response needed"
}
```

### Get Providers

```http
GET /api/v1/ai-models/providers
```

**Response:**
```json
{
  "providers": ["openai", "anthropic", "google", "cohere"]
}
```

### Get Key Pool Status

```http
GET /api/v1/ai-models/key-pool
```

### Get Key Health

```http
GET /api/v1/ai-models/key-health
```

### Get Rate Limit Status

```http
GET /api/v1/ai-models/rate-limits
```

### Get Rotation Schedule

```http
GET /api/v1/ai-models/rotation-schedule
```

### Rotate Expired Keys

```http
POST /api/v1/ai-models/rotate-expired
```

### Get Fallback Chain Status

```http
GET /api/v1/ai-models/fallback-chain
```

### Get Budget Status

```http
GET /api/v1/ai-models/budget
```

---

## Settings Hub

### List Settings

```http
GET /api/v1/settings
```

### Create Setting

```http
POST /api/v1/settings
```

**Request Body:**
```json
{
  "key": "app.name",
  "value": "Nexus Platform",
  "type": "string",
  "group": "general",
  "is_public": true,
  "description": "Application name"
}
```

### Get Grouped Settings

```http
GET /api/v1/settings/grouped
```

Returns settings organized by group.

### Get Public Settings

```http
GET /api/v1/settings/public
```

### Bulk Update Settings

```http
PUT /api/v1/settings/bulk
```

**Request Body:**
```json
{
  "settings": [
    {"key": "app.name", "value": "New Name"}
  ]
}
```

### Get Setting

```http
GET /api/v1/settings/{key}
```

### Update Setting

```http
PUT /api/v1/settings/{key}
```

### Delete Setting

```http
DELETE /api/v1/settings/{key}
```

---

## Logs Hub

### List Logs

```http
GET /api/v1/logs
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `level` | string|array | Filter by log level |
| `category` | string|array | Filter by category |
| `source` | string | Filter by source |
| `user_id` | int | Filter by user |
| `from` | string | Start date |
| `to` | string | End date |
| `per_page` | int | Items per page |

### Get Log

```http
GET /api/v1/logs/{id}
```

### Delete Log

```http
DELETE /api/v1/logs/{id}
```

### Clear Logs

```http
POST /api/v1/logs/clear
```

**Request Body:**
```json
{
  "older_than_days": 30
}
```

### Get Log Stats

```http
GET /api/v1/logs/stats
```

**Response:**
```json
{
  "total": 1000,
  "by_level": {
    "debug": 500,
    "info": 300,
    "warning": 100,
    "error": 80,
    "critical": 15,
    "alert": 4,
    "emergency": 1
  },
  "by_category": {
    "auth": 200,
    "api": 300,
    "system": 500
  }
}
```

### Get Log Levels

```http
GET /api/v1/logs/levels
```

Returns available log levels.

### Get Log Categories

```http
GET /api/v1/logs/categories
```

Returns available log categories.

### Get Error Logs

```http
GET /api/v1/logs/errors
```

Returns error-level logs (error, critical, alert, emergency).

---

## User Profile

### Get Profile

```http
GET /api/v1/profile
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Update Profile

```http
PUT /api/v1/profile
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

### Update Avatar

```http
POST /api/v1/profile/avatar
```

**Request Body:** `multipart/form-data`

**Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `avatar` | file | Image file (jpg, png) |

---

## Webhooks

### WAHA Webhook

```http
POST /api/v1/webhooks/waha
```

Handles incoming WhatsApp messages from WAHA (WhatsApp HTTP API).

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse:

| Endpoint Type | Limit |
|--------------|-------|
| Authentication | 5 requests/minute |
| Standard API | 60 requests/minute |
| AI/LLM endpoints | 30 requests/minute |

Rate limit headers are included in responses:

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

## Versioning

The API is versioned via URL prefix: `/api/v1`. Future versions will be available at `/api/v2`, etc.

---

## Support

For API support, please refer to the project documentation or contact the development team.
