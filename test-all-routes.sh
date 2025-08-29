#!/bin/bash

# 🧪 Route Testing Script for CRM Ultra
# Tests all critical routes that were recently fixed

cd /Users/vasilevelesco/Documents/crm_ultra

echo "🧪 TESTING CRM ULTRA ROUTES - August 29, 2025"
echo "================================================"

# Clear route cache first
echo "1️⃣  Clearing route cache..."
php artisan route:clear

echo ""
echo "2️⃣  Testing WhatsApp Routes..."
echo "--------------------------------"
WHATSAPP_ROUTES=(
    "whatsapp.index"
    "whatsapp.send"
    "whatsapp.send-message"
    "whatsapp.chat"
    "whatsapp.sessions.index"
)

for route in "${WHATSAPP_ROUTES[@]}"; do
    if php artisan route:list --name="$route" | grep -q "$route"; then
        echo "✅ $route - EXISTS"
    else
        echo "❌ $route - MISSING"
    fi
done

echo ""
echo "3️⃣  Testing Export Routes..."
echo "-----------------------------"
EXPORT_ROUTES=(
    "exports.index"
    "exports.create"
    "exports.show"
    "exports.start"
    "admin.exports.index"
)

for route in "${EXPORT_ROUTES[@]}"; do
    if php artisan route:list --name="$route" | grep -q "$route"; then
        echo "✅ $route - EXISTS"
    else
        echo "❌ $route - MISSING"
    fi
done

echo ""
echo "4️⃣  Testing Module Files..."
echo "----------------------------"
MODULE_FILES=(
    "routes/modules/admin.php"
    "routes/modules/email.php"
    "routes/modules/sms.php"
    "routes/modules/whatsapp.php"
)

for file in "${MODULE_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file - EXISTS"
    else
        echo "❌ $file - MISSING"
    fi
done

echo ""
echo "5️⃣  Route Statistics..."
echo "-----------------------"
TOTAL_ROUTES=$(php artisan route:list | wc -l)
WHATSAPP_COUNT=$(php artisan route:list --name=whatsapp | wc -l)
EXPORT_COUNT=$(php artisan route:list --name=export | wc -l)
ADMIN_COUNT=$(php artisan route:list --name=admin | wc -l)

echo "📊 Total Routes: $TOTAL_ROUTES"
echo "📱 WhatsApp Routes: $WHATSAPP_COUNT"
echo "📤 Export Routes: $EXPORT_COUNT"
echo "👥 Admin Routes: $ADMIN_COUNT"

echo ""
echo "🎯 Route Testing Complete!"
echo "========================="
