# Navigatr Plugin Testing

This directory contains comprehensive unit and integration tests for the Navigatr Moodle plugin.

## Test Coverage

### 📊 Current Test Results
- **PHPUnit Tests**: ✅ **17/17 passing** (100% success rate)
- **Behat Tests**: ✅ **Running successfully** (end-to-end scenarios)
- **PHP Linting**: ✅ **33/33 files** (no syntax errors)
- **Code Quality**: ✅ **Validated** (GitHub Actions + manual review)

### 🧪 Test Types
- **Unit Tests**: Individual component testing (API, tokens, caching, forms, tasks)
- **Integration Tests**: End-to-end user workflows (badge issuance, admin configuration)
- **Security Tests**: Authentication, authorization, data protection
- **Performance Tests**: Caching, API efficiency, database operations

## What is Smart Test Script?

The **Smart Test Script** (`./scripts/test.sh`) is an intelligent testing tool that automatically adapts to your environment:

### 🔍 **Environment Detection**
- **Outside Moodle**: Runs quick validation (syntax, structure, security)
- **Inside Moodle**: Runs full test suite (PHPUnit, Behat, code quality)

### 🚀 **What It Does**
```bash
# Run the smart script
./scripts/test.sh

# Automatically detects:
# - Are you in a Moodle installation?
# - Are you in a plugin subdirectory?
# - What testing tools are available?
# - Runs appropriate tests for your environment
```

## When It Runs

### 🤖 **GitHub Actions (Automated)**
**Triggers**: Push to `main`/`develop` branches, Pull requests

**What it validates**:
- ✅ Plugin structure and syntax
- ✅ Security vulnerability scanning  
- ✅ Code quality checks
- ✅ Test infrastructure validation
- ⚠️ **Cannot run full tests** (requires Moodle environment)

**Duration**: 2-3 minutes

### 🖥️ **Moodle Server (Manual)**
**Triggers**: Developer runs `./scripts/test.sh`

**What it runs**:
- ✅ **PHP Linting**: 33/33 files (syntax validation)
- ✅ **PHPUnit Tests**: 17/17 tests (functionality validation)
- ✅ **Behat Tests**: End-to-end scenarios
- ⚠️ **Code Checker**: Skipped (PHP 8.2 compatibility issues)

**Duration**: 5-10 minutes

## Installation

### 📋 **Requirements for Moodle Server Testing**

#### **1. Moodle Installation**
- **Moodle 4.0+** (tested with Moodle 5.0.3)
- **PHP 8.2+** (tested with PHP 8.2.29)
- **Database**: MySQL/PostgreSQL
- **Plugin installed** in `/local/navigatr/`

#### **2. Testing Tools**
```bash
# Install global testing tools (one-time setup)
composer global require moodlehq/moodle-plugin-ci

# Install PHP intl extension (if needed)
brew install php-intl  # macOS
# or
sudo apt-get install php-intl  # Ubuntu/Debian
```

#### **3. Moodle Dependencies**
```bash
# Install Moodle Composer dependencies
cd /path/to/moodle
composer install --ignore-platform-reqs
```

### 🐳 **Docker Setup (Recommended)**

#### **Quick Start**
```bash
# 1. Start Moodle Docker
docker run -d --name moodle-test \
  -p 8080:80 \
  -e MOODLE_DB_TYPE=mysqli \
  -e MOODLE_DB_HOST=db \
  -e MOODLE_DB_NAME=moodle \
  -e MOODLE_DB_USER=moodle \
  -e MOODLE_DB_PASS=moodle \
  moodle/moodle:latest

# 2. Copy plugin to Moodle
docker cp /path/to/moodle-local_navigatr moodle-test:/var/www/html/local/navigatr

# 3. Install dependencies
docker exec -it moodle-test bash -c "cd /var/www/html && composer install --ignore-platform-reqs"

# 4. Run tests
docker exec -it moodle-test bash -c "cd /var/www/html/local/navigatr && ./scripts/test.sh"
```

### 🔧 **Manual Setup**

#### **Step 1: Install Global Tools**
```bash
# Install moodle-plugin-ci globally
composer global require moodlehq/moodle-plugin-ci

# Verify installation
~/.composer/vendor/bin/moodle-plugin-ci --version
```

#### **Step 2: Setup Moodle**
```bash
# Install Moodle dependencies
cd /path/to/moodle
composer install --ignore-platform-reqs

# Copy plugin
cp -r /path/to/moodle-local_navigatr /path/to/moodle/local/navigatr
```

#### **Step 3: Run Tests**
```bash
# From plugin directory
cd /path/to/moodle/local/navigatr
./scripts/test.sh

# Or from Moodle root
cd /path/to/moodle
moodle-plugin-ci phpunit local/navigatr
moodle-plugin-ci behat local/navigatr
```

## 🎯 Quick Reference

### **For Development**
```bash
# Quick validation (outside Moodle)
./scripts/test.sh

# Full testing (inside Moodle)
./scripts/test.sh
```

### **For CI/CD**
- **GitHub Actions**: Automatically runs on push/PR
- **Manual**: Run `./scripts/test.sh` on Moodle server

### **For New Environments**
1. **Install tools**: `composer global require moodlehq/moodle-plugin-ci`
2. **Copy plugin**: `cp -r plugin /path/to/moodle/local/navigatr`
3. **Install deps**: `cd /path/to/moodle && composer install`
4. **Run tests**: `./scripts/test.sh`
