const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const http = require('http');
const socketIo = require('socket.io');
const axios = require('axios');
const QRCode = require('qrcode');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { v4: uuidv4 } = require('uuid');
const cron = require('node-cron');
const winston = require('winston');
require('dotenv').config();

// Initialize Express app
const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: process.env.CORS_ORIGIN || "http://localhost:8000",
        methods: ["GET", "POST"]
    }
});

// Logger configuration
const logger = winston.createLogger({
    level: process.env.LOG_LEVEL || 'info',
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.errors({ stack: true }),
        winston.format.json()
    ),
    defaultMeta: { service: 'whatsapp-server' },
    transports: [
        new winston.transports.File({ filename: './logs/error.log', level: 'error' }),
        new winston.transports.File({ filename: './logs/combined.log' }),
        new winston.transports.Console({
            format: winston.format.combine(
                winston.format.colorize(),
                winston.format.simple()
            )
        })
    ]
});

// Ensure logs directory exists
if (!fs.existsSync('./logs')) {
    fs.mkdirSync('./logs');
}

// Middleware
app.use(helmet());
app.use(cors({
    origin: process.env.CORS_ORIGIN || "http://localhost:8000",
    credentials: true
}));
app.use(morgan('combined'));
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// File upload configuration
const storage = multer.diskStorage({
    destination: (req, file, cb) => {
        const uploadDir = './uploads';
        if (!fs.existsSync(uploadDir)) {
            fs.mkdirSync(uploadDir);
        }
        cb(null, uploadDir);
    },
    filename: (req, file, cb) => {
        cb(null, `${uuidv4()}-${file.originalname}`);
    }
});
const upload = multer({ storage });

// Global variables
const clients = new Map();
const clientStates = new Map();

// WhatsApp Client Manager Class
class WhatsAppClientManager {
    constructor(sessionId) {
        this.sessionId = sessionId;
        this.client = null;
        this.qrCode = null;
        this.isReady = false;
        this.lastActivity = new Date();
        this.retryAttempts = 0;
        this.maxRetries = parseInt(process.env.MAX_RETRY_ATTEMPTS) || 3;
        this.initialize();
    }

    initialize() {
        try {
            this.client = new Client({
                authStrategy: new LocalAuth({
                    clientId: this.sessionId,
                    dataPath: process.env.WHATSAPP_SESSION_DIR || './sessions'
                }),
                puppeteer: {
                    headless: true,
                    args: [
                        '--no-sandbox',
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-accelerated-2d-canvas',
                        '--no-first-run',
                        '--no-zygote',
                        '--single-process',
                        '--disable-gpu'
                    ]
                }
            });

            this.setupEventListeners();
            this.client.initialize();
            
            logger.info(`WhatsApp client initialized for session: ${this.sessionId}`);
        } catch (error) {
            logger.error(`Error initializing WhatsApp client for session ${this.sessionId}:`, error);
            this.handleError(error);
        }
    }

