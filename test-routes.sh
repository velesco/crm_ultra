#!/bin/bash

# Test routes in CRM Ultra
cd /Users/vasilevelesco/Documents/crm_ultra

echo "🔧 Testing CRM Ultra Routes..."
echo "================================="

# Clear route cache first
echo "1. Clearing route cache..."
php artisan route:clear

# Check if the WhatsApp controller exists
echo "2. Checking WhatsApp controller..."
if [ -f "app/Http/Controllers/WhatsAppController.php" ]; then
    echo "✅ WhatsAppController exists"
else
    echo "❌ WhatsAppController missing"
fi

# Check if module files exist
echo "3. Checking module files..."
for module in admin email sms whatsapp; do
    if [ -f "routes/modules/$module.php" ]; then
        echo "✅ routes/modules/$module.php exists"
    else
        echo "❌ routes/modules/$module.php missing"
    fi
done

# Test specific routes we need
echo "4. Testing route definitions..."
echo "php artisan route:list --name=whatsapp"
php artisan route:list --name=whatsapp | head -10

echo ""
echo "🎯 Route testing complete!"
