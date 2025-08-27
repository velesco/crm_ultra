#!/bin/bash

# 🔧 Quick Fix Script for CRM Ultra
# Fixes common issues: routes, dashboard methods, caches

echo "🔧 CRM Ultra - Quick Fix Script"
echo "==============================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[FIX]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel root directory"
    exit 1
fi

echo "🧹 Step 1: Clearing Laravel Caches..."
echo "===================================="

if command -v php &> /dev/null; then
    print_status "Clearing configuration cache..."
    php artisan config:clear
    
    print_status "Clearing route cache..."
    php artisan route:clear
    
    print_status "Clearing view cache..."
    php artisan view:clear
    
    print_status "Clearing application cache..."
    php artisan cache:clear
    
    print_success "All caches cleared"
else
    print_warning "PHP not found - skipping cache clearing"
fi

echo ""
echo "🔍 Step 2: Testing Routes..."
echo "=========================="

if command -v php &> /dev/null; then
    if php artisan route:list --name=webhook > /dev/null 2>&1; then
        print_success "Routes compile successfully"
        print_status "Available webhook routes:"
        php artisan route:list --name=webhook --columns=method,uri,name 2>/dev/null || echo "  No webhook routes found"
    else
        print_error "Route compilation failed"
        print_status "Run: php artisan route:list for more details"
    fi
else
    print_warning "Cannot test routes - PHP not available"
fi

echo ""
echo "🧪 Step 3: Testing Dashboard Controller..."
echo "========================================"

if [ -f "test-dashboard.sh" ]; then
    print_status "Running dashboard method tests..."
    ./test-dashboard.sh
else
    print_warning "Dashboard test script not found"
    if command -v php &> /dev/null; then
        print_status "Quick dashboard method check..."
        php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        try {
            \$controller = new App\Http\Controllers\DashboardController();
            \$reflection = new ReflectionClass(\$controller);
            \$methods = ['calculateEmailOpenRate', 'calculateSmsDeliveryRate'];
            \$missing = [];
            foreach (\$methods as \$method) {
                if (!\$reflection->hasMethod(\$method)) {
                    \$missing[] = \$method;
                }
            }
            if (empty(\$missing)) {
                echo '✅ Dashboard methods OK' . PHP_EOL;
            } else {
                echo '❌ Missing methods: ' . implode(', ', \$missing) . PHP_EOL;
            }
        } catch (Exception \$e) {
            echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        }
        "
    fi
fi

echo ""
echo "🔧 Step 4: Optimizing for Performance..."
echo "======================================="

if command -v php &> /dev/null; then
    print_status "Caching configuration..."
    php artisan config:cache
    
    print_status "Caching routes..."
    php artisan route:cache
    
    print_status "Caching views..."
    php artisan view:cache
    
    print_success "Performance optimization completed"
else
    print_warning "PHP not available - skipping optimization"
fi

echo ""
echo "📋 Step 5: System Health Check..."
echo "================================"

print_status "Checking critical files..."

critical_files=(
    "app/Http/Controllers/DashboardController.php"
    "app/Http/Controllers/WhatsAppController.php"
    "app/Services/WhatsAppService.php"
    "routes/api.php"
    "routes/web.php"
    "whatsapp-server/server.js"
    "whatsapp-server/.env"
)

for file in "${critical_files[@]}"; do
    if [ -f "$file" ]; then
        print_success "✓ $file exists"
    else
        print_error "✗ $file missing"
    fi
done

echo ""
echo "🌐 Step 6: WhatsApp Server Check..."
echo "=================================="

if [ -d "whatsapp-server" ]; then
    print_success "WhatsApp server directory exists"
    
    if [ -f "whatsapp-server/.env" ]; then
        print_success "WhatsApp server .env configured"
    else
        print_warning "WhatsApp server .env missing"
        if [ -f "whatsapp-server/.env.example" ]; then
            print_status "Copying .env.example to .env"
            cp whatsapp-server/.env.example whatsapp-server/.env
            print_success ".env file created"
        fi
    fi
    
    if [ -f "whatsapp-server/package.json" ]; then
        print_success "WhatsApp server package.json exists"
    else
        print_error "WhatsApp server package.json missing"
    fi
else
    print_error "WhatsApp server directory missing"
fi

echo ""
echo "✅ Quick Fix Summary"
echo "=================="

echo "🔧 Fixes Applied:"
echo "- Laravel caches cleared and optimized"
echo "- Route compilation tested"
echo "- Dashboard controller methods verified"
echo "- WhatsApp server configuration checked"
echo ""

echo "📋 Manual Steps (if needed):"
echo "1. If routes still fail: Check routes/web.php and routes/api.php for conflicts"
echo "2. If dashboard errors: Run ./test-dashboard.sh for detailed testing"
echo "3. If WhatsApp issues: Check whatsapp-server/.env configuration"
echo "4. For production: Run ./deploy-production.sh"
echo ""

print_success "🎉 Quick fix completed!"

echo ""
echo "🚀 Next Steps:"
echo "- Test dashboard: Visit /dashboard in browser"
echo "- Test WhatsApp: Start whatsapp-server and test webhook"
echo "- Check logs: tail -f storage/logs/laravel.log"
echo ""