# Nexus Platform - Inline Help Reference

This document contains contextual help text, tooltips, and inline documentation for the Nexus UI.

---

## Contacts Hub

### Contact Form

| Field | Help Text |
|-------|-----------|
| **Name** | The full name of the contact. This is how they'll appear throughout the platform. |
| **Email** | Primary email address. Used for notifications and identification. |
| **Phone** | Phone number with country code. Supports WhatsApp integration. |
| **Type** | Categorize your contact: Client, Family, Friend, Fiancée, Partner, Prospect, Vendor, or generic Contact. |
| **Title** | Job title or role (e.g., "CEO", "Marketing Director"). |
| **Company** | Organization or company name. |
| **Avatar** | Profile image. Recommended: 200x200px, JPG or PNG. |

### Contact Types Explained

| Type | Best For |
|------|----------|
| **Client** | Paying customers or clients |
| **Family** | Personal family members |
| **Friend** | Personal friends and acquaintances |
| **Fiancée** | Engaged partners |
| **Partner** | Business partners or collaborators |
| **Prospect** | Potential future clients |
| **Vendor** | Suppliers and service providers |
| **Contact** | Generic catch-all category |

### Contact Rules

**What are rules?**
Rules are natural language instructions that automate actions for specific contacts.

**Rule Examples:**
- "If John emails about pricing, respond within 1 hour"
- "Tag all contacts from Acme Corp as 'Enterprise'"
- "If contact hasn't responded in 30 days, send follow-up"
- "If message contains 'urgent', set priority to 100"

**Priority Levels:**
- **1-50**: Low priority (informational, background tasks)
- **51-100**: High priority (time-sensitive, important)

---

## Agents Hub

### Agent Types

| Type | Description | Use Case |
|------|-------------|----------|
| **Reflection** | Analyzes interactions and learns from them | Improving responses over time |
| **Team** | Coordinates multiple agents | Complex multi-step tasks |
| **Autonomous** | Operates independently without supervision | Background processing |
| **Specialized** | Focused on specific domains | Customer support, sales, etc. |
| **Supervisor** | Oversees and delegates to other agents | Managing agent workflows |

### Agent Settings (JSON)

```json
{
  "model": "gpt-4",           // AI model to use
  "temperature": 0.7,          // Creativity (0-1)
  "max_tokens": 1000,          // Max response length
  "system_prompt": "You are...", // Agent personality
  "tools": ["search", "email"], // Available tools
  "memory_enabled": true        // Use memory context
}
```

### Execution Input

When executing an agent, provide:

| Field | Description |
|-------|-------------|
| **Input** | The task or question for the agent |
| **Context** | Additional JSON context data |

---

## Workflows Hub

### Workflow Triggers

| Trigger | Description |
|---------|-------------|
| **Manual** | Start manually from the UI or API |
| **Scheduled** | Run on a recurring schedule |
| **Event** | Triggered by system events |
| **Webhook** | Triggered by external HTTP requests |

### Workflow Steps

Each step can be one of these types:

| Type | Purpose |
|------|---------|
| **message** | Send a message or notification |
| **action** | Perform a system action |
| **memory** | Create or update memory |
| **condition** | Branch based on conditions |
| **delay** | Wait for a specified time |
| **agent** | Execute an agent |

### Step Configuration

```json
{
  "order": 1,              // Step sequence
  "name": "Step Name",     // Display name
  "type": "message",       // Step type
  "agent_id": 1,           // Agent to use (if type=agent)
  "config": {              // Step-specific config
    "template": "welcome",
    "to": "{{contact.email}}"
  }
}
```

---

## Memory Hub

### Memory Types

| Type | Storage | Purpose |
|------|---------|---------|
| **Working** | Redis | Temporary, session-based context |
| **Episodic** | MySQL | Events and past conversations |
| **Semantic** | Pinecone | Facts, knowledge, vector search |
| **Structured** | MySQL | Database entity relationships |
| **Graph** | MySQL | Relationship networks |

### Memory Sources

| Source | Description |
|--------|-------------|
| **conversation** | Extracted from conversations |
| **manual** | Manually created by user |
| **agent** | Created by AI agents |
| **system** | System-generated |
| **import** | Imported from external source |

### Tags

Tags help organize and filter memories:

- Use **lowercase** for consistency
- Separate words with **hyphens** (e.g., `communication-preference`)
- Limit to **3-5 tags** per memory

### Expiration

Set expiration dates for temporary memories:
- **Working memory**: Usually expires in hours/days
- **Episodic memory**: Usually permanent
- **Semantic memory**: Usually permanent

---

## AI Models Hub

