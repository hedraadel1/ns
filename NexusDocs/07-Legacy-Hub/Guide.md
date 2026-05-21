# AI Guide for Legacy Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Legacy Hub.

## Purpose
The Legacy Hub contains documentation for unused classes and components to preserve historical knowledge while indicating they are deprecated. These prompts help AI agents understand how to work with legacy documentation effectively.

## Analysis Prompts

### For Deprecated Class Documents
```
Analyze the following deprecated class document and:
1. Verify that the deprecation status is clearly marked
2. Check that the deprecation reason is documented
3. Verify that the document links to replacement components
4. Identify any migration path or upgrade instructions
5. Check that the document notes any security concerns
6. Verify that usage examples show the deprecated pattern clearly
7. Identify any remaining references to this class in the codebase
8. Recommend cleanup actions for the deprecated code
9. Ensure the document serves historical reference purposes
10. Check that the document is kept up-to-date with code status
```

### For Unused Component Documents
```
Analyze the following unused component document and:
1. Verify that the unused status is clearly marked
2. Check that the reason for removal is documented
3. Verify that the document links to successor components
4. Identify any UI/UX considerations for replacement
5. Check that the document notes any accessibility issues
6. Verify that the document includes visual design references
7. Identify any remaining references in tests or documentation
8. Recommend cleanup actions for the unused component
9. Ensure the document explains the evolution of the design
10. Check that the document notes any performance implications
```

### For Removed API Documents
```
Analyze the following removed API document and:
1. Verify that the removal status is clearly marked
2. Check that the version in which API was removed is documented
3. Verify that the document links to replacement endpoints
4. Identify any migration path for API consumers
5. Check that the document notes any data loss implications
6. Verify that the document includes example requests/responses
7. Identify any remaining references or documentation links
8. Recommend cleanup actions for the removed API
9. Ensure the document explains the rationale for removal
10. Check that the document notes any security improvements
```

## Generation Prompts

### For Creating New Deprecated Class Documents
```
Create a new deprecated class document for [ClassName] that includes:
1. Clear deprecation notice and status
2. Original purpose and functionality
3. Reason for deprecation
4. When the class was deprecated and by whom
5. Replacement components or alternatives
6. Migration path and instructions
7. Usage examples (showing deprecated pattern)
8. Known issues and limitations
9. Security or performance concerns
10. Related deprecated or replacement documentation
```

### For Creating New Unused Component Documents
```
Create a new unused component document for [ComponentName] that includes:
1. Clear unused status notice
2. Original purpose and UI context
3. Reason for removal from codebase
4. When the component was removed and by whom
5. Successor components or design patterns
6. Visual design references (screenshots, mockups)
7. Usage examples (showing original implementation)
8. Accessibility considerations
9. Performance implications of the replacement
10. Related component or design documentation
```

### For Creating New Removed API Documents
```
Create a new removed API document for [Endpoint] that includes:
1. Clear removal notice and status
2. Original endpoint specification (method, path, parameters)
3. When the API was removed and by which version
4. Reason for removal
5. Successor endpoints or alternative approaches
6. Example requests and responses from production
7. Migration guide for API consumers
8. Data implications or transformations needed
9. Security improvements in replacement
10. Related API or documentation
```

## Maintenance Prompts

### For Regular Legacy Documentation Reviews
```
Perform a quarterly review of all Legacy Hub documents:
1. Check each document for accuracy against code state
2. Verify that deprecated items are truly unused in production
3. Identify any documents that can be archived or removed
4. Note any legacy items that should have documentation
5. Recommend cleanup actions for fully obsolete items
6. Update migration guides based on user feedback
7. Verify that replacement references are accurate
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Legacy Hub and other documentation hubs:
1. Ensure legacy documentation doesn't contradict current docs
2. Verify that replacement references are accurate
3. Check that migration paths are feasible
4. Identify any legacy items still referenced in other hubs
5. Recommend updates to reflect current code state
6. Ensure clear demarcation between legacy and current
```

## Specific Focus Areas for Nexus Platform

When working with Nexus legacy documentation, pay special attention to:

1. **AI Models Hub Gaps**: Many claimed implementations are not actually done
2. **Async Engine**: UP-003 refactoring may leave behind legacy patterns
3. **Frontend Components**: UP-005 view fixes may deprecate older implementations
4. **Memory System**: Early versions before Pinecone integration
5. **Agent System**: Pre-dynamic provider registry implementations
6. **Workflow Builder**: Phase-1 skeleton before full drag-drop
7. **API Versioning**: Changes from v0 to v1 endpoints
8. **Database Schema**: Pre-migration versions
9. **Configuration**: Early environment variable patterns
10. **Testing**: Older test patterns before current conventions

## Legacy Status Markers

### Status Levels
- **Deprecated**: Still in code but should not be used for new development
- **Unused**: Removed from main branch but kept for reference
- **Removed**: Completely deleted from codebase
- **Superseded**: Replaced by newer implementation
- **Experimental**: Never made it to production

### Visual Indicators
```
⚠️ DEPRECATED - This [component/class/API] is deprecated and should not be used for new development.
🗑️ REMOVED - This [component/class/API] has been removed from the codebase.
💀 UNUSED - This [component/class/API] exists in the codebase but is not actively used.
🔄 SUPERSEDED - This [component/class/API] has been replaced by [new component].
```

## Related Documentation
- [Code Hub Guide](../02-Project-Code/Guide.md) - For current implementation patterns
- [Project Architecture ](../01-Architecture-Hub/) - For understanding architectural evolution
- [Governance Hub](../05-Governance-Hub/) - For deprecation and cleanup policies

By following these prompts and guidelines, AI agents can effectively maintain the Legacy Hub to preserve historical knowledge while guiding developers toward current best practices for the Nexus platform.
