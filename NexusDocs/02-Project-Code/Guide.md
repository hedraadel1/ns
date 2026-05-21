# AI Guide for Code Hub Documentation

This file contains specific prompts and instructions for AI agents to analyze, generate, update, or maintain documentation within the Code Hub.

## Purpose
The Code Hub contains detailed documentation of the platform's codebase, including backend services, frontend components, and interfaces. These prompts help AI agents understand how to work with code documentation effectively.

## Analysis Prompts

### For Backend Service Documents
```
Analyze the following service class document and:
1. Verify the service's responsibilities match its implementation
2. Check that all public methods are documented with parameters, return types, and exceptions
3. Identify any dependencies that are not declared in the constructor (service locator anti-pattern)
4. Verify that the service follows the Single Responsibility Principle
5. Check for proper use of dependency injection (type-hinted interfaces in constructor)
6. Identify any business logic that should be moved to a domain service or model
7. Verify that the service does not contain HTTP-specific code (request/response handling)
8. Check for proper error handling and logging
9. Identify any missing documentation for private methods that are complex
10. Recommend updates to align documentation with current implementation
```

### For Backend Controller Documents
```
Analyze the following controller document and:
1. Verify the controller is thin and delegates to services
2. Check that all endpoints are documented with HTTP method, path, parameters, and responses
3. Verify that validation is done via Form Requests (not in controller methods)
4. Check that the controller returns appropriate HTTP status codes
5. Verify that the controller uses resource collections or API resources for responses
6. Identify any business logic in the controller that should be moved to a service
7. Check for proper use of dependency injection in the constructor
8. Verify that the controller handles authentication and authorization via middleware
9. Identify any missing documentation for error responses
10. Recommend updates to align documentation with current implementation
```

### For Backend Interface/Contract Documents
```
Analyze the following interface/document and:
1. Verify the interface has a single, well-defined responsibility
2. Check that all methods are documented with parameters, return types, and exceptions
3. Verify that the interface follows the Interface Segregation Principle
4. Check for proper naming conventions (no "I" prefix in PHP)
5. Identify any methods that should be split into separate interfaces
6. Verify that implementing classes are documented elsewhere
7. Check that the interface does not contain any implementation details
8. Recommend updates to align documentation with current implementation
```

### For Frontend Component Documents
```
Analyze the following Vue component document and:
1. Verify the component has a single, well-defined responsibility
2. Check that all props are documented with type, default, and description
3. Verify that all events are documented with payload type and description
4. Check that the component follows Vue 3 Composition API best practices
5. Verify that the component does not contain complex business logic (should use services/composables)
6. Check for proper use of defineExpose if needed
7. Verify that the component template is semantic and accessible
8. Check for proper use of v-bind and v-on shorthand
9. Identify any missing documentation for slots
10. Recommend updates to align documentation with current implementation
```

### For Frontend Store Documents
```
Analyze the following Pinia store document and:
1. Verify the store has a single, well-defined responsibility (domain-specific)
2. Check that all state properties are documented with type and description
3. Verify that all getters are documented with return type and description
4. Check that all actions are documented with parameters, return type, and description
5. Verify that the store follows the principle of deriving state where possible
6. Check for proper use of plugins if applicable
7. Identify any missing documentation for subscription methods
8. Recommend updates to align documentation with current implementation
```

### For Frontend Composable Documents
```
Analyze the following Vue composable document and:
1. Verify the composable has a single, well-defined responsibility
2. Check that all returned properties and methods are documented with type and description
3. Verify that the composable follows Vue 3 Composition API best practices
4. Check for proper error handling and loading states
5. Verify that the composable does not contain complex business logic (should use services)
6. Check for proper cleanup of side effects (timers, event listeners, etc.)
7. Identify any missing documentation for optional parameters
8. Recommend updates to align documentation with current implementation
```

## Generation Prompts

### For Creating New Backend Service Documents
```
Create a new service document for [ServiceClass] that includes:
1. Purpose and responsibilities of the service
2. Dependencies (constructor injection) with types and purposes
3. Public methods with:
   - Description
   - Parameters (type, description)
   - Return type and description
   - Exceptions that may be thrown
4. Usage examples (if applicable)
5. Related services or components
6. Important considerations or limitations
7. References to related documentation (models, events, etc.)

Use the existing service documents as templates for structure and style.
```

