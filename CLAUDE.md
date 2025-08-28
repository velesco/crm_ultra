# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Laravel Application
```bash
# Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Development server
php artisan serve

# Database operations
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Queue processing
php artisan horizon  # Production queue worker
php artisan queue:work  # Development queue worker

# Cache management
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Frontend Assets
```bash
# Install dependencies
npm install

# Development build
npm run dev

# Production build
npm run build
```

### WhatsApp Server
```bash
# Navigate to WhatsApp server directory
cd whatsapp-server

# Development
npm run dev

# Production with PM2
npm run pm2:start
pm2 monitor
pm2 logs crm-ultra-whatsapp-server
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## Architecture Overview

### Core CRM System
This is a Laravel 10-based CRM application with multi-channel communication capabilities (Email, SMS, WhatsApp). The system uses a modular architecture with dedicated services for each communication channel.

### Key Components

#### Controllers Architecture
- **Main Controllers**: Contact, Dashboard, Email/SMS/WhatsApp management
- **Admin Controllers** (in `app/Http/Controllers/Admin/`): SystemLog, Backup, Security, ApiKey, WebhookLog, QueueMonitor
- **Communication Controllers**: EmailCampaign, SmsProvider, WhatsAppSession

#### Service Layer
- **EmailService**: SMTP configuration and email campaign management
- **SmsService**: Multi-provider SMS (Twilio, Vonage) with failover
- **WhatsAppService**: Integration with custom WhatsApp Web.js server
- **GoogleSheetsService**: Bidirectional contact synchronization
- **AdminService**: System administration and monitoring
- **BackupService**: Database and file backup/restore operations

#### Models & Database
- **Core Models**: Contact, User, Communication, EmailCampaign, SmsMessage, WhatsAppMessage
- **Admin Models**: SystemLog, LoginAttempt, ApiKey, WebhookLog, SystemBackup, PerformanceMetric
- **Configuration Models**: SmtpConfig, SmsProvider, WhatsAppSession, SystemSetting
- **Uses Spatie Laravel Permission** for role-based access control

#### WhatsApp Integration
- **Custom Node.js Server**: Located in `whatsapp-server/` directory
- **WhatsApp Web.js**: Headless WhatsApp integration with Puppeteer
- **Multi-session Support**: Handle multiple WhatsApp accounts simultaneously
- **Real-time WebSocket**: Live message updates and notifications
- **Production Ready**: PM2 process management with auto-restart

### Queue System
- **Laravel Horizon**: Production queue management with Redis
- **Background Jobs**: Email campaigns, SMS sending, data import/export, WhatsApp webhooks
- **Queue Monitoring**: Built-in admin controller for queue health and failed job management

### Admin Panel Features
- **User Management**: Role assignment, permission management, user statistics
- **System Logs**: Comprehensive logging with analytics and filtering
- **Security Center**: Login attempt tracking, IP blocking, threat monitoring
- **Backup Management**: Full system backups with restore capabilities
- **API Management**: API key generation with permissions and rate limiting
- **Webhook Monitoring**: Webhook debugging, retry logic, and analytics
- **System Settings**: Global configuration management with encryption support

### Authentication & Authorization
- **Laravel Breeze**: Base authentication system
- **Spatie Permission**: Role-based access control (Admin, Manager, Agent, Viewer)
- **Middleware**: Custom security middleware for rate limiting and feature access
- **Admin Protection**: Super admin and admin role protection with audit trails

### Communication Channels
- **Email**: Multi-SMTP support with campaign tracking and analytics
- **SMS**: Twilio and Vonage integration with delivery tracking
- **WhatsApp**: Custom server integration with QR authentication and media support

### Development Notes
- **PHP 8.1+** required
- **Laravel 10** framework
- **Redis** for caching and queue management
- **MySQL** database with comprehensive migrations
- **Node.js 18+** for WhatsApp server
- **Tailwind CSS** + **Alpine.js** for frontend

### Important Files
- `routes/web.php`: Main application routes
- `routes/api.php`: API routes for webhooks and AJAX endpoints  
- `config/horizon.php`: Queue worker configuration
- `whatsapp-server/server.js`: WhatsApp Web.js server implementation
- `database/seeders/`: Comprehensive test data for development