# Data Models

This document provides an overview of the Nexus platform's data models, entity relationships, and database schema.

## Database Schema Overview

The Nexus platform uses a relational database (MySQL 8.0+) with the following core tables organized by hub:

## Agents Hub

### Core Tables
- **agents**: AI agent definitions and configurations
- **agent_skills**: Skills that agents can possess
- **agent_tasks**: Tasks assigned to or performed by agents
- **agent_tools**: Tools available to agents
- **agent_tool_executions**: Logs of tool usage by agents

### Relationships
- agents 1:M agent_skills (an agent can have many skills)
- agents 1:M agent_tasks (an agent can have many tasks)
- agents 1:M agent_tools (an agent can have many tools)
- agent_tools 1:M agent_tool_executions (tools can be executed many times)

## Workflows Hub

### Core Tables
- **workflows**: Workflow definitions and configurations
- **task_steps**: Individual steps within workflows
- **workflow_executions**: Instances of workflow execution
- **task_step_executions**: Instances of task step execution

### Relationships
- workflows 1:M task_steps (a workflow contains many steps)
- workflows 1:M workflow_executions (a workflow can be executed many times)
- task_steps 1:M task_step_executions (a step can be executed many times)
- workflow_executions 1:M task_step_executions (an execution contains many step executions)

## Contacts Hub

### Core Tables
- **contacts**: Contact information and profiles
- **conversations**: Conversations between users and contacts
- **messages**: Individual messages within conversations
- **contact_notes**: Notes attached to contacts
- **contact_tags**: Tags for categorizing contacts
- **contact_tag_contact**: Pivot table for many-to-many contact-tag relationship

### Relationships
- contacts 1:M conversations (a contact can have many conversations)
- conversations 1:M messages (a conversation contains many messages)
- contacts 1:M contact_notes (a contact can have many notes)
- contacts M:M contact_tags (through contact_tag_contact)
- contacts 1:M contact_tag_contact (pivot table model)

## Memory Hub

### Core Tables
- **memories**: Memory entries and their content
- **structured_memories**: Structured/formatted memories
- **graph_nodes**: Nodes in the memory knowledge graph
- **graph_edges**: Connections between graph nodes
- **memory_indexes**: Pinecone vector index references

### Relationships
- memories 1:M structured_memories (a memory can have many structured formats)
- memories 1:M graph_nodes (memories can create graph nodes)
- graph_nodes 1:M graph_edges (nodes can have many outgoing edges)
- graph_edges 1:M graph_nodes (edges point to many nodes - incoming)
- memories 1:M memory_indexes (memories can have many vector indexes)

## AI Models Hub

### Core Tables
- **ai_providers**: AI service providers (Gemini, OpenAI, Anthropic)
- **ai_api_keys**: Encrypted API keys for providers
- **ai_models**: Available models from providers
- **intent_routing**: Rules for routing requests based on intent
- **ai_requests**: Logs of AI requests made
- **ai_responses**: Logs of AI responses received

### Relationships
- ai_providers 1:M ai_api_keys (a provider can have many API keys)
- ai_providers 1:M ai_models (a provider offers many models)
- ai_models 1:M ai_requests (a model can be used in many requests)
- ai_requests 1:M ai_responses (a request can generate many responses - retries, etc.)
- intent_routing 1:M ai_requests (many requests can follow the same routing rule)

## Settings Hub

### Core Tables
- **settings**: Key-value store for system configuration
- **setting_cache**: Cached values for performance

### Relationships
- settings 1:M setting_cache (settings can have cached values)

## Logs Hub

### Core Tables
- **logs**: General application logs
- **system_logs**: System-level logs and alerts
- **log_aggregates**: Pre-computed log statistics
- **alert_notifications**: Sent alert notifications

### Relationships
- logs 1:M log_aggregates (logs contribute to aggregates)
- system_logs 1:M alert_notifications (system logs can trigger notifications)

## Nexus Hub

### Core Tables
- **dashboard_widgets**: Configurable dashboard components
- **dashboard_layouts**: User-specific dashboard arrangements
- **system_metrics**: Collected system performance metrics

### Relationships
- dashboard_layouts 1:M dashboard_widgets (layouts contain many widgets)

## Common Patterns

### Timestamp Fields
All tables include:
- `created_at`: Timestamp when record was created
- `updated_at`: Timestamp when record was last updated
- `deleted_at`: Soft delete timestamp (nullable)

