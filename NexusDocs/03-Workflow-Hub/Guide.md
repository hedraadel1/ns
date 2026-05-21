# AI Guide for Workflow Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Workflow Hub.

## Purpose
The Workflow Hub contains step-by-step workflows at three distinct levels: Business Logic, Logical/Algorithmic Flow, and Code-level Implementation. These prompts help AI agents understand how to work with workflow documentation effectively.

## Analysis Prompts

### For Business Logic Workflow Documents
```
Analyze the following business logic workflow document and:
1. Verify that the workflow accurately represents actual business processes
2. Check that all steps are traceable to either system requirements or user stories
3. Verify that the workflow aligns with the architectural boundaries of the 8 hubs
4. Identify any steps that involve integration points between hubs
5. Check for clear decision points and branching logic
6. Verify that all participants (users, systems) are clearly identified
7. Ensure that success/failure criteria for each step are documented
8. Identify any regulatory or compliance considerations mentioned
9. Check for proper error handling and rollback procedures
10. Recommend updates to align with current business requirements
```

### For Logical/Algorithmic Flow Documents
```
Analyze the following algorithm document and:
1. Verify that the algorithm correctly implements the business logic
2. Check that all edge cases and error conditions are handled
3. Verify that the algorithm follows established design patterns
4. Identify any performance bottlenecks or optimization opportunities
5. Check for proper data validation and sanitization
6. Ensure that the algorithm is deterministic and reproducible
7. Verify that any external service calls are properly documented
8. Check for proper state management throughout the algorithm
9. Identify any security considerations in the algorithm
10. Recommend improvements for readability and maintainability
```

### For Code-Level Implementation Trace Documents
```
Analyze the following implementation trace and:
1. Verify that each step maps to actual code in the codebase
2. Check that all file paths and line numbers are accurate
3. Verify that the trace shows the complete execution path
4. Identify any error handling that is not documented
5. Check for proper logging and monitoring hooks
6. Verify that the trace includes database interactions
7. Ensure that API calls and external service interactions are documented
8. Check for proper transaction boundaries
9. Verify that caching strategies are documented where used
10. Recommend updates to reflect current code implementation
```

## Generation Prompts

### For Creating New Business Logic Workflow Documents
```
Create a new business logic workflow document for [WorkflowName] that includes:
1. Workflow overview and purpose
2. Participants (actors and systems) involved
3. Trigger conditions that start the workflow
4. Step-by-step process flow with:
   - Step number/name
   - Actor or system responsible
   - Description of action
   - Input/output data
   - Decision points and branching
   - Success/failure criteria
5. Integration points with other hubs
6. Error handling and exception paths
7. Success and failure outcomes
8. Related requirements or user stories
9. Related documentation (algorithmic flows, implementation traces)

Use the existing workflow documents as templates for structure and style.
```

### For Creating New Logical/Algorithmic Flow Documents
```
Create a new algorithm document for [AlgorithmName] that includes:
1. Algorithm overview and purpose
2. Input parameters with types and constraints
3. Output format and meaning
4. Step-by-step algorithm with:
   - Step number/name
   - Description of logic
   - Pseudocode or code snippet
   - Data transformations
   - Decision points
   - Edge cases handled
5. Error handling and validation
6. Performance considerations
7. Security considerations
8. Resource requirements
9. Related documentation (business logic, implementation traces)
```

### For Creating New Code-Level Implementation Trace Documents
```
Create a new implementation trace document for [TraceName] that includes:
1. Trace overview and purpose
2. Starting point (entry point/function)
3. Complete execution path with:
   - File path and line number range
   - Function/method name
   - Brief description of what happens
   - Key variables and data structures
   - Calls to other functions/services
4. Database operations with table names and operations
5. External service calls with endpoints and payloads
6. Error handling and logging points
7. Performance monitoring points
8. Related documentation (business logic, algorithmic flow)
```

## Maintenance Prompts

### For Regular Workflow Documentation Reviews
```
Perform a quarterly review of all Workflow Hub documents:
1. Check each document for accuracy against current implementation
2. Verify all steps and logic match current code behavior
3. Ensure all integration points are up-to-date
4. Identify any obsolete workflows that are no longer used
5. Note any new workflows that need documentation
6. Recommend retirement or archiving of obsolete documents
7. Suggest new documents needed for recent process changes
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Workflow Hub and other documentation hubs:
1. Ensure business logic aligns with architectural principles from Project Architecture 
2. Verify that workflows are implemented correctly in Code Hub
3. Check that AI context files reference correct workflow components
4. Confirm governance documentation covers workflow testing
5. Identify any contradictions between hubs that need resolution
6. Recommend updates to maintain workflow integrity across all documentation
```

