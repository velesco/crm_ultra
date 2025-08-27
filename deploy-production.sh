#!/bin/bash

# 🚀 CRM Ultra - Production Deployment Script for ultra-crm.aipro.ro
# This script prepares the WhatsApp server for production deployment

echo "🚀 CRM Ultra - Production Deployment Preparation"
echo "================================================="
echo "Target: ultra-crm.aipro.ro"
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

echo "📝 Step 1: Updating Configuration Files..."
echo "=========================================="

# Update Laravel .env for production
print_status "Updating Laravel .env configuration..."

# Check if .env exists
if [ ! -f .env ]; then
    print_warning ".env file not found, copying from .env.example"
    cp .env.example .env
fi

# Update WhatsApp server URL in Laravel .env
sed -i.bak 's|WHATSAPP_SERVER_URL=http://localhost:3001|WHATSAPP_SERVER_URL=https://ultra-crm.aipro.ro:3001|g' .env
sed -i.bak 's|APP_URL=http://localhost|APP_URL=https://ultra-crm.aipro.ro|g' .env

print_success "Laravel .env updated for production"

# Update WhatsApp server .env for production
print_status "Updating WhatsApp server configuration..."

cd whatsapp-server

if [ ! -f .env ]; then
    print_warning ".env file not found, copying from .env.example"
    cp .env.example .env
fi

# Update production settings
sed -i.bak 's|NODE_ENV=development|NODE_ENV=production|g' .env
sed -i.bak 's|http://localhost:8000|https://ultra-crm.aipro.ro|g' .env
sed -i.bak 's|LOG_LEVEL=debug|LOG_LEVEL=info|g' .env

print_success "WhatsApp server .env updated for production"

cd ..

echo ""
echo "📦 Step 2: Preparing Files for Upload..."
echo "======================================="

# Create deployment package
print_status "Creating deployment package..."

# Create a deployment directory
mkdir -p deployment/whatsapp-server
mkdir -p deployment/laravel-updates

