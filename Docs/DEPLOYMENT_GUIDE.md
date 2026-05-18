# Nexus Platform - Deployment Guide

## Table of Contents

1. [Overview](#1-overview)
2. [Prerequisites](#2-prerequisites)
3. [Server Requirements](#3-server-requirements)
4. [Initial Server Setup](#4-initial-server-setup)
5. [Application Deployment](#5-application-deployment)
6. [Environment Configuration](#6-environment-configuration)
7. [Database Setup](#7-database-setup)
8. [Queue Workers](#8-queue-workers)
9. [SSL Configuration](#9-ssl-configuration)
10. [Monitoring & Maintenance](#10-monitoring--maintenance)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. Overview

This guide covers deploying the Nexus platform to a production VPS running Ubuntu 22.04/24.04 LTS.

### 1.1 Deployment Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Web Server** | Nginx | HTTP server, reverse proxy |
| **PHP** | PHP 8.2/8.3 | Application runtime |
| **Database** | MySQL 8.0+ | Primary data storage |
| **Cache/Queue** | Redis 7.0+ | Caching, sessions, queues |
| **Queue Manager** | Laravel Horizon | Queue monitoring |
| **SSL** | Let's Encrypt | HTTPS certificates |
| **Firewall** | UFW | Security |

---

## 2. Prerequisites

### 2.1 Domain & DNS

- A domain name pointing to your server IP
- DNS A record configured (e.g., `nexus.yourdomain.com`)
- DNS propagation complete (24-48 hours)

### 2.2 Server Access

- SSH access to the server
- Sudo/root privileges
- SSH key authentication recommended

---

## 3. Server Requirements

### 3.1 Minimum Specifications

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **CPU** | 2 cores | 4+ cores |
| **RAM** | 4GB | 8GB+ |
| **Storage** | 50GB SSD | 100GB+ SSD |
| **Bandwidth** | 1TB/month | 2TB+ |
| **OS** | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |

### 3.2 Software Requirements

| Software | Version | Purpose |
|----------|---------|---------|
| **PHP** | 8.2 or 8.3 | Application runtime |
| **Composer** | 2.x | PHP dependency manager |
| **MySQL** | 8.0+ | Database |
| **Redis** | 7.0+ | Cache & queues |
| **Nginx** | 1.18+ | Web server |
| **Node.js** | 18+ | Asset compilation |
| **Git** | 2.x | Version control |

---

## 4. Initial Server Setup

### 4.1 Connect to Server

```bash
ssh root@your-server-ip
# or with a user
ssh your-user@your-server-ip
```

### 4.2 Update System

```bash
# Update package lists
sudo apt update && sudo apt upgrade -y

# Install basic utilities
sudo apt install -y git curl unzip zip vim ufw
```

### 4.3 Configure Firewall

```bash
# Enable UFW
sudo ufw enable

# Allow SSH (important - don't lock yourself out!)
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow http
sudo ufw allow https

# Allow Nginx Full (if using Nginx)
sudo ufw allow 'Nginx Full'

# Check status
sudo ufw status
```

### 4.4 Create Deploy User (Recommended)

```bash
# Create deploy user
sudo adduser deploy

# Add to sudo group
sudo usermod -aG sudo deploy

# Switch to deploy user
su - deploy

# Set up SSH key authentication
mkdir -p ~/.ssh
authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

---

## 5. Application Deployment

### 5.1 Clone Repository

```bash
# Navigate to web directory
cd /var/www

# Clone the repository
sudo git clone https://github.com/your-org/nexus-platform.git

# Set ownership
sudo chown -R deploy:deploy /var/www/nexus-platform

# Navigate to project
cd /var/www/nexus-platform
```

### 5.2 Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
npm ci --only=production

# Build frontend assets
npm run build
```

### 5.3 Set Up Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

### 5.4 Configure Web Server

Create Nginx configuration:

```bash
sudo nano /etc/nginx/sites-available/nexus
```

```nginx
server {
    listen 80;
    server_name nexus.yourdomain.com;
    root /var/www/nexus-platform/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Handle Laravel Horizon dashboard
    location /horizon {
        auth_basic "Restricted";
        auth_basic_user_file /etc/nginx/.htpasswd;
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Enable the site:

```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/nexus /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## 6. Environment Configuration

### 6.1 Production .env Settings

```env
APP_NAME="Nexus Platform"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://nexus.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nexus_production
DB_USERNAME=nexus_user
DB_PASSWORD=your-secure-password

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail (configure as needed)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-email-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Nexus Platform"

# AI Providers (configure as needed)
OPENAI_API_KEY=sk-your-openai-key
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key
GOOGLE_API_KEY=your-google-api-key

# WAHA (WhatsApp HTTP API)
WAHA_API_URL=https://waha.yourdomain.com
WAHA_API_KEY=your-waha-api-key

# Pinecone (Vector Database)
PINECONE_API_KEY=your-pinecone-api-key
PINECONE_ENVIRONMENT=your-environment
PINECONE_INDEX=nexus-memories
```

### 6.2 Security Considerations

- **Never commit `.env` to version control**
- Use strong, unique passwords for all services
- Rotate API keys regularly
- Enable 2FA on all service accounts
- Restrict database user permissions

---

## 7. Database Setup

### 7.1 Install MySQL

```bash
# Install MySQL server
sudo apt install -y mysql-server

# Secure installation
sudo mysql_secure_installation

# Start and enable MySQL
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 7.2 Create Database & User

```bash
# Log into MySQL
sudo mysql

# Create database
CREATE DATABASE nexus_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user
CREATE USER 'nexus_user'@'localhost' IDENTIFIED BY 'your-secure-password';

# Grant privileges
GRANT ALL PRIVILEGES ON nexus_production.* TO 'nexus_user'@'localhost';

# Flush privileges
FLUSH PRIVILEGES;

# Exit
EXIT;
```

### 7.3 Run Migrations

```bash
cd /var/www/nexus-platform

# Run migrations
php artisan migrate --force

# (Optional) Seed database
php artisan db:seed --force
```

---

## 8. Queue Workers

### 8.1 Install Redis

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis for production
sudo nano /etc/redis/redis.conf
```

Key settings to modify:

```conf
# Bind to localhost only
bind 127.0.0.1

# Set password (optional but recommended)
requirepass your-redis-password

# Set max memory
maxmemory 256m
maxmemory-policy allkeys-lru

# Enable AOF persistence
appendonly yes
```

Start Redis:

```bash
sudo systemctl restart redis
sudo systemctl enable redis
```

### 8.2 Configure Laravel Horizon

```bash
# Publish Horizon configuration
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"

# Edit Horizon config
nano config/horizon.php
```

### 8.3 Start Horizon Workers

```bash
# Start Horizon (runs in foreground)
php artisan horizon

# Or run as daemon
nohup php artisan horizon > /dev/null 2>&1 &
```

### 8.4 Supervisor Configuration (Recommended)

Create Supervisor config:

```bash
sudo nano /etc/supervisor/conf.d/nexus-horizon.conf
```

```ini
[program:nexus-horizon]
process_name=%(program_name)s
command=php /var/www/nexus-platform/artisan horizon
autostart=true
autorestart=true
user=deploy
redirect_stderr=true
stdout_logfile=/var/www/nexus-platform/storage/logs/horizon.log
stopwaitsecs=3600
```

```bash
# Update Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nexus-horizon
```

---

## 9. SSL Configuration

### 9.1 Install Certbot

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx
```

### 9.2 Obtain SSL Certificate

```bash
# Obtain certificate
sudo certbot --nginx -d nexus.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 9.3 Verify HTTPS

```bash
# Test HTTPS connection
curl -I https://nexus.yourdomain.com
```

---

## 10. Monitoring & Maintenance

### 10.1 Laravel Horizon Dashboard

Access the Horizon dashboard at:
```
https://nexus.yourdomain.com/horizon
```

Default credentials are configured in `.env`:
```env
HORIZON_PASSWORD=your-secure-password
HORIZON_USERNAME=admin
```

### 10.2 Log Monitoring

```bash
# View Laravel logs
tail -f /var/www/nexus-platform/storage/logs/laravel.log

# View Horizon logs
tail -f /var/www/nexus-platform/storage/logs/horizon.log

# View Nginx logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

### 10.3 Backup Strategy

#### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/backup-nexus.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/nexus"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u nexus_user -p'your-password' nexus_production > $BACKUP_DIR/db_$DATE.sql

# Compress
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup-nexus.sh

# Add to cron (daily at 2 AM)
sudo crontab -e
# Add line:
0 2 * * * /usr/local/bin/backup-nexus.sh
```

#### File Backup

```bash
# Backup uploads and storage
rsync -av /var/www/nexus-platform/storage/app/public/ /var/backups/nexus/storage/
```

### 10.4 Performance Optimization

```bash
# Optimize Laravel for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache when needed
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 11. Troubleshooting

### 11.1 Common Issues

#### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check file permissions
ls -la /var/www/nexus-platform/storage
sudo chown -R deploy:www-data /var/www/nexus-platform/storage
sudo chmod -R 775 /var/www/nexus-platform/storage
```

#### Queue Jobs Not Processing

```bash
# Check Horizon status
php artisan horizon:status

# Check Redis connection
php artisan tinker
>>> Redis::ping()

# Restart Horizon
php artisan horizon:terminate
```

#### Database Connection Error

```bash
# Test MySQL connection
mysql -u nexus_user -p -h 127.0.0.1

# Check .env database settings
cat .env | grep DB_

# Test connection via Laravel
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Permission Denied Errors

```bash
# Fix storage permissions
sudo chown -R deploy:www-data /var/www/nexus-platform/storage
sudo chown -R deploy:www-data /var/www/nexus-platform/bootstrap/cache
sudo chmod -R 775 /var/www/nexus-platform/storage
sudo chmod -R 775 /var/www/nexus-platform/bootstrap/cache
```

### 11.2 Debug Commands

```bash
# Check PHP version
php -v

# Check Composer version
composer -V

# Check Nginx version
nginx -v

# Check MySQL version
mysql --version

# Check Redis version
redis-server --version

# Check Node version
node -v

# Check npm version
npm -v
```

---

## 12. Zero-Downtime Deployment

### 12.1 Using Deployer (Recommended)

```bash
# Install Deployer
composer require deployer/deployer --dev

# Create deploy.php script
nano deploy.php
```

### 12.2 Manual Zero-Downtime

```bash
# 1. Put application in maintenance mode
php artisan down --secret="your-secret"

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --only=production
npm run build

# 4. Run migrations
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan optimize:clear
php artisan optimize

# 6. Restart queue workers
php artisan horizon:terminate

# 7. Bring application back up
php artisan up
```

---

## 13. Security Checklist

- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Restrict database user permissions
- [ ] Enable firewall (UFW)
- [ ] Disable root SSH login
- [ ] Use SSH key authentication
- [ ] Keep system packages updated
- [ ] Regular security audits
- [ ] Monitor logs for suspicious activity
- [ ] Implement rate limiting
- [ ] Use environment variables for secrets
- [ ] Regular backups
- [ ] Keep Laravel updated

---

*Last Updated: May 2026*
