#!/bin/bash

# 🔧 CRM Ultra - Configurare .env
echo "=============================================="
echo "🔧 CRM Ultra - Configurare .env"
echo "=============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_info() {
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

# Check if .env.production exists
if [ ! -f ".env.production" ]; then
    print_error "Fișierul .env.production nu există!"
    exit 1
fi

# Copy .env.production to .env if .env doesn't exist
if [ ! -f ".env" ]; then
    print_info "Copiez .env.production ca .env..."
    cp .env.production .env
    print_success "Fișier .env creat din .env.production"
else
    echo -e "${YELLOW}Fișierul .env există deja.${NC}"
    read -p "Dorești să-l înlocuiești cu template-ul? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cp .env.production .env
        print_success "Fișier .env înlocuit cu template-ul"
    else
        print_info "Păstrez fișierul .env existent"
    fi
fi

# Generate APP_KEY if needed
print_info "Generez APP_KEY..."
if php artisan key:generate --ansi; then
    print_success "APP_KEY generat cu succes"
else
    print_error "Eroare la generarea APP_KEY"
fi

echo ""
echo "=============================================="
print_info "📋 GHID DE CONFIGURARE"
echo "=============================================="
echo ""

echo "Acum trebuie să configurezi variabilele din .env:"
echo ""

echo "🔧 1. CONFIGURARE DE BAZĂ:"
echo "   - APP_URL: înlocuiește cu domeniul tău (ex: https://crm.company.com)"
echo "   - APP_ENV: 'production' pentru live, 'local' pentru development"
echo ""

echo "🗄️  2. BAZA DE DATE (OBLIGATORIU):"
echo "   - DB_HOST: IP-ul serverului MySQL"
echo "   - DB_DATABASE: numele bazei de date"  
echo "   - DB_USERNAME: username MySQL"
echo "   - DB_PASSWORD: parola MySQL"
echo "   📖 Găsești în: cPanel → MySQL Databases sau VPS config"
echo ""

echo "📧 3. EMAIL SMTP (OBLIGATORIU):"
echo "   - MAIL_HOST: smtp.gmail.com (pentru Gmail)"
echo "   - MAIL_USERNAME: email-ul tău"
echo "   - MAIL_PASSWORD: App Password (pentru Gmail)"
echo "   🔗 Gmail setup: https://support.google.com/accounts/answer/185833"
echo ""

echo "🔗 4. GOOGLE OAUTH (pentru Gmail/Sheets):"
echo "   - GOOGLE_CLIENT_ID și GOOGLE_CLIENT_SECRET"
echo "   🔗 Configurează la: https://console.cloud.google.com/"
echo "   📖 Activează: Gmail API + Google Sheets API"
echo ""

echo "📱 5. SMS (OPȚIONAL):"
echo "   - Twilio: https://www.twilio.com/"
echo "   - Vonage: https://dashboard.nexmo.com/"
echo ""

echo "💬 6. WHATSAPP (OPȚIONAL):"
echo "   - Generează tokens cu: openssl rand -hex 32"
echo "   - Configurează serverul Node.js pe portul 3001"
echo ""

echo "=============================================="
print_info "🔄 URMĂTORII PAȘI"
echo "=============================================="
echo ""

echo "1. 📝 Editează fișierul .env:"
echo "   nano .env   # sau cu editorul preferat"
echo ""

echo "2. 📚 Consultă ghidul detaliat:"
echo "   cat ENV_CONFIGURATION_GUIDE.md"
echo ""

echo "3. 🧪 Testează configurația:"
echo "   php check_config.php"
echo ""

echo "4. 🗄️  Creează baza de date:"
echo "   php artisan migrate"
echo ""

echo "5. 📊 Populează cu date inițiale:"
echo "   php artisan db:seed"
echo ""

echo "=============================================="
print_success "Template .env pregătit! Configurează variabilele și continuă."
echo "=============================================="
echo ""

print_warning "⚠️  IMPORTANT: Nu uita să configurezi cel puțin:"
echo "   • Database connection (DB_*)"
echo "   • SMTP email (MAIL_*)"  
echo "   • APP_URL cu domeniul tău real"
echo ""

print_info "Pentru ajutor detaliat: cat ENV_CONFIGURATION_GUIDE.md"
