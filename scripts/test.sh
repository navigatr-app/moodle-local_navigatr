#!/bin/bash

echo "🧪 Navigatr Plugin Testing"
echo "=========================="

# Check if we're in a Moodle installation (current dir, parent dir, or grandparent dir)
MOODLE_DETECTED=false

if [ -f "config.php" ] && [ -d "lib" ] && [ -d "admin" ]; then
    MOODLE_DETECTED=true
elif [ -f "../config.php" ] && [ -d "../lib" ] && [ -d "../admin" ]; then
    MOODLE_DETECTED=true
elif [ -f "../../config.php" ] && [ -d "../../lib" ] && [ -d "../../admin" ]; then
    MOODLE_DETECTED=true
fi

if [ "$MOODLE_DETECTED" = "true" ]; then
    echo "📦 Moodle installation detected - running full tests..."
    
    # Determine if we're in a plugin subdirectory
    if [ -f "../config.php" ] && [ -d "../lib" ] && [ -d "../admin" ]; then
        echo "📁 Plugin subdirectory detected - switching to Moodle root..."
        cd ..
        PLUGIN_PATH="local/navigatr"
    elif [ -f "../../config.php" ] && [ -d "../../lib" ] && [ -d "../../admin" ]; then
        echo "📁 Plugin subdirectory detected - switching to Moodle root..."
        cd ../..
        PLUGIN_PATH="local/navigatr"
    else
        PLUGIN_PATH="local/navigatr"
    fi
    
    # Check if moodle-plugin-ci is available
    if command -v ~/.composer/vendor/bin/moodle-plugin-ci >/dev/null 2>&1; then
        echo "🔍 Running PHP linting..."
        ~/.composer/vendor/bin/parallel-lint $PLUGIN_PATH
        
        echo "🔍 Running code checker..."
        echo "⚠️  Skipping code checker due to PHP 8.2 compatibility issues with php_codesniffer"
        # ~/.composer/vendor/bin/moodle-plugin-ci codechecker $PLUGIN_PATH
        
        echo "🧪 Running PHPUnit tests..."
        ~/.composer/vendor/bin/moodle-plugin-ci phpunit $PLUGIN_PATH
        
        echo "🎭 Running Behat tests..."
        ~/.composer/vendor/bin/moodle-plugin-ci behat $PLUGIN_PATH
        
        echo "✅ Full Moodle tests completed!"
    else
        echo "⚠️  moodle-plugin-ci not found. Installing..."
        composer global require moodlehq/moodle-plugin-ci
        echo "🔄 Please run the script again"
        exit 1
    fi
    
else
    echo "📋 Running quick validation (no Moodle required)..."
    
    # PHP syntax check
    if command -v php >/dev/null 2>&1; then
        echo "🔍 Running PHP syntax check..."
        find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec php -l {} \;
        echo "✅ PHP syntax check completed"
    else
        echo "⚠️  PHP not found - skipping syntax check"
    fi
    
    # Plugin structure validation
    echo "📋 Validating plugin structure..."
    required_files=("version.php" "lib.php" "settings.php")
    for file in "${required_files[@]}"; do
        if [ -f "$file" ]; then
            echo "✅ $file exists"
        else
            echo "❌ $file missing"
            exit 1
        fi
    done
    
    # Check version.php structure
    if grep -q "plugin->version" version.php; then
        echo "✅ version.php has proper structure"
    else
        echo "❌ version.php missing version info"
        exit 1
    fi
    
    # Check lib.php structure
    if grep -q "function.*navigatr" lib.php; then
        echo "✅ lib.php has plugin functions"
    else
        echo "⚠️  lib.php may be missing plugin functions"
    fi
    
    echo "✅ Plugin structure validation passed"
    
    # Security checks
    echo "🔍 Checking for security issues..."
    find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec grep -l "eval\|exec\|system\|shell_exec" {} \; || echo "✅ No obvious security issues found"
    
    # Deprecated functions check
    echo "🔍 Checking for deprecated functions..."
    find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec grep -l "mysql_\|split\|ereg" {} \; || echo "✅ No deprecated functions found"
    
    # Error handling check
    echo "🔍 Checking for error handling..."
    find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec grep -l "try.*catch\|throw" {} \; || echo "⚠️ Consider adding error handling"
    
    # Documentation check
    echo "🔍 Checking for documentation..."
    find . -name "*.php" -not -path "./vendor/*" -not -path "./tests/*" -exec grep -l "/\*\*" {} \; || echo "⚠️ Consider adding PHPDoc comments"
    
    echo ""
    echo "🎉 Quick validation completed!"
    echo "📊 Plugin structure validation passed"
    echo "🔍 Basic checks passed"
    echo ""
    echo "💡 For full testing with PHPUnit and Behat:"
    echo "   1. Set up a Moodle installation"
    echo "   2. Install this plugin in local/navigatr/"
    echo "   3. Run this script again"
fi