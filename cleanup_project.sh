#!/bin/bash

# 🧹 CRM Ultra - Cleanup Script pentru fișiere în plus
echo "=============================================="
echo "🧹 CRM Ultra - Cleanup Fișiere în Plus"
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

# Lista fișierelor care pot fi șterse (dezvoltare/documentație temporară)
CLEANUP_FILES=(
    # Fișiere de documentație temporară
    "ADDITIONAL_FIXES_12_13.md"
    "ARRAY_PROPERTY_ACCESS_RESOLUTION_FINAL.md"
    "CDN_MODAL_RESOLUTION_FINAL.md"
    "CLAUDE.md"
    "COMMUNICATION_SERVICES_RESOLUTION_FINAL.md"
    "FAZA_10_COMPLETION_REPORT.md"
    "FAZELE_COMPLETATE_ASTAZI.md"
    "GMAIL_IMPLEMENTATION_SUMMARY.md"
    "GMAIL_INTEGRATION_95_COMPLETE.md"
    "MIGRATION_RESOLUTION_FINAL.md"
    "MISSING_VIEW_RESOLUTION_FINAL.md"
    "ROUTE_FIX_FINAL.md"
    "RUNTIME_FIXES_BATCH_2.md"
    "RUNTIME_FIXES_FINAL.md"
    "RUNTIME_FIXES_SUMMARY.md"
    "SMTP_ISSUE_RESOLUTION_FINAL.md"
    "SWIFTMAILER_MIGRATION_FINAL.md"
    "VERIFICATION_REPORT.md"
    "errortodo.md"
    
    # Scripturi de testare/debugging
    "check_migration.php"
    "check_services.php"
    "check_system.php"
    "cleanup-tests.sh"
    "setup_smtp_configs.php"
    "test_communication_controller.php"
    "test_email_service.php"
    "verify_gmail_integration.php"
)

# Lista directoarelor care pot fi șterse
CLEANUP_DIRS=(
    "diagnostics"
    ".claude"
)

print_header "FIȘIERE ȘI DIRECTOARE PENTRU CLEANUP"

echo "Următoarele fișiere/directoare vor fi verificate pentru ștergere:"
echo ""

# Afișează fișierele existente
echo "📄 FIȘIERE:"
for file in "${CLEANUP_FILES[@]}"; do
    if [ -f "$file" ]; then
        size=$(ls -lh "$file" | awk '{print $5}')
        echo "   ✅ $file ($size)"
    else
        echo "   ❌ $file (nu există)"
    fi
done

echo ""
echo "📁 DIRECTOARE:"
for dir in "${CLEANUP_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        size=$(du -sh "$dir" 2>/dev/null | awk '{print $1}')
        echo "   ✅ $dir/ ($size)"
    else
        echo "   ❌ $dir/ (nu există)"
    fi
done

echo ""
print_warning "⚠️  ATENȚIE: Aceste fișiere vor fi ȘTERSE PERMANENT!"
echo ""
echo "Fișierele sunt considerate temporare și nu sunt necesare pentru funcționarea CRM-ului:"
echo "• Documentație de dezvoltare temporară"
echo "• Rapoarte de fix-uri și debugging" 
echo "• Scripturi de testare și verificare"
echo "• Directoare auxiliare de development"
echo ""

read -p "Continui cu cleanup-ul? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Cleanup anulat. Fișierele rămân neatinse."
    exit 0
fi

print_header "EXECUȚIE CLEANUP"

deleted_count=0
total_size=0

# Șterge fișierele
echo "🗑️ Șterg fișierele..."
for file in "${CLEANUP_FILES[@]}"; do
    if [ -f "$file" ]; then
        size_bytes=$(stat -f%z "$file" 2>/dev/null || stat -c%s "$file" 2>/dev/null || echo "0")
        total_size=$((total_size + size_bytes))
        
        if rm "$file"; then
            print_success "Șters: $file"
            deleted_count=$((deleted_count + 1))
        else
            print_error "Eroare la ștergerea: $file"
        fi
    fi
done

# Șterge directoarele
echo ""
echo "📂 Șterg directoarele..."
for dir in "${CLEANUP_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        if rm -rf "$dir"; then
            print_success "Șters director: $dir/"
            deleted_count=$((deleted_count + 1))
        else
            print_error "Eroare la ștergerea directorului: $dir/"
        fi
    fi
done

# Calculează spațiul eliberat
space_kb=$((total_size / 1024))
space_mb=$((space_kb / 1024))

echo ""
print_header "REZULTATE CLEANUP"

echo "📊 STATISTICI:"
echo "• Fișiere/directoare șterse: $deleted_count"

if [ $space_mb -gt 0 ]; then
    echo "• Spațiu eliberat: ${space_mb}MB"
elif [ $space_kb -gt 0 ]; then
    echo "• Spațiu eliberat: ${space_kb}KB"
else
    echo "• Spațiu eliberat: ${total_size} bytes"
fi

echo ""
echo "✅ PĂSTRATE (IMPORTANTE):"
echo "• README.md - documentația principală"
echo "• TODO.md - status și planuri de dezvoltare"
echo "• INSTALLATION_GUIDE.md - ghid de instalare"
echo "• QUICK_INSTALL.md - ghid rapid"
echo "• ENV_CONFIGURATION_GUIDE.md - configurare .env"
echo "• ENV_QUICK_REFERENCE.md - reference .env"
echo "• Toate scripturile de instalare (.sh și .php)"
echo "• .env.production - template pentru producție"
echo ""

echo "🧹 ȘTERSE (TEMPORARE):"
echo "• Documentație de dezvoltare temporară"
echo "• Rapoarte de fix-uri și debugging"
echo "• Scripturi de testare și verificare"
echo "• Directoare auxiliare"
echo ""

print_header "🎉 CLEANUP COMPLET!"

echo "CRM Ultra este acum curat și optimizat pentru distribuire!"
echo ""
echo "📁 STRUCTURA FINALĂ PĂSTRATĂ:"
echo "• Aplicația Laravel completă"
echo "• Documentația de instalare și utilizare"
echo "• Scripturile de setup și configurare"
echo "• Fișierele de configurare și template-uri"
echo ""

print_success "🚀 Gata pentru deployment și distribuire!"
