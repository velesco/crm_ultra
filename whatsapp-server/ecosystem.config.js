module.exports = {
  apps: [{
    name: 'crm-ultra-whatsapp-server',
    script: './server.js',
    instances: process.env.PM2_INSTANCES || 1,
    exec_mode: 'cluster',
    max_memory_restart: process.env.PM2_MAX_MEMORY_RESTART || '500M',
    autorestart: true,
    watch: false,
    env: {
      NODE_ENV: 'development',
      PORT: 3001
    },
    env_production: {
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