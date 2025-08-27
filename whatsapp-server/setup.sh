#!/bin/bash

# 🚀 CRM Ultra - WhatsApp Server Setup Script
# This script sets up the WhatsApp Web.js server for CRM Ultra

echo "🚀 Setting up CRM Ultra WhatsApp Server..."

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

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed. Please install Node.js 18 or higher."
    print_status "Visit: https://nodejs.org/en/download/"
    exit 1
fi

# Check Node.js version
NODE_VERSION=$(node --version | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    print_error "Node.js version 18 or higher is required. Current version: $(node --version)"
    exit 1
fi

print_success "Node.js $(node --version) detected"

# Change to WhatsApp server directory
cd whatsapp-server

print_status "Installing WhatsApp server dependencies..."

# Install dependencies
if npm install; then
    print_success "Dependencies installed successfully"
else
    print_error "Failed to install dependencies"
    exit 1
fi

# Check if .env file exists, if not copy from .env.example
if [ ! -f .env ]; then
    print_status "Creating .env file from .env.example..."
    cp .env.example .env
    print_success ".env file created"
    print_warning "Please edit .env file with your configuration"
else
    print_warning ".env file already exists"
fi

# Create necessary directories
print_status "Creating required directories..."
mkdir -p sessions
mkdir -p uploads
mkdir -p logs

print_success "Directories created"

# Check if PM2 is installed globally
if ! command -v pm2 &> /dev/null; then
    print_warning "PM2 not found. Installing PM2 globally..."
    if npm install -g pm2; then
        print_success "PM2 installed successfully"
    else
        print_warning "Failed to install PM2 globally. You can install it manually with: npm install -g pm2"
    fi
else
    print_success "PM2 $(pm2 --version) detected"
fi

print_status "\n📋 Setup completed! Next steps:"
echo ""
echo "1. 📝 Edit the .env file with your configuration:"
echo "   - LARAVEL_API_URL: Your Laravel application URL"
echo "   - LARAVEL_API_TOKEN: API token for authentication"
echo "   - WEBHOOK_SECRET: Secret for webhook validation"
echo ""
echo "2. 🚀 Start the WhatsApp server:"
echo "   Development: npm run dev"
echo "   Production:  npm run pm2:start"
echo ""
echo "3. 📱 The server will be available at: http://localhost:3001"
echo "4. 🔍 Health check: http://localhost:3001/health"
echo ""
echo "5. 📊 Monitor with PM2 (if using production):"
echo "   pm2 list"
echo "   pm2 logs crm-ultra-whatsapp-server"
echo "   pm2 monit"
echo ""
echo "6. 🔗 Configure Laravel .env with:"
echo "   WHATSAPP_SERVER_URL=http://localhost:3001"
echo "   WHATSAPP_API_TOKEN=your-api-token-here"
echo "   WHATSAPP_WEBHOOK_SECRET=your-webhook-secret-here"
echo ""
print_success "WhatsApp server setup completed! 🎉"

# Go back to main directory
cd ..