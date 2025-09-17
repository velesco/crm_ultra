#!/bin/bash

# 🚀 CRM Ultra - Configurare Rapidă Servicii Externe
echo "=============================================="
echo "🚀 CRM Ultra - Configurare Servicii Externe"
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

# Check if .env exists
if [ ! -f ".env" ]; then
    print_error "Fișierul .env nu există. Rulează mai întâi ./install.sh"
    exit 1
fi

echo "Acest script te ajută să configurezi rapid serviciile externe pentru CRM Ultra."
echo "Poți sări oricare din configurări apăsând Enter."
echo ""

# Google OAuth Configuration
echo "=============================================="
print_info "GOOGLE OAUTH CONFIGURATION"
echo "=============================================="
echo ""
echo "Pentru Gmail și Google Sheets integration:"
echo "1. Mergi la: https://console.cloud.google.com/"
echo "2. Creează/selectează un proiect"
echo "3. Activează: Gmail API și Google Sheets API"
echo "4. Credentials > Create > OAuth 2.0 Client ID"
echo "5. Adaugă în 'Authorized redirect URIs':"
current_url=$(grep "APP_URL=" .env | cut -d'=' -f2)
echo "   ${current_url}/google-sheets/callback"
echo ""

read -p "Google Client ID (sau Enter pentru skip): " google_id
read -p "Google Client Secret (sau Enter pentru skip): " google_secret

if [ ! -z "$google_id" ] && [ ! -z "$google_secret" ]; then
    sed -i.bak "s/GOOGLE_CLIENT_ID=.*/GOOGLE_CLIENT_ID=$google_id/" .env
    sed -i.bak "s/GOOGLE_CLIENT_SECRET=.*/GOOGLE_CLIENT_SECRET=$google_secret/" .env
    print_success "Google OAuth configurat!"
else
    print_warning "Google OAuth skipped"
fi

echo ""

# Twilio SMS Configuration
echo "=============================================="
print_info "TWILIO SMS CONFIGURATION"
echo "=============================================="
echo ""
echo "Pentru SMS functionality cu Twilio:"
echo "1. Mergi la: https://console.twilio.com/"
echo "2. Creează cont și verifică numărul de telefon"
echo "3. Din Dashboard copiază:"
echo "   - Account SID"
echo "   - Auth Token"
echo "4. Din Phone Numbers copiază numărul Twilio"
echo ""

read -p "Twilio Account SID (sau Enter pentru skip): " twilio_sid
read -p "Twilio Auth Token (sau Enter pentru skip): " twilio_token
read -p "Twilio Phone Number (ex: +1234567890): " twilio_number

if [ ! -z "$twilio_sid" ] && [ ! -z "$twilio_token" ]; then
    sed -i.bak "s/TWILIO_ACCOUNT_SID=.*/TWILIO_ACCOUNT_SID=$twilio_sid/" .env
    sed -i.bak "s/TWILIO_AUTH_TOKEN=.*/TWILIO_AUTH_TOKEN=$twilio_token/" .env
    if [ ! -z "$twilio_number" ]; then
        sed -i.bak "s/TWILIO_FROM_NUMBER=.*/TWILIO_FROM_NUMBER=$twilio_number/" .env
    fi
    print_success "Twilio SMS configurat!"
else
    print_warning "Twilio SMS skipped"
fi

echo ""

# Vonage SMS Configuration (alternative)
echo "=============================================="
print_info "VONAGE SMS CONFIGURATION (Alternativă)"
echo "=============================================="
echo ""
echo "Pentru SMS functionality cu Vonage (ex-Nexmo):"
echo "1. Mergi la: https://dashboard.nexmo.com/"
echo "2. Creează cont și verifică numărul"
echo "3. Din Dashboard copiază API Key și API Secret"
echo ""

