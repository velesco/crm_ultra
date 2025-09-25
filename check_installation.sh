#!/bin/bash

# 🚀 CRM Ultra - Script de Verificare Instalare
echo "=============================================="
echo "🚀 CRM Ultra - Verificare Configurare"
echo "=============================================="
echo ""

# Verificare PHP
echo "📋 Verificare PHP..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP Version: $PHP_VERSION"

# Verificare extensii PHP necesare
echo ""
echo "📋 Verificare extensii PHP..."
REQUIRED_EXTENSIONS=("curl" "fileinfo" "mbstring" "openssl" "PDO" "pdo_mysql" "tokenizer" "xml" "ctype" "json" "bcmath" "gd" "zip")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^$ext$"; then
        echo "✅ $ext: INSTALAT"
    else
        echo "❌ $ext: LIPSEȘTE"
    fi
done

# Verificare Composer
echo ""
echo "📋 Verificare Composer..."
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | grep -oP '\d+\.\d+\.\d+' | head -1)
    echo "✅ Composer: $COMPOSER_VERSION"
else
    echo "❌ Composer: NU ESTE INSTALAT"
fi

# Verificare Node.js și NPM
echo ""
echo "📋 Verificare Node.js și NPM..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    echo "✅ Node.js: $NODE_VERSION"
else
    echo "❌ Node.js: NU ESTE INSTALAT"
fi

if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm --version)
    echo "✅ NPM: $NPM_VERSION"
else
    echo "❌ NPM: NU ESTE INSTALAT"
fi

# Verificare fișierul .env
echo ""
echo "📋 Verificare fișier .env..."
if [ -f ".env" ]; then
    echo "✅ Fișierul .env există"
    
    # Verificare variabile critice
    echo ""
    echo "📋 Verificare variabile critice..."
    
    # APP_KEY
    if grep -q "APP_KEY=base64:" .env; then
        echo "✅ APP_KEY: CONFIGURAT"
    else
        echo "❌ APP_KEY: LIPSEȘTE (rulează: php artisan key:generate)"
    fi
    
    # Database
    if grep -q "DB_DATABASE=" .env && ! grep -q "DB_DATABASE=$" .env; then
        echo "✅ DB_DATABASE: CONFIGURAT"
    else
        echo "❌ DB_DATABASE: LIPSEȘTE"
    fi
    
    # Mail
    if grep -q "MAIL_HOST=" .env && ! grep -q "MAIL_HOST=$" .env; then
        echo "✅ MAIL_HOST: CONFIGURAT"
    else
        echo "⚠️  MAIL_HOST: LIPSEȘTE (opțional pentru testare)"
    fi
    
    # Google Services
    if grep -q "GOOGLE_CLIENT_ID=" .env && ! grep -q "GOOGLE_CLIENT_ID=$" .env; then
        echo "✅ GOOGLE_CLIENT_ID: CONFIGURAT"
    else
        echo "⚠️  GOOGLE_CLIENT_ID: LIPSEȘTE (necesar pentru Gmail/Sheets)"
    fi
    
else
    echo "❌ Fișierul .env nu există (copiază din .env.example)"
fi

# Verificare directoare și permisiuni
echo ""
echo "📋 Verificare permisiuni directoare..."
if [ -w "storage" ]; then
    echo "✅ storage/: WRITABLE"
else
    echo "❌ storage/: NOT WRITABLE (rulează: chmod -R 775 storage)"
fi

if [ -w "bootstrap/cache" ]; then
    echo "✅ bootstrap/cache/: WRITABLE"
else
    echo "❌ bootstrap/cache/: NOT WRITABLE (rulează: chmod -R 775 bootstrap/cache)"
fi

# Verificare baza de date
echo ""
echo "📋 Verificare conexiune baza de date..."
if php artisan migrate:status > /dev/null 2>&1; then
    echo "✅ Conexiune baza de date: OK"
    
    # Verificare migrări
    PENDING_MIGRATIONS=$(php artisan migrate:status | grep "Pending" | wc -l)
    if [ $PENDING_MIGRATIONS -eq 0 ]; then
        echo "✅ Migrări: TOATE RULATE"
    else
        echo "⚠️  Migrări: $PENDING_MIGRATIONS PENDING (rulează: php artisan migrate)"
    fi
else
    echo "❌ Conexiune baza de date: EȘUATĂ"
fi

# Verificare vendor și node_modules
echo ""
echo "📋 Verificare dependencies..."
if [ -d "vendor" ]; then
    echo "✅ vendor/: EXISTĂ"
else
    echo "❌ vendor/: LIPSEȘTE (rulează: composer install)"
fi

if [ -d "node_modules" ]; then
    echo "✅ node_modules/: EXISTĂ"
else
    echo "❌ node_modules/: LIPSEȘTE (rulează: npm install)"
fi

# Verificare assets compilate
if [ -d "public/build" ]; then
    echo "✅ Assets compilate: EXISTĂ"
else
    echo "⚠️  Assets compilate: LIPSESC (rulează: npm run build)"
fi

# Verificare configurări optimize
echo ""
echo "📋 Verificare optimizări..."
if [ -f "bootstrap/cache/config.php" ]; then
    echo "✅ Config cache: ACTIV"
else
    echo "⚠️  Config cache: INACTIV (pentru producție: php artisan config:cache)"
fi

if [ -f "bootstrap/cache/routes-v7.php" ]; then
    echo "✅ Route cache: ACTIV"
else
    echo "⚠️  Route cache: INACTIV (pentru producție: php artisan route:cache)"
fi

# Verificare WhatsApp Server
echo ""
echo "📋 Verificare WhatsApp Server..."
if [ -d "whatsapp-server" ]; then
    echo "✅ WhatsApp Server: EXISTĂ"
    if [ -f "whatsapp-server/.env" ]; then
        echo "✅ WhatsApp .env: EXISTĂ"
    else
        echo "⚠️  WhatsApp .env: LIPSEȘTE"
    fi
else
    echo "⚠️  WhatsApp Server: LIPSEȘTE"
fi

# Sumar final
echo ""
echo "=============================================="
echo "📊 SUMAR VERIFICARE"
echo "=============================================="
echo ""
echo "🔧 Pentru a finaliza instalarea:"
echo ""
echo "1. Dacă lipsesc extensii PHP, instalează-le:"
echo "   sudo apt-get install php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-mysql"
echo ""
echo "2. Dacă lipsește APP_KEY:"
echo "   php artisan key:generate"
echo ""
echo "3. Dacă ai migrări pending:"
echo "   php artisan migrate"
echo ""
echo "4. Dacă lipsesc dependencies:"
echo "   composer install"
echo "   npm install"
echo "   npm run build"
echo ""
echo "5. Pentru optimizare producție:"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "6. Pentru a porni queue worker:"
echo "   php artisan queue:work --daemon"
echo ""
echo "=============================================="
echo "🎉 Verificare completă!"
echo "=============================================="
