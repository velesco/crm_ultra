#!/bin/bash

# 📧 CRM Ultra - Gmail Migration Helper
echo "=============================================="
echo "📧 CRM Ultra - Gmail Tables Setup"
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

echo "Acest script verifică și creează tabelele necesare pentru Gmail integration."
echo ""

# Check if Laravel is ready
if [ ! -f "artisan" ]; then
    print_error "Nu sunt în directorul Laravel! Rulează din root-ul proiectului."
    exit 1
fi

print_info "Verificare status migrări..."

# Check migration status
if ! php artisan migrate:status > /dev/null 2>&1; then
    print_error "Nu pot verifica migrările. Verifică conexiunea la baza de date."
    exit 1
fi

# Check for Gmail related migrations
gmail_migrations=$(php artisan migrate:status | grep -i google | head -5)

if [ -z "$gmail_migrations" ]; then
    print_warning "Nu am găsit migrări Gmail în sistem"
else
    print_info "Migrări Gmail găsite:"
    echo "$gmail_migrations"
fi

echo ""

# Check for specific tables
print_info "Verificare tabele Gmail..."

tables_to_check=("google_accounts" "emails" "email_attachments" "sync_logs")
missing_tables=()

for table in "${tables_to_check[@]}"; do
    if php -r "
        require 'vendor/autoload.php';
        \$app = require 'bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        try {
            \$exists = Schema::hasTable('$table');
            exit(\$exists ? 0 : 1);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; then
        print_success "Tabela '$table' există"
    else
        print_warning "Tabela '$table' lipsește"
        missing_tables+=($table)
    fi
done

echo ""

if [ ${#missing_tables[@]} -eq 0 ]; then
    print_success "🎉 Toate tabelele Gmail sunt prezente!"
    echo ""
    print_info "Gmail integration este complet configurat și funcțional."
    echo ""
    echo "📊 Pentru a vedea status-ul complet:"
    echo "   php artisan migrate:status"
    echo ""
    echo "🔧 Pentru a testa configurația:"
    echo "   php check_config.php"
    
else
    print_warning "Lipsesc ${#missing_tables[@]} tabele Gmail: ${missing_tables[*]}"
    echo ""
    
    read -p "Dorești să rulezi migrările pentru a crea tabelele lipsă? (y/n): " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Rulez migrările..."
        
        if php artisan migrate --force; then
            print_success "✅ Migrări rulate cu succes!"
            
            # Verify again
            echo ""
            print_info "Verificare finală..."
            
            all_good=true
            for table in "${tables_to_check[@]}"; do
                if php -r "
                    require 'vendor/autoload.php';
                    \$app = require 'bootstrap/app.php';
                    \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
                    \$kernel->bootstrap();
                    try {
                        \$exists = Schema::hasTable('$table');
                        exit(\$exists ? 0 : 1);
                    } catch (Exception \$e) {
                        exit(1);
                    }
                " 2>/dev/null; then
                    print_success "Tabela '$table' creată cu succes"
                else
                    print_error "Tabela '$table' încă lipsește"
                    all_good=false
                fi
            done
            
            if $all_good; then
                echo ""
                print_success "🎉 Gmail integration este acum complet funcțional!"
                
                echo ""
                print_info "🔧 Următorii pași pentru Gmail:"
                echo "1. Configurează Google OAuth în .env (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET)"  
                echo "2. Accesează Settings → Google pentru conectarea conturilor Gmail"
                echo "3. Folosește Inbox-ul unificat pentru gestionarea email-urilor"
            fi
            
        else
            print_error "❌ Eroare la rularea migrărilor!"
            echo ""
            print_info "💡 Soluții posibile:"
            echo "• Verifică conexiunea la baza de date în .env"
            echo "• Asigură-te că utilizatorul MySQL are permisiuni de CREATE TABLE"
            echo "• Rulează manual: php artisan migrate --force"
        fi
    else
        print_info "Migrări anulate. Poți rula manual cu: php artisan migrate"
    fi
fi

echo ""
print_info "ℹ️  Notă: Gmail Badge Provider este acum resilient și funcționează"
print_info "   chiar dacă tabelele nu sunt încă create."

echo ""
echo "=============================================="
print_success "Gmail setup verification completed!"
echo "=============================================="