read -p "Vonage API Key (sau Enter pentru skip): " vonage_key
read -p "Vonage API Secret (sau Enter pentru skip): " vonage_secret
read -p "Vonage From Number (sau Enter pentru default): " vonage_number

if [ ! -z "$vonage_key" ] && [ ! -z "$vonage_secret" ]; then
    sed -i.bak "s/VONAGE_KEY=.*/VONAGE_KEY=$vonage_key/" .env
    sed -i.bak "s/VONAGE_SECRET=.*/VONAGE_SECRET=$vonage_secret/" .env
    if [ ! -z "$vonage_number" ]; then
        sed -i.bak "s/VONAGE_FROM_NUMBER=.*/VONAGE_FROM_NUMBER=$vonage_number/" .env
    fi
    print_success "Vonage SMS configurat!"
else
    print_warning "Vonage SMS skipped"
fi

echo ""

# SMTP Configuration
echo "=============================================="
print_info "SMTP EMAIL CONFIGURATION"
echo "=============================================="
echo ""
echo "Pentru trimitere emails:"
echo "Opțiuni populare:"
echo "1. Gmail SMTP: smtp.gmail.com:587 (TLS)"
echo "2. Outlook: smtp-mail.outlook.com:587 (TLS)"
echo "3. Custom SMTP server"
echo ""

read -p "SMTP Host (sau Enter pentru skip): " smtp_host
read -p "SMTP Port (default: 587): " smtp_port
read -p "SMTP Username (email): " smtp_user
read -s -p "SMTP Password: " smtp_pass
echo ""
read -p "From Email Address: " from_email

if [ ! -z "$smtp_host" ]; then
    smtp_port=${smtp_port:-587}
    sed -i.bak "s/MAIL_HOST=.*/MAIL_HOST=$smtp_host/" .env
    sed -i.bak "s/MAIL_PORT=.*/MAIL_PORT=$smtp_port/" .env
    if [ ! -z "$smtp_user" ]; then
        sed -i.bak "s/MAIL_USERNAME=.*/MAIL_USERNAME=$smtp_user/" .env
    fi
    if [ ! -z "$smtp_pass" ]; then
        sed -i.bak "s/MAIL_PASSWORD=.*/MAIL_PASSWORD=$smtp_pass/" .env
    fi
    if [ ! -z "$from_email" ]; then
        sed -i.bak "s/MAIL_FROM_ADDRESS=.*/MAIL_FROM_ADDRESS=\"$from_email\"/" .env
    fi
    sed -i.bak "s/MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/" .env
    print_success "SMTP configurat!"
else
    print_warning "SMTP configuration skipped"
fi

echo ""

# WhatsApp Configuration
echo "=============================================="
print_info "WHATSAPP INTEGRATION CONFIGURATION"
echo "=============================================="
echo ""
echo "Pentru WhatsApp integration:"
echo "1. Asigură-te că ai un server dedicat pentru WhatsApp"
echo "2. Portul 3001 trebuie să fie disponibil"
echo "3. SSL certificate pentru domeniu"
echo ""
current_domain=$(grep "APP_URL=" .env | cut -d'=' -f2 | sed 's/https\?:\/\///')
echo "Server recomandat: https://$current_domain:3001"
echo ""

read -p "WhatsApp Server URL (sau Enter pentru skip): " whatsapp_url
read -p "WhatsApp API Token (generat automat dacă lași gol): " whatsapp_token
read -p "WhatsApp Webhook Secret (generat automat dacă lași gol): " whatsapp_secret

