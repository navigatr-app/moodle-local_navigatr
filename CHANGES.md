# Changes

All notable changes to the Navigatr plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-11-29

### Fixes in 1.1.0

- Fixed password storage - password is now encrypted with AES-256-CBC before database storage. Replaced plain text password storage with secure encryption
- Removed the else block in data_submitted() processing that was saving settings without validation.
- Updated settings_page.php to use 'local/navigatr:managecredentials' instead of 'moodle/site:config'. Added missing language strings for capabilities and cache definitions.
- Access tokens now stored in cache with 4-minute TTL
- Replaced optional_param with required_param and removed unnecessary empty checks
- Privacy provider moved to correct location and implemented `delete_data_for_all_users_in_context`
- Added proper context handling
- Removed unused AJAX handler
- Replaced manual `require_login()` and `require_capability()` calls with `admin_externalpage_setup('local_navigatr_settings')`
- Handle session timeout gracefully in plugin settings page
- Replaced html_writer with Mustache templates and Output API
- Moved hard-coded language strings to the language file
- Changed reauth() method from private to public
- Implemented Behat step definitions and simplified scenarios
- Improved observer tests with real behaviour validation
- Retrieve and store badge name and image and update cache
- Implement backup/restore API for course-badge mapping
- Replaced debugging/error_log calls with custom events by implementing event system to replace all debugging() and error_log() calls
- Fixed cancel redirect in settings page by correcting URL and moving form handling before page output to prevent redirect errors
- Removed redundant course mapping check from issue_badge_task.php as observer already validates mapping exists before queuing task

## [1.0.0] - 2025-11-15

### Added in 1.0.0

- Complete Navigatr plugin with automatic badge issuance
- Site-level API configuration and course-level badge mapping
- Multi-environment support (production/staging)
- Token management with automatic refresh and caching
- Complete audit trail and GDPR compliance
- Adhoc task system with retry logic and idempotency protection

### Enhanced User Experience in 1.0.0

- Comprehensive help system with contextual documentation
- Improved error messages with troubleshooting guidance
- Enhanced form validation and security warnings
- Direct links to Navigatr Help Centre

### Code Quality Improvements in 1.0.0

- Replaced raw HTML with `html_writer` API
- Added 25+ language strings for complete i18n support
- Enhanced error handling and form help text
- Full compliance with Moodle coding standards

### Technical Details in 1.0.0

- **Moodle**: 4.1 LTS, 5.x | **PHP**: 8.2, 8.3
- **Database**: `local_navigatr_map`, `local_navigatr_audit`
- **Security**: Encrypted credential storage, HTTPS communication
- **Privacy**: Full GDPR compliance with export/delete functionality

## [0.9.1] - 2025-10-09

### Added in 0.9.1

- Enhanced course settings display and observer logging
- Comprehensive test suite with PHP CodeSniffer and PHPMD
- Developer documentation and testing instructions

### Changed in 0.9.1

- Restructured project files to root folder
- Improved API client initialization and README documentation
- Updated test workflows and CI/CD pipeline

### Fixed in 0.9.1

- Badge and provider selection functionality
- Observer registration and error message sanitization
- Course completion reset and test database setup

### Removed in 0.9.1

- Development environment configuration
- Redundant test workflows and documentation artifacts

## [0.9.0] - 2025-10-13

### Added in 0.9.0

- Initial beta release with core functionality
- Automatic badge issuance and API configuration
- Multi-environment support and token management
- Complete audit trail and GDPR compliance
- Database schema and capability-based access control

### Technical Details in 0.9.0

- **Moodle**: 4.1 LTS, 5.x | **PHP**: 8.2, 8.3
- **Database**: `local_navigatr_map`, `local_navigatr_audit`
- **API**: Navigatr REST API v1 with caching and security
