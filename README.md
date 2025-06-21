# 🎉 Events API

**Современный RESTful API для управления событиями с интеграцией Telegram уведомлений**

Laravel 12 | PHP 8.2+ | MySQL 8.0+ | Redis | Telegram Bot API

---

## 🚀 Возможности

### 📅 **Управление событиями**
- ✅ Создание, редактирование, удаление событий
- ✅ Публичные и приватные события с whitelist
- ✅ Поддержка одноразовых и многодневных событий
- ✅ Геолокация и описания событий
- ✅ Система тегов для категоризации

### 👥 **Система участников**
- ✅ Присоединение/выход из событий
- ✅ Статусы участия: pending, accepted, declined
- ✅ Управление списком участников

### 💬 **Комментарии**
- ✅ Комментирование событий
- ✅ Редактирование и удаление комментариев
- ✅ Права доступа (автор комментария + создатель события)

### 🔐 **Аутентификация**
- ✅ Регистрация и авторизация через Laravel Sanctum
- ✅ JWT токены для API доступа
- ✅ Защищенные маршруты

### 📱 **Telegram интеграция**
- ✅ Привязка Telegram аккаунтов
- ✅ Автоматические уведомления о событиях
- ✅ Telegram бот с командами /start, /link, /help
- ✅ Webhook для обработки сообщений бота

---

## 🆕 Недавние изменения

### v1.1.0 - Обновление структуры событий
- ✅ **Объединены поля времени:** `start_date` + `start_time` → `start_datetime`
- ✅ **Упрощено API:** Теперь используйте формат `"start_datetime": "2024-06-15 19:00:00"`
- ✅ **Обратная совместимость:** Старые данные автоматически мигрированы
- ⚠️ **Breaking change:** Обновите клиентские приложения для использования нового поля

---

## 🛠️ Технические требования

- **PHP:** 8.2+
- **Laravel:** 12.x
- **База данных:** MySQL 8.0+ / PostgreSQL 14+
- **Кэш:** Redis 6.0+
- **Веб-сервер:** Nginx / Apache

---

## ⚡ Быстрый старт

### 1. Установка

```bash
git clone https://github.com/your-repo/events_api.git
cd events_api
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Настройка базы данных

```bash
# Создайте базу данных MySQL
mysql -u root -p
CREATE DATABASE events_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Настройте .env файл
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=events_api
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Запустите миграции
php artisan migrate

# Если обновляете с предыдущей версии - миграция автоматически объединит поля start_date и start_time
```

### 3. Настройка Telegram бота (опционально)

```bash
# Создайте бота через @BotFather в Telegram
# Добавьте в .env:
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/events-api/telegram/webhook
```

### 4. Запуск

```bash
# Для разработки
php artisan serve

# Для продакшена - см. DEPLOYMENT.md
```

---

## 📋 API Документация

### 🔓 **Публичные эндпоинты**

#### Получить список событий
```http
GET /api/events-api/events?page=1&per_page=10&search=концерт
```
**Ответ:** Пагинированный список доступных событий

#### Получить публичные события
```http
GET /api/events-api/events/public
```
**Ответ:** Список всех публичных событий

#### Получить информацию о событии
```http
GET /api/events-api/events/{id}
```
**Ответ:** Детальная информация о событии с участниками и комментариями

**📅 Формат даты и времени:**
- Используйте формат ISO 8601: `"YYYY-MM-DD HH:MM:SS"`
- Пример: `"start_datetime": "2024-06-15 19:00:00"`
- Время указывается в локальной временной зоне сервера

---

### 🔐 **Аутентификация**

#### Регистрация
```http
POST /api/events-api/auth/register
Content-Type: application/json

{
    "login": "username",
    "email": "user@example.com",
    "password": "password123"
}
```

#### Авторизация
```http
POST /api/events-api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```
**Ответ:** `{ "token": "your_api_token", "user": {...} }`

#### Получить информацию о пользователе
```http
GET /api/events-api/auth/me
Authorization: Bearer your_api_token
```

#### Выход
```http
POST /api/events-api/auth/logout
Authorization: Bearer your_api_token
```

---

### 📅 **Управление событиями** (требует авторизации)

#### Создать событие
```http
POST /api/events-api/events
Authorization: Bearer your_api_token
Content-Type: application/json

{
    "name": "Концерт в парке",
    "start_datetime": "2024-06-15 19:00:00",
    "is_long": false,
    "location_name": "Центральный парк",
    "location_coords": [55.7558, 37.6176],
    "description": "Отличный концерт под открытым небом",
    "is_public": true,
    "tags": ["музыка", "концерт", "парк"]
}
```

#### Обновить событие
```http
PUT /api/events-api/events/{id}
Authorization: Bearer your_api_token
```
**Права:** Только создатель события

#### Удалить событие
```http
DELETE /api/events-api/events/{id}
Authorization: Bearer your_api_token
```
**Права:** Только создатель события

#### Получить мои события
```http
GET /api/events-api/events/user/me          # Все события пользователя
GET /api/events-api/events/user/created     # Созданные события
GET /api/events-api/events/user/participating # События, где участвую
```

---

### 👥 **Участники событий** (требует авторизации)

#### Получить список участников
```http
GET /api/events-api/events/{id}/participants
Authorization: Bearer your_api_token
```

#### Присоединиться к событию
```http
POST /api/events-api/events/{id}/join
Authorization: Bearer your_api_token
```

#### Покинуть событие
```http
DELETE /api/events-api/events/{id}/leave
Authorization: Bearer your_api_token
```

#### Изменить статус участия
```http
PUT /api/events-api/events/{id}/status
Authorization: Bearer your_api_token
Content-Type: application/json

