# CLAUDE.md — Navigatr Moodle Plugin

## Project Overview

`local_navigatr` is a Moodle local plugin that automatically issues Navigatr digital badges when learners complete courses. It integrates with Moodle's event system and task scheduler to issue badges asynchronously via the Navigatr API.

**Current version:** 1.2.0 (`version.php`)
**Requires:** Moodle 4.1+ (`$plugin->requires = 2022112800`)

---

## Key Architecture

### Badge Issuance Flow

1. Learner completes course → Moodle fires `\core\event\course_completed`
2. `observer::course_completed()` checks for a badge mapping in `local_navigatr_map`
3. If mapped, enqueues `issue_badge_task` (adhoc task) with `userid` + `courseid`
4. Task runs in background: validates user fields → deduplication check → calls `PUT /badge/{id}/issue` → writes audit record
5. On API failure (5xx), task re-throws exception so Moodle retries with exponential back-off

### Authentication

**PAT (Personal Access Token)** — set at Site Admin → Plugins → Navigatr.

- Stored encrypted with AES-256-CBC via `password_manager::store_pat()`
- Retrieved via `password_manager::get_pat()`, sent as `X-Access-Token: {token}` header on every request
- Verified via `GET /advanced/v1/personal_access_token/verify` (used by "Test Connection" button)
- No token exchange or refresh needed — PAT is sent directly

### Environments

| Key | Production | Staging |
|-----|-----------|---------|
| v1 API | `https://api.navigatr.app/v1` | `https://stagapi.navigatr.app/v1` |
| Advanced API | `https://api.navigatr.app/advanced/v1` | `https://stagapi.navigatr.app/advanced/v1` |
| PAT creation URL | `https://navigatr.app/settings/personal-access-tokens/` | `https://stag.navigatr.app/settings/personal-access-tokens/` |

Environment is stored in `get_config('local_navigatr', 'env')` — `'production'` or `'staging'`.

---

## File Map

```
classes/
  local/
    api_client.php          HTTP client (Moodle cURL wrapper, PAT auth)
    cache.php               Moodle cache wrapper (10-min TTL for providers/badges/user_detail)
    password_manager.php    AES-256-CBC encrypt/decrypt for PAT and legacy password
  form/
    admin_settings_form.php PAT field + environment selector (site admin)
    badge_selection_form.php Badge dropdown (per-course)
    provider_selection_form.php Provider dropdown (per-course)
  event/                    Custom Moodle events (badge issuance, API connection, etc.)
  task/
    issue_badge_task.php    Adhoc task — issues badge, writes audit record
  output/
    course_settings_output.php Renderable for course settings template
  privacy/
    provider.php            GDPR data export/deletion implementation
  observer.php              Listens to course_completed + course_restored events

db/
  install.xml               Two tables: local_navigatr_map, local_navigatr_audit
  events.php                Event listener registrations
  caches.php                Cache definitions (providers, badges, user_detail)
  access.php                Capabilities: managecredentials, configurecourse
  upgrade.php               Upgrade steps (v2026030401: clear stale auth config keys)

templates/                  Mustache/Handlebars templates for course settings UI
settings_page.php           Admin settings page (save/test/remove PAT)
course_settings.php         Course-level badge mapping UI entry point
badge_selection.php         Badge picker UI entry point
lang/en/local_navigatr.php  All user-facing strings

tests/
  local/
    api_client_test.php     URL config and no-PAT behaviour tests
    cache_test.php          Cache method structure (mostly hollow)
    password_manager_test.php PAT encrypt/decrypt/store/clear (real behavioural tests)
  form/
    admin_settings_form_test.php Form structure (mostly hollow)
  task/
    issue_badge_task_test.php Real DB-backed integration tests (no-PAT, idempotency, missing fields)
  observer_test.php         Observer method structure (mostly hollow)
  backup_restore_test.php   Backup/restore (mostly marked incomplete)
  behat/                    End-to-end Behat scenarios
```

---

## Database Schema

### `local_navigatr_map`
| Column | Type | Notes |
|--------|------|-------|
| id | int | PK |
| courseid | int | unique |
| provider_id | int | |
| badge_id | int | |
| badge_name | char(255) | |
| badge_image_url | char(255) | |
| timemodified | int | |

### `local_navigatr_audit`
| Column | Type | Notes |
|--------|------|-------|
| id | int | PK |
| userid | int | |
| courseid | int | |
| provider_id | int | |
| badge_id | int | |
| status | char(20) | `'success'` or `'error'` |
| http_code | int | |
| response_json | text | |
| dedupe_key | char(100) | unique — format `userid:courseid:badgeid` |
| timecreated | int | |

---

## Config Keys (`local_navigatr`)

| Key | Purpose |
|-----|---------|
| `personal_access_token` | Encrypted PAT |
| `encryption_key` | AES key used by password_manager |
| `env` | `'production'` or `'staging'` |
| `timeout` | HTTP timeout in seconds (default 30) |

---

## Coding Conventions

- **PHP namespace:** `local_navigatr\*` (classes auto-loaded by Moodle)
- **Moodle cURL:** `new \curl()` (from `$CFG->libdir . '/filelib.php'`) — not Guzzle
- **Config:** `get_config('local_navigatr', 'key')` / `set_config('key', $val, 'local_navigatr')`
- **Events:** Extend `\core\event\base`, triggered via `::create([...])->trigger()`
- **Adhoc tasks:** Extend `\core\task\adhoc_task`, data passed via `set_custom_data()` / `get_custom_data()`
- **Forms:** Extend `\moodleform`, defined in `definition()`, validated in `validation()`
- **Tests:** Extend `advanced_testcase`, call `$this->resetAfterTest()` for any test that modifies DB or config

---

## Testing

PHPUnit tests require a full Moodle installation with PHPUnit initialised.

```bash
# From Moodle root (after composer install + php admin/tool/phpunit/cli/init.php)
vendor/bin/phpunit --testsuite local_navigatr

# Or via moodle-plugin-ci (global install)
~/.composer/vendor/bin/moodle-plugin-ci phpunit local/navigatr
```

**Quick syntax check (no Moodle needed):**
```bash
find . -name "*.php" | xargs php -l
```

**Test quality note:** Most tests in `observer_test.php`, `cache_test.php`, and `admin_settings_form_test.php` only check `method_exists()` — they pass trivially. The genuinely behavioural tests are in `password_manager_test.php` and `issue_badge_task_test.php`.

---

## Git

- Default branch is `develop` (there is no `main`)
- PRs target `develop`
- Do not add `Co-Authored-By` tags to commit messages

## Release Process

```bash
./scripts/release.sh X.Y.Z
```

Bumps `version.php` (both `$plugin->version` YYYYMMDDXX and `$plugin->release`), updates `CHANGES.md`, creates a `release/X.Y.Z` branch.

---

## CI/CD

`.github/workflows/moodle-plugin-ci.yml` runs on push/PR to `develop`:
- Matrix: PHP 8.1 + Moodle 4.1, PHP 8.2 + Moodle 4.4, PHP 8.3 + Moodle 4.5
- Blocking checks: `phplint`, `phpcs --max-warnings 0`, `validate`, `savepoints`, `phpunit`
- Advisory (non-blocking): `phpdoc`, `behat`
- Uses `moodlehq/moodle-plugin-ci` with a real PostgreSQL-backed Moodle install
