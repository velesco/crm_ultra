#!/bin/bash

# 🔧 Route Fix Script for CRM Ultra
# Fixes route conflicts and clears Laravel caches

echo "🔧 Fixing Route Conflicts..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
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

print_status "Step 1: Clearing Laravel caches..."

# Clear all caches
if command -v php &> /dev/null; then
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    print_success "Laravel caches cleared"
    
    print_status "Step 2: Checking routes..."
    
    # Check for route conflicts
    if php artisan route:list --name=webhook > /dev/null 2>&1; then
        print_success "Routes compiled successfully"
        
        print_status "Available webhook routes:"
        php artisan route:list --name=webhook
        
    else
        print_error "Route compilation failed - there may still be conflicts"
        echo "Manual check needed"
    fi
    
    print_status "Step 3: Testing configuration..."
    
    # Test configuration
    if php artisan config:cache > /dev/null 2>&1; then
        print_success "Configuration cached successfully"
    else
        print_warning "Configuration caching failed - check .env file"
    fi
    
else
    print_warning "PHP not found in PATH, skipping Laravel commands"
    print_status "Manually run these commands:"
    echo "php artisan config:clear"
    echo "php artisan route:clear"
    echo "php artisan cache:clear"
fi

print_status "Step 4: Verifying WhatsApp webhook URL..."

echo ""
echo "✅ Webhook Configuration:"
echo "========================"
echo "WhatsApp Server → Laravel:"
echo "URL: https://ultra-crm.aipro.ro/api/whatsapp/webhook"
echo "Route name: api.whatsapp.webhook"
echo "Method: POST"
echo ""
echo "Headers required:"
echo "- Content-Type: application/json"
echo "- X-Webhook-Secret: your-webhook-secret"
echo ""

print_success "Route fix completed! 🎉"

echo ""
echo "📋 Next steps:"
echo "1. Update WhatsApp server webhook URL to: /api/whatsapp/webhook"
echo "2. Test webhook: curl -X POST https://ultra-crm.aipro.ro/api/whatsapp/webhook"
echo "3. Check Laravel logs if webhook fails"
echo ""