# Changelog

All notable changes to the Navigatr plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.9.0] - 2025-01-07

### Added

- Initial beta release of Navigatr plugin
- Automatic badge issuance on course completion
- Site-level Navigatr API configuration
- Course-level provider and badge mapping
- Multi-environment support (production, staging)
- Token management with automatic refresh
- Caching for providers and badges
- Complete audit trail for badge issuance
- GDPR compliance with privacy API
- Admin settings page with connection testing
- Course settings integration
- Adhoc task system for background badge issuance
- Retry logic with exponential backoff
- Idempotency protection against duplicate issuances
- Comprehensive error handling and logging
- Database schema for mappings and audit records
- Capability-based access control
- Language string support
- README documentation

### Technical Details

- **Moodle Compatibility**: 4.1 LTS, 5.x
- **PHP Requirements**: 8.2, 8.3
- **Database Tables**: `local_navigatr_map`, `local_navigatr_audit`
- **API Integration**: Navigatr REST API v1
- **Caching**: Moodle Universal Cache (MUC)
- **Security**: Encrypted credential storage, HTTPS communication
- **Privacy**: Full GDPR compliance with export/delete functionality

### Features

- One-to-one course-to-badge mapping
- Automatic token refresh with lock-based concurrency control
- Provider and badge caching (10-minute TTL)
- Background task processing with retry logic
- Comprehensive audit logging
- Privacy API implementation
- Multi-environment configuration
- Admin connection testing
- Course navigation integration
