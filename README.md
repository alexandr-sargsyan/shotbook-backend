# Filmmaker Reference Platform - Backend

Backend часть проекта на Laravel 12 с PostgreSQL.

## 📋 Требования

- PHP 8.4+
- Composer
- PostgreSQL 12+
- Node.js и npm (для фронтенда)

## 🚀 Установка и запуск

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd project_x_backend
```

### 2. Установка зависимостей

```bash
composer install
```

### 3. Настройка окружения

Скопируйте файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

Отредактируйте `.env` файл и настройте подключение к базе данных:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=project_x
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Генерация ключа приложения

```bash
php artisan key:generate
```

### 5. Создание базы данных

Создайте базу данных PostgreSQL:

```bash
# Войдите в PostgreSQL
psql -U postgres

# Создайте базу данных
CREATE DATABASE project_x;
```

### 6. Запуск миграций

```bash
php artisan migrate
```

### 7. Заполнение базы данных тестовыми данными (опционально)

```bash
php artisan db:seed
```

Это создаст:
- Категории (на английском языке)
- Теги
- 30 тестовых видео-референсов

### 8. Запуск сервера разработки

```bash
php artisan serve
```

Сервер будет доступен по адресу: `http://localhost:8000`

API endpoints будут доступны по адресу: `http://localhost:8000/api`

## 📡 API Endpoints

### Video References

- `GET /api/video-references` - Список всех видео-референсов
- `GET /api/video-references/{id}` - Получить видео-референс по ID
- `POST /api/video-references` - Создать новый видео-референс
- `PUT /api/video-references/{id}` - Обновить видео-референс
- `DELETE /api/video-references/{id}` - Удалить видео-референс

### Categories

- `GET /api/categories` - Список всех категорий
- `GET /api/categories/{id}` - Получить категорию по ID
- `POST /api/categories` - Создать новую категорию
- `PUT /api/categories/{id}` - Обновить категорию
- `DELETE /api/categories/{id}` - Удалить категорию

### Tags

- `GET /api/tags` - Список всех тегов
  - Параметр `search` - поиск тегов по имени (case-insensitive)
  - Пример: `GET /api/tags?search=cinematic`
- `GET /api/tags/{id}` - Получить тег по ID
- `POST /api/tags` - Создать новый тег
- `PUT /api/tags/{id}` - Обновить тег
- `DELETE /api/tags/{id}` - Удалить тег

## 🔍 Поиск и фильтрация

API поддерживает full-text search через PostgreSQL (tsvector/tsquery) и расширенную фильтрацию.

**Примеры запросов:**

Поиск с фильтрами:
```
GET /api/video-references?search=cinematic&category_id=1&platform=youtube
```

Фильтрация по тегам (логика OR - хотя бы один из выбранных тегов):
```
GET /api/video-references?tag_ids[]=1&tag_ids[]=2
```

Множественный выбор категорий:
```
GET /api/video-references?category_id[]=1&category_id[]=2
```

**Поддерживаемые фильтры:**
- `search` - full-text search по title, search_profile, search_metadata
- `category_id` - фильтр по категории (integer или array)
- `platform` - фильтр по платформе (youtube, tiktok, instagram)
- `pacing` - фильтр по темпу (slow, fast, mixed)
- `production_level` - фильтр по уровню продакшена (low, mid, high)
- `tag_ids` - фильтр по тегам (array, логика OR)
- `has_visual_effects`, `has_3d`, `has_animations`, `has_typography`, `has_sound_design` - boolean фильтры
- `has_tutorial` - фильтр по наличию обучающих материалов
- `sort_by` - сортировка (quality_score, created_at, relevance)
- `page` - номер страницы
- `per_page` - количество элементов на странице (max 100)

## 🗄️ Структура базы данных

- `categories` - Категории (с поддержкой подкатегорий через adjacency list)
- `video_references` - Видео-референсы
  - Поле `platform_video_id` - ID видео на платформе после нормализации URL
  - Computed column `search_vector` (tsvector) - для full-text search
- `tags` - Теги
- `video_reference_tag` - Связь many-to-many между видео и тегами
- `tutorials` - Tutorials для видео-референсов

## 🧪 Тестирование

```bash
php artisan test
```

## 📝 Полезные команды

```bash
# Очистить кэш
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Откатить миграции
php artisan migrate:rollback

# Откатить все миграции и применить заново
php artisan migrate:fresh

# Откатить все миграции, применить заново и заполнить данными
php artisan migrate:fresh --seed
```

## 🔧 Конфигурация

Основные настройки находятся в файле `.env`. Убедитесь, что PostgreSQL настроен для поддержки full-text search (tsvector/tsquery).

## 📚 Документация

Подробная техническая документация находится в папке `documentation/`:
- `business-requirements.md` - Бизнес-требования
- `technical-implementation-plan.md` - Технический план реализации
- `video-player-architecture.md` - Архитектура видео-плееров
