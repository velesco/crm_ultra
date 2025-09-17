# 🚀 CRM Ultra - Ghid de Instalare și Configurare

## 📋 Cerințe de Sistem

### Server Requirements
- **PHP**: 8.1 sau superior
- **Laravel**: 10.x
- **MySQL**: 8.0 sau superior
- **Node.js**: 18.x sau superior
- **Composer**: 2.x
- **Redis**: 6.x (opțional dar recomandat)

### Extensii PHP Necesare
```bash
php -m | grep -E "(curl|fileinfo|mbstring|openssl|PDO|Tokenizer|XML|ctype|json|bcmath|gd|zip)"
```

---

## 🛠️ Pași de Instalare

### 1. Clonarea Proiectului
```bash
git clone <repository-url> crm-ultra
cd crm-ultra
```

### 2. Instalarea Dependencies
```bash
# Composer dependencies
composer install --optimize-autoloader --no-dev

# NPM dependencies
npm install
npm run build
```

### 3. Configurarea Mediului

#### Copierea fișierului de configurare
```bash
cp .env.example .env
```

#### Generarea cheii aplicației
```bash
php artisan key:generate
```

---

## 🔧 Configurarea Variabilelor de Mediu (.env)

### 📊 **1. Configurarea Bazei de Date**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm_ultra
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 🌐 **2. Configurarea Aplicației**
```env
APP_NAME="CRM Ultra"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_VERSION=1.0.0
```

### 📧 **3. Configurarea Email-ului**
```env
# Configurare SMTP principală
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 📱 **4. Configurarea SMS (Twilio)**
```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

### 📱 **5. Configurarea SMS (Vonage - alternativ)**
```env
# Vonage Configuration
VONAGE_KEY=your_vonage_key
VONAGE_SECRET=your_vonage_secret
VONAGE_FROM_NUMBER=your_from_number
```

### 📱 **6. Configurarea SMS (Orange România - opțional)**
```env
# Orange SMS Configuration
ORANGE_API_KEY=your_orange_api_key
ORANGE_API_SECRET=your_orange_api_secret
ORANGE_FROM_NUMBER=your_orange_number
```

### 💬 **7. Configurarea WhatsApp**
```env
# WhatsApp Server Configuration
WHATSAPP_SERVER_URL=https://your-domain.com:3001
WHATSAPP_API_TOKEN=your-secure-api-token
WHATSAPP_WEBHOOK_SECRET=your-secure-webhook-secret
WHATSAPP_TIMEOUT=30
```

### 📊 **8. Configurarea Google Services (Gmail & Sheets)**
```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-google-client-id.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/google-sheets/callback"
```

