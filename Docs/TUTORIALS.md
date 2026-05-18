# Nexus Platform - Tutorials

## Table of Contents

1. [Getting Started Tutorial](#1-getting-started-tutorial)
2. [Creating Your First Agent](#2-creating-your-first-agent)
3. [Building a Workflow](#3-building-a-workflow)
4. [Setting Up Memory](#4-setting-up-memory)
5. [Configuring AI Models](#5-configuring-ai-models)
6. [Automating with Rules](#6-automating-with-rules)
7. [Using the API](#7-using-the-api)
8. [Deploying to Production](#8-deploying-to-production)

---

## 1. Getting Started Tutorial

This tutorial will guide you through setting up your Nexus platform and completing your first tasks.

### Prerequisites

- A server with Ubuntu 22.04/24.04
- Domain name pointing to your server
- SSH access

### Step 1: Server Setup

Follow the [Deployment Guide](DEPLOYMENT_GUIDE.md) to set up your server.

### Step 2: Initial Configuration

1. Log into your Nexus instance
2. Complete your profile:
   - Upload a profile picture
   - Set your timezone
   - Configure email notifications

3. Go to **Settings** → **General**
   - Set your application name
   - Configure default language
   - Set timezone

### Step 3: Create Your First Contact

1. Click **Contacts** → **Add Contact**
2. Enter:
   - Name: "John Doe"
   - Email: "john@example.com"
   - Type: "Client"
3. Click **Save**

### Step 4: Add a Note

1. Open John's profile
2. Click **Add Note**
3. Write: "Prefers email communication, available weekdays 9-5"
4. Click **Save**

### Step 5: Create a Memory

1. Go to **Memory** → **Add Memory**
2. Select contact: John Doe
3. Type: "Semantic"
4. Title: "Communication Preference"
5. Content: "John prefers email over phone calls"
6. Tags: `preference`, `communication`
7. Click **Save**

### Step 6: Test the API

```bash
# Get your API token from Profile → API Tokens
TOKEN="your-token-here"

# Test health endpoint
curl https://nexus.yourdomain.com/api/v1/health

# List contacts
curl -H "Authorization: Bearer $TOKEN" \
  https://nexus.yourdomain.com/api/v1/contacts
```

---

## 2. Creating Your First Agent

Learn to create and configure an AI agent.

### Step 1: Navigate to Agents

1. Click **Agents** in the sidebar
2. Click **Create Agent**

### Step 2: Configure the Agent

| Field | Value |
|-------|-------|
| **Name** | "Support Assistant" |
| **Key** | "support-assistant" |
| **Description** | "Handles customer support inquiries" |
| **Type** | "Specialized" |
| **Provider** | "OpenAI" |

### Step 3: Add Settings

In the **Settings** JSON field:

```json
{
  "model": "gpt-4",
  "temperature": 0.7,
  "max_tokens": 1000,
  "system_prompt": "You are a helpful support assistant."
}
```

### Step 4: Save and Test

1. Click **Save**
2. Select the agent
3. Click **Execute**
4. Enter test input: "Hello, can you help me?"
5. Click **Run**
6. View the response

### Step 5: Monitor Execution

Check the agent's status:

- **Idle**: Ready for next task
- **Running**: Currently processing
- **Success Rate**: Track performance over time

---

## 3. Building a Workflow

Create a multi-step workflow for automated processes.

### Step 1: Plan Your Workflow

Example: **New Contact Onboarding**

1. Send welcome message
2. Add contact tags
3. Create initial memory
4. Schedule follow-up

### Step 2: Create the Workflow

1. Click **Workflows** → **Create Workflow**
2. Configure:

| Field | Value |
|-------|-------|
| **Name** | "Contact Onboarding" |
| **Key** | "contact-onboarding" |
| **Trigger** | "Manual" |

### Step 3: Add Steps

Click **Add Step** for each step:

**Step 1: Welcome Message**
```json
{
  "order": 1,
  "name": "Send Welcome",
  "agent_id": 1,
  "type": "message",
  "config": {
    "template": "welcome_new_contact"
  }
}
```

**Step 2: Add Tags**
```json
{
  "order": 2,
  "name": "Tag Contact",
  "type": "action",
  "config": {
    "action": "add_tags",
    "tags": ["new", "onboarded"]
  }
}
```

**Step 3: Create Memory**
```json
{
  "order": 3,
  "name": "Record Onboarding",
  "type": "memory",
  "config": {
    "type": "semantic",
    "title": "Onboarding Completed",
    "content": "Contact completed onboarding on {{date}}"
  }
}
```

### Step 4: Save and Execute

1. Click **Save**
2. Click **Execute**
3. Select a contact
4. Click **Run Workflow**
5. Monitor progress in real-time

---

## 4. Setting Up Memory

Configure the five-layer memory system.

### Step 1: Understand Memory Layers

| Layer | Purpose | Example |
|-------|---------|---------|
| **Working** | Current conversation context | Active chat context |
| **Episodic** | Past events | "Met at conference 2024" |
| **Semantic** | Facts and knowledge | "Prefers email communication" |
| **Structured** | Database entities | Contact profile data |
| **Graph** | Relationships | "John works with Jane" |

### Step 2: Configure Pinecone (Semantic Memory)

1. Sign up for [Pinecone](https://pinecone.io)
2. Create a new index
3. Get your API key
4. Add to `.env`:

```env
PINECONE_API_KEY=your-pinecone-api-key
PINECONE_ENVIRONMENT=your-environment
PINECONE_INDEX=nexus-memories
```

### Step 3: Index Existing Memories

1. Go to **Memory**
2. Select memories to index
3. Click **Index Selected**
4. Wait for processing

### Step 4: Test Vector Search

```bash
curl -H "Authorization: Bearer $TOKEN" \
  "https://nexus.yourdomain.com/api/v1/memories/search?q=preference"
```

---

## 5. Configuring AI Models

Set up multiple AI providers with fallback chains.

### Step 1: Add Primary Model

1. Click **AI Models** → **Add Model**
2. Configure:

| Field | Value |
|-------|-------|
| **Name** | "GPT-4" |
| **Provider** | "OpenAI" |
| **Model ID** | "gpt-4" |
| **Priority** | 1 |

3. Click **Save**

### Step 2: Add Fallback Models

Add additional models with lower priority:

| Name | Provider | Model ID | Priority |
|------|----------|----------|----------|
| GPT-3.5 | OpenAI | gpt-3.5-turbo | 2 |
| Claude | Anthropic | claude-2 | 3 |
| Gemini | Google | gemini-pro | 4 |

### Step 3: Configure API Keys

Add your API keys to `.env`:

```env
OPENAI_API_KEY=sk-your-openai-key
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key
GOOGLE_API_KEY=your-google-api-key
```

### Step 4: Test the Chain

1. Select GPT-4
2. Click **Test**
3. Enter a prompt
4. Verify response
5. Disable GPT-4 temporarily
6. Test again - should use fallback

---

## 6. Automating with Rules

Create automation rules for contacts.

### Step 1: Create a Rule

1. Open a contact profile
2. Go to **Rules** tab
3. Click **Add Rule**

### Step 2: Define the Rule

**Example 1: Auto-Response**
```
If this contact sends a message after hours,
send an auto-reply saying "I'll respond tomorrow morning"
```

**Example 2: Auto-Tagging**
```
If contact's company is "Acme Corp",
automatically tag them as "Enterprise"
```

**Example 3: Priority Escalation**
```
If contact type is "Client" and message contains "urgent",
set priority to 100 and notify me immediately
```

### Step 3: Set Priority

- **1-50**: Low priority
- **51-100**: High priority
- Higher numbers execute first

### Step 4: Test the Rule

1. Trigger the rule condition
2. Check **Logs** to verify execution
3. Adjust rule if needed

---

## 7. Using the API

Integrate Nexus with your applications.

### Step 1: Get API Token

1. Go to **Profile** → **API Tokens**
2. Click **Create Token**
3. Copy the token (shown only once!)

### Step 2: Test Authentication

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://nexus.yourdomain.com/api/v1/user
```

### Step 3: Create a Contact via API

```bash
curl -X POST https://nexus.yourdomain.com/api/v1/contacts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "type": "client"
  }'
```

### Step 4: Send a Message

```bash
curl -X POST https://nexus.yourdomain.com/api/v1/conversations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "contact_id": 1,
    "subject": "Support Request"
  }'
```

### Step 5: Execute an Agent

```bash
curl -X POST https://nexus.yourdomain.com/api/v1/agents/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "input": "Process this request",
    "context": {"source": "api"}
  }'
```

### Step 6: Search Memories

```bash
curl "https://nexus.yourdomain.com/api/v1/memories/search?q=preference" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 8. Deploying to Production

Follow these steps for a production deployment.

### Step 1: Prepare Your Server

Follow the [Deployment Guide](DEPLOYMENT_GUIDE.md) for initial setup.

### Step 2: Configure Environment

1. Copy `.env.example` to `.env`
2. Set `APP_ENV=production`
3. Set `APP_DEBUG=false`
4. Configure database connection
5. Add API keys for all services

### Step 3: Optimize for Production

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --only=production
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Step 4: Set Up Queue Workers

```bash
# Start Horizon
php artisan horizon

# Or use Supervisor for auto-restart
sudo supervisorctl start nexus-horizon
```

### Step 5: Configure SSL

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d nexus.yourdomain.com
```

### Step 6: Verify Deployment

```bash
# Check application
curl https://nexus.yourdomain.com/api/v1/health

# Check Horizon dashboard
https://nexus.yourdomain.com/horizon

# Check logs
tail -f storage/logs/laravel.log
```

---

## 9. Next Steps

After completing these tutorials:

1. **Explore the API**: Read the [API Documentation](API_DOCUMENTATION.md)
2. **Understand Architecture**: Read [Architecture Details](ARCHITECTURE_DETAILS.md)
3. **Review Database Schema**: Read [Database Schema](DB_SCHEMA.md)
4. **Set Up Monitoring**: Configure alerts and notifications
5. **Join the Community**: Connect with other Nexus users

---

*Last Updated: May 2026*
