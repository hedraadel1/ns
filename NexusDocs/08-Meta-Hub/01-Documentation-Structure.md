# NexusDocs Documentation Structure

This document describes the organization of the NexusDocs directory, which serves as the centralized documentation hub for the Nexus platform, providing a "single source of truth" for both human developers and AI agents.

## Directory Structure

```
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
│   │   ├── 03-Interfaces/            # PHP interfaces and contracts
│   │   └── Guide.md                  # AI prompts for backend code documentation
│   ├── 03-Frontend/
│   │   ├── 01-Components/            # Vue 3 components
│   │   ├── 02-Stores/                # Pinia stores
│   │   ├── 03-Composables/           # Vue composables
│   │   └── Guide.md                  # AI prompts for frontend code documentation
│   └── Guide.md                      # AI prompts for overall code documentation
│
├── 03-Workflow-Hub/
│   ├── 01-Business-Logic-Workflows/  # High-level business processes
│   ├── 02-Logical-Algorithmic-Flows/ # Step-by-step algorithms and logic
│   ├── 03-Code-Level-Implementation-Traces
│   └── Guide.md                      # AI prompts for workflow documentation
│
├── 04-AI-Context-Hub/
│   ├── 01-System-Prompts/            # Prompts for initializing AI agents
│   ├── 02-Context-Maps/              # Pre-built context for specific tasks
│   ├── 03-Configuration-Templates/   # Template files for AI context
│   └── Guide.md                      # AI prompts for managing AI context files
│
├── 05-Governance-Hub/
│   ├── 01-Testing-Protocols/
│   ├── 02-Update-Deployment-Rules/
│   ├── 03-Coding-Standards/
│   └── Guide.md                      # AI prompts for governance documentation
│
├── 06-Prompt-Library-Hub/
│   ├── 01-Code-Generation-Prompts/   # Prompts for generating code
│   ├── 02-Documentation-Generation-Prompts/
│   ├── 03-Refactoring-Prompts/       # Prompts for code improvement
│   ├── 04-Testing-Prompts/           # Prompts for generating tests
│   └── Guide.md                      # AI prompts for managing the prompt library
│
├── 07-Legacy-Hub/
│   ├── 01-Deprecated-Classes/        # Documentation for deprecated PHP classes
│   ├── 02-Unused-Components/         # Documentation for unused Vue components
│   ├── 03-Removed-APIs/              # Documentation for deprecated API endpoints
│   └── Guide.md                      # AI prompts for legacy documentation
│
└── 08-Meta-Hub/                      # Documentation about the documentation system
    ├── 01-Documentation-Structure.md # This file - explains the NexusDocs organization
    ├── 02-Style-Guide.md             # Writing style, tone, and formatting rules
    ├── 03-Update-Process.md          # How to keep documentation in sync with code
    ├── 04-Tools-And-Automation.md    # Scripts and tools for doc generation/validation
    └── Guide.md                      # AI prompts for maintaining the documentation system
```

## Detailed File and Folder Descriptions

### 00-Project-Root/

**Purpose**: Entry point for understanding and contributing to NexusDocs

- **README.md**: Introduction to NexusDocs, its purpose, directory overview, and how to navigate the documentation for both human developers and AI agents. Includes quick links to key areas.

- **CONTRIBUTING.md**: Guidelines for contributing to the documentation, including how to suggest changes, report issues, and maintain documentation quality standards.

- **GLOSSARY.md**: Definitions of project-specific terminology, acronyms, and concepts used throughout the Nexus platform and its documentation.

### 01-Architecture-Hub/

**Purpose**: High-level architectural documentation serving as the foundation for understanding the system

- **01-High-Level-Overview.md**: System architecture overview including the 8 core hubs (Agents, Workflows, Contacts, Memory, AI Models, Settings, Logs, Nexus), technology stack (Laravel 11, Vue 3, MySQL, Redis, etc.), and the 5-layer clean architecture pattern.

- **02-System-Requirements.md**: Comprehensive functional requirements (API endpoints, contact management, memory system, etc.) and non-functional requirements (performance, security, reliability) for the platform.

- **03-Technical-Specifications.md**: Detailed technical specifications covering API standards, authentication, database connections, external service integrations (Gemini, OpenAI, Anthropic, Pinecone, WhatsApp), WebSocket configuration, security measures, and deployment specifications.

- **04-Data-Models.md**: Overview of database table organization by hub, common patterns (UUIDs, JSON fields, relationships), naming conventions, indexing strategy, and available relationship diagrams.

- **Guide.md**: AI prompts for analyzing architecture documents, generating new architecture documentation, and maintaining consistency with the codebase.

### 02-Code-Hub/

**Purpose**: Granular code documentation organized by backend and frontend concerns

