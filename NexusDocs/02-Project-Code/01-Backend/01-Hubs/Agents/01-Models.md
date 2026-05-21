# Agent Models

This document describes the Eloquent models used in the Agents Hub.

## Agent Model (`app/Models/Agent.php`)

### Purpose
Represents an AI agent in the Nexus platform with its configuration, status, and metadata.

### Database Table
`agents`

### Fillable Attributes
- `name`: Human-readable name for the agent
- `description`: Detailed description of the agent's purpose and capabilities
- `status`: Current status (active, inactive, maintenance, retired)
- `configuration`: JSON field for agent-specific configuration
- `metadata`: JSON field for additional metadata
- `uuid`: Unique identifier for the agent
- `created_by`: Reference to the user who created the agent
- `updated_by`: Reference to the user who last updated the agent

### Relationships
- `skills()`: BelongsToMany relationship with AgentSkill
- `tasks()`: HasMany relationship with AgentTask
- `tools()`: BelongsToMany relationship with AgentTool
- `creator()`: BelongsToMany relationship with User (created_by)
- `updater()`: BelongsToMany relationship with User (updated_by)

### Methods
- `activate()`: Sets agent status to active
- `deactivate()`: Sets agent status to inactive
- `retire()`: Sets agent status to retired
- `assignSkill(AgentSkill $skill)`: Assigns a skill to the agent
- `removeSkill(AgentSkill $skill)`: Removes a skill from the agent
- `assignTool(AgentTool $tool)`: Assigns a tool to the agent
- `removeTool(AgentTool $tool)`: Removes a tool from the agent
- `getCurrentTasks()`: Returns tasks currently assigned to the agent
- `getAvailableSkills()`: Returns skills not currently assigned to the agent
- `getAvailableTools()`: Returns tools not currently assigned to the agent

### Observers
- **AgentObserver**: Handles events like creating, updating, deleting
  - Generates UUID on creation
  - Clears related caches on update/delete
  - Logs significant status changes

## AgentSkill Model (`app/Models/AgentSkill.php`)

### Purpose
Defines a skill that an agent can possess.

### Database Table
`agent_skills`

### Fillable Attributes
- `name`: Name of the skill
- `description`: Description of what the skill enables
- `category`: Classification of the skill (nlp, vision, analysis, etc.)
- `configuration`: JSON field for skill-specific configuration
- `is_active`: Whether the skill is currently available for assignment
- `uuid`: Unique identifier for the skill

### Relationships
- `agents()`: BelongsToMany relationship with Agent
- `tasks()`: BelongsToMany relationship with AgentTask (tasks that require this skill)

### Methods
- `activate()`: Sets skill as active
- `deactivate()`: Sets skill as inactive
- `getAgentsUsingSkill()`: Returns agents that have this skill assigned
- `getTasksRequiringSkill()`: Returns tasks that require this skill

## AgentTask Model (`app/Models/AgentTask.php`)

### Purpose
Represents a task assigned to or performed by an agent.

### Database Table
`agent_tasks`

### Fillable Attributes
- `title`: Short title of the task
- `description`: Detailed description of the task
- `status`: Current status (pending, assigned, in_progress, completed, failed)
- `priority`: Priority level (low, medium, high, urgent)
- `configuration`: JSON field for task-specific configuration
- `result`: JSON field for task results or output
- `agent_id`: Foreign key to the assigned agent
- `skill_required`: Reference to the skill required to perform this task
- `uuid`: Unique identifier for the task
- `created_by`: Reference to the user who created the task
- `assigned_at`: Timestamp when task was assigned to an agent
- `started_at`: Timestamp when task execution began
- `completed_at`: Timestamp when task was completed
- `due_date`: Timestamp by which task should be completed

### Relationships
- `agent()`: BelongsToMany relationship with Agent
- `requiredSkill()`: BelongsToMany relationship with AgentSkill
- `creator()`: BelongsToMany relationship with User (created_by)
- `assignee()`: BelongsToMany relationship with User (if different from agent's owner)
- `events()`: HasMany relationship with AgentTaskEvent
- `logs()`: HasMany relationship with AgentTaskLog

### Methods
- `assignToAgent(Agent $agent)`: Assigns the task to an agent
- `start()`: Marks task as in progress and sets started_at
- `complete(mixed $result)`: Marks task as completed and stores result
- `fail(string $errorMessage)`: Marks task as failed with error message
- `isOverdue()`: Returns true if past due_date and not completed
- `getDuration()`: Returns time elapsed since started_at
- `canBeAssignedTo(Agent $agent)`: Checks if agent has required skills and capacity

## AgentTool Model (`app/Models/AgentTool.php`)

### Purpose
Defines a tool that agents can use.

### Database Table
`agent_tools`

### Fillable Attributes
- `name`: Name of the tool
- `description`: Description of what the tool does
- `tool_class`: Fully qualified class name of the tool implementation
- `configuration`: JSON field for tool-specific configuration
- `is_active`: Whether the tool is currently available for use
- `uuid`: Unique identifier for the tool

### Relationships
- `agents()`: BelongsToMany relationship with Agent
- `executions()`: HasMany relationship with AgentToolExecution

### Methods
- `activate()`: Sets tool as active
- `deactivate()`: Sets tool as inactive
- `execute(array $parameters)`: Executes the tool with given parameters
- `getExecutionHistory()`: Returns past executions of this tool
- `getAgentsWithAccess()`: Returns agents that have this tool assigned

## AgentToolExecution Model (`app/Models/AgentToolExecution.php`)

### Purpose
Logs executions of tools by agents.

### Database Table
`agent_tool_executions`

### Fillable Attributes
- `agent_id`: Foreign key to the agent that used the tool
- `tool_id`: Foreign key to the tool that was used
- `parameters`: JSON field for input parameters
- `result`: JSON field for output or result
- `status`: Execution status (pending, running, completed, failed)
- `execution_time`: Integer milliseconds taken to execute
- `uuid`: Unique identifier for the execution
- `executed_at`: Timestamp when execution occurred

### Relationships
- `agent()`: BelongsToMany relationship with Agent
- `tool()`: BelongsToMany relationship with AgentTool

### Methods
- `markAsRunning()`: Sets status to running
- `markAsCompleted(mixed $result)`: Sets status to completed and stores result
- `markAsFailed(string $errorMessage, int $executionTime)`: Sets status to failed
- `getDuration()`: Returns execution_time if completed

## Common Patterns

### UUID Usage
All models use UUIDs as primary keys for:
- Security: Prevents enumeration of records
- Distribution: Safe for use in distributed systems
- Universality: Globally unique across systems

### JSON Fields
Used for flexible configuration and storage of:
- Agent-specific settings
- Skill configuration parameters
- Tool execution parameters
- Task input/output data
- Metadata that doesn't fit rigid schema

### Status Fields
Follow consistent patterns:
- Agents: active, inactive, maintenance, retired
- Skills: active, inactive
- Tasks: pending, assigned, in_progress, completed, failed
- Tool Executions: pending, running, completed, failed

### Relationship Naming
- Direct relationships use singular form (`agent()`, `tool()`)
- Many-to-many relationships use plural form (`agents()`, `skills()`)
- Reverse relationships are named descriptively when needed

## Related Documentation
- [Agent Lifecycle Service](../02-Services.md) - Business logic for agent management
- [Agent API Endpoints](../03-Controllers.md) - REST API interfaces
- [Agent Events](../04-Events.md) - Events fired by agent operations
- [Agent Jobs](../05-Jobs.md) - Background processing for agents
- [Database Schema](../../../../../01-Architecture-Hub/04-Data-Models.md) - Full schema details