if [ ! -z "$whatsapp_url" ]; then
    # Generate random tokens if not provided
    if [ -z "$whatsapp_token" ]; then
        whatsapp_token=$(openssl rand -hex 16)
        print_info "Token generat automat: $whatsapp_token"
    fi
    
    if [ -z "$whatsapp_secret" ]; then
        whatsapp_secret=$(openssl rand -hex 16)
        print_info "Secret generat automat: $whatsapp_secret"
    fi
    
    sed -i.bak "s|WHATSAPP_SERVER_URL=.*|WHATSAPP_SERVER_URL=$whatsapp_url|" .env
    sed -i.bak "s/WHATSAPP_API_TOKEN=.*/WHATSAPP_API_TOKEN=$whatsapp_token/" .env
    sed -i.bak "s/WHATSAPP_WEBHOOK_SECRET=.*/WHATSAPP_WEBHOOK_SECRET=$whatsapp_secret/" .env
    
    print_success "WhatsApp configurat!"
    print_warning "Nu uita să configurezi și serverul WhatsApp cu aceleași token-uri!"
else
    print_warning "WhatsApp configuration skipped"
fi

echo ""

# Queue Configuration
echo "=============================================="
print_info "QUEUE CONFIGURATION"
echo "=============================================="
echo ""
echo "Pentru background jobs (recomandat pentru producție):"
echo "1. database - folosește MySQL (recomandat)"
echo "2. redis - folosește Redis (mai rapid)"
echo "3. sync - procesare imediată (doar pentru testare)"
echo ""

read -p "Queue driver (database/redis/sync) [database]: " queue_driver
queue_driver=${queue_driver:-database}

sed -i.bak "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=$queue_driver/" .env
print_success "Queue driver setat la: $queue_driver"

if [ "$queue_driver" != "sync" ]; then
    print_warning "Pentru $queue_driver queue, nu uita să rulezi: php artisan queue:work"
fi

echo ""

# Final Configuration
echo "=============================================="
print_info "OPTIMIZARE FINALĂ"
echo "=============================================="
echo ""

print_info "Aplicare configurări..."
php artisan config:cache
php artisan route:cache
print_success "Cache-urile au fost regenerate!"

echo ""
echo "=============================================="
print_success "🎉 CONFIGURARE SERVICII COMPLETĂ!"
echo "=============================================="
echo ""

echo "📋 SUMAR CONFIGURĂRI:"
echo ""

# Check what was configured
if grep -q "GOOGLE_CLIENT_ID=." .env && ! grep -q "GOOGLE_CLIENT_ID=$" .env; then
    echo "✅ Google OAuth: CONFIGURAT"
else
    echo "❌ Google OAuth: NU E CONFIGURAT"
fi

if grep -q "TWILIO_ACCOUNT_SID=." .env && ! grep -q "TWILIO_ACCOUNT_SID=$" .env; then
    echo "✅ Twilio SMS: CONFIGURAT"
elif grep -q "VONAGE_KEY=." .env && ! grep -q "VONAGE_KEY=$" .env; then
    echo "✅ Vonage SMS: CONFIGURAT"
else
    echo "❌ SMS Service: NU E CONFIGURAT"
fi

if grep -q "MAIL_HOST=." .env && ! grep -q "MAIL_HOST=$" .env; then
    echo "✅ SMTP Email: CONFIGURAT"
else
    echo "❌ SMTP Email: NU E CONFIGURAT"
fi

if grep -q "WHATSAPP_SERVER_URL=." .env && ! grep -q "WHATSAPP_SERVER_URL=$" .env; then
    echo "✅ WhatsApp: CONFIGURAT"
else
    echo "❌ WhatsApp: NU E CONFIGURAT"
fi

queue_current=$(grep "QUEUE_CONNECTION=" .env | cut -d'=' -f2)
echo "✅ Queue Driver: $queue_current"

echo ""
echo "🔧 URMĂTORII PAȘI:"
echo ""
echo "1. Testează configurările: php check_config.php"
echo "2. Pentru background jobs: php artisan queue:work"
echo "3. Pentru WhatsApp server: cd whatsapp-server && npm start"
echo "4. Accesează aplicația: $(grep 'APP_URL=' .env | cut -d'=' -f2)"
echo ""
print_success "Mult succes cu CRM Ultra! 🚀"
echo ""
