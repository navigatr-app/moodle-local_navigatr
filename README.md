# Navigatr Plugin for Moodle

A Moodle local plugin that automatically issues Navigatr digital badges when learners complete courses.

## What is Navigatr?

Navigatr is a digital badge pathways platform. We help organisations recognise skills, connect learning to work, and make achievements visible and verifiable. Badges issued through Navigatr are portable, shareable credentials that learners can display on LinkedIn and other platforms. Each badge contains data about what was achieved, when it was achieved, and acts as built-in recognition from your organisation.

This plugin connects Moodle's course completion system to Navigatr. When a learner finishes a course, a badge is automatically issued — no manual steps required.

**You'll need a Navigatr provider account to use this plugin.** [Register for a free trial](https://navigatr.app/register/plan/launch) to get started.

![Issued Badge](images/issued-badge.png)

## Requirements

- **Moodle**: 4.1 or later
- **PHP**: 8.1, 8.2, or 8.3
- **Navigatr account**: With provider admin access (see below)

## What is a provider?

In Navigatr, a **provider** is the organisation that owns and issues badges — a university, a training body, a local authority, and so on. Your Navigatr user account must have **provider admin** access to at least one provider before you can map courses to badges. If you are unsure whether you have this access, check in your Navigatr account settings or contact your Navigatr administrator.

## Installation

1. **Get a Navigatr account.** If you don't have one, [register for a free trial](https://navigatr.app/register/plan/launch).

1. **Download the plugin.** Get the latest release from the repository's releases page, or clone the repository directly.

1. **Copy files to Moodle.** Place the plugin folder at `local/navigatr/` inside your Moodle installation. These items are optional and can be omitted:
   - `/.github` (CI/CD configuration)
   - `/.gitignore` (Git configuration)

1. **Run the Moodle upgrade.** Visit the admin notifications page (`/admin/index.php`) to complete installation.

1. **Configure credentials.** Go to **Site Administration → Plugins → Local plugins → Navigatr** and add your Personal Access Token (see below).

## Configuration

### Personal Access Token

The plugin authenticates with Navigatr using a **Personal Access Token (PAT)**. PATs give secure API access without sharing your account password.

To create one:

1. Log in to your Navigatr account
1. Go to **Account Settings → Personal Access Tokens**
1. Create a new token and copy it

Then in Moodle:

1. Go to **Site Administration → Plugins → Local plugins → Navigatr**
1. Paste the token into the **Personal Access Token** field
1. Click **Test Connection** to verify it works
1. Click **Save Changes**

Tokens are encrypted with AES-256-CBC before storage. They are never logged or exposed in plain text.

![Admin Settings Page](images/plugin-settings.png)

To disconnect, click **Remove Connection**. This clears the stored token and disables existing badge mappings on all courses.

### Environment

Choose **Production** for your live Moodle site. Choose **Staging** if you are testing with a Navigatr staging account. The default is Production.

### Course Badge Mapping

For each course where you want to issue badges on completion:

1. Go to the course.
1. Navigate to **Course settings → Navigatr Badge**.

![Course Settings Menu](images/course-settings-menu.png)

1. Select a provider from the dropdown and click **Continue to Select Badge**.

![Provider Selection](images/provider-selection.png)

1. Choose a badge and click **Save Mapping**.

![Badge Selection](images/badge-selection.png)

1. The mapping is now active. From this page you can:
   - **View Badge** — open the badge in Navigatr
   - **Change Badge** — select a different badge
   - **Remove Badge** — remove the mapping

![Current Badge Mapping](images/current-mapping.png)

## How badge issuance works

When a learner completes a course, Moodle fires a `course_completed` event. The plugin picks this up and queues a background task to issue the badge via the Navigatr API. This happens asynchronously — learners do not wait.

If the API is unavailable, the task retries automatically at increasing intervals (1 min, 5 min, 15 min, 1 hr, 6 hr, 24 hr). No completions are lost during outages.

Duplicate issuance is prevented: a badge is only issued once per learner per course per badge.

## Capabilities

| Capability | Who it's for |
| ---------- | ------------ |
| `local/navigatr:managecredentials` | Site admins — configure the PAT and plugin settings |
| `local/navigatr:configurecourse` | Teachers and course managers — map courses to badges |

## Privacy & GDPR

