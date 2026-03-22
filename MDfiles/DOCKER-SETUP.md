# 🐳 Запуск 7-Eleven Shop в Docker

## 📋 Быстрый старт

### 1. Убедитесь, что Docker запущен

Проверьте, что Docker Desktop работает:
```powershell
docker --version
docker-compose --version
```

### 2. Перейдите в папку проекта

```powershell
cd C:\Users\ADMIN\.gemini\antigravity\scratch\7-eleven-shop
```

### 3. Создайте .env файл (опционально)

```powershell
Copy-Item .env.example .env
```

### 4. Запустите контейнеры

```powershell
docker-compose up -d
```

**Что происходит:**
- ✅ Создается PHP 8.2 + Apache контейнер
- ✅ Создается MySQL 8.0 контейнер
- ✅ Автоматически импортируется схема БД из `database/schema.sql`
- ✅ Запускается phpMyAdmin

### 5. Откройте сайт

После запуска контейнеров откройте в браузере:

- **Сайт:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

---

## 📂 Где хранятся файлы

### Артефакты (документация)
```
C:\Users\ADMIN\.gemini\antigravity\brain\9a803b95-dfa3-4a02-a7c5-e44a6ff5a78f\
├── walkthrough.md          # Подробное описание проекта
├── implementation_plan.md  # План реализации
├── task.md                 # Чек-лист задач
└── ecommerce-design-prompt.md
```

### Код проекта (основная папка)
```
C:\Users\ADMIN\.gemini\antigravity\scratch\7-eleven-shop\
├── index.php              # Главная страница
├── cart.php               # Корзина
├── login.php              # Вход
├── register.php           # Регистрация
├── Dockerfile             # Docker конфигурация PHP
├── docker-compose.yml     # Docker Compose конфигурация
├── .env.example           # Пример переменных окружения
├── config/
│   └── database.php       # Конфигурация БД (поддерживает Docker и Laragon)
├── includes/
├── api/
├── assets/
└── database/
    └── schema.sql         # SQL схема (автоматически импортируется)
```

---

## 🔧 Полезные команды Docker

### Просмотр логов

```powershell
# Все контейнеры
docker-compose logs -f

# Только web сервер
docker-compose logs -f web

# Только база данных
docker-compose logs -f db
```

### Остановка контейнеров

```powershell
docker-compose stop
```

### Запуск остановленных контейнеров

```powershell
docker-compose start
```

### Перезапуск контейнеров

```powershell
docker-compose restart
```

### Остановка и удаление контейнеров

```powershell
docker-compose down
```

### Пересборка контейнеров (после изменения Dockerfile)

```powershell
docker-compose up -d --build
```

### Удаление всего (включая данные БД)

```powershell
docker-compose down -v
```

---

## 🗄️ Работа с базой данных

### Вариант 1: Через phpMyAdmin (рекомендуется)

1. Откройте http://localhost:8081
2. Логин: `root`
3. Пароль: `root`
4. База данных `seven_eleven_shop` уже создана и заполнена!

### Вариант 2: Через командную строку

```powershell
# Подключиться к контейнеру MySQL
docker exec -it 7-eleven-shop-db mysql -uroot -proot seven_eleven_shop

# Посмотреть таблицы
SHOW TABLES;

# Посмотреть товары
SELECT * FROM products;

# Выйти
exit
```

### Вариант 3: Переимпорт схемы

Если нужно пересоздать базу данных:

```powershell
# Остановить и удалить контейнеры с данными
docker-compose down -v

# Запустить заново (схема импортируется автоматически)
docker-compose up -d
```

---

## 🎯 Тестирование

### 1. Проверка главной страницы

Откройте: http://localhost:8080

**Должны увидеть:**
- ✅ Шапку с логотипом "7 SELECT"
- ✅ Промо-баннер красно-оранжевый
- ✅ 6 категорий с иконками
- ✅ Товары в сетке

### 2. Проверка корзины

1. Добавьте товар в корзину (кнопка "+")
2. Нажмите на иконку корзины
3. Откроется http://localhost:8080/cart.php

**Должны увидеть:**
- ✅ Список товаров слева
- ✅ Order Summary справа
- ✅ Оранжевые кнопки +/-

### 3. Проверка промокода

В корзине введите: `WELCOME10` и нажмите "Apply"

**Результат:**
- ✅ Скидка 10% применена
- ✅ Total пересчитан

### 4. Проверка входа

Откройте: http://localhost:8080/login.php

**Демо-аккаунт:**
- Email: `test@example.com`
- Password: `password123`

---

## 🐛 Решение проблем

### Проблема: Контейнеры не запускаются

```powershell
# Проверьте статус
docker-compose ps

# Посмотрите логи
docker-compose logs
```

### Проблема: База данных не создается

```powershell
# Пересоздайте контейнеры
docker-compose down -v
docker-compose up -d

# Подождите 10-15 секунд для инициализации БД
```

### Проблема: Порт 8080 занят

Измените порт в `.env`:
```
WEB_PORT=8090
```

Затем перезапустите:
```powershell
docker-compose down
docker-compose up -d
```

### Проблема: "Connection refused" к БД

Подождите 10-20 секунд после запуска. MySQL требует времени для инициализации.

Проверьте статус:
```powershell
docker-compose ps
```

Все сервисы должны быть в состоянии "Up (healthy)".

---

## 📊 Порты

| Сервис | Порт | URL |
|--------|------|-----|
| Web (Apache + PHP) | 8080 | http://localhost:8080 |
| MySQL | 3307 | localhost:3307 |
| phpMyAdmin | 8081 | http://localhost:8081 |

---

## 🔄 Переключение между Docker и Laragon

Файл `config/database.php` автоматически определяет окружение:

- **В Docker:** подключается к `db:3306`
- **В Laragon:** подключается к `localhost:3306`

Вы можете использовать один и тот же код в обоих окружениях!

---

## 📝 Демо-данные

После запуска Docker автоматически создаются:

- **Категории:** 6 штук
- **Товары:** 17 штук
- **Промокоды:** 3 штуки (WELCOME10, SAVE5, FREESHIP)
- **Пользователь:** test@example.com / password123

---

## ✅ Готово!

Теперь ваш интернет-магазин 7-Eleven работает в Docker! 🎉

Откройте http://localhost:8080 и начните тестировать!
