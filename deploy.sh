#!/bin/bash

# Скрипт для деплоя Events API

echo "🚀 Начинаем деплой Events API..."

# Переходим в директорию проекта
cd /var/www/events_api

# Получаем последние изменения
echo "📥 Получаем последние изменения из Git..."
sudo -u www-data git pull origin main

# Устанавливаем/обновляем зависимости
echo "📦 Обновляем зависимости..."
sudo -u www-data composer install --no-dev --optimize-autoloader

# Очищаем кэш
echo "🧹 Очищаем кэш..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Запускаем миграции
echo "🗄️ Запускаем миграции..."
sudo -u www-data php artisan migrate --force

# Кэшируем конфигурацию для продакшена
echo "⚡ Кэшируем конфигурацию..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache

# Устанавливаем правильные права доступа
echo "🔐 Устанавливаем права доступа..."
sudo chown -R www-data:www-data /var/www/events_api
sudo chmod -R 755 /var/www/events_api
sudo chmod -R 775 /var/www/events_api/storage
sudo chmod -R 775 /var/www/events_api/bootstrap/cache

# Перезапускаем сервисы
echo "🔄 Перезапускаем сервисы..."
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm

echo "✅ Деплой завершен успешно!"

# Проверяем статус
echo "🔍 Проверяем статус приложения..."
curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com/api/events

echo ""
echo "🎉 Приложение готово к работе!" 
