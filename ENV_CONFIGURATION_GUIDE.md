# 🔧 CRM Ultra - Ghid Completare Variabile .env

## 📋 Instrucțiuni pas cu pas pentru completarea .env

### 🚀 **1. CONFIGURAREA DE BAZĂ**

```env
APP_NAME="CRM Ultra"                    # Numele aplicației (poți schimba)
APP_ENV=production                      # production pentru live, local pentru dev
APP_KEY=base64:REPLACE_WITH_GENERATED_KEY  # Generat automat cu: php artisan key:generate
APP_DEBUG=false                         # true doar pentru development
APP_URL=https://your-domain.com         # ÎNLOCUIEȘTE cu domeniul tău real
APP_VERSION=1.0.0                       # Versiunea aplicației
```

**De unde:**
- `APP_URL`: Domeniul unde va fi hosted CRM-ul (ex: https://crm.company.com)
- `APP_KEY`: Se generează automat când rulezi `php artisan key:generate`

---

### 🗄️ **2. BAZA DE DATE (OBLIGATORIU)**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1                       # IP-ul serverului MySQL
DB_PORT=3306                            # Portul MySQL (default 3306)
DB_DATABASE=crm_ultra                   # Numele bazei de date
DB_USERNAME=your_username               # Username MySQL
DB_PASSWORD=your_password               # Parola MySQL
```

**De unde iei datele:**
- **Hosting Provider**: cPanel → MySQL Databases
- **VPS/Dedicated**: Din configurarea MySQL
- **Local**: XAMPP/MAMP/Laragon settings

**Exemple populare:**
- **cPanel**: Host: `localhost`, User: `cpanel_user`, DB: `cpanel_crmultra`
- **AWS RDS**: Host: `mydb.xyz.rds.amazonaws.com`, Port: `3306`
- **DigitalOcean**: Host: `db-mysql-fra1-12345-do-user-8901234-0.b.db.ondigitalocean.com`

---

### 📧 **3. EMAIL / SMTP (OBLIGATORIU)**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com                # SMTP server
MAIL_PORT=587                           # Port SMTP
MAIL_USERNAME=your-email@gmail.com      # Email-ul de trimitere  
MAIL_PASSWORD=your-app-password         # Parola sau App Password
MAIL_ENCRYPTION=tls                     # tls sau ssl
MAIL_FROM_ADDRESS="noreply@your-domain.com"  # Email-ul afișat ca sender
MAIL_FROM_NAME="${APP_NAME}"            # Numele afișat ca sender
```

**Pentru Gmail:**
1. Mergi la [Google Account Settings](https://myaccount.google.com/)
2. Security → 2-Step Verification (activează)
3. Security → App passwords → Generate pentru "Mail"
4. Folosește parola generată la `MAIL_PASSWORD`

**Pentru alte providere:**
- **Outlook**: `smtp-mail.outlook.com:587` (TLS)
- **Yahoo**: `smtp.mail.yahoo.com:587` (TLS)  
- **Custom SMTP**: Întreabă hosting provider-ul pentru detalii

---

### 🔗 **4. GOOGLE OAUTH (Pentru Gmail & Sheets)**

```env
GOOGLE_CLIENT_ID=your-google-client-id.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/google-sheets/callback"
```

**Unde obții:**
1. Mergi la [Google Cloud Console](https://console.cloud.google.com/)
2. **Create Project** sau selectează unul existent
3. **APIs & Services** → **Library**
4. Activează: **Gmail API** și **Google Sheets API**
5. **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth 2.0 Client IDs**
6. **Application type**: Web application
7. **Authorized redirect URIs**: Adaugă `https://your-domain.com/google-sheets/callback`
8. **Download JSON** și extrage `client_id` și `client_secret`

---

### 📱 **5. SMS CU TWILIO**

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

**Unde obții:**
1. Creează cont la [Twilio](https://www.twilio.com/)
2. **Console Dashboard** → Account SID (copiază)
3. **Console Dashboard** → Auth Token (click pe Show, copiază)
4. **Phone Numbers** → **Manage** → **Active numbers** → copiază numărul

**Cost aproximativ**: $1/lună pentru număr + $0.0075/SMS

---

### 📱 **6. SMS CU VONAGE (Alternativă)**

```env
VONAGE_KEY=your_api_key
VONAGE_SECRET=your_api_secret  
VONAGE_FROM_NUMBER=your_number
```

**Unde obții:**
1. Creează cont la [Vonage](https://dashboard.nexmo.com/) (ex-Nexmo)
2. **Dashboard** → API Key și API Secret (sunt afișate direct)
3. **Numbers** → **Buy numbers** pentru un număr dedicated

---

### 💬 **7. WHATSAPP INTEGRATION**

```env
WHATSAPP_SERVER_URL=https://your-domain.com:3001
WHATSAPP_API_TOKEN=your-secure-api-token
WHATSAPP_WEBHOOK_SECRET=your-secure-webhook-secret
WHATSAPP_TIMEOUT=30
```

**Ce trebuie să faci:**
1. **Server URL**: Același domeniu ca CRM-ul, port 3001
2. **API Token**: Generează un token securizat (ex: `openssl rand -hex 32`)
3. **Webhook Secret**: Alt token securizat pentru webhook
4. Configurează același token în `whatsapp-server/.env`

**Exemplu generare tokens:**
```bash
# API Token
openssl rand -hex 32
# Output: a1b2c3d4e5f6...

# Webhook Secret  
openssl rand -hex 32
# Output: x1y2z3w4v5u6...
```

---

### ⚡ **8. REDIS (Opțional dar Recomandat)**

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Instalare Redis:**
- **Ubuntu**: `sudo apt install redis-server`
- **CentOS**: `sudo yum install redis`  
- **Hosting**: Mulți provideri oferă Redis ca add-on
- **Fără Redis**: Poți folosi `CACHE_DRIVER=file` și `SESSION_DRIVER=file`

---

### 🔄 **9. QUEUE & BACKGROUND JOBS**

```env
QUEUE_CONNECTION=database
```

**Opțiuni:**
- `database` - Folosește MySQL (recomandat pentru început)
- `redis` - Folosește Redis (mai rapid, necesită Redis)
- `sync` - Execută imediat (doar pentru development)

**Pentru producție** rulează: `php artisan queue:work --daemon`

---

### 🎛️ **10. BROADCASTING (Real-time)**

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=crm-ultra-app
PUSHER_APP_KEY=crm-ultra-key  
PUSHER_APP_SECRET=crm-ultra-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

**Pentru development**: Valorile default merg  
**Pentru producție**: Poți folosi servicii ca Pusher.com sau Ably

---

## 🚀 **CONFIGURARE RAPIDĂ MINIMALĂ**

Pentru a porni CRM-ul rapid, ai nevoie **OBLIGATORIU** de:

### ✅ **Minim Necesar:**
```env
APP_URL=https://your-domain.com
DB_HOST=your-mysql-host
DB_DATABASE=crm_ultra
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

### 🔧 **Pentru Funcționalitate Completă:**
- **Google OAuth** (pentru Gmail integration)
- **Twilio sau Vonage** (pentru SMS)  
- **WhatsApp Server** (pentru WhatsApp)
- **Redis** (pentru performanță)

---

## 📋 **CHECKLIST CONFIGURARE**

- [ ] Database connection funcționează
- [ ] Email sending funcționează  
- [ ] Google OAuth configurat
- [ ] SMS provider configurat
- [ ] WhatsApp server pornit
- [ ] Queue worker pornit
- [ ] Redis instalat și configurat
- [ ] SSL certificate activ
- [ ] Domain pointează la server

---

## 🆘 **AJUTOR RAPID**

### Test Configurare
```bash
# Test database
php artisan migrate:status

# Test email
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Verificare completă  
php check_config.php
```

### Probleme Frecvente
- **Database connection failed**: Verifică host, user, password
- **SMTP auth failed**: Pentru Gmail folosește App Password, nu parola normală  
- **Google OAuth redirect mismatch**: Verifică redirect URI în Google Console
- **WhatsApp not connecting**: Verifică că portul 3001 este deschis

---

## 📞 **CONTACTE UTILE**

**Hosting cu suport Laravel:**
- DigitalOcean App Platform
- AWS Lightsail  
- Cloudways
- Forge + DigitalOcean/Linode

**SMS Provideri România:**
- Twilio (internațional)
- Vonage (ex-Nexmo)
- Orange Business Services
- Clickatell

Pentru întrebări specifice, consultă documentația fiecărui serviciu sau contactează support-ul lor.
