# Technical Specifications

This document provides detailed technical specifications for the Nexus platform, covering APIs, databases, integrations, and system interfaces.

## API Specifications

### Authentication & Authorization
- **Auth Mechanism**: Laravel Sanctum token-based authentication
- **Token Format**: Bearer tokens in Authorization header
- **Token Expiry**: Configurable (default: 24 hours)
- **Refresh Tokens**: Supported for long-lived sessions
- **Permission System**: Role-based access control with fine-grained permissions
- **API Versioning**: URL-based versioning (/api/v1/, /api/v2/, etc.)
- **Rate Limiting**: Configurable per-endpoint limits (default: 60 requests/minute)

### Standard API Responses
#### Success Responses
```json
{
  "success": true,
  "data": {/* resource data */},
  "meta": {
    "timestamp": "2026-05-19T23:47:10+00:00",
    "request_id": "unique-request-identifier"
  }
}
```

#### Error Responses
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "details": {/* optional validation errors */}
  },
  "meta": {
    "timestamp": "2026-05-19T23:47:10+00:00",
    "request_id": "unique-request-identifier"
  }
}
```

### Pagination Format
```json
{
  "success": true,
  "data": [
    {/* array of resources */}
  ],
  "meta": {
    "pagination": {
      "total": 100,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "total_pages": 7,
      "links": {
        "previous": null,
        "next": "http://api.example.com/resource?page=2"
      }
    }
  }
}
```

## Database Specifications

### Connection Settings
- **Driver**: MySQL 8.0+
- **Host**: Configurable via DB_HOST
- **Port**: 3306 (default)
- **Database**: nexus (configurable via DB_DATABASE)
- **Username**: Configurable via DB_USERNAME
- **Password**: Configurable via DB_PASSWORD
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Strict Mode**: Enabled

### Connection Pooling
- **Min Connections**: 5
- **Max Connections**: 50
- **Connection Timeout**: 10 seconds
- **Read/Write Timeout**: 30 seconds

### Table Naming Conventions
- **Format**: plural, snake_case
- **Examples**: contacts, conversations, agent_tasks
- **Pivot Tables**: alphabetical order of related tables (e.g., contact_tag)

### Indexing Strategy
- **Primary Keys**: Auto-incrementing big integers
- **Foreign Keys**: Indexed with cascade constraints where appropriate
- **Common Queries**: Composite indexes on frequently filtered/sorted columns
- **Full-Text**: MySQL full-text indexes on searchable text fields
- **Spatial**: GIS indexes on location-based data (where applicable)

### Backup & Recovery
- **Automated Backups**: Daily full backups, hourly incremental
- **Retention**: 30 days for daily, 7 days for hourly
- **Point-in-Time Recovery**: Enabled via binary logging
- **Verification**: Weekly backup restoration tests

## External Service Integrations

### AI Providers
#### Gemini (Google)
- **API Endpoint**: https://generativelanguage.googleapis.com/v1beta/models/
- **Authentication**: API key in request header
- **Rate Limits**: 60 requests/minute (tier-dependent)
- **Supported Models**: gemini-pro, gemini-pro-vision
- **Features**: Text generation, multimodal understanding
- **Fallback**: Automatic retry with exponential backoff

#### OpenAI
- **API Endpoint**: https://api.openai.com/v1/
- **Authentication**: Bearer token in Authorization header
- **Rate Limits**: Tier-based (requests/minute and tokens/minute)
- **Supported Models**: gpt-4, gpt-4-turbo, gpt-3.5-turbo
- **Features**: Text generation, embeddings, function calling
- **Fallback**: Automatic retry with exponential backoff

#### Anthropic
- **API Endpoint**: https://api.anthropic.com/v1/
- **Authentication**: API key in request header
- **Rate Limits**: Tier-based
- **Supported Models**: claude-3-opus, claude-3-sonnet, claude-3-haiku
- **Features**: Text generation, vision capabilities
- **Fallback**: Automatic retry with exponential backoff

### Vector Store
#### Pinecone
- **Environment**: Configurable (us-west1-gcp, etc.)
- **Index Name**: nexus-memory-vectors (configurable)
- **Dimension**: 768 (for sentence-transformers/all-MiniLM-L6-v2)
- **Metric**: Cosine similarity
- **Pod Type**: p1.x1 (development), p2.x1 (production)
- **Replicas**: 1 (dev), 2+ (prod)
- **Metadata Fields**: text content, source reference, timestamps, hub origin
- **Namespace Strategy**: Separate namespaces per memory layer

### External APIs
#### WhatsApp (WAHA)
- **URL**: Configurable via WAHA_URL
- **Authentication**: API token via WAHA_API_TOKEN
- **Features**: Message sending/receiving, group management
- **Webhooks**: Incoming message notifications
- **Rate Limits**: Provider-dependent

### Caching & Queueing
#### Redis
- **Host**: Configurable via REDIS_HOST
- **Port**: 6379 (default)
- **Password**: Configurable via REDIS_PASSWORD (if set)
- **Database**: 0 for cache, 1 for queues, 2 for sessions
- **Persistence**: AOF enabled for critical data
- **Memory Policy**: allkeys-lru for cache databases
- **Max Memory**: Configurable per use case
- **Connection Timeout**: 2.5 seconds
- **Read/Write Timeout**: 5 seconds

## System Interfaces

### Event Broadcasting
#### Laravel Reverb (WebSocket)
- **App ID**: Configurable via REVERB_APP_ID
- **App Key**: Configurable via REVERB_APP_KEY
- **App Secret**: Configurable via REVERB_APP_SECRET
- **Host**: Configurable (typically same as APP_URL)
- **Port**: 8080 (default)
- **Scheme**: https (wss for WebSocket connections)
- **Authentication**: Laravel Sanctum token validation
- **Channel Types**: 
  - Private: For user-specific data (prefixed with "private-")
  - Presence: For online user tracking (prefixed with "presence-")
  - Public: For broadcast data (prefixed with "public-")
- **Event Sanitization**: All broadcasted data must be sanitized to remove sensitive information
- **Maximum Payload**: 64KB per event
- **Reconnection**: Automatic with exponential backoff

### File Storage
#### Local Development
- **Driver**: local
- **Root**: storage/app/
- **Visibility**: private by default
- **URL**: /storage/ (symbolic link from public/storage)

#### Production (Cloud)
- **Driver**: s3 (or equivalent cloud provider)
- **Bucket**: Configurable via FILESYSTEM_DRIVER and associated credentials
- **Region**: Configurable
- **CDN**: Optional integration for global distribution
- **Visibility**: private with signed URLs for temporary access
- **Lifecycle Rules**: Automatic deletion of temporary files after 24 hours

## Security Specifications

### Data Protection
#### Encryption at Rest
- **Sensitive Fields**: API keys, personal identifiers, credentials
- **Algorithm**: AES-256-GCM
- **Key Management**: 
  - Master key stored in environment variable (ENCRYPTION_KEY)
  - Key rotation supported via key versioning
  - Automatic re-encryption during key rotation
- **Fields Encrypted**: 
  - api_keys table (key field)
  - settings table (value field for sensitive settings)
  - Any PII fields as required by compliance

#### Encryption in Transit
- **TLS Version**: 1.2 or higher
- **Cipher Suites**: Strong forward secrecy ciphers
- **HSTS**: Enabled with max-age of 31536000 seconds
- **Certificate Validation**: Strict validation of peer certificates

### Input Validation
#### Validation Rules
- **Required Fields**: Explicitly marked in request validation
- **Data Types**: Strict type checking (string, integer, boolean, array, object)
- **Length Limits**: 
  - Strings: Maximum 65,535 characters (configurable per field)
  - Arrays: Maximum 1,000 items (configurable per endpoint)
  - Objects: Maximum nesting depth of 10
- **Format Validation**: 
  - Email: RFC 5322 compliant
  - URLs: Valid URI format
  - Dates: ISO 8601 format
  - UUIDs: Version 4 format
- **Sanitization**: 
  - HTML entities encoded for display contexts
  - SQL injection prevention via parameterized queries
  - XSS prevention via output encoding
  - Command injection prevention via strict parameter validation

### Authentication & Session Security
#### Token Security
- **Token Length**: 64 characters minimum
- **Entropy**: Cryptographically secure random generation
- **Storage**: Hashed in database (bcrypt with cost factor 12)
- **Transmission**: HTTPS only, secure cookies
- **Rotation**: On privilege change, periodic rotation (configurable)
- **Revocation**: Immediate on logout/password change, timed expiration

#### Session Management
- **Driver**: Redis (database 2)
- **Lifetime**: Configurable (default: 120 minutes)
- **Garbage Collection**: Automatic cleanup of expired sessions
- **Concurrent Limits**: Configurable maximum sessions per user
- **IP Binding**: Optional IP address binding for sensitive operations
- **User Agent Validation**: Optional validation for session integrity

## Monitoring & Observability

### Logging Structure
#### Log Channels
- **stack**: Multi-channel logger (default)
- **single**: Single file logger (storage/logs/laravel.log)
- **daily**: Rotating daily files (storage/logs/laravel-YYYY-MM-DD.log)
- **syslog**: System logger
- **errorlog**: PHP error logger
- **custom**: Application-specific channels per hub

#### Log Levels
- **emergency**: System is unusable
- **alert**: Immediate action required
- **critical**: Critical conditions
- **error**: Error conditions
- **warning**: Warning conditions
- **notice**: Normal but significant conditions
- **informational**: Informational messages
- **debug**: Debug-level messages

#### Log Format
```json
{
  "timestamp": "2026-05-19T23:47:10+00:00",
  "level": "info",
  "message": "Log message",
  "context": {
    "hub": "contacts",
    "user_id": 123,
    "request_id": "req-abc123",
    "trace_id": "trace-xyz789"
  }
}
```

### Metrics Collection
#### Application Metrics
- **Request Rate**: Requests per second by endpoint
- **Response Time**: Percentiles (p50, p95, p99) by endpoint
- **Error Rate**: Percentage of failed requests
- **Throughput**: Bytes transferred per second
- **Business Metrics**: Hub-specific KPIs (agents created, memories stored, etc.)

#### System Metrics
- **CPU Usage**: Percentage utilization per core
- **Memory Usage**: RSS and virtual memory consumption
- **Disk I/O**: Read/write operations per second
- **Network I/O**: Bytes sent/received per second
- **Database Connections**: Active/idle connection counts
- **Queue Depth**: Jobs waiting in each queue
- **Cache Hit Ratio**: Percentage of cache hits vs misses

#### Health Checks
- **Liveness Probe**: Basic application responsiveness
- **Readiness Probe**: Ability to serve traffic (dependencies healthy)
- **Startup Probe**: Application initialization completion
- **Dependency Checks**: Database, Redis, external APIs, Pinecone

## Performance Specifications

### Response Time Targets
- **API Endpoints**: 
  - 95% of requests < 200ms
  - 99% of requests < 500ms
  - Maximum 2 seconds for complex operations
- **WebSocket Updates**: < 100ms from event to client update
- **Database Queries**: 
  - Simple queries: < 10ms
  - Complex queries: < 100ms
  - Reports/aggregations: < 2 seconds
- **External API Calls**: 
  - AI providers: < 3 seconds (with fallback)
  - Pinecone: < 100ms for vector operations
  - WhatsApp: < 500ms for message operations

### Throughput Requirements
- **API Requests**: 1,000 requests/second sustained
- **WebSocket Connections**: 10,000 concurrent connections
- **Database Transactions**: 500 transactions/second
- **Queue Processing**: 1,000 jobs/second per worker
- **File Uploads**: 100 MB/second aggregate bandwidth
- **Vector Searches**: 100 searches/second with < 50ms latency

### Resource Utilization
- **Memory**: 
  - Application: < 50% of allocated RAM
  - Database: < 60% of allocated RAM
  - Redis: < 70% of allocated RAM
- **CPU**: 
  - Average: < 60% utilization
  - Peak: < 85% utilization for < 5 minutes
- **Disk**: 
  - Utilization: < 80% capacity
  - IOPS: < 80% of provisioned capacity
- **Network**: 
  - Bandwidth: < 70% of provisioned capacity
  - Packet loss: < 0.1%

### Scaling Characteristics
- **Horizontal Scaling**: 
  - Stateless services: Linear scaling with instance count
  - Database: Read replicas for scaling reads
  - Queue workers: Linear scaling with worker count
- **Vertical Scaling Limits**: 
  - Database: Scale up to 32 vCPU, 256GB RAM
  - Application: Scale up to 16 vCPU, 64GB RAM
  - Redis: Scale up to 8 vCPU, 32GB RAM
- **Auto-Scaling Triggers**:
  - CPU > 70% for 5 minutes
  - Memory > 80% for 5 minutes
  - Queue depth > 1000 jobs for 2 minutes
  - Response time > 1s p95 for 5 minutes

## Deployment Specifications

### Environment Strategy
#### Development
- **Branch**: feature/* or develop
- **Database**: SQLite in-memory for testing, MySQL for manual testing
- **Cache**: Array driver, Redis optional
- **Queue**: Sync mode (immediate execution)
- **External APIs**: Mocked or sandbox credentials
- **Debug**: Enabled with verbose logging
- **Asset Compilation**: Development mode (unminimized, source maps)

#### Staging
- **Branch**: release/* or staging
- **Database**: MySQL mirroring production schema
- **Cache**: Redis production configuration
- **Queue**: Redis connection with workers
- **External APIs**: Sandbox or test credentials
- **Debug**: Limited to error level
- **Asset Compilation**: Production mode with source maps

#### Production
- **Branch**: main or master
- **Database**: MySQL with read replicas
- **Cache**: Redis cluster configuration
- **Queue**: Multiple workers with supervision
- **External APIs**: Production credentials with rate limiting
- **Debug**: Disabled (error level only)
- **Asset Compilation**: Production mode (minimized, cached)
- **HTTPS**: Enforced with valid certificates
- **CDN**: Enabled for static assets

### Deployment Process
#### Pre-Deployment
1. **Code Review**: Minimum 2 approvers for production changes
2. **Automated Testing**: 
   - Unit tests: > 90% coverage
   - Feature tests: Critical paths covered
   - Security scans: No high/severe vulnerabilities
3. **Dependency Check**: 
   - PHP: Composer audit
   - JavaScript: npm audit
   - License compliance verified
4. **Database Backup**: Full backup before migration
5. **Notification**: Deployment announcement to stakeholders

#### Deployment Steps
1. **Maintenance Mode**: Application put into maintenance mode
2. **Code Update**: 
   - Pull latest code from deployment branch
   - Composer install --no-dev --optimize-autoloader
   - npm ci && npm run build --production
3. **Configuration**: 
   - Environment variables validated
   - Configuration cache cleared and rebuilt
4. **Database**: 
   - Migrations run with --force flag
   - Seeders run for reference data (if needed)
5. **Cache**: 
   - Application cache cleared
   - Views compiled
   - Routes cached
6. **Queue**: 
   - Workers restarted gracefully
   - Supervisor configuration reloaded
7. **Assets**: 
   - Public directory updated
   - Symbolic links refreshed
   - CDN cache purged (if applicable)
8. **Services**: 
   - Web server restarted (nginx/php-fpm)
   - Horizon supervisors restarted
   - Reverb WebSocket server restarted
9. **Health Checks**: 
   - Application endpoints verified
   - Database connectivity confirmed
   - External service connections tested
   - WebSocket handshake validated

#### Post-Deployment
1. **Maintenance Mode**: Application taken out of maintenance mode
2. **Smoke Tests**: Critical user journeys validated
3. **Monitoring**: Enhanced monitoring for 30 minutes post-deploy
4. **Logging**: Debug logs enabled temporarily if needed
5. **Notification**: Deployment completion announcement
6. **Rollback Plan**: 
   - Database backup ready for restoration
   - Previous code version tagged and available
   - Automated rollback triggers on health check failures

### Rollback Procedure
1. **Detection**: Health check failures or manual trigger
2. **Maintenance Mode**: Application put into maintenance mode
3. **Code Revert**: 
   - Checkout previous deployment tag
   - Composer install --no-dev --optimize-autoloader
   - npm ci && npm run build --production
4. **Configuration**: 
   - Restore previous environment variables
   - Rebuild configuration cache
5. **Database**: 
   - Run migration rollbacks if schema changed
   - Restore from backup if data corruption occurred
6. **Cache**: 
   - Clear all caches
   - Rebuild as needed
7. **Services**: 
   - Restart all application services
8. **Validation**: 
   - Health checks pass
   - Smoke tests successful
9. **Notification**: Rollback completion announcement
10. **Post-Mortem**: Incident analysis and prevention planning

## Related Documentation
- [High-Level Overview](../01-Architecture-Hub/01-High-Level-Overview.md)
- [System Requirements](../01-Architecture-Hub/02-System-Requirements.md)
- [Data Models](./04-Data-Models.md)
- [Architecture Details](../Docs/ARCHITECTURE_DETAILS.md) (existing documentation)
- [API Documentation](../02-Project-Code/01-Backend/01-Hubs/*/*-Controllers.md) (to be created)
