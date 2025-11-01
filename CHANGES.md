# Changes

All notable changes to the Navigatr plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2024-10-31

### Code Quality Improvements in 1.1.1

- Fixed all 44 PHP files by removing the blank line after `<?php` so the copyright header starts on line 2. This resolves ~70 prechecker errors.
- Fixed PHPDoc incomplete parameter lists.
- Fixed test directory structure by moving test files from `tests/classes/*` to `tests/*` directories to match Moodle's expected structure.
- Replaced `error_log()` with `debugging()` in `badge_selection.php` to comply with Moodle coding standards.
- Fixed MOODLE_INTERNAL checks. Added `MOODLE_INTERNAL` check to `api_client.php` (has require_once side effect). Removed unnecessary `MOODLE_INTERNAL` checks from class-only files
- Replaced `elseif` with `else if` (two words) in all files to comply with Moodle coding standards
- Fixed line length violations by breaking long lines to stay under Moodle's 132 character limit
- Fixed array trailing commas by adding trailing commas to all multi-line arrays to comply with Moodle coding standards
- Fixed minor code quality issues:
  - Replaced Perl-style comment with standard comment syntax
  - Removed commented-out code comments
  - Fixed comment capitalization
  - Removed duplicate empty lines
  - Removed trailing whitespace from strings
  - Replaced long list syntax with array destructuring
  - Sorted interfaces alphabetically
- Removed empty IF/CATCH statements.
- Sorted language file string keys alphabetically in `lang/en/local_navigatr.php` to comply with Moodle coding standards.
- Added missing `@template` section to `templates/course/course_settings.mustache` with template context variable documentation.
- Added missing language strings.
- Added the correct header and copyright/author notes to the files which were missing.

## [1.1.0] - 2024-10-29

### Major Security & Quality Improvements

This release delivers a major security and code quality enhancement, systematically addressing identified vulnerabilities whilst modernising the code base to meet current Moodle development standards. The improvements span security hardening, performance optimisation, and user experience refinements.

### Security Enhancements in 1.1.0

- Password encryption with AES-256-CBC
- Form validation improvements
- Capability system modernisation
- Event-based logging system
- GDPR compliance improvements

### Code Quality Improvements in 1.1.0

- Template system modernisation
- Parameter handling standardisation
- Unused code removal
- Test infrastructure enhancement
- Audit logging integration

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
- Fixed course progress bug by passing course object instead of course ID to get_course_progress_percentage method
- Fixed capability check mismatch by adding explicit require_capability check in settings_page.php for consistent permission validation
- Fixed unused capability issue by making local/navigatr:managecredentials actively used in settings_page.php
- Fixed privacy provider issue by confirming all required GDPR compliance methods are already implemented
- Fixed form setDefaults usage by replacing setDefault() calls with proper set_data() method following Moodle best practices
- Remove unused plugin loglevel configuration that was never actually used for conditional logging
- Deleted unused XML fixture file
- Removed unused functions from cache.php and provider_selection_form.php
- Added core event triggers for audit logging integration with Moodle's logging system
- Simplified parameter handling by replacing optional_param with required_param
- Resolved deduplication key concern by confirming badge IDs are globally unique across providers

## [1.0.0] - 2024-11-15

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
- Improved API client initialisation and README documentation
- Updated test workflows and CI/CD pipeline

### Fixed in 0.9.1

- Badge and provider selection functionality
- Observer registration and error message sanitisation
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
