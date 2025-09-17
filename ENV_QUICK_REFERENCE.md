# 🎯 CRM Ultra - .env Configuration Summary

## 📋 VARIABILE .env - GHID RAPID

### 🔑 **UNDE GĂSEȘTI FIECARE VARIABILĂ:**

---

## 🚀 **CONFIGURARE DE BAZĂ**
```env
APP_URL=https://your-domain.com        # Domeniul tău (ex: https://crm.company.com)
APP_ENV=production                     # production/local  
APP_DEBUG=false                        # false pentru producție
```
**Source**: Domeniul unde hostezi aplicația

---

## 🗄️ **DATABASE** (OBLIGATORIU)
```env  
DB_HOST=127.0.0.1                      # IP serverului MySQL
DB_DATABASE=crm_ultra                  # Numele bazei de date
DB_USERNAME=your_username              # Username MySQL  
DB_PASSWORD=your_password              # Parola MySQL
```
**Source**: 
- **cPanel**: MySQL Databases section
- **VPS**: Configurația MySQL locală
- **Cloud**: AWS RDS, DigitalOcean Databases, etc.

---

## 📧 **EMAIL SMTP** (OBLIGATORIU)
```env
MAIL_HOST=smtp.gmail.com               # Gmail, Outlook, etc.
MAIL_USERNAME=your-email@gmail.com     # Email-ul de trimitere
MAIL_PASSWORD=your-app-password        # App Password pentru Gmail
MAIL_FROM_ADDRESS="noreply@your-domain.com"  # Email sender
```
**Source**: 
- **Gmail**: [App Passwords](https://support.google.com/accounts/answer/185833)
- **Outlook**: Account settings → Security → App passwords
- **Custom SMTP**: Hosting provider documentation

---

## 🔗 **GOOGLE OAUTH** (Pentru Gmail/Sheets)
```env
GOOGLE_CLIENT_ID=xxx.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxx
```
**Source**: [Google Cloud Console](https://console.cloud.google.com/)
1. Create project → APIs & Services → Library
2. Enable: Gmail API + Google Sheets API  
3. Credentials → Create → OAuth 2.0 Client ID
4. Add redirect URI: `https://your-domain.com/google-sheets/callback`

---

## 📱 **SMS - TWILIO**
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```
**Source**: [Twilio Console](https://console.twilio.com/)
- Dashboard → Account SID & Auth Token
- Phone Numbers → Active numbers

---

## 📱 **SMS - VONAGE** (Alternativă)
```env
VONAGE_KEY=your_api_key
VONAGE_SECRET=your_api_secret
```
**Source**: [Vonage Dashboard](https://dashboard.nexmo.com/)
- API Key & Secret (afișate în dashboard)

---

## 💬 **WHATSAPP INTEGRATION**
```env
WHATSAPP_SERVER_URL=https://your-domain.com:3001
WHATSAPP_API_TOKEN=your-secure-token
WHATSAPP_WEBHOOK_SECRET=your-webhook-secret  
```
**Source**: Generat manual
```bash
# Generare tokens securizați
openssl rand -hex 32  # pentru API_TOKEN
openssl rand -hex 32  # pentru WEBHOOK_SECRET
```

---

## ⚡ **REDIS** (Opțional dar recomandat)
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```
**Source**: 
- **Local**: Instalare Redis pe server
- **Cloud**: Redis Cloud, AWS ElastiCache
- **Fără Redis**: Folosește `file` în loc de `redis`

---

## 🔄 **BACKGROUND JOBS**
```env
QUEUE_CONNECTION=database              # database/redis/sync
```
**Options**:
- `database`: Folosește MySQL (recomandat)
- `redis`: Folosește Redis (mai rapid)
- `sync`: Execută imediat (doar development)

---

## 🌍 **EXEMPLE REALE DE CONFIGURĂRI**

### 🏢 **Hosting Shared (cPanel)**
```env
APP_URL=https://crm.yoursite.com
DB_HOST=localhost
DB_DATABASE=cpanel_crmultra
DB_USERNAME=cpanel_user  
DB_PASSWORD=cpanel_password
MAIL_HOST=mail.yoursite.com
MAIL_USERNAME=noreply@yoursite.com
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### ☁️ **VPS/Cloud Server**
```env
APP_URL=https://crm.company.com
DB_HOST=127.0.0.1
DB_DATABASE=crm_ultra
DB_USERNAME=crmuser
DB_PASSWORD=secure_password
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=company@gmail.com
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 🚀 **AWS/DigitalOcean**
```env
APP_URL=https://crm.company.com
DB_HOST=mysql-db.internal.company.com
DB_DATABASE=crm_production
DB_USERNAME=app_user
DB_PASSWORD=complex_password_123
MAIL_HOST=smtp.ses.us-east-1.amazonaws.com
CACHE_DRIVER=redis
REDIS_HOST=redis.internal.company.com
```

---

## 📋 **CHECKLIST CONFIGURARE**

### ✅ **Minim Funcțional:**
- [ ] APP_URL configurat cu domeniul real
- [ ] Database connection (DB_*)
- [ ] SMTP email (MAIL_*)
- [ ] APP_KEY generat

### 🔧 **Pentru Funcționalitate Completă:**
- [ ] Google OAuth (GOOGLE_CLIENT_ID/SECRET)
- [ ] SMS Provider (Twilio sau Vonage)  
- [ ] WhatsApp server (WHATSAPP_*)
- [ ] Redis pentru performance (REDIS_*)

### 🚀 **Pentru Producție:**
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] QUEUE_CONNECTION=database sau redis
- [ ] SSL certificate activ
- [ ] Backup configurat

---

## 🆘 **TROUBLESHOOTING RAPID**

### Database Connection Failed
```bash
# Testează conexiunea
php artisan tinker
DB::connection()->getPdo();
```
**Fix**: Verifică DB_HOST, DB_USERNAME, DB_PASSWORD

### Email Not Sending  
```bash
# Test email
php artisan tinker
Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });
```
**Fix**: 
- Gmail: Folosește App Password, nu parola normală
- 2FA: Activează 2-Factor Authentication în Google

### Google OAuth Error
**Error**: `redirect_uri_mismatch`
**Fix**: În Google Console, adaugă exact: `https://your-domain.com/google-sheets/callback`

### WhatsApp Not Connecting
**Fix**: 
- Verifică că portul 3001 este deschis
- Configurează aceleași tokens în `whatsapp-server/.env`

---

## 🔧 **COMENZI UTILE**

```bash
# Verifică configurația completă
php check_config.php

# Test database
php artisan migrate:status

# Generare APP_KEY nouă
php artisan key:generate

# Clear cache după modificări .env
php artisan config:clear
php artisan cache:clear

# Pentru producție
php artisan config:cache
php artisan route:cache
```

---

## 🎯 **CONTACT PROVIDERI**

### 📧 **Email SMTP**
- **Gmail**: Free cu limite, App Password necesar
- **SendGrid**: $14.95/lună pentru 40k emails
- **Mailgun**: $35/lună pentru 50k emails
- **AWS SES**: $0.10 per 1k emails

### 📱 **SMS Providers**  
- **Twilio**: $1/lună + $0.0075/SMS
- **Vonage**: €2/lună + €0.05/SMS
- **ClickSend**: $0.025/SMS pentru România

### ☁️ **Hosting Recomandat**
- **DigitalOcean**: $12/lună VPS + App Platform
- **AWS Lightsail**: $10/lună cu database
- **Cloudways**: $14/lună managed Laravel
- **Forge + Linode**: $15/lună pentru management

---

**📚 Pentru ghid complet**: `ENV_CONFIGURATION_GUIDE.md`  
**🚀 Pentru instalare**: `./master_install.sh`