The plugin implements Moodle's privacy API. When issuing a badge, we send the learner's email address, first name, and last name to Navigatr. No other personal data is transmitted.

Learners can export or request deletion of their badge issuance audit records through Moodle's standard privacy tools.

## Course Backup & Restore

Badge mappings are included in course backups and restored automatically when a course is imported or duplicated.

Audit records (individual badge issuance history) are included only when the backup includes user data.

Restoring a course does not re-issue badges — badges already issued on the Navigatr platform remain as they are.

## Troubleshooting

### Connection test fails

- Check the PAT is copied correctly — no trailing spaces or newlines
- Verify the environment setting matches your Navigatr account (production vs staging)
- Check your Moodle server can reach the Navigatr API (`api.navigatr.app`)

### No providers in the dropdown

- Run **Test Connection** to confirm the PAT is valid
- Check your Navigatr user has provider admin access on at least one provider
- If the connection test passes but the dropdown is empty, contact your Navigatr administrator

### Badge not issued after course completion

- Check **Site Administration → Reports → Logs** for events from `local_navigatr`
- Check the `local_navigatr_audit` table for the status and HTTP response code
- Confirm the learner has an email address, first name, and last name on their Moodle profile
- If the task failed with a 5xx error, Moodle will retry automatically

### Observer not triggering

If badges stop being issued after a Moodle upgrade, re-register observers by running:

```bash
sudo -u www-data /usr/bin/php admin/cli/upgrade.php --non-interactive
```

### HTTP status codes

| Code | Meaning |
| ---- | ------- |
| 200 / 201 | Badge issued successfully |
| 400 | Missing user fields (email, firstname, lastname) |
| 401 | PAT invalid or revoked — generate a new one |
| 404 | Badge or provider not found — check the course mapping |
| 5xx | Navigatr API error — task will retry automatically |

## API reference

The plugin uses these Navigatr API endpoints:

| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| `GET` | `/v1/user_detail/0/providers` | List providers available to the authenticated user |
| `GET` | `/v1/badge?provider_id={id}&page={n}&size={m}` | List badges for a provider |
| `PUT` | `/v1/badge/{badge_id}/issue` | Issue a badge to a recipient |

| Environment | Base URL |
| ----------- | -------- |
| Production | `https://api.navigatr.app/v1` |
| Staging | `https://stagapi.navigatr.app/v1` |

## Database tables

### `local_navigatr_map`

One row per course. Stores which badge to issue when a learner completes that course.

| Column | Description |
| ------ | ----------- |
| `courseid` | Moodle course ID (unique) |
| `provider_id` | Navigatr provider ID |
| `badge_id` | Navigatr badge ID |
| `badge_name` | Badge name (cached from API) |
| `badge_image_url` | Badge image URL (cached from API) |

### `local_navigatr_audit`

One row per badge issuance attempt. Used for deduplication, debugging, and audit.

| Column | Description |
| ------ | ----------- |
| `userid` | Moodle user ID |
| `courseid` | Moodle course ID |
| `provider_id` | Navigatr provider ID |
| `badge_id` | Navigatr badge ID |
| `status` | `success` or `error` |
| `http_code` | HTTP response code from Navigatr API |
| `response_json` | Raw API response |
| `dedupe_key` | Unique key (`userid:courseid:badgeid`) — prevents duplicate issuance |

## Testing

### Manual

1. Configure a PAT and click **Test Connection**
1. Map a course to a badge
1. Enrol a test user and mark the course complete
1. Check the audit table for a successful issuance record

### Automated

```bash
# Syntax check (no Moodle needed)
find . -name "*.php" | xargs php -l

# Full CI checks (requires moodle-plugin-ci)
moodle-plugin-ci phplint
moodle-plugin-ci phpcs
moodle-plugin-ci phpunit
```

## Versioning and contributing

Development happens on the `develop` branch. Releases are tagged and branched from there.

To report a bug or request a feature, open an issue on GitHub. Pull requests are welcome — please follow Moodle coding standards and include tests for new behaviour.

## Help and support

For platform questions — badge creation, account management, provider setup — visit the [Navigatr Help Centre](https://help.navigatr.app/).

For plugin issues, check the Moodle logs and audit table first. If the issue is with the Navigatr API, contact Navigatr support.

## Changelog

See [CHANGES.md](CHANGES.md) for version history.
