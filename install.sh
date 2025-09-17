#!/bin/bash

# 🚀 CRM Ultra - Script de Instalare Automată
echo "=============================================="
echo "🚀 CRM Ultra - Instalare Automată"
echo "=============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "Nu rulați acest script ca root!"
   exit 1
fi

print_status "Începe instalarea CRM Ultra..."

# Step 1: Check PHP version
print_status "Verificare versiune PHP..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")

if [[ $PHP_MAJOR -lt 8 ]] || [[ $PHP_MAJOR -eq 8 && $PHP_MINOR -lt 1 ]]; then
    print_error "PHP 8.1 sau superior este necesar. Versiunea curentă: $PHP_VERSION"
    exit 1
else
    print_success "PHP versiune: $PHP_VERSION"
fi

# Step 2: Check required PHP extensions
print_status "Verificare extensii PHP..."
REQUIRED_EXTENSIONS=("curl" "fileinfo" "mbstring" "openssl" "pdo_mysql" "tokenizer" "xml" "ctype" "json" "bcmath" "gd" "zip")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "^$ext$"; then
        MISSING_EXTENSIONS+=($ext)
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -ne 0 ]; then
    print_error "Lipsesc următoarele extensii PHP: ${MISSING_EXTENSIONS[*]}"
    print_status "Pentru a le instala pe Ubuntu/Debian:"
    echo "sudo apt-get install php8.1-${MISSING_EXTENSIONS[*]// / php8.1-}"
    exit 1
else
    print_success "Toate extensiile PHP sunt instalate"
fi

# Step 3: Check Composer
print_status "Verificare Composer..."
if ! command -v composer &> /dev/null; then
    print_error "Composer nu este instalat"
    print_status "Instalează Composer de la: https://getcomposer.org/"
    exit 1
else
    COMPOSER_VERSION=$(composer --version | grep -oP '\d+\.\d+\.\d+' | head -1)
    print_success "Composer versiune: $COMPOSER_VERSION"
fi

# Step 4: Check Node.js and NPM
print_status "Verificare Node.js și NPM..."
if ! command -v node &> /dev/null; then
    print_error "Node.js nu este instalat"
    exit 1
else
    NODE_VERSION=$(node --version)
    print_success "Node.js versiune: $NODE_VERSION"
fi

if ! command -v npm &> /dev/null; then
    print_error "NPM nu este instalat"
    exit 1
else
    NPM_VERSION=$(npm --version)
    print_success "NPM versiune: $NPM_VERSION"
fi

# Step 5: Install Composer dependencies
print_status "Instalare dependințe Composer..."
if composer install --optimize-autoloader --no-dev; then
    print_success "Dependințe Composer instalate"
else
    print_error "Eroare la instalarea dependințelor Composer"
    exit 1
fi

# Step 6: Install NPM dependencies
print_status "Instalare dependințe NPM..."
if npm install; then
    print_success "Dependințe NPM instalate"
else
    print_error "Eroare la instalarea dependințelor NPM"
    exit 1
fi

# Step 7: Copy .env file if it doesn't exist
if [ ! -f ".env" ]; then
    print_status "Copiere fișier .env..."
    if cp .env.example .env; then
        print_success "Fișier .env creat din .env.example"
    else
        print_error "Eroare la copierea fișierului .env"
        exit 1
    fi
else
    print_warning "Fișierul .env există deja"
fi

# Step 8: Generate application key
print_status "Generare cheie aplicație..."
if php artisan key:generate --ansi; then
    print_success "Cheie aplicație generată"
else
    print_error "Eroare la generarea cheii aplicației"
    exit 1
fi

# Step 9: Set proper permissions
print_status "Setare permisiuni..."
if chmod -R 775 storage bootstrap/cache; then
    print_success "Permisiuni setate pentru storage și bootstrap/cache"
else
    print_warning "Nu s-au putut seta permisiunile. Încearcă manual:"
    echo "sudo chown -R www-data:www-data storage bootstrap/cache"
    echo "sudo chmod -R 775 storage bootstrap/cache"
fi

# Step 10: Build assets
print_status "Compilare assets..."
if npm run build; then
    print_success "Assets compilate cu succes"
else
    print_error "Eroare la compilarea assets-urilor"
    exit 1
fi

# Step 11: Database setup prompt
echo ""
print_status "=============================================="
print_status "CONFIGURARE BAZA DE DATE"
print_status "=============================================="
echo ""

read -p "Dorești să configurezi baza de date acum? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Host baza de date (default: 127.0.0.1): " DB_HOST
    DB_HOST=${DB_HOST:-127.0.0.1}
    
    read -p "Port baza de date (default: 3306): " DB_PORT
    DB_PORT=${DB_PORT:-3306}
    
    read -p "Nume baza de date: " DB_DATABASE
    read -p "Username baza de date: " DB_USERNAME
    read -s -p "Parolă baza de date: " DB_PASSWORD
    echo ""
    
    # Update .env file
    sed -i.bak "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i.bak "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
    sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
    sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    
    print_success "Configurații baza de date salvate în .env"
    
    # Test database connection and run migrations
    print_status "Testare conexiune și rulare migrări..."
    if php artisan migrate --force; then
        print_success "Migrări rulate cu succes"
    else
        print_error "Eroare la rularea migrărilor. Verifică configurația bazei de date"
    fi
fi

# Step 12: Google OAuth setup prompt
echo ""
print_status "=============================================="
print_status "CONFIGURARE GOOGLE OAUTH (Opțional)"
print_status "=============================================="
echo ""

