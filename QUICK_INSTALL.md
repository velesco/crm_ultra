# 🚀 CRM Ultra - Instalare Rapidă

## 📟 Cerințe Minimum
- PHP 8.1+
- MySQL 8.0+
- Composer 2.x
- Node.js 18.x+
- Apache/Nginx cu SSL

## ⚡ Instalare în 1 Pas (Recomandat)

### 🎆 **Master Installer**
```bash
./master_install.sh
```
**Ce face**: Installer complet cu wizard interactiv (10-15 min)

## ⚡ Instalare în 4 Pași (Manual)

### 1️⃣ Verificare Sistem
```bash
./check_installation.sh
```
Verifică dacă toate cerințele sunt întrunite.

### 2️⃣ Instalare Automată
```bash
./install.sh
```
Script interactiv care instalează și configurează tot ce e necesar.

### 3️⃣ Configurare .env
```bash
./configure_env.sh
# SAU
./setup_services.sh  # pentru servicii externe
```
Configurează toate variabilele necesare.

### 4️⃣ Verificare Finală
```bash
php check_config.php
```
Testează toate configurările și conexiunile.

## 📚 Documentație Completă

### 📄 **Ghiduri de Instalare**
- **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)** - Ghid complet detaliat
- **[ENV_CONFIGURATION_GUIDE.md](ENV_CONFIGURATION_GUIDE.md)** - Configurare .env pas cu pas
- **[ENV_QUICK_REFERENCE.md](ENV_QUICK_REFERENCE.md)** - Reference rapid .env

### 🤖 **Scripturi Disponibile**
- **master_install.sh** - 🎆 Installer complet cu wizard
- **check_installation.sh** - 🔍 Verificare cerințe sistem  
- **install.sh** - 🛠️ Instalare dependințe și setup de bază
- **configure_env.sh** - 🔧 Wizard configurare .env
- **setup_services.sh** - ⚙️ Configurare servicii externe
- **check_config.php** - 📊 Verificare configurări Laravel
- **cleanup_project.sh** - 🧹 Curățare fișiere temporare
- **auto_cleanup.sh** - 🔍 Detecție automată cleanup

## 🆘 Ajutor Rapid

### Probleme Frecvente
```bash
# Permisiuni
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Cache clear
php artisan config:clear
php artisan cache:clear

# Restart queue
php artisan queue:restart
```

### Servicii Externe Necesare
- **Google OAuth**: Gmail + Google Sheets integration
- **Twilio/Vonage**: SMS functionality  
- **WhatsApp Server**: WhatsApp integration (opțional)

## 🎯 După Instalare

1. **Configurează Web Server** să pointeze la `public/`
2. **Pornește Queue Worker**: `php artisan queue:work`  
3. **Accesează aplicația**: `https://your-domain.com`
4. **Creează primul admin** din interfața web

## 🧹 Curățare Proiect

Pentru a curăța fișierele temporare de dezvoltare:

```bash
# Detecție automată fișiere temporare
./auto_cleanup.sh

# Sau cleanup manual cu listă predefinită
./cleanup_project.sh
```

**Ce se șterge**: Documentația de debug, scripturi de testare, directoare auxiliare  
**Ce se păstrează**: Aplicația Laravel, documentația importantă, scripturile de instalare

## 📊 Status Proiect
**100% Production Ready** ✅  
**Toate funcționalitățile implementate** ✅  
**Gmail Integration completă** ✅  
**Zero runtime errors** ✅

---
**CRM Ultra** - Professional Customer Relationship Management System 🚀
