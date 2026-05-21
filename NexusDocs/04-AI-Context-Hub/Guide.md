# AI Guide for AI Context Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the AI Context Hub.

## Purpose
The AI Context Hub contains files specifically designed to provide context to AI agents working on the Nexus platform. These prompts help AI agents understand how to work with context files effectively.

## Analysis Prompts

### For System Prompt Documents
```
Analyze the following system prompt and:
1. Verify that the prompt clearly defines the AI's role and responsibilities
2. Check that the prompt includes necessary constraints and boundaries
3. Verify that the prompt includes important project context (8 hubs, clean architecture)
4. Identify any missing key information that would help an AI work effectively
5. Check that the tone and style are appropriate for the intended use case
6. Verify that the prompt is appropriately scoped (not too broad or too narrow)
7. Identify any conflicting instructions or unclear phrasing
8. Recommend improvements for clarity and effectiveness
9. Check that the prompt references relevant documentation or code areas
10. Ensure the prompt reflects current project state and priorities
```

### For Context Map Documents
```
Analyze the following context map and:
1. Verify that the context provides sufficient background for the specified task
2. Check that all referenced hubs and components are clearly described
3. Verify that data flows and relationships are accurately represented
4. Identify any missing context that would be important for the task
5. Check that the context is appropriately scoped for the intended use
6. Verify that all technical terms are defined or referenced
7. Identify any outdated information that should be updated
8. Recommend improvements for clarity and completeness
9. Ensure the context aligns with architectural documentation
10. Check that the context is suitable for AI consumption (structured, concise)
```

### For Configuration Template Documents
```
Analyze the following configuration template and:
1. Verify that all required fields are documented
2. Check that default values are sensible and documented
3. Verify that field descriptions are clear and complete
4. Identify any missing configuration options that should be included
5. Check that the template follows established naming conventions
6. Verify that security-sensitive fields are properly handled
7. Identify any deprecated or obsolete fields
8. Recommend improvements for usability and security
9. Ensure the template aligns with project configuration patterns
10. Check that examples provided are accurate and helpful
```

## Generation Prompts

### For Creating New System Prompt Documents
```
Create a new system prompt for [Purpose/Role] that includes:
1. Clear role definition and primary responsibilities
2. Key constraints and boundaries
3. Project context (8 hubs, clean architecture, tech stack)
4. Important conventions (naming, patterns, rules)
5. Available resources and documentation
6. Expected output format and style
7. Error handling and escalation procedures
8. Related system prompts for reference
9. Versioning and update history
10. Examples of appropriate use

Make the prompt concise yet comprehensive, suitable for AI consumption.
```

### For Creating New Context Map Documents
```
Create a new context map for [Specific Task/Area] that includes:
1. Executive summary of the context
2. Key components involved
3. Data flow diagrams or descriptions
4. Integration points with other hubs
5. Important business rules and constraints
6. Current implementation status
7. Known limitations and gotchas
8. Related documentation references
9. Common questions and answers
10. Quick reference for key terms

Structure the context to be easily consumable by AI agents.
```

### For Creating New Configuration Template Documents
```
Create a new configuration template for [System/Feature] that includes:
1. Configuration file name and location
2. Field definitions with:
   - Field name
   - Data type
   - Default value
   - Description
   - Whether required/optional
   - Validation rules
3. Example configurations
4. Security considerations
5. Performance implications
6. Troubleshooting tips
7. Related configuration files
8. Environment-specific notes
9. Versioning information
10. References to documentation
```

## Maintenance Prompts

### For Regular Context Documentation Reviews
```
Perform a monthly review of all AI Context Hub documents:
1. Check each document for accuracy against current project state
2. Verify all references to code and documentation are current
3. Ensure all context reflects current architectural decisions
4. Identify any deprecated or outdated context information
5. Note any new contexts that need documentation
6. Recommend retirement or archiving of obsolete documents
7. Suggest updates based on recent project changes
```

### For Cross-Hub Consistency Checks
```
Verify consistency between AI Context Hub and other documentation hubs:
1. Ensure context aligns with architectural principles from Project Architecture 
2. Verify that context reflects current code structure from Code Hub
3. Check that system prompts reference correct workflows from Workflow Hub
4. Confirm governance documentation influences context appropriately
5. Identify any contradictions between context and other hubs
6. Recommend updates to maintain consistency across documentation
```

## Specific Focus Areas for Nexus Platform

When working with Nexus context documentation, pay special attention to:

1. **8-Hub Architecture**: All context should emphasize the 8 hub boundaries
2. **Clean Architecture**: Context should respect layer dependencies
3. **Event-Driven Patterns**: Context should explain event communication
4. **Async Operations**: Context should clarify job queue usage
5. **AI Provider Integration**: Context should explain intent routing and fallback
6. **Memory System**: Context should cover the 5-layer memory architecture
7. **Security Practices**: Context should emphasize encrypted keys and SSRF protection
8. **Development Workflow**: Context should reflect current branch and priorities
9. **Testing Standards**: Context should reference PHPUnit and testing conventions
10. **Documentation Standards**: Context should link to existing docs in NexusDocs

By following these prompts and guidelines, AI agents can effectively maintain the AI Context Hub to provide accurate and helpful context for AI operations on the Nexus platform.
