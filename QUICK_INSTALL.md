# 🚀 CRM Ultra - Instalare Rapidă

## 📋 Cerințe Minimum
- PHP 8.1+
- MySQL 8.0+
- Composer 2.x
- Node.js 18.x+
- Apache/Nginx cu SSL

## ⚡ Instalare în 3 Pași

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

### 3️⃣ Verificare Finală
```bash
php check_config.php
```
Testează toate configurările și conexiunile.

## 📚 Documentație Completă
Pentru ghid detaliat: **[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)**

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

## 📊 Status Proiect
**100% Production Ready** ✅  
**Toate funcționalitățile implementate** ✅  
**Gmail Integration completă** ✅  
**Zero runtime errors** ✅

---
**CRM Ultra** - Professional Customer Relationship Management System 🚀
