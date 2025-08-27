#!/bin/bash

# 🧪 Dashboard Controller Methods Test
# Tests if all required methods exist and dashboard loads correctly

echo "🧪 Testing Dashboard Controller Methods..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[TEST]${NC} $1"
}

print_pass() {
    echo -e "${GREEN}[PASS]${NC} $1"
}

print_fail() {
    echo -e "${RED}[FAIL]${NC} $1"
}

print_info() {
    echo -e "${YELLOW}[INFO]${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_fail "This script must be run from the Laravel root directory"
    exit 1
fi

print_status "Checking PHP availability..."
if ! command -v php &> /dev/null; then
    print_fail "PHP not found in PATH"
    exit 1
fi
print_pass "PHP is available"

print_status "Testing Dashboard Controller methods..."

# Test 1: Check if calculateEmailOpenRate method exists
print_status "Checking calculateEmailOpenRate method..."
if php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$controller = new App\Http\Controllers\DashboardController();
\$reflection = new ReflectionClass(\$controller);
if (\$reflection->hasMethod('calculateEmailOpenRate')) {
    echo 'METHOD_EXISTS';
} else {
    echo 'METHOD_MISSING';
}
" | grep -q "METHOD_EXISTS"; then
    print_pass "calculateEmailOpenRate method exists"
else
    print_fail "calculateEmailOpenRate method missing"
fi

# Test 2: Check if calculateSmsDeliveryRate method exists
print_status "Checking calculateSmsDeliveryRate method..."
if php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$controller = new App\Http\Controllers\DashboardController();
\$reflection = new ReflectionClass(\$controller);
if (\$reflection->hasMethod('calculateSmsDeliveryRate')) {
    echo 'METHOD_EXISTS';
} else {
    echo 'METHOD_MISSING';
}
" | grep -q "METHOD_EXISTS"; then
    print_pass "calculateSmsDeliveryRate method exists"
else
    print_fail "calculateSmsDeliveryRate method missing"
fi

# Test 3: Check if User model has required methods
print_status "Checking User model methods..."
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$user = new App\Models\User();
\$reflection = new ReflectionClass(\$user);
\$methods = ['canSendEmails', 'canSendSms', 'canUseWhatsApp', 'canImportData', 'canManageSettings'];
\$missing = [];
foreach (\$methods as \$method) {
    if (!\$reflection->hasMethod(\$method)) {
        \$missing[] = \$method;
    }
}
if (empty(\$missing)) {
    echo 'USER_METHODS_OK';
} else {
    echo 'MISSING: ' . implode(', ', \$missing);
}
" > /tmp/user_methods_test.txt

if grep -q "USER_METHODS_OK" /tmp/user_methods_test.txt; then
    print_pass "All User model methods exist"
else
    print_fail "Missing User model methods: $(cat /tmp/user_methods_test.txt | sed 's/MISSING: //')"
fi

# Test 4: Check model scopes
print_status "Checking model scopes..."
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$errors = [];

// Check SmtpConfig active scope
try {
    \$smtp = new App\Models\SmtpConfig();
    \$reflection = new ReflectionClass(\$smtp);
    if (!\$reflection->hasMethod('scopeActive')) {
        \$errors[] = 'SmtpConfig::scopeActive';
    }
} catch (Exception \$e) {
    \$errors[] = 'SmtpConfig error: ' . \$e->getMessage();
}

// Check SmsProvider active scope
try {
    \$sms = new App\Models\SmsProvider();
    \$reflection = new ReflectionClass(\$sms);
    if (!\$reflection->hasMethod('scopeActive')) {
        \$errors[] = 'SmsProvider::scopeActive';
    }
} catch (Exception \$e) {
    \$errors[] = 'SmsProvider error: ' . \$e->getMessage();
}

// Check WhatsAppSession scopes
try {
    \$wa = new App\Models\WhatsAppSession();
    \$reflection = new ReflectionClass(\$wa);
    if (!\$reflection->hasMethod('scopeActive')) {
        \$errors[] = 'WhatsAppSession::scopeActive';
    }
    if (!\$reflection->hasMethod('scopeConnected')) {
        \$errors[] = 'WhatsAppSession::scopeConnected';
    }
} catch (Exception \$e) {
    \$errors[] = 'WhatsAppSession error: ' . \$e->getMessage();
}

if (empty(\$errors)) {
    echo 'SCOPES_OK';
} else {
    echo 'MISSING_SCOPES: ' . implode(', ', \$errors);
}
" > /tmp/scopes_test.txt

if grep -q "SCOPES_OK" /tmp/scopes_test.txt; then
    print_pass "All model scopes exist"
else
    print_fail "Missing scopes: $(cat /tmp/scopes_test.txt | sed 's/MISSING_SCOPES: //')"
fi

# Test 5: Try to instantiate DashboardController (syntax check)
print_status "Testing DashboardController instantiation..."
if php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
try {
    \$controller = new App\Http\Controllers\DashboardController();
    echo 'CONTROLLER_OK';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
" | grep -q "CONTROLLER_OK"; then
    print_pass "DashboardController instantiation successful"
else
    print_fail "DashboardController instantiation failed"
    php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    try {
        \$controller = new App\Http\Controllers\DashboardController();
    } catch (Exception \$e) {
        echo 'Error details: ' . \$e->getMessage() . PHP_EOL;
        echo 'File: ' . \$e->getFile() . ':' . \$e->getLine() . PHP_EOL;
    }
    "
fi

# Cleanup temp files
rm -f /tmp/user_methods_test.txt /tmp/scopes_test.txt

echo ""
print_info "✅ Dashboard Controller method tests completed!"
print_info "If all tests passed, the dashboard should load without method errors."
print_info "If any tests failed, check the specific missing methods or scopes."

echo ""
echo "📋 Quick fixes if needed:"
echo "- Missing methods: Add them to DashboardController"
echo "- Missing scopes: Add scopeActive() to model classes"
echo "- User methods: Check User model for permission helpers"
echo ""