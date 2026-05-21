# AI Guide for Project Architecture  Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Project Architecture .

## Purpose
The Project Architecture  contains high-level system overviews, requirements, technical specifications, and data models. These prompts help AI agents understand how to work with architectural documentation effectively.

## Analysis Prompts

### For High-Level Overview Documents
```
Analyze the following architecture document and identify:
1. Core architectural patterns and principles described
2. Technology stack components and their purposes
3. Key system boundaries and interfaces
4. Scalability and performance considerations mentioned
5. Security approaches outlined
6. Any inconsistencies with the actual codebase structure
7. Missing elements that should be added based on current implementation

Provide specific recommendations for updates with reference to code locations where applicable.
```

### For System Requirements Documents
```
Review the requirements document and:
1. Categorize each requirement as functional, non-functional, or constraint
2. Identify which requirements are fully implemented, partially implemented, or not implemented
3. Trace each requirement to specific code components or configuration where possible
4. Identify  gaps between documented requirements and actual implementation
5. Suggest clarifications or additions based on current system behavior
6. Note any requirements that may be outdated or no longer relevant
7. Recommend priority updates based on current development focus areas
```

### For Technical Specifications Documents
```
Examine the technical specifications and:
1. Verify API specifications against actual route definitions and controller implementations
2. Check database specifications against migration files and model definitions
3. Validate external service integrations against service implementations and configuration
4. Confirm security specifications match actual implementation in middleware and services
5. Identify any specification details that are not reflected in the codebase
6. Find code implementations that extend or differ from documented specifications
7. Recommend updates to align documentation with current implementation or vice versa
```

### For Data Models Documents
```
Analyze the data model documentation and:
1. Compare documented tables and relationships against actual database schema
2. Verify field types, constraints, and indexing strategies match migrations
3. Check model relationships against Eloquent model definitions
4. Identify documented fields that don't exist in actual models/migrations
5. Find model properties or relationships that are not documented
6. Verify naming conventions are followed consistently
7. Recommend updates to ensure documentation matches current schema
```

## Generation Prompts

### For Creating New Architecture Documents
```
Create a new architecture document for [specific topic] that includes:
1. Clear overview and purpose statement
2. Relevant diagrams or structural descriptions (where applicable)
3. Key components and their responsibilities
4. Interfaces and communication patterns
5. Performance and scalability considerations
6. Security considerations and implementation details
7. Dependencies on other system components
8. Configuration and customization options
9. Known limitations or constraints
10. References to related documentation and code locations

Use the existing architecture documents as templates for structure and style.
```

### For Updating Existing Architecture Documents
```
Update the following architecture document to reflect current implementation:
1. Identify sections that are outdated or inaccurate
2. Add missing information based on recent code changes
3. Remove or deprecated information that is no longer relevant
4. Update diagrams or structural descriptions to match current state
5. Ensure all technical details (versions, configurations, etc.) are current
6. Verify consistency with other architecture documents
7. Maintain the same tone, style, and level of detail as the original
```

## Maintenance Prompts

### For Regular Documentation Reviews
```
Perform a quarterly review of all Project Architecture  documents:
1. Check each document for accuracy against current codebase
2. Verify all links to other documentation are valid and relevant
3. Ensure technical details (version numbers, config values) are up-to-date
4. Identify any ambiguous or unclear sections that need clarification
5. Note any duplication of information that should be consolidated
6. Recommend retirement or archiving of obsolete documents
7. Suggest new documents needed to cover recent architectural changes
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Project Architecture  and other documentation hubs:
1. Ensure architectural principles are reflected in Code Hub documentation
2. Verify that workflow documentation aligns with architectural boundaries
3. Check that AI context files reference correct architectural components
4. Confirm governance documentation enforces architectural standards
5. Identify any contradictions between hubs that need resolution
6. Recommend updates to maintain architectural integrity across all documentation
```

## Specific Focus Areas for Nexus Platform

When working with Nexus architecture documentation, pay special attention to:

1. **Hub Boundaries**: Ensure documentation clearly defines responsibilities of each of the 8 hubs
2. **Layer Dependencies**: Verify documentation maintains the strict 5-layer architecture rules
3. **Event-Driven Patterns**: Check that asynchronous communication via events is properly documented
4. **Real-Time Requirements**: Validate that WebSocket and broadcasting specifications are current
5. **AI Integration Points**: Ensure AI provider orchestration and intent routing concepts are accurately represented
6. **Memory System**: Verify the five-layer memory system is consistently described
7. **Extensibility Mechanisms**: Document how the system accommodates new features and integrations

## Output Format Guidelines

When generating or updating architecture documentation, follow these formats:

### For Overview Documents
```
# [Component/System] Overview

## Purpose
[Brief statement of what this component/system does]

## Responsibilities
- [Specific responsibility 1]
- [Specific responsibility 2]
- [etc.]

## Key Components
- [Component 1]: [Brief description]
- [Component 2]: [Brief description]
- [etc.]

## Interfaces
### Provides
- [Interface/API 1]: [Description]
- [Interface/API 2]: [Description]

### Consumes
- [Interface/API 1]: [Description]
- [Interface/API 2]: [Description]

## Dependencies
- [Dependency 1]: [Version/Purpose]
- [Dependency 2]: [Version/Purpose]

## Important Considerations
- [Performance/Scaling note]
- [Security note]
- [Maintainability note]
```

### For Specification Documents
```
# [Feature/API] Specification

## Overview
[High-level description of what this specifies]

## Detailed Specification
### [Section 1]
- [Detail 1]: [Specification]
- [Detail 2]: [Specification]
- [etc.]

### [Section 2]
- [Detail 1]: [Specification]
- [Detail 2]: [Specification]
- [etc.]

## Implementation Notes
- [How this is implemented in code]
- [Any deviations or special considerations]
- [Reference to key code files]

## Related Specifications
- [Link to related specification documents]
- [Link to relevant Code Hub documentation]
```

By following these prompts and guidelines, AI agents can effectively maintain the Project Architecture  as a reliable source of architectural truth for the Nexus platform.