#### 01-Backend/

- **01-Modules/**: Documentation for each of the 8 hubs, organized into:
  - README.md: Module overview and responsibilities
  - 01-Models.md: Eloquent model documentation
  - 02-Services.md: Service class documentation
  - 03-Controllers.md: API endpoint documentation
  - 04-Events.md: Domain event documentation
  - 05-Jobs.md: Async job documentation
  - Guide.md: AI prompts for documenting that specific module

- **02-Core-Services/**: Documentation for cross-cutting services (CircuitBreaker, Idempotency, etc.) that operate across multiple hubs.

- **03-Interfaces/**: Documentation for PHP interfaces and contracts that define service boundaries.

- **Guide.md**: AI prompts for backend code analysis, generation, and maintenance.

#### 03-Frontend/

- **01-Components/**: Vue 3 component documentation including props, events, slots, and usage examples.

- **02-Stores/**: Pinia store documentation including state, getters, and actions.

- **03-Composables/**: Reusable composition function documentation.

- **Guide.md**: AI prompts for frontend code analysis, generation, and maintenance.

- **Guide.md** (root): Overall code hub maintenance prompts.

### 03-Workflow-Hub/

**Purpose**: Step-by-step workflows at three distinct levels

- **01-Business-Logic-Workflows/**: High-level business processes (Contact Enrichment, Memory Consolidation, AI Request Processing) with participants, decision points, and outcomes.

- **02-Logical-Algorithmic-Flows/**: Step-by-step algorithms and logic flows (Memory Indexing, Intent Routing, Payload Adaptation) with pseudocode and edge cases.

- **03-Code-Level-Implementation-Traces**: Execution traces with specific file paths and line numbers (AI Request Handling, Memory Storage, Contact Creation).

- **Guide.md**: AI prompts for analyzing and documenting workflows.

### 04-AI-Context-Hub/

**Purpose**: Files providing context to AI agents working on Nexus

- **01-System-Prompts/**: Pre-built prompts for initializing AI agents (architect, developer, tester, documentation roles).

- **02-Context-Maps/**: Pre-built context documents for specific tasks (full system context, hub interactions, data flow).

- **03-Configuration-Templates/**: Templates for AI context configuration files (.cursorrules, .windsurfrules).

- **Guide.md**: AI prompts for managing and updating context files.

### 05-Governance-Hub/

**Purpose**: Standards and protocols for testing, deployment, and coding

- **01-Testing-Protocols/**: Testing guidelines including unit testing, feature testing, mocking strategies, and database testing.

- **02-Update-Deployment-Rules/**: Deployment procedures including checklists, versioning policies, backup/restore, and environment variables.

- **03-Coding-Standards/**: Coding standards for PHP, JavaScript/Vue, and documentation.

- **Guide.md**: AI prompts for governance documentation maintenance.

### 06-Prompt-Library-Hub/

**Purpose**: Optimized prompts for common AI tasks

- **01-Code-Generation-Prompts/**: Prompts for generating service classes, Vue components, API endpoints, etc.

- **02-Documentation-Generation-Prompts/**: Prompts for generating class docs, function docs, workflow docs.

- **03-Refactoring-Prompts/**: Prompts for extracting services, optimizing queries, adding broadcasting.

- **04-Testing-Prompts/**: Prompts for generating unit and feature tests.

- **Guide.md**: AI prompts for managing the prompt library.

### 07-Legacy-Hub/

**Purpose**: Historical documentation for deprecated/removed functionality

- **01-Deprecated-Classes/**: Documentation for PHP classes that are no longer recommended but may still exist.

- **02-Unused-Components/**: Documentation for Vue components that are not actively used.

- **03-Removed-APIs/**: Documentation for API endpoints that have been removed.

- **Guide.md**: AI prompts for legacy documentation maintenance.

### 08-Meta-Hub/

**Purpose**: Documentation about the documentation system itself

- **01-Documentation-Structure.md**: This file - the complete directory structure and description.

- **02-Style-Guide.md**: Writing style, tone, and formatting rules for NexusDocs.

- **03-Update-Process.md**: Procedures for keeping documentation synchronized with code changes.

- **04-Tools-And-Automation.md**: Scripts and tools for automated documentation generation and validation.

- **Guide.md**: AI prompts for maintaining the documentation system itself.

## Blueprint System (Guide Files)

Each hub and major category includes a `Guide.md` file containing specific prompts designed to instruct AI agents on how to:

1. Analyze existing documentation in that category
2. Generate new documentation following established patterns
3. Update documentation to reflect code changes
4. Maintain consistency within the category
5. Ensure cross-category consistency

These guides provide a blueprint system that enables any AI agent to effectively contribute to and maintain the NexusDocs documentation structure.