### For Creating New Backend Controller Documents
```
Create a new controller document for [ControllerClass] that includes:
1. Purpose and responsibilities of the controller
2. Dependencies (constructor injection) with types and purposes
3. API endpoints with:
   - HTTP method and path
   - Description
   - Request parameters (query, route, body) with types and descriptions
   - Request validation (if using Form Requests)
   - Response format (success and error)
   - HTTP status codes
4. Related services or components
5. Important considerations (authentication, authorization, rate limiting)
6. References to related documentation (services, requests, resources)
```

### For Creating New Frontend Component Documents
```
Create a new component document for [ComponentName] that includes:
1. Purpose and responsibilities of the component
2. Props with:
   - Name
   - Type
   - Default value (if any)
   - Description
3. Events with:
   - Name
   - Payload type
   - Description
4. Slots with:
   - Name
   - Description
   - Scope (if applicable)
5. Usage example
6. Related components or composables
7. Important considerations (accessibility, performance, responsiveness)
8. References to related documentation (stores, composables, services)
```

### For Creating New Frontend Store Documents
```
Create a new store document for [StoreName] that includes:
1. Purpose and responsibilities of the store
2. State properties with:
   - Name
   - Type
   - Description
3. Getters with:
   - Name
   - Return type
   - Description
4. Actions with:
   - Name
   - Parameters (type, description)
   - Return type (if Promise, describe resolved value)
   - Description
5. Usage example
6. Related stores or composables
7. Important considerations (performance, memory leaks, debugging)
8. References to related documentation (APIs, services, components)
```

### For Creating New Frontend Composable Documents
```
Create a new composable document for [ComposableName] that includes:
1. Purpose and responsibilities of the composable
2. Returned properties with:
   - Name
   - Type
   - Description
3. Returned methods with:
   - Name
   - Parameters (type, description)
   - Return type (if Promise, describe resolved value)
   - Description
4. Usage example
5. Related composables or services
6. Important considerations (side effects, cleanup, performance)
7. References to related documentation (APIs, services, components)
```

## Maintenance Prompts

### For Regular Code Documentation Reviews
```
Perform a monthly review of all Code Hub documents:
1. Check each document for accuracy against current codebase
2. Verify all code examples are syntactically correct and follow current conventions
3. Ensure all dependencies and related components are correctly referenced
4. Identify any duplicated information that should be consolidated
5. Note any obsolete documentation for removed or deprecated code
6. Recommend retirement or archiving of obsolete documents
7. Suggest new documents needed for recent code additions
```

### For Cross-Hub Consistency Checks
```
Verify consistency between Code Hub and other documentation hubs:
1. Ensure code documentation reflects architectural principles from Project Architecture 
2. Verify that workflow documentation aligns with code-level implementation traces
3. Check that AI context files reference correct code components
4. Confirm governance documentation enforces coding standards reflected in code docs
5. Identify any contradictions between hubs that need resolution
6. Recommend updates to maintain consistency across all documentation
```

## Specific Focus Areas for Nexus Platform

When working with Nexus code documentation, pay special attention to:

1. **Service Layer Purity**: Ensure documentation emphasizes that business logic lives in services, not models or controllers
2. **Dependency Injection**: Verify that all dependencies are properly injected via constructors
3. **Event-Driven Patterns**: Check that services emit events appropriately and document which events they listen to
4. **Real-Time Requirements**: Validate that components using WebSocket or broadcasting are documented
5. **API Contracts**: Ensure controller documentation matches actual API behavior and validation
6. **Component Reusability**: Check that frontend components are documented with clear props and events for reuse
7. **State Management**: Verify that Pinia store documentation reflects actual state mutation patterns
8. **Composable Usage**: Ensure composables are documented with clear inputs/outputs and side effects
9. **Error Handling**: Check that all documents mention error handling strategies and logging
10. **Testing**: Verify that documents reference unit tests where applicable

## Output Format Guidelines

When generating or updating code documentation, follow these formats:

