# Nexus Platform - Database Schema Documentation

## Table of Contents

1. [Overview](#1-overview)
2. [Entity Relationship Diagram](#2-entity-relationship-diagram)
3. [Table Reference](#3-table-reference)
4. [Indexes & Constraints](#4-indexes--constraints)
5. [Migration History](#5-migration-history)

---

## 1. Overview

The Nexus platform uses **MySQL 8.0+** as its primary database with **Redis** for caching and sessions. The schema consists of **20+ tables** organized into functional domains.

### 1.1 Database Statistics

| Metric | Value |
|--------|-------|
| **Total Tables** | 20 |
| **Core Domain Tables** | 9 |
| **Agent Tables** | 5 |
| **Memory Tables** | 4 |
| **System Tables** | 5 |
| **Total Columns** | 200+ |

### 1.2 Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| **Tables** | Plural, snake_case | `contact_notes` |
| **Columns** | snake_case | `created_at` |
| **Foreign Keys** | `{table}_id` | `contact_id` |
| **Indexes** | `{column}_index` | `email_index` |
| **Primary Keys** | `id` (bigint) | Auto-increment |

---

## 2. Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        DATABASE SCHEMA                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐               │
│  │   users      │  │   contacts   │  │ conversations│               │
│  │              │  │              │  │              │               │
│  │ • id         │  │ • id         │  │ • id         │               │
│  │ • name       │  │ • uuid       │  │ • contact_id │──┐            │
│  │ • email      │  │ • user_id    │  │ • topic_id   │  │            │
│  │ • password   │  │ • phone      │  │ • status     │  │            │
│  │ • created_at │  │ • name       │  │ • metadata   │  │            │
│  └──────────────┘  │ • type       │  └──────────────┘  │            │
│         │           │ • metadata   │         │          │            │
│         │           └──────────────┘         │          │            │
│         │                  │                 │          │            │
│         │                  │                 │          │            │
│  ┌──────┴──────┐   ┌───────┴───────┐   ┌──────┴───────┐  │            │
│  │   sessions  │   │  contact_     │   │ conversation │  │            │
│  │             │   │  rules        │   │ _sessions    │  │            │
│  │ • id        │   │               │   │              │  │            │
│  │ • user_id   │   │ • id          │   │ • id         │  │            │
│  │ • ip        │   │ • contact_id  │   │ • conv_id    │  │            │
│  │ • payload   │   │ • rule        │   │ • status     │  │            │
│  └──────────────┘   │ • priority    │   └──────────────┘  │            │
│                     └──────────────┘                     │            │
│                                                             │            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │            │
│  │   messages   │  │ contact_notes│  │ contact_tags │     │            │
│  │              │  │              │  │              │     │            │
│  │ • id         │  │ • id         │  │ • id         │     │            │
│  │ • conv_id    │  │ • contact_id │  │ • contact_id │     │            │
│  │ • sender     │  │ • note       │  │ • name       │     │            │
│  │ • content    │  │ • summary    │  │ • color      │     │            │
│  │ • status     │  │ • is_pinned  │  └──────────────┘     │            │
│  └──────────────┘  └──────────────┘                       │            │
│         │                                                    │            │
│         │                                                    │            │
│  ┌──────┴──────┐  ┌──────────────┐  ┌──────────────┐      │            │
│  │ contact_    │  │   agents     │  │  agent_tasks │      │            │
│  │ custom_fields│  │              │  │              │      │            │
│  │             │  │ • id         │  │ • id         │      │            │
│  │ • id        │  │ • name       │  │ • agent_id   │      │            │
│  │ • contact_id│  │ • key        │  │ • workflow_id│      │            │
│  │ • field_key │  │ • type       │  │ • title      │      │            │
│  │ • value     │  │ • status     │  │ • status     │      │            │
│  └──────────────┘  └──────────────┘  └──────────────┘      │            │
│                            │                    │         │            │
│                            │                    │         │            │
│                     ┌──────┴──────┐      ┌──────┴──────┐  │            │
│                     │ agent_tools │      │ task_steps  │  │            │
│                     │             │      │             │  │            │
│                     │ • id        │      │ • id        │  │            │
│                     │ • agent_id  │      │ • task_id   │  │            │
│                     │ • name      │      │ • step_order│  │            │
│                     │ • type      │      │ • status    │  │            │
│                     └──────────────┘      └──────────────┘  │            │
│                                                             │            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │            │
│  │   memories   │  │ structured_  │  │   graph_     │      │            │
│  │              │  │  memories    │  │   nodes      │      │            │
│  │ • id         │  │              │  │              │      │            │
│  │ • contact_id │  │ • id         │  │ • id         │      │            │
│  │ • source     │  │ • contact_id │  │ • label      │      │            │
│  │ • type       │  │ • fact_type  │  │ • type       │      │            │
│  │ • content    │  │ • data       │  │ • related_id │      │            │
│  │ • vector     │  └──────────────┘  └──────────────┘      │            │
│  └──────────────┘         │                    │           │            │
│         │                  │                    │           │            │
│         │                  │                    │           │            │
│  ┌──────┴──────┐     ┌──────┴──────┐     ┌──────┴──────┐    │            │
│  │   settings  │     │    logs     │     │  ai_models  │    │            │
│  │             │     │             │     │             │    │            │
│  │ • id        │     │ • id        │     │ • id        │    │            │
│  │ • key       │     │ • level     │     │ • name      │    │            │
│  │ • value     │     │ • message   │     │ • provider  │    │            │
│  │ • type      │     │ • context   │     │ • model_id  │    │            │
│  │ • group     │     │ • user_id   │     │ • status    │    │            │
│  └──────────────┘     └──────────────┘     └──────────────┘    │            │
│                                                             │            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │            │
│  │   api_keys   │  │  workflows   │  │  graph_edges │       │            │
│  │              │  │              │  │              │       │            │
│  │ • id         │  │ • id         │  │ • id         │       │            │
│  │ • name       │  │ • name       │  │ • from_node  │       │            │
│  │ • key        │  │ • key        │  │ • to_node    │       │            │
│  │ • type       │  │ • steps      │  │ • label      │       │            │
│  │ • permissions│  │ • status     │  └──────────────┘       │            │
│  └──────────────┘  └──────────────┘                          │            │
│                                                                 │            │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Table Reference

### 3.1 Core Domain Tables

#### `users`

User accounts for platform access.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | User's full name |
| `email` | varchar(255) | Unique, Not null | Login email |
| `email_verified_at` | timestamp | Nullable | Email verification timestamp |
| `password` | varchar(255) | Not null | Hashed password |
| `remember_token` | varchar(100) | Nullable | Remember me token |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Indexes:**
- `users_email_unique` (email)

---

#### `contacts`

Contact profiles representing people in the user's network.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `uuid` | char(36) | Unique, Nullable | Public identifier |
| `user_id` | bigint | FK → users.id, Nullable | Owner user |
| `phone` | varchar(255) | Index, Nullable | Phone number |
| `name` | varchar(255) | Nullable | Contact name |
| `email` | varchar(255) | Index, Nullable | Email address |
| `type` | varchar(255) | Default: 'contact' | Contact type |
| `title` | varchar(255) | Nullable | Job title |
| `company` | varchar(255) | Nullable | Company name |
| `avatar_url` | varchar(255) | Nullable | Profile image URL |
| `metadata` | json | Nullable | Flexible metadata |
| `attributes` | json | Nullable | Custom attributes |
| `is_active` | boolean | Default: true | Active status |
| `last_seen_at` | timestamp | Nullable | Last interaction |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Indexes:**
- `contacts_uuid_unique` (uuid)
- `contacts_phone_index` (phone)
- `contacts_email_index` (email)
- `contacts_user_id_index` (user_id)
- `contacts_type_index` (type)

**Contact Types:**
- `contact` - Generic contact
- `client` - Business client
- `family` - Family member
- `friend` - Personal friend
- `fiancée` - Fiancée/Fiancé
- `partner` - Business partner
- `prospect` - Sales prospect
- `vendor` - Vendor/Supplier

---

#### `conversations`

Conversation threads between the user and contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id, Nullable | Associated contact |
| `topic_id` | bigint | FK → topics.id, Nullable | Conversation topic |
| `title` | varchar(255) | Nullable | Conversation title |
| `status` | varchar(255) | Default: 'open' | Status |
| `metadata` | json | Nullable | Flexible metadata |
| `last_message_at` | timestamp | Nullable | Last message timestamp |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Indexes:**
- `conversations_contact_id_index` (contact_id)
- `conversations_topic_id_index` (topic_id)
- `conversations_status_index` (status)

---

#### `conversation_sessions`

Session tracking for conversations (Nexus-specific, separate from Laravel sessions).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `conversation_id` | bigint | FK → conversations.id | Parent conversation |
| `name` | varchar(255) | Nullable | Session name |
| `status` | varchar(255) | Default: 'active' | Session status |
| `source` | varchar(255) | Nullable | Session source |
| `metadata` | json | Nullable | Flexible metadata |
| `started_at` | timestamp | Nullable | Session start |
| `ended_at` | timestamp | Nullable | Session end |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `messages`

Individual messages within conversations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `conversation_id` | bigint | FK → conversations.id | Parent conversation |
| `sender_type` | varchar(255) | Default: 'contact' | Sender type |
| `sender_id` | varchar(255) | Nullable | Sender identifier |
| `direction` | varchar(255) | Default: 'inbound' | Message direction |
| `content_type` | varchar(255) | Default: 'text' | Content type |
| `content` | longtext | Nullable | Message content |
| `metadata` | json | Nullable | Flexible metadata |
| `status` | varchar(255) | Default: 'delivered' | Delivery status |
| `sent_at` | timestamp | Nullable | Sent timestamp |
| `received_at` | timestamp | Nullable | Received timestamp |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Indexes:**
- `messages_conversation_id_index` (conversation_id)
- `messages_sender_type_index` (sender_type)
- `messages_status_index` (status)

---

#### `contact_rules`

Automation rules for contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id | Associated contact |
| `rule` | text | Not null | Rule definition |
| `priority` | int | Default: 50 | Rule priority |
| `is_active` | boolean | Default: true | Active status |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `contact_notes`

Notes attached to contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id | Associated contact |
| `user_id` | bigint | FK → users.id, Nullable | Note author |
| `note` | text | Not null | Note content |
| `summary` | text | Nullable | AI-generated summary |
| `is_pinned` | boolean | Default: false | Pinned status |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `contact_tags`

Tags for categorizing contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id | Associated contact |
| `name` | varchar(255) | Not null | Tag name |
| `color` | varchar(255) | Nullable | Tag color |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Unique:** `contact_id` + `name`

---

#### `contact_custom_fields`

Custom fields for contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id | Associated contact |
| `field_key` | varchar(255) | Not null | Field identifier |
| `label` | varchar(255) | Nullable | Field label |
| `value` | text | Nullable | Field value |
| `type` | varchar(255) | Default: 'string' | Field type |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Unique:** `contact_id` + `field_key`

---

#### `topics`

Conversation topics/categories.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | Topic name |
| `slug` | varchar(255) | Unique, Nullable | URL slug |
| `category` | varchar(255) | Nullable | Topic category |
| `description` | text | Nullable | Topic description |
| `is_active` | boolean | Default: true | Active status |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

### 3.2 Agent Tables

#### `agents`

AI agent definitions and configurations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | Agent name |
| `key` | varchar(255) | Unique | Agent identifier |
| `description` | text | Nullable | Agent description |
| `provider` | varchar(255) | Nullable | AI provider |
| `type` | varchar(255) | Default: 'reflection' | Agent type |
| `status` | varchar(255) | Default: 'active' | Agent status |
| `settings` | json | Nullable | Agent settings |
| `metadata` | json | Nullable | Flexible metadata |
| `is_active` | boolean | Default: true | Active status |
| `last_executed_at` | timestamp | Nullable | Last execution |
| `execution_count` | int | Default: 0 | Total executions |
| `success_count` | int | Default: 0 | Successful executions |
| `error_count` | int | Default: 0 | Failed executions |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Agent Types:**
- `reflection` - Reflection agent
- `team` - Team agent
- `autonomous` - Autonomous agent
- `specialized` - Specialized agent
- `supervisor` - Supervisor agent

---

#### `agent_tools`

Tools available to agents.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `agent_id` | bigint | FK → agents.id | Parent agent |
| `name` | varchar(255) | Not null | Tool name |
| `type` | varchar(255) | Default: 'tool' | Tool type |
| `description` | text | Nullable | Tool description |
| `metadata` | json | Nullable | Flexible metadata |
| `is_active` | boolean | Default: true | Active status |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `agent_skills`

Skills possessed by agents.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `agent_id` | bigint | FK → agents.id | Parent agent |
| `name` | varchar(255) | Not null | Skill name |
| `category` | varchar(255) | Nullable | Skill category |
| `level` | varchar(255) | Default: 'basic' | Skill level |
| `status` | varchar(255) | Default: 'active' | Skill status |
| `description` | text | Nullable | Skill description |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Unique:** `agent_id` + `name`

---

#### `agent_tasks`

Tasks assigned to agents.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `agent_id` | bigint | FK → agents.id, Nullable | Assigned agent |
| `workflow_id` | bigint | FK → workflows.id, Nullable | Parent workflow |
| `title` | varchar(255) | Not null | Task title |
| `description` | text | Nullable | Task description |
| `status` | varchar(255) | Default: 'pending' | Task status |
| `priority` | int | Default: 50 | Task priority |
| `progress` | int | Default: 0 | Completion progress |
| `due_at` | timestamp | Nullable | Due date |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `task_steps`

Steps within agent tasks.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `agent_task_id` | bigint | FK → agent_tasks.id | Parent task |
| `title` | varchar(255) | Not null | Step title |
| `description` | text | Nullable | Step description |
| `step_order` | int | Default: 0 | Step sequence |
| `status` | varchar(255) | Default: 'pending' | Step status |
| `completed_at` | timestamp | Nullable | Completion timestamp |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

### 3.3 Memory Tables

#### `memories`

General memory storage for contacts and conversations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id, Nullable | Associated contact |
| `conversation_id` | bigint | FK → conversations.id, Nullable | Associated conversation |
| `source` | varchar(255) | Nullable | Memory source |
| `type` | varchar(255) | Default: 'memory' | Memory type |
| `title` | varchar(255) | Nullable | Memory title |
| `content` | longtext | Nullable | Memory content |
| `vector` | json | Nullable | Vector embedding |
| `metadata` | json | Nullable | Flexible metadata |
| `tags` | json | Nullable | Memory tags |
| `expires_at` | timestamp | Nullable | Expiration timestamp |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Memory Types:**
- `semantic` - Semantic/factual memory
- `episodic` - Event-based memory
- `working` - Temporary working memory
- `procedural` - Process memory

---

#### `structured_memories`

Structured fact storage for contacts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `contact_id` | bigint | FK → contacts.id, Nullable | Associated contact |
| `fact_type` | varchar(255) | Index | Fact category |
| `data` | longtext | Nullable | Fact data (JSON) |
| `metadata` | json | Nullable | Flexible metadata |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `graph_nodes`

Nodes in the relationship graph memory.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `label` | varchar(255) | Index | Node label |
| `type` | varchar(255) | Index | Node type |
| `related_id` | bigint | Index, Nullable | Related entity ID |
| `related_type` | varchar(255) | Nullable | Related entity type |
| `properties` | json | Nullable | Node properties |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Node Types:**
- `contact` - Contact node
- `topic` - Topic node
- `concept` - Concept node
- `event` - Event node

---

#### `graph_edges`

Edges connecting nodes in the relationship graph.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `from_node` | bigint | FK → graph_nodes.id | Source node |
| `to_node` | bigint | FK → graph_nodes.id | Target node |
| `label` | varchar(255) | Index | Relationship type |
| `properties` | json | Nullable | Edge properties |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Edge Labels:**
- `knows` - Knows relationship
- `related_to` - General relationship
- `works_at` - Employment
- `member_of` - Group membership
- `mentioned` - Mentioned in

---

### 3.4 System Tables

#### `settings`

Application configuration settings.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `key` | varchar(255) | Unique, Not null | Setting key |
| `value` | text | Nullable | Setting value |
| `type` | varchar(255) | Default: 'string' | Value type |
| `group` | varchar(255) | Nullable | Setting group |
| `description` | text | Nullable | Setting description |
| `is_public` | boolean | Default: false | Public visibility |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Setting Types:**
- `string` - Text value
- `integer` - Numeric value
- `boolean` - True/False
- `json` - JSON object
- `text` - Long text

**Setting Groups:**
- `general` - General settings
- `security` - Security settings
- `ai` - AI configuration
- `notifications` - Notification settings
- `integrations` - External integrations
- `ui` - User interface

---

#### `logs`

Application log entries.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `level` | varchar(255) | Not null | Log level |
| `channel` | varchar(255) | Nullable | Log channel |
| `message` | text | Not null | Log message |
| `context` | json | Nullable | Log context |
| `type` | varchar(255) | Default: 'application' | Log type |
| `user_id` | bigint | FK → users.id, Nullable | Associated user |
| `related_id` | unsigned bigint | Nullable | Related entity ID |
| `related_type` | varchar(255) | Nullable | Related entity type |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Log Levels:**
- `debug` - Debug information
- `info` - Informational
- `notice` - Notice
- `warning` - Warning
- `error` - Error
- `critical` - Critical
- `alert` - Alert
- `emergency` - Emergency

**Log Categories:**
- `auth` - Authentication
- `security` - Security events
- `api` - API requests
- `workflow` - Workflow execution
- `agent` - Agent activity
- `ai` - AI operations
- `system` - System events
- `database` - Database operations
- `cache` - Cache operations
- `queue` - Queue operations

---

#### `ai_models`

AI model configurations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | Model name |
| `provider` | varchar(255) | Nullable | AI provider |
| `external_id` | varchar(255) | Unique, Nullable | Provider model ID |
| `description` | text | Nullable | Model description |
| `capabilities` | json | Nullable | Model capabilities |
| `metadata` | json | Nullable | Flexible metadata |
| `status` | varchar(255) | Default: 'active' | Model status |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `api_keys`

API key management for service-to-service authentication.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | Key name |
| `key` | varchar(255) | Unique, Not null | API key value |
| `type` | varchar(255) | Default: 'api' | Key type |
| `permissions` | json | Nullable | Key permissions |
| `last_used_at` | timestamp | Nullable | Last usage |
| `expires_at` | timestamp | Nullable | Expiration date |
| `is_active` | boolean | Default: true | Active status |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

---

#### `workflows`

Workflow definitions for multi-step operations.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint | PK, Auto-increment | Primary key |
| `name` | varchar(255) | Not null | Workflow name |
| `key` | varchar(255) | Unique | Workflow identifier |
| `description` | text | Nullable | Workflow description |
| `steps` | json | Nullable | Workflow steps |
| `trigger_type` | varchar(255) | Default: 'manual' | Trigger type |
| `trigger_config` | json | Nullable | Trigger configuration |
| `status` | varchar(255) | Default: 'draft' | Workflow status |
| `settings` | json | Nullable | Workflow settings |
| `metadata` | json | Nullable | Flexible metadata |
| `is_active` | boolean | Default: true | Active status |
| `last_executed_at` | timestamp | Nullable | Last execution |
| `execution_count` | int | Default: 0 | Total executions |
| `success_count` | int | Default: 0 | Successful executions |
| `error_count` | int | Default: 0 | Failed executions |
| `created_at` | timestamp | Nullable | Creation timestamp |
| `updated_at` | timestamp | Nullable | Update timestamp |

**Trigger Types:**
- `manual` - Manual trigger
- `scheduled` - Scheduled trigger
- `event` - Event trigger
- `webhook` - Webhook trigger

---

## 4. Indexes & Constraints

### 4.1 Foreign Key Constraints

| Table | Column | References | On Delete |
|-------|--------|------------|-----------|
| `contacts` | `user_id` | `users.id` | CASCADE |
| `conversations` | `contact_id` | `contacts.id` | CASCADE |
| `conversations` | `topic_id` | `topics.id` | NULL |
| `conversation_sessions` | `conversation_id` | `conversations.id` | CASCADE |
| `messages` | `conversation_id` | `conversations.id` | CASCADE |
| `contact_rules` | `contact_id` | `contacts.id` | CASCADE |
| `contact_notes` | `contact_id` | `contacts.id` | CASCADE |
| `contact_notes` | `user_id` | `users.id` | NULL |
| `contact_tags` | `contact_id` | `contacts.id` | CASCADE |
| `contact_custom_fields` | `contact_id` | `contacts.id` | CASCADE |
| `agent_tools` | `agent_id` | `agents.id` | CASCADE |
| `agent_skills` | `agent_id` | `agents.id` | CASCADE |
| `agent_tasks` | `agent_id` | `agents.id` | NULL |
| `agent_tasks` | `workflow_id` | `workflows.id` | NULL |
| `task_steps` | `agent_task_id` | `agent_tasks.id` | CASCADE |
| `memories` | `contact_id` | `contacts.id` | NULL |
| `memories` | `conversation_id` | `conversations.id` | NULL |
| `graph_edges` | `from_node` | `graph_nodes.id` | CASCADE |
| `graph_edges` | `to_node` | `graph_nodes.id` | CASCADE |
| `logs` | `user_id` | `users.id` | NULL |

### 4.2 Unique Constraints

| Table | Columns |
|-------|---------|
| `users` | `email` |
| `contacts` | `uuid` |
| `contacts` | `phone` |
| `agents` | `key` |
| `agent_skills` | `agent_id`, `name` |
| `contact_tags` | `contact_id`, `name` |
| `contact_custom_fields` | `contact_id`, `field_key` |
| `topics` | `slug` |
| `workflows` | `key` |
| `ai_models` | `external_id` |
| `api_keys` | `key` |
| `settings` | `key` |

---

## 5. Migration History

| Migration | Date | Description |
|-----------|------|-------------|
| `0001_01_01_000000_create_users_table.php` | Initial | Users, sessions, password resets |
| `2026_05_17_080000_create_phase_02_database_models.php` | 2026-05-17 | Core domain tables (contacts, conversations, messages, agents, settings, logs, etc.) |
| `2026_05_17_090000_create_structured_memories_table.php` | 2026-05-17 | Structured memory table |
| `2026_05_17_100000_create_graph_memory_tables.php` | 2026-05-17 | Graph memory (nodes, edges) |
| `2026_05_17_145955_add_missing_columns_to_agents_table.php` | 2026-05-17 | Agent type, execution tracking |
| `2026_05_17_150325_create_workflows_table.php` | 2026-05-17 | Workflow definitions |
| `2026_05_17_150326_add_missing_columns_to_agent_tasks_table.php` | 2026-05-17 | Workflow relationship for tasks |
| `2026_05_17_151413_add_description_column_to_settings_table.php` | 2026-05-17 | Settings description field |

---

*Last Updated: May 2026*