read -p "Dorești să configurezi Google OAuth pentru Gmail/Sheets? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Pentru a configura Google OAuth:"
    echo "1. Mergi la https://console.cloud.google.com/"
    echo "2. Creează un proiect nou sau selectează unul existent"
    echo "3. Activează Gmail API și Google Sheets API"
    echo "4. Creează credentials (OAuth 2.0 Client ID)"
    echo "5. Adaugă domeniile autorizate"
    echo ""
    
    read -p "Google Client ID: " GOOGLE_CLIENT_ID
    read -p "Google Client Secret: " GOOGLE_CLIENT_SECRET
    
    if [ ! -z "$GOOGLE_CLIENT_ID" ] && [ ! -z "$GOOGLE_CLIENT_SECRET" ]; then
        sed -i.bak "s/GOOGLE_CLIENT_ID=.*/GOOGLE_CLIENT_ID=$GOOGLE_CLIENT_ID/" .env
        sed -i.bak "s/GOOGLE_CLIENT_SECRET=.*/GOOGLE_CLIENT_SECRET=$GOOGLE_CLIENT_SECRET/" .env
        print_success "Configurații Google OAuth salvate"
    fi
fi

# Step 13: SMS Configuration
echo ""
print_status "=============================================="
print_status "CONFIGURARE SMS (Opțional)"
print_status "=============================================="
echo ""

read -p "Dorești să configurezi un provider SMS? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Selectează provider SMS:"
    echo "1) Twilio"
    echo "2) Vonage"
    echo "3) Skip"
    read -p "Opțiunea ta (1-3): " SMS_CHOICE
    
    case $SMS_CHOICE in
        1)
            read -p "Twilio Account SID: " TWILIO_SID
            read -p "Twilio Auth Token: " TWILIO_TOKEN
            read -p "Twilio Phone Number: " TWILIO_NUMBER
            
            sed -i.bak "s/TWILIO_ACCOUNT_SID=.*/TWILIO_ACCOUNT_SID=$TWILIO_SID/" .env
            sed -i.bak "s/TWILIO_AUTH_TOKEN=.*/TWILIO_AUTH_TOKEN=$TWILIO_TOKEN/" .env
            sed -i.bak "s/TWILIO_FROM_NUMBER=.*/TWILIO_FROM_NUMBER=$TWILIO_NUMBER/" .env
            print_success "Configurații Twilio salvate"
            ;;
        2)
            read -p "Vonage API Key: " VONAGE_KEY
            read -p "Vonage API Secret: " VONAGE_SECRET
            read -p "Vonage From Number: " VONAGE_NUMBER
            
            sed -i.bak "s/VONAGE_KEY=.*/VONAGE_KEY=$VONAGE_KEY/" .env
            sed -i.bak "s/VONAGE_SECRET=.*/VONAGE_SECRET=$VONAGE_SECRET/" .env
            sed -i.bak "s/VONAGE_FROM_NUMBER=.*/VONAGE_FROM_NUMBER=$VONAGE_NUMBER/" .env
            print_success "Configurații Vonage salvate"
            ;;
        3)
            print_status "SMS configuration skipped"
            ;;
    esac
fi

# Step 14: WhatsApp Configuration
echo ""
print_status "=============================================="
print_status "CONFIGURARE WHATSAPP (Opțional)"
print_status "=============================================="
echo ""

read -p "Dorești să configurezi WhatsApp integration? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "WhatsApp Server URL (ex: https://your-domain.com:3001): " WHATSAPP_URL
    read -p "WhatsApp API Token: " WHATSAPP_TOKEN
    
    if [ ! -z "$WHATSAPP_URL" ] && [ ! -z "$WHATSAPP_TOKEN" ]; then
        sed -i.bak "s|WHATSAPP_SERVER_URL=.*|WHATSAPP_SERVER_URL=$WHATSAPP_URL|" .env
        sed -i.bak "s/WHATSAPP_API_TOKEN=.*/WHATSAPP_API_TOKEN=$WHATSAPP_TOKEN/" .env
        print_success "Configurații WhatsApp salvate"
    fi
fi

# Step 15: Final optimizations
echo ""
print_status "=============================================="
print_status "OPTIMIZĂRI FINALE"
print_status "=============================================="
echo ""

print_status "Optimizare pentru producție..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize

print_success "Optimizări aplicate"

# Step 16: Create admin user prompt
echo ""
read -p "Dorești să creezi un utilizator administrator? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Email administrator: " ADMIN_EMAIL
    read -s -p "Parolă administrator: " ADMIN_PASSWORD
    echo ""
    read -p "Nume administrator: " ADMIN_NAME
    
    # Create admin user via artisan command (assuming it exists)
    if php artisan make:admin "$ADMIN_EMAIL" "$ADMIN_PASSWORD" "$ADMIN_NAME" 2>/dev/null; then
        print_success "Utilizator administrator creat"
    else
        print_warning "Comanda make:admin nu există. Creează manualadministratorul din interfața web"
    fi
fi

# Final success message
echo ""
print_status "=============================================="
print_success "🎉 INSTALARE COMPLETĂ!"
print_status "=============================================="
echo ""

echo "CRM Ultra a fost instalat cu succes!"
echo ""
echo "📋 Următorii pași:"
echo "1. Configurează serverul web (Apache/Nginx) să pointeze la directorul 'public/'"
echo "2. Asigură-te că SSL certificatul este configurat"
echo "3. Pentru background jobs, rulează: php artisan queue:work"
echo "4. Pentru WhatsApp, pornește serverul Node.js din directorul 'whatsapp-server/'"
echo "5. Verifică configurația cu: php check_config.php"
echo ""
echo "🌐 Acces aplicație: https://your-domain.com"
echo ""
echo "📚 Documentație completă: INSTALLATION_GUIDE.md"
echo ""
print_success "Mult succes cu CRM Ultra! 🚀"
