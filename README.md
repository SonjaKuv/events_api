
# API для проекта [friends_events](https://github.com/StickToYourGuns/friends-events)

RESTful API для управления пользователями и их эвентами, реализованный на Laravel 12.

## 🔧 Установка

```bash
git clone git@github.com:SonjaKuv/events_api.git
cd events_api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## 🚀 Запуск

```bash
php artisan serve
```

## 📦 Маршруты API

### Получить всех пользователей
**GET** `/api/users`  
Ответ: массив пользователей.

---

### Получить краткую информацию о пользователе
**GET** `/api/users/{id}`  
Ответ: `{ id, login, avatar }`  
Ошибка 404, если пользователь не найден.

---

### Получить полную информацию о пользователе
**GET** `/api/users/{id}/full`  
Ответ: все поля пользователя.  
Ошибка 404, если пользователь не найден.

---

### Создать нового пользователя
**POST** `/api/users`  
Параметры (form-data или JSON):
- `login` (строка, обязательно)
- `email` (email, обязательно, уникально)
- `password` (строка, ≥ 8 символов, обязательно)
- `avatar` (файл jpg/png, опционально)
- `telegram_id`, `vk_id`, `instagram_id` (числа, опционально)
- `user_events`, `join_events`, `friends` (массивы, опционально)
- `is_deleted` (boolean, опционально)

Ответ: созданный пользователь.  
Код ответа: `201 Created`.

---

### Обновить пользователя
**PUT** `/api/users/{id}`  
Параметры (любые из тех, что в POST).  
Если передан новый `password` — он будет захеширован.  
Если передан новый `avatar` — он будет загружен и заменит старый.

---

### Удалить пользователя (мягкое удаление)
**DELETE** `/api/users/{id}`  
Пользователь не удаляется физически, только устанавливается `is_deleted = true`.

---

### Получить друзей пользователя
**GET** `/api/users/{id}/friends`  
Ответ: массив пользователей-друзей (по полю `friends`).

---

## 📁 Структура
- Контроллер: `App\Http\Controllers\Api\UserController`
- Модель: `App\Models\User`
- Роуты: `routes/api.php`