## Specific Focus Areas for Nexus Platform

When working with Nexus workflow documentation, pay special attention to:

1. **Hub Boundaries**: Ensure workflows respect the boundaries between the 8 hubs
2. **Event-Driven Patterns**: Document how workflows communicate via events
3. **Async Operations**: Clearly mark steps that are asynchronous (background jobs)
4. **Error Recovery**: Document retry logic, dead letter queues, and manual intervention points
5. **Real-Time Updates**: Note steps where real-time updates are sent to UI
6. **AI Integration**: Clearly document where AI services are involved in workflows
7. **Memory System**: Show where memories are created, read, updated, or deleted
8. **Agent Interaction**: Document agent task assignments and completions
9. **Contact Intelligence**: Note contact-related data processing steps
10. **Monitoring & Logging**: Ensure observability points are documented

## Output Format Guidelines

When generating or updating workflow documentation, follow these formats:

### For Business Logic Workflow Documents
```
# [WorkflowName]

## Purpose
[Brief statement of what this workflow accomplishes]

## Participants
- **[Participant Name]**: [Role in workflow]
- [etc.]

## Trigger
[Conditions that start this workflow]

## Workflow Steps
1. **[Step Name]** (by [Actor/System])
   - **Action**: [Description]
   - **Input**: [Data required]
   - **Output**: [Data produced]
   - **Decision Point**: [Yes/No, conditions]
   - **Success Criteria**: [What defines success]
   - **Failure Handling**: [What happens on failure]

[etc.]

## Integration Points
- **[Hub Name]**: [How workflow interacts with this hub]

## Error Paths
[Alternative paths for error scenarios]

## Outcomes
- **Success**: [What happens when workflow completes successfully]
- **Failure**: [What happens when workflow fails]

## Related Documentation
- [Algorithmic Flow Document]
- [Implementation Trace Document]
- [Code Hub Documentation]
```

### For Logical/Algorithmic Flow Documents
```
# [AlgorithmName]

## Purpose
[Brief statement of what this algorithm does]

## Inputs
- **[Parameter Name]** ([Type]): [Description, constraints]

## Outputs
- **[Return Value]** ([Type]): [Description]

## Algorithm Steps
1. **[Step Name]**
   ```
   [Pseudocode or code snippet]
   ```
   - **Description**: [What this step does]
   - **Transformations**: [How data is transformed]
   - **Edge Cases**: [Conditions handled]

[etc.]

## Error Handling
[How errors are handled and reported]

## Performance Considerations
[Any performance notes or optimizations]

## Security Considerations
[Any security notes]

## Related Documentation
- [Business Logic Workflow]
- [Implementation Trace]
```

### For Code-Level Implementation Trace Documents
```
# [TraceName] Implementation Trace

## Purpose
[Brief statement of what this trace represents]

## Entry Point
- **File**: `[file path]`
- **Line**: `[line number]`
- **Function**: `[function name]`

## Execution Path
1. **Lines [start]-[end]** in `[file path]`
   - **Function**: `[function name]`
   - **Description**: [What happens in this section]
   - **Key Operations**:
     - `[operation 1]`: [description]
     - `[operation 2]`: [description]
   - **Database**: `[table] - [operation]`
   - **External Calls**: `[service] - [endpoint]`

[etc.]

## Database Operations
| Step | Table | Operation | Key Fields |
|------|-------|-----------|------------|
| [Step] | [Table] | [OP] | [Fields] |

## External Service Calls
| Step | Service | Endpoint | Purpose |
|------|---------|----------|---------|
| [Step] | [Service] | [Endpoint] | [Purpose] |

## Error Handling
[Where errors are caught and how they're handled]

## Logging & Monitoring
[Where logs are written and metrics recorded]

## Related Documentation
- [Business Logic Workflow]
- [Algorithmic Flow]
```

By following these prompts and guidelines, AI agents can effectively maintain the Workflow Hub as a reliable source of process and implementation truth for the Nexus platform.
