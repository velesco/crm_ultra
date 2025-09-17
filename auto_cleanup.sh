#!/bin/bash

# 🔍 CRM Ultra - Auto-detect și cleanup fișiere temporare
echo "=============================================="
echo "🔍 CRM Ultra - Auto Cleanup Detector"
echo "=============================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
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

# Detectează automat fișierele care par temporare
detect_temp_files() {
    echo "🔍 Detectez fișierele temporare..."
    echo ""
    
    # Fișiere .md care par temporare (conțin FINAL, FIX, RESOLUTION, etc.)
    temp_md_files=$(find . -maxdepth 1 -name "*.md" -type f | grep -E "(FINAL|FIX|RESOLUTION|BATCH|SUMMARY|COMPLETION|VERIFICATION|FIXES|ERROR)" | grep -v README | grep -v TODO | grep -v INSTALLATION | grep -v QUICK | grep -v ENV)
    
    # Fișiere PHP de testare
    temp_php_files=$(find . -maxdepth 1 -name "*.php" -type f | grep -E "(test_|check_|verify_|setup_)" | head -10)
    
    # Fișiere .sh de cleanup/test
    temp_sh_files=$(find . -maxdepth 1 -name "*.sh" -type f | grep -E "(cleanup|test)" | head -5)
    
    # Directoare temporare
    temp_dirs=$(find . -maxdepth 1 -type d | grep -E "(\\.claude|diagnostics|temp|tmp)")
    
    total_temp_files=0
    
    # Afișează rezultatele
    if [ ! -z "$temp_md_files" ]; then
        echo "📄 FIȘIERE .MD TEMPORARE DETECTATE:"
        echo "$temp_md_files" | while read file; do
            if [ -f "$file" ]; then
                size=$(ls -lh "$file" | awk '{print $5}')
                echo "   • $file ($size)"
                total_temp_files=$((total_temp_files + 1))
            fi
        done
        echo ""
    fi
    
    if [ ! -z "$temp_php_files" ]; then
        echo "🐘 FIȘIERE PHP DE TEST DETECTATE:"
        echo "$temp_php_files" | while read file; do
            if [ -f "$file" ]; then
                size=$(ls -lh "$file" | awk '{print $5}')
                echo "   • $file ($size)"
                total_temp_files=$((total_temp_files + 1))
            fi
        done
        echo ""
    fi
    
    if [ ! -z "$temp_sh_files" ]; then
        echo "🛠️ SCRIPTURI TEMPORARE DETECTATE:"
        echo "$temp_sh_files" | while read file; do
            if [ -f "$file" ]; then
                size=$(ls -lh "$file" | awk '{print $5}')
                echo "   • $file ($size)"
                total_temp_files=$((total_temp_files + 1))
            fi
        done
        echo ""
    fi
    
    if [ ! -z "$temp_dirs" ]; then
        echo "📁 DIRECTOARE TEMPORARE DETECTATE:"
        echo "$temp_dirs" | while read dir; do
            if [ -d "$dir" ]; then
                size=$(du -sh "$dir" 2>/dev/null | awk '{print $1}')
                echo "   • $dir/ ($size)"
                total_temp_files=$((total_temp_files + 1))
            fi
        done
        echo ""
    fi
    
    # Compilează lista pentru ștergere
    temp_files_list="temp_files_list.txt"
    {
        echo "$temp_md_files"
        echo "$temp_php_files" 
        echo "$temp_sh_files"
        echo "$temp_dirs"
    } | grep -v "^$" > "$temp_files_list"
    
    file_count=$(cat "$temp_files_list" | wc -l)
    
    if [ $file_count -eq 0 ]; then
        print_success "Nu am găsit fișiere temporare de șters!"
        rm -f "$temp_files_list"
        return
    fi
    
    echo "📊 SUMAR:"
    echo "• Fișiere/directoare temporare găsite: $file_count"
    echo ""
    
    print_warning "⚠️  Acestea par să fie fișiere temporare de dezvoltare."
    echo ""
    
    read -p "Ștergi fișierele temporare detectate? (y/n): " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo ""
        print_info "Șterg fișierele temporare..."
        
        deleted_count=0
        while IFS= read -r file; do
            if [ -f "$file" ] || [ -d "$file" ]; then
                if rm -rf "$file" 2>/dev/null; then
                    print_success "Șters: $file"
                    deleted_count=$((deleted_count + 1))
                else
                    print_warning "Nu am putut șterge: $file"
                fi
            fi
        done < "$temp_files_list"
        
        echo ""
        print_success "✅ Cleanup finalizat! Șterse: $deleted_count fișiere/directoare"
    else
        print_info "Cleanup anulat. Fișierele rămân neatinse."
    fi
    
    rm -f "$temp_files_list"
}

# Afișează fișierele importante care vor rămâne
show_important_files() {
    echo ""
    echo "=============================================="
    print_info "📋 FIȘIERE IMPORTANTE PĂSTRATE"
    echo "=============================================="
    echo ""
    
    echo "📚 DOCUMENTAȚIE PRINCIPALĂ:"
    important_docs=("README.md" "TODO.md" "INSTALLATION_GUIDE.md" "QUICK_INSTALL.md" "ENV_CONFIGURATION_GUIDE.md" "ENV_QUICK_REFERENCE.md")
    for doc in "${important_docs[@]}"; do
        if [ -f "$doc" ]; then
            size=$(ls -lh "$doc" | awk '{print $5}')
            echo "   ✅ $doc ($size)"
        fi
    done
    
    echo ""
    echo "🤖 SCRIPTURI DE INSTALARE:"
    install_scripts=("master_install.sh" "install.sh" "check_installation.sh" "configure_env.sh" "setup_services.sh" "check_config.php")
    for script in "${install_scripts[@]}"; do
        if [ -f "$script" ]; then
            size=$(ls -lh "$script" | awk '{print $5}')
            echo "   ✅ $script ($size)"
        fi
    done
    
    echo ""
    echo "📄 TEMPLATE-URI:"
    templates=(".env.production" ".env.example")
    for template in "${templates[@]}"; do
        if [ -f "$template" ]; then
            size=$(ls -lh "$template" | awk '{print $5}')
            echo "   ✅ $template ($size)"
        fi
    done
    
    echo ""
    echo "🏗️ APLICAȚIA LARAVEL:"
    echo "   ✅ app/ - Codul aplicației"
    echo "   ✅ resources/ - View-uri și assets"
    echo "   ✅ config/ - Configurații Laravel"
    echo "   ✅ database/ - Migrări și seeders"
    echo "   ✅ routes/ - Rutele aplicației"
    echo "   ✅ public/ - Assets publice"
    echo "   ✅ whatsapp-server/ - Server WhatsApp"
}

# Main execution
echo "Acest script detectează automat fișierele temporare de dezvoltare."
echo ""

detect_temp_files
show_important_files

echo ""
echo "=============================================="
print_success "🎉 ANALIZA COMPLETĂ!"
echo "=============================================="
echo ""
print_info "💡 Pentru cleanup manual, folosește: ./cleanup_project.sh"