    setupEventListeners() {
        // QR Code generation
        this.client.on('qr', (qr) => {
            logger.info(`QR code generated for session: ${this.sessionId}`);
            QRCode.toDataURL(qr, (err, url) => {
                if (err) {
                    logger.error(`Error generating QR code for session ${this.sessionId}:`, err);
                    return;
                }
                this.qrCode = url;
                clientStates.set(this.sessionId, {
                    status: 'qr_code',
                    qrCode: url,
                    timestamp: new Date()
                });
                
                // Emit QR code to connected clients
                io.emit('qr_code', { sessionId: this.sessionId, qrCode: url });
                
                // Notify Laravel backend
                this.notifyLaravel('qr_generated', { qr_code: url });
            });
        });

        // Client ready
        this.client.on('ready', () => {
            logger.info(`WhatsApp client ready for session: ${this.sessionId}`);
            this.isReady = true;
            this.retryAttempts = 0;
            this.lastActivity = new Date();
            
            clientStates.set(this.sessionId, {
                status: 'ready',
                timestamp: new Date()
            });
            
            // Emit ready status
            io.emit('client_ready', { sessionId: this.sessionId });
            
            // Notify Laravel backend
            this.notifyLaravel('ready', { 
                session_id: this.sessionId,
                status: 'connected'
            });
        });

        // Authentication
        this.client.on('authenticated', () => {
            logger.info(`WhatsApp client authenticated for session: ${this.sessionId}`);
            clientStates.set(this.sessionId, {
                status: 'authenticated',
                timestamp: new Date()
            });
            
            io.emit('client_authenticated', { sessionId: this.sessionId });
        });

        // Authentication failure
        this.client.on('auth_failure', (msg) => {
            logger.error(`Authentication failed for session ${this.sessionId}:`, msg);
            this.handleError(new Error(`Authentication failed: ${msg}`));
        });

        // Disconnection
        this.client.on('disconnected', (reason) => {
            logger.warn(`WhatsApp client disconnected for session ${this.sessionId}:`, reason);
            this.isReady = false;
            
            clientStates.set(this.sessionId, {
                status: 'disconnected',
                reason: reason,
                timestamp: new Date()
            });
            
            io.emit('client_disconnected', { sessionId: this.sessionId, reason });
            
            // Notify Laravel backend
            this.notifyLaravel('disconnected', { 
                session_id: this.sessionId,
                status: 'disconnected',
                reason: reason
            });

            // Attempt to reconnect
            this.attemptReconnect();
        });

        // Message received
        this.client.on('message', async (message) => {
            try {
                logger.info(`Message received for session ${this.sessionId} from ${message.from}`);
                this.lastActivity = new Date();

                const messageData = {
                    id: message.id._serialized,
                    session_id: this.sessionId,
                    from: message.from,
                    to: message.to,
                    body: message.body,
                    type: message.type,
                    timestamp: message.timestamp,
                    hasMedia: message.hasMedia
                };

                // Handle media messages
                if (message.hasMedia) {
                    try {
                        const media = await message.downloadMedia();
                        messageData.media = {
                            mimetype: media.mimetype,
                            data: media.data,
                            filename: media.filename
                        };
                    } catch (mediaError) {
                        logger.error(`Error downloading media for message ${message.id._serialized}:`, mediaError);
                    }
                }

                // Emit message to connected clients
                io.emit('message_received', messageData);

                // Notify Laravel backend
                this.notifyLaravel('message_received', messageData);

            } catch (error) {
                logger.error(`Error processing received message for session ${this.sessionId}:`, error);
            }
        });

        // Message sent
        this.client.on('message_create', (message) => {
            if (message.fromMe) {
                logger.info(`Message sent from session ${this.sessionId} to ${message.to}`);
                this.lastActivity = new Date();

                const messageData = {
                    id: message.id._serialized,
                    session_id: this.sessionId,
                    from: message.from,
                    to: message.to,
                    body: message.body,
                    type: message.type,
                    timestamp: message.timestamp,
                    fromMe: true
                };

                io.emit('message_sent', messageData);
                this.notifyLaravel('message_sent', messageData);
            }
        });

        // Group join
        this.client.on('group_join', (notification) => {
            logger.info(`Group join notification for session ${this.sessionId}`);
            io.emit('group_join', { sessionId: this.sessionId, notification });
        });

        // Group leave
        this.client.on('group_leave', (notification) => {
            logger.info(`Group leave notification for session ${this.sessionId}`);
            io.emit('group_leave', { sessionId: this.sessionId, notification });
        });
    }

    async attemptReconnect() {
        if (this.retryAttempts < this.maxRetries) {
            this.retryAttempts++;
            const delay = Math.pow(2, this.retryAttempts) * 1000; // Exponential backoff
            
            logger.info(`Attempting to reconnect session ${this.sessionId} (attempt ${this.retryAttempts}/${this.maxRetries}) in ${delay}ms`);
            
            setTimeout(() => {
                this.initialize();
            }, delay);
        } else {
            logger.error(`Maximum reconnection attempts reached for session ${this.sessionId}`);
            this.notifyLaravel('max_retries_reached', { 
                session_id: this.sessionId,
                status: 'failed'
            });
        }
    }

    async sendMessage(to, message, options = {}) {
        if (!this.isReady) {
            throw new Error('WhatsApp client is not ready');
        }

        try {
            let result;
            
            if (options.media) {
                const media = MessageMedia.fromFilePath(options.media);
                result = await this.client.sendMessage(to, media, { caption: message });
            } else {
                result = await this.client.sendMessage(to, message);
            }

            this.lastActivity = new Date();
            logger.info(`Message sent from session ${this.sessionId} to ${to}`);
            
            return {
                success: true,
                messageId: result.id._serialized,
                timestamp: result.timestamp
            };
        } catch (error) {
            logger.error(`Error sending message from session ${this.sessionId}:`, error);
            throw error;
        }
    }

