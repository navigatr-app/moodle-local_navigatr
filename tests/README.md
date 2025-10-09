# Navigatr Plugin Testing

This directory contains comprehensive unit and integration tests for the Navigatr Moodle plugin.

## Test Structure

```text
tests/
├── classes/
│   ├── local/
│   │   ├── api_client_test.php          # API client unit tests
│   │   ├── token_manager_test.php      # Token management tests
│   │   └── cache_test.php              # Caching tests
│   ├── form/
│   │   └── admin_settings_form_test.php # Form validation tests
│   └── task/
│       └── issue_badge_task_test.php   # Badge issuance task tests
├── behat/
│   ├── features/
│   │   └── navigatr_badge_issuance.feature # End-to-end scenarios
│   └── behat_navigatr.php              # Behat context
├── fixtures/
│   └── navigatr_test_data.xml          # Test data fixtures
└── observer_test.php                   # Observer tests
```

## Running Tests

### Local Testing (Quick Validation)

```bash
# Run smart testing script (adapts to environment)
./scripts/test.sh

# This script automatically detects your environment:
# - Outside Moodle: Quick validation (syntax, structure, security)
# - Inside Moodle: Full testing suite (PHPUnit, Behat, code quality)
```

### Unit Tests (PHPUnit)

```bash
# Run all tests
vendor/bin/phpunit tests/

# Run specific test class
vendor/bin/phpunit tests/classes/local/api_client_test.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/ tests/
```

### Integration Tests (Behat)

```bash
# Run Behat tests
vendor/bin/behat tests/behat/features/

# Run specific feature
vendor/bin/behat tests/behat/features/navigatr_badge_issuance.feature
```

### Using moodle-plugin-ci

```bash
# Run all checks
moodle-plugin-ci phplint
moodle-plugin-ci codechecker
moodle-plugin-ci phpunit
moodle-plugin-ci behat
```

## Test Categories

### Unit Tests

- **API Client Tests**: Test HTTP requests, authentication, error handling
- **Token Manager Tests**: Test token refresh, expiration, concurrency
- **Cache Tests**: Test caching mechanisms, TTL, invalidation
- **Form Tests**: Test form validation, submission, error handling
- **Task Tests**: Test badge issuance, retry logic, audit trails
- **Observer Tests**: Test course completion detection, event handling

### Integration Tests

- **End-to-End Scenarios**: Complete badge issuance workflows
- **API Integration**: Real API interactions (with test credentials)
- **Database Operations**: CRUD operations, data integrity
- **User Workflows**: Admin configuration, course mapping, completion

## Test Data

The `fixtures/navigatr_test_data.xml` file contains:

- Test users (admin, teachers, students)
- Test courses with enrolments
- Mock Navigatr providers and badges
- Sample course-to-badge mappings
- Audit trail examples

## Test Configuration

### Environment Variables

```bash
# Navigatr test credentials
export NAVIGATR_TEST_USERNAME="test_user"
export NAVIGATR_TEST_PASSWORD="test_password"
export NAVIGATR_TEST_ENVIRONMENT="staging"
```

### Moodle Configuration

```php
// config.php additions for testing
$CFG->behat_wwwroot = 'http://localhost:8000';
$CFG->behat_dataroot = '/path/to/behat/data';
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run PHPUnit tests
        run: vendor/bin/phpunit tests/
      - name: Run Behat tests
        run: vendor/bin/behat tests/behat/features/
```

## Test Coverage

Aim for:

- **Unit Tests**: 80%+ code coverage
- **Integration Tests**: All critical user workflows
- **API Tests**: All HTTP endpoints and error scenarios
- **Database Tests**: All CRUD operations

## Debugging Tests

### PHPUnit Debugging

```bash
# Run with verbose output
vendor/bin/phpunit --verbose tests/

# Run single test method
vendor/bin/phpunit --filter test_get_token_success tests/classes/local/api_client_test.php
```

### Behat Debugging

```bash
# Run with debug output
vendor/bin/behat --format=pretty --out=std tests/behat/features/

# Run specific scenario
vendor/bin/behat --name="Configure Navigatr credentials" tests/behat/features/
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Data Cleanup**: Use `resetAfterTest()` for database cleanup
3. **Mocking**: Mock external API calls in unit tests
4. **Real API**: Use real API calls in integration tests
5. **Error Scenarios**: Test both success and failure paths
6. **Performance**: Test with realistic data volumes
7. **Security**: Test authentication and authorization
8. **Documentation**: Keep tests readable and well-documented

## Troubleshooting

### Common Issues

1. **Database Errors**: Ensure test database is properly configured
2. **API Timeouts**: Increase timeout values for slow networks
3. **Memory Issues**: Use `--memory-limit=512M` for large test suites
4. **Permission Errors**: Ensure test user has required capabilities

### Test Data Issues

1. **Missing Fixtures**: Check that test data is properly loaded
2. **Data Conflicts**: Use unique identifiers for test data
3. **Cleanup**: Ensure tests clean up after themselves
