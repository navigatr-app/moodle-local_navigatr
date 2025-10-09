# Changelog

All notable changes to the Navigatr plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.9.1] - 2025-10-09

### Changes in this release
**Commit Count:** 33 commits
**Commit Range:** HEAD

- Release: Fix  typos in the comments (c4f4ca4)
- Release: Create script for creating a release (197c855)
- Docs: Remove more redundant asterisk (0e8dc7a)
- Docs: Update the test readme (9cf2009)
- Test: Add local testing and add instructions to docs (dd0903c)
- Test: Change advanced testing workflow by integrating PHP CodeSniffer and PHPMD (12262a1)
- Refactor: Simplify advanced testing workflow by removing redundant steps and consolidating test execution (c88814b)
- Test: another attempt at fixing the step 'Setup test database' (8e2d912)
- Test: Fix the `Setup test database` step and delete redundant test workflows (8d96a44)
- Test: Fix the 'Install plugin in Moodle' step (3bc2442)
- Test: Fix the test workflow (ada01c5)
- Tests: Add the initial tests (dc803df)
- Trivial: Remove unnecessary asterisk (15deadf)
- Docs: Update README to clarify badge mapping instructions and connection removal warnings (c565ed7)
- Docs: Remove the script for resetting course completion (28f5ad5)
- Docs: Add developer instructions for resetting course completion (3bce57b)
- Observer: Try a different way of getting the user id for logs (807a747)
- Observer: Remove the script for registering observer and add logs for debugging (016002c)
- Refactor: Simplify installation and upgrade hooks by removing event observer registration logic. (6b9677f)
- Enhancement: Sanitize error messages in settings page and update observer logging to use Moodle's debugging system. (c25d78c)
- Readme: Add sections about versioning, contributions and help. (694c01b)
- README: Add section on API outages and retry mechanism for badge issuance (0a17466)
- Badge: Fix automatic badge issuing and improve documentation. (dfde212)
- Remove the `Development` environment (ab37b7a)
- Git: Ignore VSCode folder (0c6db81)
- Restructure: Move files to the root folder (857c3b3)
- Course: Display the current badge mapping (5cd270f)
- Course: Fix the badge selection (3e3de2c)
- Course: Fix provider selection (e7f3f1d)
- Refactor API client initialization to use default base URL and timeout settings. (480b3f1)
- Spelling: Add more words to the dictionary (0e01441)
- The `test connection` is successful (10b3154)
- Initial version of the Navigatr plugin (cdb36b1)

## [1.0.0] - 2025-01-07

### Added

- Initial release of Navigatr plugin
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
