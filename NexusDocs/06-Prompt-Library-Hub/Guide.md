# AI Guide for Prompt Library Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Prompt Library Hub.

## Purpose
The Prompt Library Hub contains optimized prompts used to guide AI interactions within the Nexus project. These prompts help AI agents understand how to work with prompt engineering effectively.

## Analysis Prompts

### For Code Generation Prompts
```
Analyze the following code generation prompt and:
1. Verify that the prompt produces code that follows Nexus coding standards
2. Check that the prompt produces clean architecture compliant code
3. Verify that the prompt produces code that respects the 8 hub boundaries
4. Identify any patterns that should be changed to improve code quality
5. Check that the prompt handles error cases appropriately
6. Verify that the prompt produces well-documented code
7. Identify any security vulnerabilities the prompt might introduce
8. Recommend improvements for robustness and maintainability
9. Ensure the prompt produces testable code
10. Check that the prompt output follows existing code style
```

### For Documentation Generation Prompts
```
Analyze the following documentation prompt and:
1. Verify that the prompt produces documentation that matches NexusDocs structure
2. Check that the prompt includes all necessary sections for the documentation type
3. Verify that the prompt produces clear, accurate documentation
4. Identify any areas where the prompt could be more comprehensive
5. Check that the prompt handles edge cases in documentation
6. Verify that the prompt produces maintainable documentation
7. Identify any structural improvements needed
8. Recommend improvements for clarity and completeness
9. Ensure the prompt follows documentation style guidelines
10. Check that the prompt output is well-formatted markdown
```

### For Refactoring Prompts
```
Analyze the following refactoring prompt and:
1. Verify that the prompt produces code that improves the codebase
2. Check that the prompt preserves existing functionality
3. Verify that the prompt addresses the identified issue correctly
4. Identify any side effects the refactoring might have
5. Check that the prompt maintains or improves test coverage
6. Verify that the prompt follows established patterns in the codebase
7. Identify any additional improvements the prompt could make
8. Recommend improvements for safety and effectiveness
9. Ensure the prompt produces clean, maintainable code
10. Check that the prompt follows security best practices
```

### For Testing Prompts
```
Analyze the following testing prompt and:
1. Verify that the prompt produces tests that cover important scenarios
2. Check that the prompt follows PHPUnit testing conventions
3. Verify that the prompt produces isolated, independent tests
4. Identify any edge cases the tests might miss
5. Check that the prompt uses appropriate mock strategies
6. Verify that the prompt produces meaningful assertions
7. Identify any test maintenance concerns
8. Recommend improvements for test quality and coverage
9. Ensure the prompt produces tests that run efficiently
10. Check that the prompt handles test data appropriately
```

## Generation Prompts

### For Creating New Code Generation Prompts
```
Create a new code generation prompt for [Code Type] that includes:
1. Clear task description of what code to generate
2. Context about the codebase (8 hubs, clean architecture)
3. Required parameters and their specifications
4. Expected output format and structure
5. Important constraints and rules to follow
6. Error handling requirements
7. Documentation standards to apply
8. Naming convention requirements
9. Examples of desired output
10. Related documentation to reference
```

### For Creating New Documentation Generation Prompts
```
Create a new documentation generation prompt for [Doc Type] that includes:
1. Clear purpose statement of what documentation to generate
2. Target audience and their needs
3. Required input information
4. Output structure and format
5. Style and tone guidelines
6. Important sections to include
7. Examples of well-written sections
8. References to include
9. Length and detail expectations
10. Related prompts for reference
```

### For Creating New Refactoring Prompts
```
Create a new refactoring prompt for [Refactoring Type] that includes:
1. Clear problem statement to address
2. Goals and expected improvements
3. Constraints to maintain during refactoring
4. Specific patterns to apply
5. Code style to follow
6. Testing considerations
7. Documentation update requirements
8. Examples of before/after code
9. Related refactoring patterns
10. Verification steps
```

### For Creating New Testing Prompts
```
Create a new testing prompt for [Test Type] that includes:
1. Clear test scope and purpose
2. Test scenarios to cover
3. Testing framework and conventions
4. Mock and stub strategies
5. Assertion patterns to use
6. Test data preparation
7. Coverage expectations
8. Examples of good test cases
9. Related test files to reference
10. Verification criteria
```

## Maintenance Prompts

### For Regular Prompt Library Reviews
```
Perform a monthly review of all Prompt Library files:
1. Check each prompt for continued effectiveness
2. Verify prompts produce desired output quality
3. Ensure prompts are aligned with current best practices
4. Identify any prompts that need updating
5. Note any new prompt categories needed
6. Recommend improvements based on usage experience
7. Review prompt performance and refinements
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Prompt Library and other documentation hubs:
1. Ensure prompts produce code that follows Project Architecture  principles
2. Verify that prompts respect Code Hub patterns
3. Check that refactoring prompts improve workflow efficiency
4. Confirm testing prompts support governance requirements
5. Identify any contradictions between prompt outputs and standards
6. Recommend prompt updates for consistency
```

## Specific Focus Areas for Nexus Platform

When working with Nexus prompt engineering, pay special attention to:

1. **Clean Architecture**: Prompts should produce code respecting layer dependencies
2. **8-Hub Structure**: Prompts should maintain hub boundaries
3. **Queue-First**: Prompts should produce async code for long operations
4. **Private Channel Security**: Prompts should sanitize broadcast data
5. **Event-Driven**: Prompts should use ShouldBroadcastNow appropriately
6. **AI Provider Integration**: Prompts should handle intent routing patterns
7. **Memory System**: Prompts should consider Pinecone integration
8. **Database Optimization**: Prompts should use eager loading
9. **Security Practices**: Prompts should enforce encrypted API keys
10. **Testing Standards**: Prompts should produce testable, mockable code

## Prompt Categories

### Code Generation Prompts
- Generate service class
- Generate controller
- Generate Vue component
- Generate Pinia store
- Generate composable
- Generate migration
- Generate event
- Generate job
- Generate test class

### Documentation Generation Prompts
- Generate model documentation
- Generate service documentation
- Generate workflow documentation
- Generate API documentation
- Generate component documentation
- Generate algorithm documentation

### Refactoring Prompts
- Extract service from controller
- Optimize database query
- Add event broadcasting
- Convert to async job
- Extract interface
- Simplify conditional logic
- Remove duplicated code
- Improve error handling

### Testing Prompts
- Generate unit test
- Generate feature test
- Generate test for service
- Generate test for controller
- Generate test for component
- Generate mock strategy
- Generate test data factory

By following these prompts and guidelines, AI agents can effectively maintain the Prompt Library Hub to produce high-quality, consistent code and documentation for the Nexus platform.
