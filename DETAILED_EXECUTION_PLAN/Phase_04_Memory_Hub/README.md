# Phase 04: Memory Hub

This phase implements the Memory Hub component of the Nexus platform, which provides advanced cognitive memory management capabilities.

## Overview

The Memory Hub implements a five-layer memory architecture:
1. **Working Memory** - Real-time context storage using Redis
2. **Episodic Memory** - Event and conversation history storage
3. **Semantic Memory** - Facts and knowledge storage using vector databases (Pinecone)
4. **Structured Memory** - Database entities and relationships storage
5. **Graph Memory** - Knowledge graphs and relationship networks

## Components Implemented

### Memory Services
- `WorkingMemoryService` - Redis-based temporary context storage
- `EpisodicMemoryService` - MySQL-based event and conversation history
- `SemanticMemoryService` - Pinecone vector database integration
- `StructuredMemoryService` - MySQL-based structured facts storage
- `GraphMemoryService` - Graph database for relationship networks
- `MemoryRouter` - Routes memory operations to appropriate storage
- `MemoryMaintenanceService` - Memory merging and pruning logic
- `MemorySummaryService` - Memory summarization for prompt injection


### Controllers
- `MemoryController` - RESTful API endpoints for memory operations

### Jobs
- `SyncMemoryJob` - Queued jobs for syncing memory to external stores

### Database Migrations
- `create_structured_memories_table` - Structured memories storage
- `create_graph_memory_tables` - Graph nodes and edges storage

## Features Implemented

1. **Memory CRUD Operations** - Create, read, update, delete memories across all storage types
2. **Memory Routing** - Intelligent routing of memory operations to appropriate storage layers
3. **Memory Maintenance** - Merge duplicate memories and prune stale data
4. **Memory Summarization** - Condense memory content for efficient prompt injection
5. **Memory Search** - Search functionality across all memory types
6. **Background Jobs** - Queued synchronization with internal memory services

## Configuration

Memory services are configured through the standard Laravel configuration system:
- Redis configuration in `config/database.php`
- Pinecone configuration in `config/services.php`

## Usage

The Memory Hub can be accessed through the RESTful API endpoints:
- `GET /api/v1/memories` - List memories
- `POST /api/v1/memories` - Create a new memory
- `GET /api/v1/memories/{id}` - Retrieve a specific memory
- `PUT /api/v1/memories/{id}` - Update a memory
- `DELETE /api/v1/memories/{id}` - Delete a memory
- `GET /api/v1/memories/search` - Search memories
- `POST /api/v1/memories/{id}/index` - Index memory for semantic search

## Testing

Unit tests and feature tests should be created to validate the functionality of all memory services and integrations.