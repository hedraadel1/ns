# NexusDocs Documentation Structure

This document describes the organization of the NexusDocs directory, which serves as the centralized documentation hub for the Nexus platform.

## Directory Structure

NexusDocs/
├── 00-Project-Root/
│   ├── README.md                     # Overview of NexusDocs structure and usage
│   ├── CONTRIBUTING.md               # Guidelines for contributing to documentation
│   └── GLOSSARY.md                   # Project-specific terminology and acronyms
│
├── 01-Architecture-Hub/
│   ├── 01-High-Level-Overview.md     # System architecture, technology stack, and design principles
│   ├── 02-System-Requirements.md     # Functional and non-functional requirements
│   ├── 03-Technical-Specifications.md# Detailed technical specs (APIs, databases, integrations)
│   ├── 04-Data-Models.md             # Entity relationships, database schema overview
│   └── Guide.md                      # AI prompts for analyzing and updating architecture documentation
│
├── 02-Code-Hub/
│   ├── 01-Backend/
│   │   ├── 01-Modules/               # Documentation organized by hub/module
│   │   │   ├── Agents/
│   │   │   │   ├── README.md         # Module overview and responsibilities
│   │   │   │   ├── 01-Models.md      # Agent, AgentSkill, AgentTask, AgentTool models
│   │   │   │   ├── 02-Services.md    # AgentLifecycleService, AgentRegistry, etc.
│   │   │   │   ├── 03-Controllers.md # API endpoints for agent management
│   │   │   │   ├── 04-Events.md      # Domain events (AgentExecuted, etc.)
│   │   │   │   ├── 05-Jobs.md        # Async jobs related to agents
│   │   │   │   └── Guide.md          # AI prompts for documenting the Agents module
│   │   │   ├── Workflows/
│   │   │   ├── Contacts/
│   │   │   ├── Memory/
│   │   │   ├── AI-Models/
│   │   │   ├── Settings/
│   │   │   ├── Logs/
│   │   │   └── Nexus/
│   │   ├── 02-Core-Services/         # Cross-cutting services (CircuitBreaker, Idempotency, etc.)
│   │   │   ├── CircuitBreakerService.md
│   │   │   ├── IdempotencyService.md
│   │   │   └── Guide.md
│   │   ├── 03-Interfaces/            # PHP interfaces and contracts
│   │   │   ├── AIProviderContract.md
│   │   │   ├── MemoryContract.md
│   │   │   └── Guide.md
│   │   └── Guide.md                  # AI prompts for backend code documentation
│   ├── 03-Frontend/
│   │   ├── 01-Components/            # Vue 3 components
│   │   │   ├── NxAiPulse.vue.md
│   │   │   ├── NxGlassCard.vue.md
│   │   │   ├── NxTokenMeter.vue.md
│   │   │   ├── NxLiveLoader.vue.md
│   │   │   ├── NxActionButton.vue.md
│   │   │   ├── LiveChatStream.vue.md
│   │   │   ├── GlobalJobMonitor.vue.md
│   │   │   └── Guide.md
│   │   ├── 02-Stores/                # Pinia stores
│   │   │   ├── useContactStore.md
│   │   │   ├── useWorkflowStore.md
│   │   │   └── Guide.md
│   │   ├── 03-Composables/           # Vue composables
│   │   │   ├── useApi.md
│   │   │   ├── useWebSocket.md
│   │   │   └── Guide.md
│   │   └── Guide.md                  # AI prompts for frontend code documentation
│   └── Guide.md                      # AI prompts for overall code documentation
│
├── 03-Workflow-Hub/
│   ├── 01-Business-Logic-Workflows/  # High-level business processes
│   │   ├── Contact-Enrichment-Workflow.md
│   │   ├── Memory-Consolidation-Workflow.md
│   │   ├── AI-Request-Processing-Workflow.md
│   │   └── Guide.md
│   ├── 02-Logical-Algorithmic-Flows/ # Step-by-step algorithms and logic
│   │   ├── Memory-Indexing-Algorithm.md
│   │   ├── Intent-Routing-Algorithm.md
│   │   ├── Payload-Adaptation-Algorithm.md
│   │   └── Guide.md
│   ├── 03-Code-Level-Implementation-Traces# Execution traces with code references
│   │   ├── AiRequest-Handling-Trace.md   # From API endpoint to AI response
│   │   ├── Memory-Storage-Trace.md       # From event to Pinecone storage
│   │   ├── Contact-Creation-Trace.md     # From API to database
│   │   └── Guide.md
│   └── Guide.md                      # AI prompts for workflow documentation
│
├── 04-AI-Context-Hub/
│   ├── 01-System-Prompts/            # Prompts for initializing AI agents
│   │   ├── architect-prompt.txt
│   │   ├── developer-prompt.txt
│   │   ├── tester-prompt.txt
│   │   └── documentation-prompt.txt
│   ├── 02-Context-Maps/              # Pre-built context for specific tasks
│   │   ├── full-system-context.md
│   │   ├── hub-interactions-context.md
│   │   ├── data-flow-context.md
│   │   └── Guide.md
│   ├── 03-Configuration-Templates/   # Template files for AI context
│   │   ├── .cursorrules-template
│   │   ├── .windsurfrules-template
│   │   └── Guide.md
│   └── Guide.md                      # AI prompts for managing AI context files
│
├── 05-Governance-Hub/
│   ├── 01-Testing-Protocols/
│   │   ├── 01-Unit-Testing-Guidelines.md
│   │   ├── 02-Feature-Testing-Guidelines.md
│   │   ├── 03-Testing-With-Mocks.md  # Mocking external services (AI providers, Pinecone)
│   │   ├── 04-Testing-Database.md    # Using transactions and factories
│   │   └── Guide.md
│   ├── 02-Update-Deployment-Rules/
│   │   ├── 01-Deployment-Checklist.md
│   │   ├── 02-Versioning-Policy.md   # Semantic versioning guidelines
│   │   ├── 03-Backup-And-Restore.md
│   │   ├── 04-Environment-Variables.md# Required .env variables and validation
│   │   └── Guide.md
│   ├── 03-Coding-Standards/
│   │   ├── 01-PHP-Coding-Standards.md# PSR-12 with project-specific additions
│   │   ├── 02-JS-Vue-Coding-Standards.md# ESLint, Prettier, and Vue 3 best practices
│   │   ├── 03-Documentation-Standards.md# How to write docs in this repo
│   │   └── Guide.md
│   └── Guide.md                      # AI prompts for governance documentation
│
├── 06-Prompt-Library-Hub/
│   ├── 01-Code-Generation-Prompts/   # Prompts for generating code
│   │   ├── generate-service-class.prompt
│   │   ├── generate-vue-component.prompt
│   │   ├── generate-api-endpoint.prompt
│   │   └── Guide.md
│   ├── 02-Documentation-Generation-Prompts/# Prompts for generating documentation
│   │   ├── generate-class-doc.prompt
│   │   ├── generate-function-doc.prompt
│   │   ├── generate-workflow-doc.prompt
│   │   └── Guide.md
│   ├── 03-Refactoring-Prompts/       # Prompts for code improvement
│   │   ├── extract-service-from-controller.prompt
│   │   ├── optimize-database-query.prompt
│   │   ├── add-event-broadcasting.prompt
│   │   └── Guide.md
│   ├── 04-Testing-Prompts/           # Prompts for generating tests
│   │   ├── generate-unit-test.prompt
│   │   ├── generate-feature-test.prompt
│   │   └── Guide.md
│   └── Guide.md                      # AI prompts for managing the prompt library
│
├── 07-Legacy-Hub/
│   ├── 01-Deprecated-Classes/        # Documentation for deprecated PHP classes
│   │   ├── OldContactService.md
│   │   ├── LegacyMemoryManager.md
│   │   └── Guide.md
│   ├── 02-Unused-Components/         # Documentation for unused Vue components
│   │   ├── OldContactCard.vue.md
│   │   └── Guide.md
│   ├── 03-Removed-APIs/              # Documentation for deprecated API endpoints
│   │   ├── /api/v0/contacts.md
│   │   └── Guide.md
│   └── Guide.md                      # AI prompts for legacy documentation
│
└── 08-Meta-Hub/                      # Documentation about the documentation system
    ├── 01-Documentation-Structure.md # This file - explains the NexusDocs organization
    ├── 02-Style-Guide.md             # Writing style, tone, and formatting rules
    ├── 03-Update-Process.md          # How to keep documentation in sync with code
    ├── 04-Tools-And-Automation.md    # Scripts and tools for doc generation/validation
    └── Guide.md                      # AI prompts for maintaining the documentation system