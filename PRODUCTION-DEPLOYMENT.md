# 🚀 CRM Ultra - Production Deployment Guide
## For ultra-crm.aipro.ro

### 📋 Pre-deployment Checklist

#### 1. Server Requirements
- ✅ Node.js 18+ installed
- ✅ PM2 process manager
- ✅ Nginx web server
- ✅ SSL certificate configured
- ✅ Port 3001 available for WhatsApp server

#### 2. Domain Configuration
- **Main Domain**: ultra-crm.aipro.ro (Laravel app)
- **WhatsApp Server**: ultra-crm.aipro.ro:3001 (Node.js server)
- **SSL**: Required for WhatsApp Web.js in production

### 🔧 Deployment Steps

#### Step 1: Upload WhatsApp Server Files
```bash
# Upload the whatsapp-server directory to your server
scp -r whatsapp-server/ user@ultra-crm.aipro.ro:/path/to/crm_ultra/

# SSH into server
ssh user@ultra-crm.aipro.ro
cd /path/to/crm_ultra/whatsapp-server
```

#### Step 2: Install Dependencies
```bash
# Install Node.js dependencies
npm install --production

# Install PM2 globally (if not installed)
npm install -g pm2

# Create required directories
mkdir -p sessions logs uploads
```

#### Step 3: Configure Environment
```bash
# Copy production environment file
cp .env.example .env

# Edit with production values
nano .env
```

**Important Environment Variables:**
```bash
PORT=3001
NODE_ENV=production
API_HOST=https://ultra-crm.aipro.ro
LARAVEL_API_URL=https://ultra-crm.aipro.ro/api
LARAVEL_API_TOKEN=your-secure-production-token
WEBHOOK_SECRET=your-secure-production-webhook-secret
CORS_ORIGIN=https://ultra-crm.aipro.ro
```

#### Step 4: Update Laravel Configuration
```bash
# Edit Laravel .env file
nano /path/to/crm_ultra/.env

# Add these lines:
WHATSAPP_SERVER_URL=https://ultra-crm.aipro.ro:3001
WHATSAPP_API_TOKEN=your-secure-production-token
WHATSAPP_WEBHOOK_SECRET=your-secure-production-webhook-secret
```

#### Step 5: Start WhatsApp Server
```bash
# Start with PM2
npm run pm2:start

# Check status
pm2 list

# View logs
pm2 logs crm-ultra-whatsapp-server

# Save PM2 configuration for auto-restart
pm2 save
pm2 startup
```

### 🌐 Nginx Configuration

Add this to your Nginx configuration:

```nginx
# WhatsApp Server Proxy - Add to your existing ultra-crm.aipro.ro config
location /whatsapp-server/ {
    proxy_pass http://localhost:3001/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}

# Direct access to WhatsApp server (alternative)
server {
    listen 443 ssl;
    server_name ultra-crm.aipro.ro;
    
    # Your existing SSL configuration
    
    location /whatsapp/ {
        proxy_pass http://localhost:3001/;
        # ... same proxy settings as above
    }
}
```

**Or create a separate subdomain (recommended):**
```nginx
server {
    listen 443 ssl;
    server_name whatsapp.ultra-crm.aipro.ro;
    
    ssl_certificate /path/to/certificate;
    ssl_certificate_key /path/to/private-key;
    
    location / {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

#### Reload Nginx
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 🔐 Firewall Configuration

```bash
# Allow port 3001 for WhatsApp server
sudo ufw allow 3001

# Or if using specific IP restrictions
sudo ufw allow from your.server.ip to any port 3001
```

### 🧪 Testing Deployment

#### Test 1: Server Health Check
```bash
curl https://ultra-crm.aipro.ro:3001/health
# Should return: {"status":"OK","timestamp":"...","uptime":...,"sessions":0}
```

#### Test 2: Create Test Session
```bash
curl -X POST https://ultra-crm.aipro.ro:3001/sessions \
  -H "Content-Type: application/json" \
  -d '{"sessionId": "test-production"}'
```

#### Test 3: Laravel Integration
```bash
# Login to Laravel admin: https://ultra-crm.aipro.ro
# Navigate to WhatsApp section
# Try to create a new session
# Check if QR code appears
```

### 📊 Production Monitoring

#### PM2 Monitoring
```bash
# List processes
pm2 list

# Monitor in real-time
pm2 monit

# View logs
pm2 logs crm-ultra-whatsapp-server

# Restart if needed
pm2 restart crm-ultra-whatsapp-server
```

#### Log Files
```bash
# WhatsApp server logs
tail -f /path/to/crm_ultra/whatsapp-server/logs/combined.log
tail -f /path/to/crm_ultra/whatsapp-server/logs/error.log

# PM2 logs
tail -f ~/.pm2/logs/crm-ultra-whatsapp-server-out.log
tail -f ~/.pm2/logs/crm-ultra-whatsapp-server-error.log
```

### 🚨 Troubleshooting

#### Issue: Server won't start
```bash
# Check if port is available
sudo netstat -tlnp | grep :3001

# Check Node.js version
node --version  # Should be 18+

# Check dependencies
npm audit
```

#### Issue: SSL/HTTPS Problems
```bash
# Ensure SSL certificate is valid
openssl s_client -connect ultra-crm.aipro.ro:443

# Check Nginx configuration
sudo nginx -t
```

#### Issue: WhatsApp connection fails
```bash
# Clear sessions
rm -rf sessions/*

# Restart server
pm2 restart crm-ultra-whatsapp-server

# Check logs for errors
pm2 logs crm-ultra-whatsapp-server
```

### 🔄 Updates and Maintenance

#### Update WhatsApp Server
```bash
# Backup current version
cp -r whatsapp-server whatsapp-server-backup-$(date +%Y%m%d)

# Upload new files
# Update dependencies
npm install --production

# Restart server
pm2 restart crm-ultra-whatsapp-server
```

#### Regular Maintenance
```bash
# Clean old sessions (monthly)
find sessions/ -type f -mtime +30 -delete

# Rotate logs (weekly)
pm2 flush

# Monitor disk space
df -h
```

### ⚡ Performance Optimization

#### PM2 Clustering (for high traffic)
```javascript
// ecosystem.config.js
module.exports = {
  apps: [{
    name: 'crm-ultra-whatsapp-server',
    script: './server.js',
    instances: 2, // Or 'max' for all CPU cores
    exec_mode: 'cluster',
    max_memory_restart: '500M',
    // ... other settings
  }]
};
```

#### Server Resources
- **RAM**: Minimum 1GB per session (5-10 sessions = 8GB recommended)
- **CPU**: 2+ cores recommended
- **Disk**: 20GB+ for sessions and logs
- **Network**: Stable connection for WhatsApp Web

---

## 🎯 Final Production URLs

- **CRM Admin**: https://ultra-crm.aipro.ro
- **WhatsApp Server**: https://ultra-crm.aipro.ro:3001
- **Health Check**: https://ultra-crm.aipro.ro:3001/health
- **API Documentation**: https://ultra-crm.aipro.ro:3001 (shows available endpoints)

## ✅ Deployment Complete!

Your WhatsApp server is now ready for production use on ultra-crm.aipro.ro. Users can access the WhatsApp functionality through the Laravel admin panel, and the system will handle QR code authentication, message sending/receiving, and all WhatsApp integration seamlessly.

**🔐 Security Note**: Make sure to change the default API tokens in production and keep your SSL certificates up to date!