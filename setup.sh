#!/bin/bash

# 🚀 CRM Ultra - Complete Project Setup Script
# This script sets up both Laravel and WhatsApp server components

echo "🚀 CRM Ultra - Complete Setup"
echo "=================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if we're in the correct directory
if [ ! -f "composer.json" ]; then
    print_error "This script must be run from the CRM Ultra root directory"
    exit 1
fi

echo "🔧 Step 1: Setting up Laravel application..."
echo "============================================"

# Check if .env exists
if [ ! -f .env ]; then
    print_status "Creating .env file from .env.example..."
    cp .env.example .env
    print_success ".env file created"
else
    print_warning ".env file already exists"
fi

# Install Composer dependencies
print_status "Installing Composer dependencies..."
if composer install; then
    print_success "Composer dependencies installed"
else
    print_error "Failed to install Composer dependencies"
    exit 1
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate

# Install NPM dependencies
print_status "Installing NPM dependencies..."
if npm install; then
    print_success "NPM dependencies installed"
else
    print_error "Failed to install NPM dependencies"
    exit 1
fi

# Build assets
print_status "Building frontend assets..."
npm run build

echo ""
echo "📱 Step 2: Setting up WhatsApp Server..."
echo "======================================="

# Run WhatsApp server setup
if [ -f "whatsapp-server/setup.sh" ]; then
    ./whatsapp-server/setup.sh
else
    print_error "WhatsApp server setup script not found"
fi

echo ""
echo "🗄️  Step 3: Database Setup..."
echo "============================="

print_warning "Please configure your database in .env file before running migrations"
print_status "Database configuration required:"
echo "  - DB_HOST=127.0.0.1"
echo "  - DB_DATABASE=crm_ultra"
echo "  - DB_USERNAME=your_username"
echo "  - DB_PASSWORD=your_password"
echo ""

read -p "Have you configured your database? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Running database migrations..."
    if php artisan migrate; then
        print_success "Database migrations completed"
        
        print_status "Running database seeders..."
        if php artisan db:seed; then
            print_success "Database seeded with sample data"
        else
            print_warning "Failed to seed database"
        fi
    else
        print_error "Database migration failed"
    fi
else
    print_warning "Skipping database setup. Run 'php artisan migrate && php artisan db:seed' after configuring database"
fi

echo ""
echo "🔧 Step 4: Final Configuration..."
echo "================================"

print_status "Optimizing Laravel application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Creating storage link..."
php artisan storage:link

echo ""
echo "✅ Setup Summary:"
echo "================"
echo ""
echo "📊 Laravel Application:"
echo "  - URL: http://localhost:8000 (run: php artisan serve)"
echo "  - Admin: superadmin@crmultra.com / SuperAdmin123!"
echo "  - User: admin@crmultra.com / Admin123!"
echo ""
echo "📱 WhatsApp Server:"
echo "  - URL: http://localhost:3001"
echo "  - Health: http://localhost:3001/health"
echo "  - Start: cd whatsapp-server && npm run dev"
echo ""
echo "🗄️  Database:"
echo "  - Sample data loaded (if configured)"
echo "  - 50+ contacts with various segments"
echo "  - 10 email templates"
echo "  - Complete role system"
echo ""
echo "🚀 Quick Start Commands:"
echo "======================="
echo ""
echo "# Terminal 1 - Laravel Server"
echo "php artisan serve"
echo ""
echo "# Terminal 2 - WhatsApp Server"
echo "cd whatsapp-server"
echo "npm run dev"
echo ""
echo "# Optional: Laravel Horizon (queue processing)"
echo "php artisan horizon"
echo ""
print_success "🎉 CRM Ultra setup completed successfully!"
print_status "📚 Check README.md for detailed documentation"