### For Service Documents
```
# [ServiceName] Service

## Purpose
[Brief statement of what this service does]

## Responsibilities
- [Specific responsibility 1]
- [Specific responsibility 2]
- [etc.]

## Dependencies
- [Dependency 1] ([Type]): [Purpose]
- [Dependency 2] ([Type]): [Purpose]
- [etc.]

## Public Methods
### [methodName]([parameters])
- **Description**: [What this method does]
- **Parameters**:
  - `[paramName]` ([Type]): [Description]
  - [etc.]
- **Returns**: [Type] - [Description]
- **Throws**: 
  - `[ExceptionType]`: [Condition]
  - [etc.]

## Usage Example
```php
[Example code showing how to use the service]
```

## Important Considerations
- [Performance/Scaling note]
- [Security note]
- [Maintainability note]
- [Testing note]
```

### For Controller Documents
```
# [ControllerName] Controller

## Purpose
[Brief statement of what this controller does]

## Responsibilities
- [Specific responsibility 1]
- [Specific responsibility 2]
- [etc.]

## Dependencies
- [Dependency 1] ([Type]): [Purpose]
- [Dependency 2] ([Type]): [Purpose]
- [etc.]

## API Endpoints
### [HTTP METHOD] [path]
- **Description**: [What this endpoint does]
- **Parameters**:
  - `[paramName]` ([Type], [query/route/body]): [Description]
  - [etc.]
- **Request Validation**: [Form Request class name or inline validation rules]
- **Success Response**:
  - **Status**: [HTTP status code]
  - **Body**: [Description of response format]
- **Error Responses**:
  - **[HTTP status code]**: [Description]
  - [etc.]

## Important Considerations
- [Authentication/Authorization]
- [Rate Limiting]
- [Validation]
- [Error Handling]
```

### For Component Documents
```
# [ComponentName] Component

## Purpose
[Brief statement of what this component does]

## Props
| Prop Name | Type | Default | Description |
|-----------|------|---------|-------------|
| [propName] | [Type] | [defaultValue] | [Description] |
| [etc.] | [etc.] | [etc.] | [etc.] |

## Events
| Event Name | Payload Type | Description |
|------------|--------------|-------------|
| [eventName] | [Type] | [Description] |
| [etc.] | [etc.] | [etc.] |

## Slots
| Slot Name | Description | Scope |
|-----------|-------------|-------|
| [slotName] | [Description] | [scope] |
| [etc.] | [etc.] | [etc.] |

## Usage Example
```vue
[Example code showing how to use the component]
```

## Important Considerations
- [Accessibility]
- [Performance]
- [Responsiveness]
- [Reusability]
```

### For Store Documents
```
# [StoreName] Store

## Purpose
[Brief statement of what this store does]

## State
| State Property | Type | Description |
|----------------|------|-------------|
| [stateName] | [Type] | [Description] |
| [etc.] | [etc.] | [etc.] |

## Getters
| Getter Name | Return Type | Description |
|-------------|-------------|-------------|
| [getterName] | [Type] | [Description] |
| [etc.] | [etc.] | [etc.] |

## Actions
| Action Name | Parameters | Return Type | Description |
|-------------|------------|-------------|-------------|
| [actionName] | [paramName]: [Type], ... | [Type] (if Promise, describe resolved value) | [Description] |
| [etc.] | [etc.] | [etc.] | [etc.] |

## Usage Example
```js
[Example code showing how to use the store]
```

## Important Considerations
- [Performance]
- [Memory Leaks]
- [Debugging]
- [Plugins]
```

### For Composable Documents
```
# [ComposableName] Composable

## Purpose
[Brief statement of what this composable does]

## Returned Properties
| Property Name | Type | Description |
|---------------|------|-------------|
| [propertyName] | [Type] | [Description] |
| [etc.] | [etc.] | [etc.] |

## Returned Methods
| Method Name | Parameters | Return Type (if Promise) | Description |
|-------------|------------|--------------------------|-------------|
| [methodName] | [paramName]: [Type], ... | [Type] (describe resolved value) | [Description] |
| [etc.] | [etc.] | [etc.] | [etc.] |

## Usage Example
```js
[Example code showing how to use the composable]
```

## Important Considerations
- [Side Effects]
- [Cleanup]
- [Performance]
- [Reusability]
```

By following these prompts and guidelines, AI agents can effectively maintain the Code Hub as a reliable source of code truth for the Nexus platform.
