# 🚀 Развертывание Events API на VDS

## Системные требования

- **OS:** Ubuntu 22.04 LTS
- **PHP:** 8.2+
- **MySQL:** 8.0+
- **Nginx:** 1.18+
- **Redis:** 6.0+
- **RAM:** минимум 2GB
- **Диск:** минимум 20GB SSD

## 1. Подготовка сервера

```bash
# Обновляем систему
sudo apt update && sudo apt upgrade -y

# Устанавливаем необходимые пакеты
sudo apt install software-properties-common curl wget git unzip -y
```

## 2. Установка PHP 8.2

```bash
# Добавляем репозиторий PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Устанавливаем PHP и расширения
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl \
php8.2-mbstring php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath \
php8.2-redis php8.2-cli -y

# Настраиваем PHP
sudo nano /etc/php/8.2/fpm/php.ini
```

### Настройки PHP (php.ini):
```ini
memory_limit = 256M
max_execution_time = 120
upload_max_filesize = 100M
post_max_size = 100M
date.timezone = Europe/Moscow
```

## 3. Установка MySQL

```bash
# Устанавливаем MySQL
sudo apt install mysql-server -y

# Настраиваем безопасность
sudo mysql_secure_installation

# Создаем базу данных
sudo mysql -u root -p
```

```sql
CREATE DATABASE events_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'events_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON events_api.* TO 'events_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Установка Nginx

```bash
# Устанавливаем Nginx
sudo apt install nginx -y

# Применяем оптимизированную основную конфигурацию
sudo cp nginx-main.conf /etc/nginx/nginx.conf

# Копируем конфигурацию сайта
sudo cp nginx.conf.example /etc/nginx/sites-available/events_api

# Редактируем конфигурацию под ваш домен
sudo nano /etc/nginx/sites-available/events_api
# Замените yourdomain.com на ваш домен

# Активируем сайт
sudo ln -s /etc/nginx/sites-available/events_api /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default

# Проверяем конфигурацию
sudo nginx -t
```

## 5. Установка Redis

```bash
# Устанавливаем Redis
sudo apt install redis-server -y

# Настраиваем автозапуск
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

## 6. Установка Composer

```bash
# Скачиваем и устанавливаем Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

## 7. Развертывание приложения

```bash
# Клонируем проект
cd /var/www
sudo git clone https://github.com/your-repo/events_api.git
cd events_api

# Устанавливаем зависимости
sudo -u www-data composer install --no-dev --optimize-autoloader

# Настраиваем права доступа
sudo chown -R www-data:www-data /var/www/events_api
sudo chmod -R 755 /var/www/events_api
sudo chmod -R 775 /var/www/events_api/storage
sudo chmod -R 775 /var/www/events_api/bootstrap/cache
```

## 8. Настройка окружения

```bash
# Копируем файл окружения
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```

### Пример .env:
```env
APP_NAME="Events API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=events_api
DB_USERNAME=events_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
```

```bash
# Генерируем ключ приложения
sudo -u www-data php artisan key:generate

# Запускаем миграции
sudo -u www-data php artisan migrate --force

# Кэшируем конфигурацию
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
```

## 9. SSL сертификат

```bash
# Устанавливаем Certbot
sudo apt install certbot python3-certbot-nginx -y

# Получаем сертификат
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## 10. Настройка Cron

```bash
# Добавляем задачи в cron
sudo -u www-data crontab -e
```

Добавляем строки:
```cron
# Laravel Scheduler
* * * * * cd /var/www/events_api && php artisan schedule:run >> /dev/null 2>&1

# Telegram уведомления каждые 10 минут
*/10 * * * * cd /var/www/events_api && php artisan notifications:send >> /dev/null 2>&1
```

## 11. Запуск сервисов

```bash
# Перезапускаем все сервисы
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo systemctl restart mysql
sudo systemctl restart redis-server

# Включаем автозапуск
sudo systemctl enable php8.2-fpm
sudo systemctl enable nginx
sudo systemctl enable mysql
sudo systemctl enable redis-server
```

## 12. Настройка Telegram Webhook

```bash
# Устанавливаем webhook
curl -X POST "https://api.telegram.org/bot{YOUR_BOT_TOKEN}/setWebhook" \
     -H "Content-Type: application/json" \
     -d '{"url": "https://yourdomain.com/api/telegram/webhook"}'
```

## 13. Проверка работоспособности

```bash
# Проверяем статус сервисов
sudo systemctl status php8.2-fpm
sudo systemctl status nginx
sudo systemctl status mysql
sudo systemctl status redis-server

# Тестируем API
curl https://yourdomain.com/api/events
```

## 🔧 Автоматический деплой

Используйте готовый скрипт `deploy.sh` для автоматического обновления:

```bash
# Делаем скрипт исполняемым
chmod +x deploy.sh

# Запускаем деплой
./deploy.sh
```

## 📊 Мониторинг

### Логи приложения:
```bash
# Laravel логи
tail -f /var/www/events_api/storage/logs/laravel.log

# Nginx логи
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# PHP-FPM логи
tail -f /var/log/php8.2-fpm.log
```

### Производительность:
```bash
# Мониторинг ресурсов
htop

# Статус MySQL
sudo systemctl status mysql

# Статус Redis
redis-cli ping
```

## 🛡️ Безопасность

1. **Firewall:**
```bash
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
```

2. **Обновления:**
```bash
# Автоматические обновления безопасности
sudo apt install unattended-upgrades -y
sudo dpkg-reconfigure unattended-upgrades
```

3. **Backup:**
```bash
# Создаем скрипт бэкапа базы данных
sudo nano /usr/local/bin/backup-db.sh
```

## ✅ Готово!

Ваш Events API готов к работе на `https://yourdomain.com`

### Основные эндпоинты:
- `GET /api/events` - список событий
- `POST /api/auth/register` - регистрация
- `POST /api/auth/login` - авторизация
- `GET /api/telegram/status` - статус Telegram
