# 🚀 CRM Ultra - Quick Start Guide

## 📱 WhatsApp Integration Setup

### 1. First Time Setup
```bash
# Complete project setup
./setup.sh

# Or manual WhatsApp server setup
cd whatsapp-server
./setup.sh
```

### 2. Start Development Environment
```bash
# Terminal 1 - Laravel Application
php artisan serve
# Access: http://localhost:8000

# Terminal 2 - WhatsApp Server
cd whatsapp-server
npm run dev
# Access: http://localhost:3001

# Terminal 3 - Queue Processing (Optional)
php artisan horizon
```

### 3. WhatsApp Connection Process

1. **Login to CRM**: http://localhost:8000
   - Email: `admin@crmultra.com`
   - Password: `Admin123!`

2. **Go to WhatsApp Section**: Navigate to WhatsApp in the menu

3. **Create New Session**: Click "Create Session" button

4. **Scan QR Code**: Use your phone's WhatsApp to scan the QR code

5. **Start Messaging**: Once connected, you can send messages to contacts

### 4. Testing WhatsApp Server
```bash
# Test server functionality
cd whatsapp-server
./test.sh

# Check health status
curl http://localhost:3001/health

# View logs
tail -f logs/combined.log
```

### 5. Common Issues & Solutions

#### Server Won't Start
```bash
# Check if port 3001 is available
lsof -i :3001

# Kill process if needed
kill -9 $(lsof -t -i:3001)

# Restart server
npm run dev
```

#### QR Code Not Appearing
```bash
# Clear sessions folder
rm -rf sessions/*

# Restart server
npm run dev

# Create new session in Laravel admin
```

#### Connection Lost
- The server automatically attempts to reconnect
- Check logs: `tail -f logs/combined.log`
- If needed, restart session from Laravel admin

### 6. Production Deployment

#### Using PM2
```bash
cd whatsapp-server
npm run pm2:start

# Monitor
pm2 list
pm2 logs crm-ultra-whatsapp-server
pm2 monit
```

#### Using Docker
```bash
# Build Docker image
docker build -t crm-ultra-whatsapp-server whatsapp-server/

# Run container
docker run -d -p 3001:3001 \
  -v $(pwd)/whatsapp-server/sessions:/app/sessions \
  crm-ultra-whatsapp-server
```

### 7. Environment Configuration

#### Laravel (.env)
```bash
WHATSAPP_SERVER_URL=http://localhost:3001
WHATSAPP_API_TOKEN=your-secure-token
WHATSAPP_WEBHOOK_SECRET=your-webhook-secret
```

#### WhatsApp Server (.env)
```bash
PORT=3001
LARAVEL_API_URL=http://localhost:8000/api
LARAVEL_API_TOKEN=your-secure-token
WEBHOOK_SECRET=your-webhook-secret
```

### 8. API Usage Examples

#### Create Session
```bash
curl -X POST http://localhost:3001/sessions \
  -H "Content-Type: application/json" \
  -d '{"sessionId": "my-session"}'
```

#### Send Message
```bash
curl -X POST http://localhost:3001/sessions/my-session/send \
  -H "Content-Type: application/json" \
  -d '{
    "to": "1234567890@c.us",
    "message": "Hello from CRM Ultra!"
  }'
```

#### Get Session Status
```bash
curl http://localhost:3001/sessions/my-session
```

### 9. Monitoring & Logs

#### Server Logs
```bash
# All logs
tail -f whatsapp-server/logs/combined.log

# Error logs only
tail -f whatsapp-server/logs/error.log

# PM2 logs
pm2 logs crm-ultra-whatsapp-server
```

#### Health Monitoring
```bash
# Server health
curl http://localhost:3001/health

# Session count
curl http://localhost:3001/sessions | jq '.sessions | length'
```

### 10. Security Considerations

- Change default API tokens in production
- Use HTTPS in production environment
- Configure firewall rules for port 3001
- Regular backup of sessions folder
- Monitor logs for suspicious activity

---

## 📞 Support

For issues or questions:
1. Check logs first: `tail -f whatsapp-server/logs/combined.log`
2. Test server health: `curl http://localhost:3001/health`
3. Review Laravel logs: `tail -f storage/logs/laravel.log`
4. Refer to full documentation in `whatsapp-server/README.md`

🎉 **Happy messaging with CRM Ultra!**