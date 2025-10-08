# Navigatr Plugin for Moodle

A Moodle local plugin that automatically issues Navigatr digital badges when learners complete courses.

## Overview

This plugin integrates Moodle with the Navigatr badge platform, providing:

- **Automatic Badge Issuance**: Issues badges automatically when learners complete courses
- **Course-to-Badge Mapping**: One-to-one mapping between courses and badges
- **Multi-Environment Support**: Production and staging environments
- **GDPR Compliance**: Full privacy API implementation
- **Audit Trail**: Complete logging of badge issuance attempts

## Requirements

- **Moodle**: 4.1 LTS or 5.x
- **PHP**: 8.2 or 8.3
- **Navigatr Account**: Valid Navigatr credentials

## Installation

1. Copy the plugin files to `local/navigatr/` in your Moodle installation
2. Visit the Moodle admin notifications page to install the plugin
3. Configure Navigatr credentials in Site Administration → Plugins → Local plugins → Navigatr

## Configuration

### Site-Level Settings

Navigate to **Site Administration → Plugins → Local plugins → Navigatr** to configure:

- **Environment**: Choose between Production or Staging
- **Credentials**: Enter your Navigatr username and password
- **Advanced Settings**: HTTP timeout and logging level

### Course-Level Mapping

For each course where you want to issue badges:

1. Go to the course
2. Navigate to **Course settings → Navigatr Issuance**
3. Select a provider from the dropdown
4. Choose a badge from the provider's available badges
5. Save the mapping

## API Endpoints

The plugin integrates with the following Navigatr API endpoints:

### Authentication

- `POST /v1/token` - Authenticate with username/password

### Providers

- `GET /v1/user_detail/{user_id}/providers` - List available providers

### Badges

- `GET /v1/badge?provider_id={id}&page={n}&size={m}` - List badges for a provider

### Badge Issuance

- `PUT /v1/badge/{badge_id}/issue` - Issue a badge to a recipient

## Environments

| Environment | Base URL |
|-------------|----------|
| Production | [https://api.navigatr.app/v1/](https://api.navigatr.app/v1/) |
| Staging | [https://stagapi.navigatr.app/v1/](https://stagapi.navigatr.app/v1/) |

## Database Schema

The plugin creates two database tables:

### `local_navigatr_map`

Stores course-to-badge mappings:

- `courseid` - Course ID (unique)
- `provider_id` - Navigatr provider ID
- `badge_id` - Navigatr badge ID
- `badge_name` - Badge name (cached)
- `badge_image_url` - Badge image URL (cached)

### `local_navigatr_audit`

Stores badge issuance audit records:

- `userid` - User ID
- `courseid` - Course ID
- `provider_id` - Provider ID
- `badge_id` - Badge ID
- `status` - Issuance status (success/error)
- `http_code` - HTTP response code
- `response_json` - Raw API response
- `dedupe_key` - Unique key for idempotency

## Capabilities

- `local/navigatr:managecredentials` - Manage site-level Navigatr credentials (admin only)
- `local/navigatr:configurecourse` - Configure course badge mappings (teachers/managers)

## Privacy & GDPR

The plugin implements Moodle's privacy API:

- **Data Export**: Users can export their badge issuance audit records
- **Data Deletion**: Users can request deletion of their audit records
- **External Data Transfer**: Documents transfer of PII (email, firstname, lastname) to Navigatr

## Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Verify Navigatr credentials are correct
   - Check environment setting matches your Navigatr account
   - Ensure Navigatr API is accessible from your Moodle server

2. **No Providers Available**
   - Run "Test Connection" in admin settings
   - Verify your Navigatr account has access to providers

3. **Badge Issuance Fails**
   - Check audit records in database for error details
   - Verify user has required fields (email, firstname, lastname)
   - Check Navigatr API status

### Debugging

Enable debug logging in plugin settings to see detailed API interactions. Logs are stored in Moodle's debug log.

### HTTP Status Codes

- `200/201` - Badge issued successfully
- `400` - Bad request (missing user fields)
- `401` - Authentication failed (token expired)
- `404` - Badge or provider not found
- `5xx` - Server error (retry automatically)

## Testing

### Manual Testing

1. **Configuration Test**
   - Configure Navigatr credentials
   - Run "Test Connection" to verify authentication
   - Verify providers are loaded

2. **Course Mapping Test**
   - Create a test course
   - Map a provider and badge
   - Verify mapping is saved

3. **Badge Issuance Test**
   - Enrol a test user in the course
   - Mark the course as complete
   - Check audit records for successful issuance

### Automated Testing

The plugin includes support for moodle-plugin-ci:

```bash
moodle-plugin-ci phplint
moodle-plugin-ci codechecker
moodle-plugin-ci phpunit
```

## Security Notes

- Passwords are stored encrypted in Moodle's config
- Access tokens are never logged
- All API communications use HTTPS
- User PII is only sent to Navigatr for badge issuance

## Support

For issues related to:

- **Plugin functionality**: Check Moodle logs and audit records
- **Navigatr API**: Contact Navigatr support
- **Moodle integration**: Check plugin capabilities and permissions

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.
