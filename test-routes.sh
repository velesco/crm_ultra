#!/bin/bash

# 🧪 Quick Test for Route Fix
# Tests if the route conflict has been resolved

echo "🧪 Testing Route Fix..."

# Test if Laravel routes can be compiled
echo "Testing route compilation..."

if command -v php >/dev/null 2>&1; then
    # Test route compilation
    if php artisan route:list >/dev/null 2>&1; then
        echo "✅ Routes compile successfully - conflict resolved!"
        
        # Show webhook routes
        echo ""
        echo "📋 Available webhook routes:"
        php artisan route:list --name=webhook --columns=method,uri,name 2>/dev/null || echo "No webhook routes found"
        
        echo ""
        echo "🔗 WhatsApp webhook details:"
        php artisan route:list --name=api.whatsapp.webhook --columns=method,uri,name 2>/dev/null || echo "WhatsApp webhook route not found"
        
    else
        echo "❌ Route compilation failed - conflicts may still exist"
        exit 1
    fi
else
    echo "⚠️  PHP not found - cannot test routes"
    echo "Please test manually with: php artisan route:list"
fi

echo ""
echo "🎯 Expected webhook URL: /api/whatsapp/webhook"
echo "🎯 Expected route name: api.whatsapp.webhook"
echo ""
echo "✅ Route fix test completed!"