# Copy WhatsApp server files
cp -r whatsapp-server/* deployment/whatsapp-server/
# Remove node_modules and logs from package
rm -rf deployment/whatsapp-server/node_modules
rm -rf deployment/whatsapp-server/logs
rm -rf deployment/whatsapp-server/sessions
rm -rf deployment/whatsapp-server/uploads

# Copy Laravel updates
cp app/Services/WhatsAppService.php deployment/laravel-updates/
cp app/Http/Controllers/WhatsAppController.php deployment/laravel-updates/
cp config/services.php deployment/laravel-updates/
cp routes/api.php deployment/laravel-updates/
cp .env deployment/laravel-updates/laravel.env

print_success "Deployment package created in ./deployment/"

echo "🔧 Step 3: Production Optimizations & Route Fix..."
echo "===================================================="

# Fix route conflicts first
print_status "Fixing route conflicts..."
if [ -f "fix-routes.sh" ]; then
    ./fix-routes.sh
else
    print_warning "Route fix script not found, clearing caches manually"
    php artisan config:clear
    php artisan route:clear
    php artisan cache:clear
fi

# Optimize Laravel for production
print_status "Optimizing Laravel for production..."

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_success "Laravel optimized for production"

# Create PM2 ecosystem file with production settings
print_status "Creating production PM2 configuration..."

cat > deployment/whatsapp-server/ecosystem.production.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'crm-ultra-whatsapp-server',
    script: './server.js',
    instances: 1,
    exec_mode: 'cluster',
    max_memory_restart: '500M',
    autorestart: true,
    watch: false,
    env: {
      NODE_ENV: 'production',
      PORT: 3001
    },
    error_file: './logs/pm2-error.log',
    out_file: './logs/pm2-out.log',
    log_file: './logs/pm2-combined.log',
    time: true,
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
    merge_logs: true,
    kill_timeout: 5000,
    wait_ready: true,
    listen_timeout: 10000,
    reload_delay: 1000,
    max_restarts: 10,
    min_uptime: '10s'
  }]
};
EOF

print_success "PM2 production configuration created"

echo ""
echo "📄 Step 4: Creating Deployment Instructions..."
echo "============================================="

# Create deployment instructions file
cat > deployment/DEPLOYMENT-INSTRUCTIONS.md << 'EOF'
# 🚀 WhatsApp Server Deployment Instructions for ultra-crm.aipro.ro

## Quick Deployment Steps

### 1. Upload Files to Server
```bash
# Upload whatsapp-server directory
scp -r whatsapp-server/ user@ultra-crm.aipro.ro:/path/to/crm_ultra/

# Upload Laravel updates
scp laravel-updates/* user@ultra-crm.aipro.ro:/path/to/crm_ultra/
```

### 2. SSH into Server and Install
```bash
ssh user@ultra-crm.aipro.ro
cd /path/to/crm_ultra

# Install WhatsApp server
cd whatsapp-server
npm install --production
mkdir -p sessions logs uploads

# Update Laravel files
cp laravel-updates/WhatsAppService.php app/Services/
cp laravel-updates/WhatsAppController.php app/Http/Controllers/
cp laravel-updates/services.php config/
cp laravel-updates/api.php routes/
cp laravel-updates/laravel.env .env

# Start WhatsApp server
pm2 start ecosystem.production.config.js
pm2 save
pm2 startup
```

### 3. Configure Nginx (if needed)
Add to your Nginx configuration:

```nginx
location /whatsapp-server/ {
    proxy_pass http://localhost:3001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

### 4. Test Deployment
```bash
# Test server health
curl https://ultra-crm.aipro.ro:3001/health

# Check PM2 status
pm2 list
pm2 logs crm-ultra-whatsapp-server
```

### 5. Laravel Configuration
- Login to Laravel admin: https://ultra-crm.aipro.ro
- Go to WhatsApp section
- Create new session
- Scan QR code with your phone
- Start messaging!

## Important Notes
- Make sure port 3001 is open in firewall
- SSL certificate must be valid for HTTPS
- Change API tokens in production
- Monitor logs regularly
- Backup sessions directory
EOF

print_success "Deployment instructions created"

echo ""
echo "📦 Step 5: Creating Upload Package..."
echo "==================================="

# Create tar package for easy upload
print_status "Creating compressed package for upload..."

cd deployment
tar -czf crm-ultra-whatsapp-deployment.tar.gz whatsapp-server/ laravel-updates/ DEPLOYMENT-INSTRUCTIONS.md

print_success "Package created: deployment/crm-ultra-whatsapp-deployment.tar.gz"

cd ..

echo ""
echo "🔐 Step 6: Security Configuration..."
echo "=================================="

print_warning "IMPORTANT SECURITY REMINDERS:"
echo "- Change API tokens in production (.env files)"
echo "- Ensure SSL certificate is valid"
echo "- Configure firewall to allow port 3001"
echo "- Set up regular backups of sessions directory"
echo "- Monitor server logs regularly"

echo ""
echo "✅ Production Deployment Preparation Complete!"
echo "=============================================="
echo ""
echo "📋 Next Steps:"
echo "1. Upload deployment/crm-ultra-whatsapp-deployment.tar.gz to your server"
echo "2. Extract and follow DEPLOYMENT-INSTRUCTIONS.md"
echo "3. Test the WhatsApp functionality"
echo "4. Monitor logs and performance"
echo ""
echo "📦 Package Location: $(pwd)/deployment/crm-ultra-whatsapp-deployment.tar.gz"
echo "📄 Instructions: $(pwd)/deployment/DEPLOYMENT-INSTRUCTIONS.md"
echo ""
echo "🚀 Ready for production deployment on ultra-crm.aipro.ro!"

# Clean up backup files
rm -f .env.bak
rm -f whatsapp-server/.env.bak

print_success "Deployment preparation completed successfully! 🎉"