{
    "status": "accepted" // pending, accepted, declined
}
```

---

### 💬 **Комментарии** (требует авторизации)

#### Получить комментарии события
```http
GET /api/events-api/events/{id}/comments
Authorization: Bearer your_api_token
```

#### Добавить комментарий
```http
POST /api/events-api/events/{id}/comments
Authorization: Bearer your_api_token
Content-Type: application/json

{
    "content": "Отличное событие! Обязательно приду."
}
```

#### Редактировать комментарий
```http
PUT /api/events-api/events/{event_id}/comments/{comment_id}
Authorization: Bearer your_api_token
```
**Права:** Только автор комментария

#### Удалить комментарий
```http
DELETE /api/events-api/events/{event_id}/comments/{comment_id}
Authorization: Bearer your_api_token
```
**Права:** Автор комментария или создатель события

---

### 📱 **Telegram интеграция** (требует авторизации)

#### Получить статус привязки
```http
GET /api/events-api/telegram/status
Authorization: Bearer your_api_token
```

#### Сгенерировать код для привязки
```http
POST /api/events-api/telegram/generate-code
Authorization: Bearer your_api_token
```
**Ответ:** `{ "code": "ABC123", "expires_at": "2024-06-15T20:00:00Z" }`

#### Отвязать Telegram аккаунт
```http
DELETE /api/events-api/telegram/unlink
Authorization: Bearer your_api_token
```

---

## 🤖 Telegram бот

### Команды бота:
- `/start` - Приветствие и инструкции
- `/link ABC123` - Привязка аккаунта по коду
- `/help` - Справка по командам

### Уведомления:
- 🔔 За 1 час до начала события
- 📅 Информация о событии, времени и месте
- 👥 Отправляется создателю и всем участникам со статусом "accepted"

---

## 🏗️ Архитектура проекта

```
app/
├── Http/Controllers/Api/
│   ├── AuthController.php           # Аутентификация
│   ├── EventsController.php         # Управление событиями
│   ├── EventParticipantsController.php # Участники
│   ├── EventCommentController.php   # Комментарии
│   ├── TelegramController.php       # Telegram API
│   └── TelegramWebhookController.php # Webhook бота
├── Models/
│   ├── User.php                     # Пользователи
│   ├── Event.php                    # События
│   ├── EventParticipants.php       # Участники (pivot)
│   └── EventComment.php             # Комментарии
├── Services/
│   └── TelegramService.php          # Telegram интеграция
└── Console/Commands/
    └── SendEventNotifications.php   # Cron уведомления
```

---

## 🚀 Развертывание на продакшене

### Автоматическое развертывание:
```bash
# Используйте готовый скрипт
chmod +x deploy.sh
./deploy.sh
```

### Подробная инструкция:
См. файл [DEPLOYMENT.md](DEPLOYMENT.md) для пошаговой настройки VDS сервера.

### Настройка cron для уведомлений:
```bash
# Добавьте в crontab:
*/10 * * * * cd /var/www/events_api && php artisan notifications:send
```

---

## 🔧 Конфигурация

### Основные настройки .env:
```env
APP_NAME="Events API"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# База данных
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=events_api
DB_USERNAME=events_user
DB_PASSWORD=secure_password

# Redis для кэша и очередей
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Telegram бот
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/events-api/telegram/webhook
```

---

## 🧪 Тестирование API

### Примеры запросов с curl:

```bash
# Получить публичные события
curl -X GET "https://yourdomain.com/api/events-api/events/public"

# Авторизация
curl -X POST "https://yourdomain.com/api/events-api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Создать событие
curl -X POST "https://yourdomain.com/api/events-api/events" \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{"name":"Тест событие","start_datetime":"2024-06-15 19:00:00","location_name":"Тестовое место","description":"Описание","is_public":true}'
```

---

## 📊 Мониторинг и логи

### Логи приложения:
```bash
tail -f storage/logs/laravel.log
```

### Логи Nginx:
```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Мониторинг производительности:
- Используйте `htop` для мониторинга ресурсов
- Redis: `redis-cli ping`
- MySQL: `SHOW PROCESSLIST;`

---

## 🤝 Разработка

### Структура базы данных:
- `users` - Пользователи системы
- `events` - События
- `event_participants` - Участники событий (many-to-many)
- `event_comments` - Комментарии к событиям

### Ключевые особенности:
- **Laravel Sanctum** для API аутентификации
- **Eloquent ORM** с правильными связями
- **Валидация** всех входящих данных
- **Права доступа** на уровне контроллеров
- **Telegram webhook** для real-time уведомлений

### Миграция с предыдущих версий:
Если вы обновляете проект с версии, где использовались отдельные поля `start_date` и `start_time`:

1. **Автоматическая миграция:** При запуске `php artisan migrate` данные автоматически объединятся в поле `start_datetime`
2. **Обновите клиентский код:** Замените отправку двух полей на одно:
   ```javascript
   // Старый формат
   { "start_date": "2024-06-15", "start_time": "19:00" }
   
   // Новый формат  
   { "start_datetime": "2024-06-15 19:00:00" }
   ```
3. **Откат возможен:** Миграция поддерживает `php artisan migrate:rollback`

---

## 📝 Лицензия

MIT License - см. файл [LICENSE](LICENSE)

---

## 🆘 Поддержка

При возникновении проблем:
1. Проверьте логи приложения
2. Убедитесь в правильности настройки .env
3. Проверьте права доступа к файлам
4. Создайте issue в репозитории

---

**🎉 Events API готов к использованию!**
