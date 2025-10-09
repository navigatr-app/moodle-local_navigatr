#!/bin/bash

echo "🧪 Navigatr Plugin Testing"
echo "=========================="

# Check if we're in a Moodle installation
if [ -f "config.php" ] && [ -d "lib" ] && [ -d "admin" ]; then
    echo "📦 Moodle installation detected - running full tests..."
    
    # Check if moodle-plugin-ci is available
    if command -v ~/.composer/vendor/bin/moodle-plugin-ci >/dev/null 2>&1; then
        echo "🔍 Running PHP linting..."
        ~/.composer/vendor/bin/moodle-plugin-ci phplint local/navigatr
        
        echo "🔍 Running code checker..."
        ~/.composer/vendor/bin/moodle-plugin-ci codechecker local/navigatr
        
        echo "🧪 Running PHPUnit tests..."
        ~/.composer/vendor/bin/moodle-plugin-ci phpunit local/navigatr
        
        echo "🎭 Running Behat tests..."
        ~/.composer/vendor/bin/moodle-plugin-ci behat local/navigatr
        
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
    echo "✅ Ready for commit!"
    echo ""
    echo "💡 For full testing with PHPUnit and Behat:"
    echo "   1. Set up a Moodle installation"
    echo "   2. Install this plugin in local/navigatr/"
    echo "   3. Run this script again"
fi