#!/bin/bash

# 🚀 CRM Ultra - Master Installation Script
echo "=============================================="
echo "🚀 CRM Ultra - Complete Installation Wizard"
echo "=============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m'

print_header() {
    echo -e "${PURPLE}=============================================="
    echo -e "🎯 $1"
    echo -e "===============================================${NC}"
    echo ""
}

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

# Welcome message
print_header "BINE AI VENIT LA CRM ULTRA INSTALLER"
echo "Acest script te va ghida prin întregul proces de instalare."
echo "Durează aproximativ 10-15 minute și include:"
echo ""
echo "✅ Verificarea cerințelor sistem"
echo "✅ Instalarea dependințelor"  
echo "✅ Configurarea .env"
echo "✅ Setup-ul bazei de date"
echo "✅ Configurarea serviciilor externe"
echo "✅ Testarea și optimizarea"
echo ""

read -p "Să începem? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Instalare anulată. Poți relua oricând cu ./master_install.sh"
    exit 0
fi

# Step 1: System check
print_header "STEP 1: VERIFICARE CERINȚE SISTEM"
if [ -f "check_installation.sh" ]; then
    if ./check_installation.sh; then
        print_success "Toate cerințele sistem sunt îndeplinite!"
    else
        print_error "Unele cerințe nu sunt îndeplinite. Verifică și rezolvă problemele."
        exit 1
    fi
else
    print_warning "Script check_installation.sh nu găsit. Continuăm..."
fi

echo ""
read -p "Continuăm cu instalarea? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 0
fi

# Step 2: Dependencies installation
print_header "STEP 2: INSTALAREA DEPENDINȚELOR"
print_info "Instalez Composer dependencies..."
if composer install --optimize-autoloader --no-dev; then
    print_success "Composer dependencies instalate"
else
    print_error "Eroare la instalarea Composer dependencies"
    exit 1
fi

print_info "Instalez NPM dependencies..."
if npm install; then
    print_success "NPM dependencies instalate"
else
    print_error "Eroare la instalarea NPM dependencies"
    exit 1
fi

print_info "Compilez assets..."
if npm run build; then
    print_success "Assets compilate cu succes"
else
    print_error "Eroare la compilarea assets-urilor"
    exit 1
fi

# Step 3: Environment configuration  
print_header "STEP 3: CONFIGURAREA .env"
if [ -f "configure_env.sh" ]; then
    ./configure_env.sh
else
    print_info "Configurez .env manual..."
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            cp .env.production .env
            print_success ".env creat din template"
        else
            cp .env.example .env
            print_success ".env creat din .env.example"
        fi
    fi
    
    if php artisan key:generate --ansi; then
        print_success "APP_KEY generat"
    else
        print_error "Eroare la generarea APP_KEY"
    fi
fi

echo ""
print_warning "⚠️  IMPORTANT: Verifică că ai configurat în .env:"
echo "   • Database connection (DB_*)"
echo "   • SMTP settings (MAIL_*)"
echo "   • APP_URL cu domeniul tău"
echo ""
read -p "Ai configurat variabilele necesare în .env? (y/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Configurează .env și rulează din nou scriptul"
    print_info "Ghid detaliat: cat ENV_CONFIGURATION_GUIDE.md"
    exit 0
fi

# Step 4: Database setup
print_header "STEP 4: CONFIGURAREA BAZEI DE DATE"
print_info "Testez conexiunea la baza de date..."

if php artisan migrate:status > /dev/null 2>&1; then
    print_success "Conexiunea la baza de date funcționează!"
    
    print_info "Rulez migrările..."
    if php artisan migrate --force; then
        print_success "Migrări rulate cu succes"
        
        read -p "Dorești să populezi cu date inițiale? (y/n): " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            if php artisan db:seed; then
                print_success "Date inițiale populate cu succes"
            else
                print_warning "Eroare la popularea datelor - nu e critic"
            fi
        fi
    else
        print_error "Eroare la rularea migrărilor"
        exit 1
    fi
else
    print_error "Nu pot să mă conectez la baza de date!"
    print_info "Verifică configurațiile DB_* din .env"
    exit 1
fi

# Step 5: External services
print_header "STEP 5: CONFIGURAREA SERVICIILOR EXTERNE"
echo "Dorești să configurezi serviciile externe acum?"
echo "• Google OAuth (pentru Gmail/Sheets)"
echo "• SMS providers (Twilio/Vonage)"  
echo "• WhatsApp integration"
echo ""

read -p "Configurezi serviciile externe? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    if [ -f "setup_services.sh" ]; then
        ./setup_services.sh
    else
        print_warning "Script setup_services.sh nu găsit"
    fi
else
    print_info "Poți configura serviciile mai târziu cu: ./setup_services.sh"
fi

# Step 6: Optimization
print_header "STEP 6: OPTIMIZARE PENTRU PRODUCȚIE"
print_info "Setez permisiunile..."
chmod -R 775 storage bootstrap/cache
print_success "Permisiuni setate"

print_info "Optimizez pentru producție..."
php artisan config:cache
php artisan route:cache  
php artisan view:cache
composer dump-autoload --optimize
print_success "Optimizări aplicate"

# Step 7: Final verification
print_header "STEP 7: VERIFICARE FINALĂ"
if [ -f "check_config.php" ]; then
    print_info "Rulez verificarea finală..."
    php check_config.php
else
    print_warning "Script check_config.php nu găsit"
fi

# Final success
echo ""
print_header "🎉 INSTALARE COMPLETĂ!"
echo ""
print_success "CRM Ultra a fost instalat cu succes!"
echo ""

echo "📊 SUMAR INSTALARE:"
echo "✅ Dependințe instalate (Composer + NPM)"
echo "✅ Assets compilate" 
echo "✅ .env configurat"
echo "✅ Baza de date configurată"
echo "✅ Migrări rulate"
echo "✅ Optimizări aplicate"
echo ""

echo "🔧 URMĂTORII PAȘI:"
echo ""
echo "1. 🌐 Configurează web server (Apache/Nginx):"
echo "   - Document root: $(pwd)/public"
echo "   - SSL certificate recomandat"
echo ""

echo "2. 🔄 Pentru background jobs:"
echo "   php artisan queue:work --daemon"
echo ""

echo "3. 💬 Pentru WhatsApp (dacă l-ai configurat):"
echo "   cd whatsapp-server && npm start"
echo ""

echo "4. 🌍 Accesează aplicația:"
current_url=$(grep "APP_URL=" .env | cut -d'=' -f2)
echo "   $current_url"
echo ""

echo "5. 👤 Login credentials (după seeding):"
echo "   Admin: admin@crmultra.com / Admin123!"
echo "   Manager: manager@crmultra.com / Manager123!"
echo ""

print_header "🎯 APLICAȚIA ESTE GATA DE UTILIZARE!"
echo ""
print_info "Pentru suport și documentație:"
echo "• INSTALLATION_GUIDE.md - ghid complet"
echo "• ENV_CONFIGURATION_GUIDE.md - configurare .env"
echo "• README.md - informații generale"
echo ""

print_success "🚀 Mult succes cu CRM Ultra!"