### UUID Primary Keys
- All tables use UUIDs as primary keys (except where noted)
- Format: 36-character string (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
- Generated using Ramsey\Uuid\Uuid::uuid4()

### Foreign Key Constraints
- All foreign keys use `ON DELETE CASCADE` where appropriate
- Some relationships use `ON DELETE SET NULL` for optional relationships
- Database enforces referential integrity

### Indexing Strategy
- Primary keys: Automatic indexing
- Foreign keys: Indexed for join performance
- Common query fields: Indexed based on query patterns
- Composite indexes: For multi-field filtering/sorting
- Full-text indexes: On searchable text fields (where applicable)

### Enum-like Fields
- Status fields: Use string values with defined constants
- Type fields: Use string values with defined constants
- Validation: Application-level validation of allowed values
- Database: VARCHAR fields with application-level constraints

### JSON Fields
- Used for flexible, schema-less data storage
- Examples: configuration metadata, API request/response payloads
- Indexed: Generated columns for frequently queried JSON paths
- Validation: Application-level JSON schema validation

## Relationship Diagrams

Due to the complexity of the full schema, relationship diagrams are available in the following formats:
- ERD Diagram: `docs/database-schema-erd.png`
- SchemaSpy Report: `docs/schemaspy/` (interactive HTML)
- CSV Export: `docs/database-schema-tables.csv`

## Data Types Standards

### String Fields
- **Short Text**: VARCHAR(255) for names, codes, identifiers
- **Medium Text**: VARCHAR(1000) for descriptions, titles
- **Long Text**: TEXT for paragraphs, content
- **Very Long Text**: MEDIUMTEXT for large content
- **Extremely Long Text**: LONGTEXT for documents, logs

### Numeric Fields
- **Integers**: 
  - TINYINT: Boolean flags, small counters
  - SMALLINT: Medium-range counters
  - MEDIUMINT: Large counters
  - INT: Standard integer fields
  - BIGINT: Auto-increment primary keys, large counters
- **Decimals**: 
  - DECIMAL(10,2): Currency, percentages
  - DECIMAL(16,6): Scientific measurements, coordinates
  - FLOAT: Approximate numeric values
  - DOUBLE: High-precision approximate values

### Date/Time Fields
- **DATE**: Date only (YYYY-MM-DD)
- **TIME**: Time only (HH:MM:SS)
- **DATETIME**: Date and time (YYYY-MM-DD HH:MM:SS)
- **TIMESTAMP**: Date and time with timezone awareness
- **YEAR**: Year only (YYYY)

### Special Fields
- **CHAR**: Fixed-length strings (UUIDs, codes)
- **BINARY**: Binary data (hashed values)
- **VARBINARY**: Variable-length binary data
- **JSON**: JSON-formatted data
- **ENUM**: Enumerated values (limited use)
- **SET**: Set of values (limited use)

## Naming Conventions

### Tables
- **Format**: plural, snake_case
- **Examples**: agents, agent_skills, contact_tags
- **Pivot Tables**: alphabetical order of related table names (e.g., contact_tag_contact)

### Columns
- **Format**: snake_case
- **Examples**: first_name, created_at, is_active
- **Foreign Keys**: `{singular_table}_id` (e.g., agent_id, contact_id)
- **Timestamps**: created_at, updated_at, deleted_at
- **Primary Keys**: id (UUID) or `{table}_id` for explicit clarity

### Indexes
- **Format**: `idx_{table}_{columns}`
- **Examples**: idx_contacts_email, idx_agent_tasks_status_created
- **Unique Indexes**: `uidx_{table}_{columns}`
- **Primary Keys**: `primary` (automatic) or explicit name
- **Foreign Keys**: `fk_{table}_{referenced_table}`

### Constraints
- **Format**: `ck_{table}_{condition}`
- **Examples**: ck_agents_status_valid, ck_settings_key_unique

## Related Documentation
- [High-Level Overview](../01-Architecture-Hub/01-High-Level-Overview.md)
- [System Requirements](../01-Architecture-Hub/02-System-Requirements.md)
- [Technical Specifications](../01-Architecture-Hub/03-Technical-Specifications.md)
- [Existing DB Schema](../Docs/DB_SCHEMA.md) (current database schema documentation)
- [Code Hub](../02-Code-Hub/) - Model implementations
- [Migration Files](../database/migrations/) - Schema evolution history