**Pași pentru configurarea Google OAuth:**
1. Accesează [Google Cloud Console](https://console.cloud.google.com/)
2. Creează un proiect nou sau selectează unul existent
3. Activează Gmail API și Google Sheets API
4. Creează credentials (OAuth 2.0 Client ID)
5. Adaugă domeniile autorizate în "Authorized redirect URIs"

### 🔄 **9. Configurarea Queue și Broadcasting**
```env
# Queue Configuration
QUEUE_CONNECTION=database

# Broadcasting Configuration
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=crm-ultra-app
PUSHER_APP_KEY=crm-ultra-key
PUSHER_APP_SECRET=crm-ultra-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

### 🗂️ **10. Configurarea File Storage**
```env
# Local Storage
FILESYSTEM_DISK=local

# AWS S3 (opțional)
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket_name
```

### 🔒 **11. Configurarea Redis (recomandat pentru performanță)**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Pentru cache și sesiuni
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### ⚙️ **12. Configurări CRM Specifice**
```env
# CRM Settings
CRM_DEFAULT_TIMEZONE=Europe/Bucharest
CRM_DEFAULT_LANGUAGE=en
CRM_MAX_CONTACTS_PER_USER=10000
CRM_MAX_EMAILS_PER_HOUR=1000
CRM_MAX_SMS_PER_DAY=500
CRM_UPLOAD_MAX_SIZE=10240

# Security Settings
SECURITY_TWO_FACTOR_ENABLED=false
SECURITY_PASSWORD_RESET_TIMEOUT=60
SECURITY_SESSION_TIMEOUT=120

# Rate Limiting
RATE_LIMIT_API=60
RATE_LIMIT_EMAIL=100
RATE_LIMIT_SMS=50
RATE_LIMIT_WHATSAPP=200
```

### 💾 **13. Configurarea Backup**
```env
# Backup Configuration
BACKUP_ENABLED=true
BACKUP_SCHEDULE="0 2 * * *"
BACKUP_RETENTION_DAYS=30
```

---

## 🗄️ Configurarea Bazei de Date

### 1. Crearea Bazei de Date
```sql
CREATE DATABASE crm_ultra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON crm_ultra.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Rularea Migrărilor
```bash
# Rulează migrările
php artisan migrate

# Populează cu date inițiale
php artisan db:seed
```

### 3. Optimizarea pentru Producție
```bash
# Clear și cache configurări
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizare autoloader
composer dump-autoload --optimize
```

---

## 🔄 Configurarea Queue Workers

### 1. Supervisor Configuration
Creează fișierul `/etc/supervisor/conf.d/crm-ultra.conf`:
```ini
[program:crm-ultra-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/crm-ultra/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/path/to/crm-ultra
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/crm-ultra-worker.log
stopwaitsecs=3600
```

### 2. Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start crm-ultra-worker:*
```

---

## 🌐 Configurarea Web Server

### Apache Virtual Host
```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /path/to/crm-ultra/public
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory /path/to/crm-ultra/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/crm-ultra-error.log
    CustomLog ${APACHE_LOG_DIR}/crm-ultra-access.log combined
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/crm-ultra/public;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/private.key;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📱 Configurarea WhatsApp Server

### 1. Instalarea Node.js Server
```bash
# Navigează în directorul WhatsApp server
cd whatsapp-server

# Instalează dependencies
npm install

# Configurează variabilele de mediu
cp .env.example .env
```

### 2. Configurarea .env pentru WhatsApp Server
```env
PORT=3001
API_TOKEN=your-secure-api-token
WEBHOOK_SECRET=your-secure-webhook-secret
WEBHOOK_URL=https://your-domain.com/webhook/whatsapp
```

### 3. Start WhatsApp Server
```bash
# Development
npm run dev

# Production cu PM2
npm install -g pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

---

## 🔍 Verificarea Instalării

### 1. Verificarea Sistem
```bash
php artisan app:check-system
```

### 2. Test Email
```bash
php artisan app:test-email your-email@domain.com
```

### 3. Test SMS
```bash
php artisan app:test-sms +40123456789
```

### 4. Test WhatsApp
```bash
php artisan app:test-whatsapp
```

### 5. Test Gmail Integration
```bash
php artisan gmail:test-connection
```

---

## 📊 Monitorizarea și Mentenanța

### Loguri Importante
```bash
# Loguri Laravel
tail -f storage/logs/laravel.log

# Loguri Queue Worker
tail -f /var/log/crm-ultra-worker.log

# Loguri Web Server
tail -f /var/log/nginx/crm-ultra-error.log
```

### Comenzi Utile de Mentenanță
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizare
php artisan optimize
php artisan queue:restart

# Update database
php artisan migrate --force
```

---

## 🚨 Troubleshooting

### Probleme Comune

1. **Eroare "Permission denied"**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Queue nu procesează joburi**
   ```bash
   php artisan queue:restart
   supervisorctl restart crm-ultra-worker:*
   ```

3. **Gmail OAuth nu funcționează**
   - Verifică dacă domeniile sunt adăugate în Google Console
   - Verifică GOOGLE_CLIENT_ID și GOOGLE_CLIENT_SECRET

4. **WhatsApp nu se conectează**
   - Verifică dacă serverul Node.js rulează pe portul corect
   - Verifică firewall-ul pentru portul 3001

---

## 🎯 Configurarea Finală

### 1. Crearea Administratorului
```bash
php artisan app:create-admin
```

### 2. Import Date Inițiale
```bash
php artisan db:seed --class=InitialDataSeeder
```

### 3. Configurarea Cron Jobs
Adaugă în crontab (`crontab -e`):
```bash
* * * * * cd /path/to/crm-ultra && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ Checklist Final

- [ ] Baza de date configurată și migrată
- [ ] Variabilele .env completate
- [ ] Google OAuth configurat
- [ ] SMTP/Email funcțional
- [ ] SMS provider configurat (Twilio/Vonage)
- [ ] WhatsApp server pornit
- [ ] Queue workers activi
- [ ] Web server configurat cu SSL
- [ ] Cron jobs configurate
- [ ] Backup configurat
- [ ] Loguri monitorizabile
- [ ] Cont administrator creat

---

## 🎉 Gata de Utilizare!

CRM Ultra este acum complet instalat și configurat pentru producție! 

**URL de acces:** https://your-domain.com  
**Login:** Admin panel → Users → Create Admin

Pentru suport tehnic sau întrebări, consultă documentația din `TODO.md` sau contactează echipa de dezvoltare.
