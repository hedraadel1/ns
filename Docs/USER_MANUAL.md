# Nexus Platform - User Manual

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Dashboard Overview](#2-dashboard-overview)
3. [Contacts Hub](#3-contacts-hub)
4. [Agents Hub](#4-agents-hub)
5. [Workflows Hub](#5-workflows-hub)
6. [Memory Hub](#6-memory-hub)
7. [AI Models Hub](#7-ai-models-hub)
8. [Settings Hub](#8-settings-hub)
9. [Logs Hub](#9-logs-hub)
10. [Profile Management](#10-profile-management)

---

## 1. Getting Started

### 1.1 First Login

1. Navigate to your Nexus instance URL
2. Enter your email and password
3. Click "Sign In"

If you don't have an account, click "Register" to create one.

### 1.2 Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────┐
│  Nexus                    │ Notifications │ Profile ▼           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ Contacts │ │ Agents   │ │Workflows │ │ Memory   │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ AI Models│ │ Settings │ │  Logs    │ │  Nexus   │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 1.3 Navigation

- **Sidebar**: Access all 8 hubs
- **Top Bar**: Notifications, profile menu, search
- **Main Content**: Active hub content
- **Breadcrumbs**: Current location within the app

---

## 2. Dashboard Overview

The Nexus Hub (main dashboard) provides an overview of your platform activity.

### 2.1 Key Metrics

| Metric | Description |
|--------|-------------|
| **Total Contacts** | Number of contacts in your network |
| **Active Agents** | Number of running AI agents |
| **Running Workflows** | Currently executing workflows |
| **Memory Items** | Total memories stored |
| **Recent Activity** | Latest platform events |

### 2.2 Quick Actions

- **Add Contact**: Create a new contact
- **Create Agent**: Set up a new AI agent
- **Start Workflow**: Execute a workflow
- **View Logs**: Check recent activity

---

## 3. Contacts Hub

Manage your contact network with AI-powered intelligence.

### 3.1 Viewing Contacts

1. Click **Contacts** in the sidebar
2. Browse your contact list
3. Use filters to narrow results:
   - **Type**: Client, Family, Friend, etc.
   - **Status**: Active/Inactive
   - **Search**: Name, email, or phone

### 3.2 Creating a Contact

1. Click **Add Contact** button
2. Fill in the contact form:
   - **Name** (required)
   - **Email**
   - **Phone**
   - **Type** (Client, Family, Friend, etc.)
   - **Title**
   - **Company**
3. Click **Save**

### 3.3 Contact Profile

Click on any contact to view their full profile:

```
┌─────────────────────────────────────────────────────────────────┐
│  John Doe                              │ Edit │ Delete │ Back   │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  📧 john@example.com    📞 +1 234 567 8900                     │
│  💼 CEO at Acme Corp    🏷️ Client                               │
│                                                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ Overview │ │ Notes    │ │ Rules    │ │ Memory   │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                 │
│  [Contact details, notes, rules, and memories appear here]      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.4 Contact Notes

- **Add Note**: Click "Add Note" to record information
- **Pin Note**: Click the pin icon to keep important notes visible
- **AI Summary**: Notes are automatically summarized by AI

### 3.5 Contact Rules

Create automation rules for contacts:

1. Go to contact profile → **Rules** tab
2. Click **Add Rule**
3. Define the rule in natural language
4. Set priority (1-100)
5. Click **Save**

**Example Rules:**
- "If John emails about pricing, respond within 1 hour"
- "Tag all contacts from Acme Corp as 'Enterprise'"
- "If contact hasn't responded in 30 days, send follow-up"

### 3.6 Contact Tags

- **Add Tag**: Click "Add Tag" in the contact profile
- **Color Code**: Choose a color for visual organization
- **Filter**: Use tags to filter contact lists

### 3.7 Import/Export

- **Import**: Go to Contacts → Import → Upload CSV
- **Export**: Go to Contacts → Export → Download CSV

---

## 4. Agents Hub

Create and manage AI agents that work on your behalf.

### 4.1 Viewing Agents

1. Click **Agents** in the sidebar
2. See all configured agents
3. View status: Idle, Running, Paused, Error

### 4.2 Creating an Agent

1. Click **Create Agent**
2. Configure the agent:

| Field | Description |
|-------|-------------|
| **Name** | Display name |
| **Key** | Unique identifier (used in API) |
| **Description** | What the agent does |
| **Type** | Reflection, Team, Autonomous, Specialized, Supervisor |
| **Provider** | AI provider (OpenAI, Anthropic, etc.) |
| **Settings** | JSON configuration |

3. Click **Save**

### 4.3 Agent Types

| Type | Purpose |
|------|---------|
| **Reflection** | Analyzes and reflects on interactions |
| **Team** | Coordinates multiple agents |
| **Autonomous** | Operates independently |
| **Specialized** | Focused on specific tasks |
| **Supervisor** | Oversees other agents |

### 4.4 Executing an Agent

1. Select an agent from the list
2. Click **Execute**
3. Enter input data
4. Click **Run**
5. View results in real-time

### 4.5 Agent Status

| Status | Description |
|--------|-------------|
| **Idle** | Waiting for work |
| **Running** | Currently executing |
| **Paused** | Temporarily stopped |
| **Error** | Encountered an error |
| **Completed** | Finished successfully |

---

## 5. Workflows Hub

Orchestrate multi-step operations with workflows.

### 5.1 Viewing Workflows

1. Click **Workflows** in the sidebar
2. See all workflow definitions
3. View status and progress

### 5.2 Creating a Workflow

1. Click **Create Workflow**
2. Define workflow:

| Field | Description |
|-------|-------------|
| **Name** | Workflow name |
| **Key** | Unique identifier |
| **Description** | What the workflow does |
| **Steps** | Ordered list of steps |
| **Trigger** | Manual, Scheduled, Event, Webhook |

3. Click **Save**

### 5.3 Workflow Steps

Each step in a workflow:

```
┌─────────────────────────────────────────────────────────────────┐
│  Step 1: Welcome Message                                        │
│  Agent: Support Agent                                           │
│  Status: Pending                                                │
└─────────────────────────────────────────────────────────────────┘
```

### 5.4 Executing a Workflow

1. Select a workflow
2. Click **Execute**
3. Provide input data
4. Click **Run Workflow**
5. Monitor progress in real-time

### 5.5 Workflow Templates

Pre-built templates are available:

- **Onboarding Flow**: Welcome new contacts
- **Follow-up Sequence**: Automated follow-ups
- **Support Handoff**: Escalate to human
- **Data Sync**: Sync with external systems

---

## 6. Memory Hub

Manage the five-layer memory system.

### 6.1 Memory Layers

| Layer | Storage | Purpose |
|-------|---------|---------|
| **Working** | Redis | Real-time context |
| **Episodic** | MySQL | Events & conversations |
| **Semantic** | Pinecone | Facts & knowledge |
| **Structured** | MySQL | Database entities |
| **Graph** | MySQL | Relationship networks |

### 6.2 Viewing Memories

1. Click **Memory** in the sidebar
2. Browse memories by:
   - Contact
   - Type
   - Source
   - Date range

### 6.3 Creating a Memory

1. Click **Add Memory**
2. Select contact (optional)
3. Choose memory type
4. Enter title and content
5. Add tags
6. Set expiration (optional)
7. Click **Save**

### 6.4 Memory Search

Use the search bar to find memories:

- **Full-text search**: Searches content
- **Vector search**: Semantic similarity
- **Filter by contact**: Limit to specific contact
- **Filter by type**: Semantic, Episodic, etc.

### 6.5 Memory Indexing

Index memories for vector search:

1. Select a memory
2. Click **Index**
3. Wait for processing
4. Memory is now searchable via vector similarity

---

## 7. AI Models Hub

Configure and manage AI model providers.

### 7.1 Viewing Models

1. Click **AI Models** in the sidebar
2. See all configured AI models
3. View status and capabilities

### 7.2 Adding a Model

1. Click **Add Model**
2. Configure:

| Field | Description |
|-------|-------------|
| **Name** | Display name |
| **Provider** | OpenAI, Anthropic, Google, etc. |
| **Model ID** | Provider's model identifier |
| **Description** | Model description |
| **Capabilities** | Chat, Completion, Embedding, etc. |
| **Priority** | Selection priority (1-10) |

3. Click **Save**

### 7.3 Model Testing

Test a model before using it:

1. Select a model
2. Click **Test**
3. Enter a test prompt
4. Click **Run Test**
5. Review the response

### 7.4 Fallback Chains

Configure fallback models:

1. Go to **AI Models** → **Fallback Chains**
2. Add models in priority order
3. If primary fails, automatically try next

### 7.5 Cost Optimization

- **Route by Cost**: Select cheapest model for task
- **Route by Quality**: Select best model for complex tasks
- **Route by Speed**: Select fastest model for simple tasks

---

## 8. Settings Hub

Configure application-wide settings.

### 8.1 Viewing Settings

1. Click **Settings** in the sidebar
2. Browse settings by group:
   - **General**: App name, timezone, locale
   - **Security**: Authentication, encryption
   - **AI**: Default models, prompts
   - **Notifications**: Email, push notifications
   - **Integrations**: WAHA, external APIs
   - **UI**: Theme, language

### 8.2 Editing Settings

1. Find the setting you want to change
2. Click the edit icon
3. Enter the new value
4. Click **Save**

### 8.3 Public vs Private Settings

- **Public Settings**: Visible to all users
- **Private Settings**: Admin only

---

## 9. Logs Hub

Monitor application activity and debug issues.

### 9.1 Viewing Logs

1. Click **Logs** in the sidebar
2. Browse log entries
3. Filter by:
   - **Level**: Debug, Info, Warning, Error, etc.
   - **Category**: Auth, API, System, etc.
   - **Date Range**: Specific time period
   - **User**: Filter by user ID

### 9.2 Log Levels

| Level | Color | Description |
|-------|-------|-------------|
| **Debug** | Gray | Detailed debug info |
| **Info** | Blue | General information |
| **Notice** | Cyan | Notable events |
| **Warning** | Yellow | Warning messages |
| **Error** | Red | Error conditions |
| **Critical** | Red | Critical issues |
| **Alert** | Red | Immediate action needed |
| **Emergency** | Red | System unusable |

### 9.3 Log Actions

- **View Details**: Click a log entry for full context
- **Delete**: Remove individual logs
- **Clear All**: Remove all logs older than specified days

---

## 10. Profile Management

### 10.1 Viewing Profile

1. Click your profile picture/name in the top right
2. Select **Profile**
3. View your account details

### 10.2 Editing Profile

1. Go to **Profile**
2. Click **Edit**
3. Update:
   - **Name**
   - **Email**
4. Click **Save**

### 10.3 Changing Avatar

1. Go to **Profile**
2. Click **Change Avatar**
3. Select an image file
4. Click **Upload**

### 10.4 Password

To change your password:

1. Go to **Profile** → **Security**
2. Enter current password
3. Enter new password
4. Confirm new password
5. Click **Update Password**

---

## 11. Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + K` | Open search |
| `Ctrl/Cmd + N` | New contact |
| `Ctrl/Cmd + A` | New agent |
| `Ctrl/Cmd + W` | New workflow |
| `Ctrl/Cmd + M` | New memory |
| `Esc` | Close modal |
| `?` | Show keyboard shortcuts |

---

## 12. Getting Help

- **Documentation**: Check the Docs folder
- **API Reference**: See `Docs/API_DOCUMENTATION.md`
- **Architecture**: See `Docs/ARCHITECTURE_DETAILS.md`
- **Database Schema**: See `Docs/DB_SCHEMA.md`
- **Deployment**: See `Docs/DEPLOYMENT_GUIDE.md`

---

*Last Updated: May 2026*
