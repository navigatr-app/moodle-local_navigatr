# Scripts Directory

This directory contains utility scripts for the Navigatr Moodle plugin.

## Available Scripts

### `test.sh`

Smart testing script that adapts to your environment:

**When run outside Moodle (default):**

- PHP syntax validation
- Plugin structure validation
- Security checks
- Code quality checks
- Documentation checks

**When run inside Moodle installation:**

- Full moodle-plugin-ci testing
- PHP linting
- Code checking
- PHPUnit tests
- Behat integration tests

**Usage:**

```bash
./scripts/test.sh
```

## Requirements

- **Bash**: Available on all Unix-like systems
- **PHP** (optional): For syntax checking and advanced validation
- **Git**: For version control integration

## Benefits

- ✅ **No commits required** - Test before pushing
- ✅ **Fast feedback** - Immediate results
- ✅ **Same as CI** - Identical to GitHub Actions
- ✅ **Debug easily** - See exactly what's wrong
- ✅ **Iterate quickly** - Fix issues before committing

## Integration

These scripts are designed to work with:

- **GitHub Actions** - Same validation as CI
- **Local development** - Test before committing
- **Code review** - Validate changes before PR
- **Deployment** - Ensure code quality before release