    async getChats() {
        if (!this.isReady) {
            throw new Error('WhatsApp client is not ready');
        }

        try {
            const chats = await this.client.getChats();
            this.lastActivity = new Date();
            
            return chats.map(chat => ({
                id: chat.id._serialized,
                name: chat.name,
                isGroup: chat.isGroup,
                isReadOnly: chat.isReadOnly,
                unreadCount: chat.unreadCount,
                timestamp: chat.timestamp,
                lastMessage: chat.lastMessage ? {
                    body: chat.lastMessage.body,
                    type: chat.lastMessage.type,
                    timestamp: chat.lastMessage.timestamp
                } : null
            }));
        } catch (error) {
            logger.error(`Error getting chats for session ${this.sessionId}:`, error);
            throw error;
        }
    }

    async getContacts() {
        if (!this.isReady) {
            throw new Error('WhatsApp client is not ready');
        }

        try {
            const contacts = await this.client.getContacts();
            this.lastActivity = new Date();
            
            return contacts.map(contact => ({
                id: contact.id._serialized,
                name: contact.name,
                pushname: contact.pushname,
                number: contact.number,
                isMe: contact.isMe,
                isUser: contact.isUser,
                isGroup: contact.isGroup,
                isWAContact: contact.isWAContact,
                profilePicUrl: contact.profilePicUrl
            }));
        } catch (error) {
            logger.error(`Error getting contacts for session ${this.sessionId}:`, error);
            throw error;
        }
    }

    async destroy() {
        try {
            if (this.client) {
                await this.client.destroy();
                logger.info(`WhatsApp client destroyed for session: ${this.sessionId}`);
            }
        } catch (error) {
            logger.error(`Error destroying WhatsApp client for session ${this.sessionId}:`, error);
        }
    }