### Providers

| Provider | Best For | Models |
|----------|----------|--------|
| **OpenAI** | General purpose, high quality | GPT-4, GPT-3.5 |
| **Anthropic** | Complex reasoning, long context | Claude 2, Claude Instant |
| **Google** | Multimodal, large context | Gemini Pro, Gemini Ultra |
| **Cohere** | Embeddings, reranking | Embed, Rerank |

### Model Capabilities

| Capability | Description |
|------------|-------------|
| **chat** | Conversational interactions |
| **completion** | Text completion tasks |
| **embedding** | Vector generation |
| **function_call** | Tool/function calling |
| **vision** | Image understanding |
| **audio** | Speech processing |

### Fallback Chains

Configure fallback chains for reliability:

```
Primary: GPT-4
  ↓ (if fails)
Fallback 1: GPT-3.5
  ↓ (if fails)
Fallback 2: Claude
  ↓ (if fails)
Fallback 3: Gemini
```

### Cost Optimization

| Strategy | When to Use |
|----------|-------------|
| **Route by Cost** | Simple tasks, high volume |
| **Route by Quality** | Complex reasoning, important tasks |
| **Route by Speed** | Real-time, low-latency needs |

---

## Settings Hub

### Setting Types

| Type | Description | Example |
|------|-------------|---------|
| **string** | Short text values | App name, email |
| **integer** | Numeric values | Rate limits, timeouts |
| **boolean** | True/False values | Feature flags |
| **json** | Complex objects | Agent configurations |
| **text** | Long text values | Email templates |

### Setting Groups

| Group | Purpose |
|-------|---------|
| **general** | App name, timezone, locale |
| **security** | Authentication, encryption |
| **ai** | Default models, prompts |
| **notifications** | Email, push notifications |
| **integrations** | WAHA, external APIs |
| **ui** | Theme, language, display |

---

## Logs Hub

### Log Levels

| Level | When to Use | Action |
|-------|-------------|--------|
| **debug** | Detailed diagnostic info | Review during development |
| **info** | General informational | Normal operation |
| **notice** | Notable but normal events | Awareness |
| **warning** | Potential issues | Investigate soon |
| **error** | Error conditions | Investigate immediately |
| **critical** | Critical failures | Immediate action required |
| **alert** | System alerts | Immediate action required |
| **emergency** | System unusable | Emergency response |

### Log Categories

| Category | Description |
|----------|-------------|
| **auth** | Authentication events |
| **security** | Security-related events |
| **api** | API request/response |
| **workflow** | Workflow execution |
| **agent** | Agent activity |
| **ai** | AI operations |
| **system** | System events |
| **database** | Database operations |
| **cache** | Cache operations |
| **queue** | Queue operations |

---

## Profile

### API Tokens

API tokens allow programmatic access to your Nexus instance.

**Security Tips:**
- Treat tokens like passwords
- Never share tokens publicly
- Use different tokens for different applications
- Rotate tokens regularly
- Revoke unused tokens

### Avatar Guidelines

- **Format**: JPG, PNG, or GIF
- **Size**: Minimum 200x200px
- **File size**: Under 2MB
- **Aspect ratio**: 1:1 (square) works best

---

## Keyboard Shortcuts

| Shortcut | Action | Context |
|----------|--------|--------|
| `Ctrl/Cmd + K` | Open search | Global |
| `Ctrl/Cmd + N` | New contact | Contacts Hub |
| `Ctrl/Cmd + A` | New agent | Agents Hub |
| `Ctrl/Cmd + W` | New workflow | Workflows Hub |
| `Ctrl/Cmd + M` | New memory | Memory Hub |
| `Esc` | Close modal | Any |
| `?` | Show shortcuts | Global |

---

## Error Messages

### Common Errors

| Error | Cause | Solution |
|-------|-------|----------|
| **"Unauthenticated"** | Missing or invalid token | Check your API token |
| **"Validation Error"** | Invalid input data | Check required fields |
| **"Rate Limited"** | Too many requests | Wait and retry |
| **"Not Found"** | Resource doesn't exist | Check the ID |
| **"Server Error"** | Internal error | Check logs, contact support |

### HTTP Status Codes

| Code | Meaning | Action |
|------|---------|--------|
| **200** | Success | None |
| **201** | Created | None |
| **400** | Bad Request | Check request format |
| **401** | Unauthorized | Check authentication |
| **403** | Forbidden | Check permissions |
| **404** | Not Found | Check resource ID |
| **429** | Too Many Requests | Wait before retrying |
| **500** | Server Error | Contact support |

---

*Last Updated: May 2026*
