# AI Guide for Governance Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Governance Hub.

## Purpose
The Governance Hub contains documentation for testing protocols, update/deployment rules, and coding standards. These prompts help AI agents understand how to work with governance documentation effectively.

## Analysis Prompts

### For Testing Protocol Documents
```
Analyze the following testing protocol document and:
1. Verify that the testing guidelines align with PHPUnit best practices
2. Check that the document follows the project's test organization (Unit vs Feature)
3. Verify that all testing patterns mentioned are actually used in the codebase
4. Identify any testing approaches that are missing or under-documented
5. Check that mock strategies are appropriate for the service types
6. Verify that database testing guidance includes transactions and factories
7. Identify any inconsistencies with the phpunit.xml configuration
8. Recommend improvements for test coverage and maintainability
9. Ensure the document emphasizes queue-first and async testing principles
10. Check that integration testing with external APIs is properly addressed
```

### For Update/Deployment Rule Documents
```
Analyze the following deployment rule document and:
1. Verify that the deployment steps align with Laravel deployment best practices
2. Check that the checklist items are comprehensive and actionable
3. Verify that environment variable documentation is complete and accurate
4. Identify any deployment considerations specific to external services (AI providers, Pinecone)
5. Check that the backup and restore procedures cover all critical data
6. Verify that versioning policy aligns with semantic versioning principles
7. Identify any safety checks missing from the deployment process
8. Recommend improvements for deployment reliability and safety
9. Ensure the document covers rollback procedures appropriately
10. Check that monitoring and health check considerations are included
```

### For Coding Standard Documents
```
Analyze the following coding standard document and:
1. Verify that standards align with PSR-12 for PHP code
2. Check that naming conventions match the project's established patterns
3. Verify that directory structure guidelines match actual codebase
4. Identify any inconsistencies with existing code in the repository
5. Check that documentation standards promote clear, useful documentation
6. Verify that standards address security concerns
7. Identify any missing areas (error handling, logging, etc.)
8. Recommend improvements for code quality and maintainability
9. Ensure standards support the clean architecture principles
10. Check that standards are enforceable via linters/formatters
```

## Generation Prompts

### For Creating New Testing Protocol Documents
```
Create a new testing protocol document for [Testing Type] that includes:
1. Purpose and scope of the testing approach
2. Prerequisites and setup requirements
3. Step-by-step testing procedure with:
   - Test organization and file location
   - Naming conventions for tests
   - Assertion patterns and best practices
   - Mock and stub strategies
   - Database handling (transactions, factories)
   - External service mocking
4. Examples of well-written tests
5. Common pitfalls and how to avoid them
6. Integration with CI/CD pipeline
7. Coverage targets and measurement
8. Related documentation
9. Troubleshooting common issues
10. References to testing tools and resources
```

### For Creating New Update/Deployment Rule Documents
```
Create a new deployment rule document for [Deployment Aspect] that includes:
1. Purpose and importance of the deployment consideration
2. Prerequisites and pre-deployment checks
3. Step-by-step procedure with:
   - Commands and expected outputs
   - Safety checks at each step
   - Verification methods
   - Rollback procedures
4. Common failure scenarios and recovery steps
5. Validation and post-deployment verification
6. Security considerations
7. Performance implications
8. Automation opportunities
9. Related deployment steps
10. References to tools and documentation
```

### For Creating New Coding Standard Documents
```
Create a new coding standard document for [Language/Framework Area] that includes:
1. Purpose and scope of the coding standards
2. File and directory naming conventions
3. Code organization patterns with examples
4. Best practices for:
   - Error handling
   - Logging
   - Security
   - Performance
   - Testing
5. Anti-patterns to avoid with examples
6. Code review checklist items
7. Automation tools (linters, formatters)
8. References to style guides
9. Related documentation
10. Change history
```

## Maintenance Prompts

### For Regular Governance Documentation Reviews
```
Perform a quarterly review of all Governance Hub documents:
1. Check each document for alignment with current best practices
2. Verify all procedures are still accurate and effective
3. Ensure all tool recommendations are current
4. Identify any gaps in governance coverage
5. Note any new areas needing governance documentation
6. Recommend updates based on recent project learnings
7. Verify compliance requirements are up-to-date
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Governance Hub and other documentation hubs:
1. Ensure testing standards support architectural principles from Architecture Hub
2. Verify that coding standards are reflected in Code Hub documentation
3. Check that deployment rules consider workflow complexity from Workflow Hub
4. Confirm AI context includes governance constraints
5. Identify any contradictions between governance and other hubs
6. Recommend updates to maintain governance consistency
```

## Specific Focus Areas for Nexus Platform

When working with Nexus governance documentation, pay special attention to:

1. **Queue-First Principle**: All testing and deployment should account for async job processing
2. **Private Channel Security**: Documentation should emphasize sanitizing broadcast data
3. **API Key Protection**: Standards should require encrypted storage and never expose keys
4. **Clean Architecture**: Coding standards should enforce layer dependency rules
5. **Event-Driven Patterns**: Testing should cover event emission and handling
6. **AI Provider Integration**: Governance should address rate limiting and fallback strategies
7. **Memory System**: Standards should cover Pinecone integration and testing
8. **WebSocket Security**: Documentation should address Reverb integration testing
9. **Database Optimization**: Standards should emphasize eager loading and query optimization
10. **Environment Security**: Documentation should stress never committing .env files

By following these prompts and guidelines, AI agents can effectively maintain the Governance Hub to ensure consistent, secure, and maintainable code for the Nexus platform.