    async notifyLaravel(event, data) {
        try {
            const laravelUrl = process.env.LARAVEL_API_URL;
            const webhookSecret = process.env.WEBHOOK_SECRET;
            
            if (!laravelUrl) {
                logger.warn('Laravel API URL not configured, skipping webhook');
                return;
            }

            await axios.post(`${laravelUrl}/whatsapp/webhook`, {
                event,
                session_id: this.sessionId,
                data,
                timestamp: new Date().toISOString()
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${process.env.LARAVEL_API_TOKEN}`,
                    'X-Webhook-Secret': webhookSecret
                },
                timeout: 5000
            });

            logger.info(`Laravel webhook sent for event: ${event}, session: ${this.sessionId}`);
        } catch (error) {
            logger.error(`Error sending webhook to Laravel:`, error.message);
        }
    }

    handleError(error) {
        logger.error(`Client error for session ${this.sessionId}:`, error);
        
        clientStates.set(this.sessionId, {
            status: 'error',
            error: error.message,
            timestamp: new Date()
        });
        
        io.emit('client_error', { 
            sessionId: this.sessionId, 
            error: error.message 
        });

        this.notifyLaravel('error', { 
            session_id: this.sessionId,
            error: error.message
        });
    }

    getStatus() {
        return {
            sessionId: this.sessionId,
            isReady: this.isReady,
            qrCode: this.qrCode,
            lastActivity: this.lastActivity,
            retryAttempts: this.retryAttempts,
            state: clientStates.get(this.sessionId) || { status: 'initializing' }
        };
    }
}

// API Routes

// Health check
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        sessions: clients.size
    });
});

// Get all sessions
app.get('/sessions', (req, res) => {
    try {
        const sessions = Array.from(clients.entries()).map(([sessionId, client]) => ({
            sessionId,
            ...client.getStatus()
        }));
        
        res.json({
            success: true,
            sessions
        });
    } catch (error) {
        logger.error('Error getting sessions:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Create new session
app.post('/sessions', async (req, res) => {
    try {
        const { sessionId } = req.body;
        
        if (!sessionId) {
            return res.status(400).json({
                success: false,
                error: 'Session ID is required'
            });
        }

        if (clients.has(sessionId)) {
            return res.status(409).json({
                success: false,
                error: 'Session already exists'
            });
        }

        const client = new WhatsAppClientManager(sessionId);
        clients.set(sessionId, client);

        logger.info(`New session created: ${sessionId}`);
        
        res.json({
            success: true,
            sessionId,
            message: 'Session created successfully'
        });
    } catch (error) {
        logger.error('Error creating session:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get session status
app.get('/sessions/:sessionId', (req, res) => {
    try {
        const { sessionId } = req.params;
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        res.json({
            success: true,
            ...client.getStatus()
        });
    } catch (error) {
        logger.error('Error getting session status:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Delete session
app.delete('/sessions/:sessionId', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        await client.destroy();
        clients.delete(sessionId);
        clientStates.delete(sessionId);

        logger.info(`Session destroyed: ${sessionId}`);

        res.json({
            success: true,
            message: 'Session destroyed successfully'
        });
    } catch (error) {
        logger.error('Error destroying session:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Send message
app.post('/sessions/:sessionId/send', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const { to, message, media } = req.body;
        
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        if (!client.isReady) {
            return res.status(400).json({
                success: false,
                error: 'Session not ready'
            });
        }

        const result = await client.sendMessage(to, message, { media });

        res.json({
            success: true,
            result
        });
    } catch (error) {
        logger.error('Error sending message:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Send bulk messages
app.post('/sessions/:sessionId/send-bulk', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const { messages } = req.body;
        
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        if (!client.isReady) {
            return res.status(400).json({
                success: false,
                error: 'Session not ready'
            });
        }

        const results = [];
        
        for (const msg of messages) {
            try {
                const result = await client.sendMessage(msg.to, msg.message, { media: msg.media });
                results.push({
                    to: msg.to,
                    success: true,
                    result
                });
                
                // Add delay between messages to avoid rate limiting
                await new Promise(resolve => setTimeout(resolve, 1000));
            } catch (error) {
                results.push({
                    to: msg.to,
                    success: false,
                    error: error.message
                });
            }
        }

        res.json({
            success: true,
            results
        });
    } catch (error) {
        logger.error('Error sending bulk messages:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get chats
app.get('/sessions/:sessionId/chats', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        const chats = await client.getChats();

        res.json({
            success: true,
            chats
        });
    } catch (error) {
        logger.error('Error getting chats:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Get contacts
app.get('/sessions/:sessionId/contacts', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const client = clients.get(sessionId);

        if (!client) {
            return res.status(404).json({
                success: false,
                error: 'Session not found'
            });
        }

        const contacts = await client.getContacts();

        res.json({
            success: true,
            contacts
        });
    } catch (error) {
        logger.error('Error getting contacts:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Upload media
app.post('/upload', upload.single('media'), (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({
                success: false,
                error: 'No file uploaded'
            });
        }

        res.json({
            success: true,
            filename: req.file.filename,
            path: req.file.path,
            originalName: req.file.originalname,
            mimetype: req.file.mimetype,
            size: req.file.size
        });
    } catch (error) {
        logger.error('Error uploading file:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    logger.info(`Socket client connected: ${socket.id}`);

    socket.on('join_session', (sessionId) => {
        socket.join(`session_${sessionId}`);
        logger.info(`Socket ${socket.id} joined session: ${sessionId}`);
    });

    socket.on('leave_session', (sessionId) => {
        socket.leave(`session_${sessionId}`);
        logger.info(`Socket ${socket.id} left session: ${sessionId}`);
    });

    socket.on('disconnect', () => {
        logger.info(`Socket client disconnected: ${socket.id}`);
    });
});

// Cleanup inactive sessions (runs every 30 minutes)
cron.schedule('*/30 * * * *', () => {
    logger.info('Running session cleanup task');
    
    const now = new Date();
    const timeout = parseInt(process.env.WHATSAPP_SESSION_TIMEOUT) || 300000; // 5 minutes default
    
    for (const [sessionId, client] of clients.entries()) {
        const timeSinceLastActivity = now - client.lastActivity;
        
        if (timeSinceLastActivity > timeout && !client.isReady) {
            logger.info(`Cleaning up inactive session: ${sessionId}`);
            client.destroy();
            clients.delete(sessionId);
            clientStates.delete(sessionId);
        }
    }
});

// Error handling
app.use((err, req, res, next) => {
    logger.error('Unhandled error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        error: 'Endpoint not found'
    });
});

// Graceful shutdown
process.on('SIGTERM', async () => {
    logger.info('Received SIGTERM, shutting down gracefully');
    
    // Close all WhatsApp clients
    for (const [sessionId, client] of clients.entries()) {
        await client.destroy();
    }
    
    server.close(() => {
        logger.info('Server closed');
        process.exit(0);
    });
});

process.on('SIGINT', async () => {
    logger.info('Received SIGINT, shutting down gracefully');
    
    // Close all WhatsApp clients
    for (const [sessionId, client] of clients.entries()) {
        await client.destroy();
    }
    
    server.close(() => {
        logger.info('Server closed');
        process.exit(0);
    });
});

// Start server
const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
    logger.info(`🚀 WhatsApp Server running on port ${PORT}`);
    logger.info(`📱 Environment: ${process.env.NODE_ENV || 'development'}`);
    logger.info(`🔗 Laravel API: ${process.env.LARAVEL_API_URL || 'not configured'}`);
});

module.exports = { app, server, io };
