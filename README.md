# Content Scheduling API

A Laravel-based backend API for scheduling and managing social media posts across multiple platforms.

## Table of Contents

-   [Installation](#installation)
-   [Database](#database)
-   [API Endpoints](#api-endpoints)
-   [Postman Collection](#postman-collection)
-   [Running the Application](#running-the-application)
-   [Approach & Architecture](#approach--architecture)
-   [Trade-offs & Decisions](#trade-offs--decisions)

---

## Installation

### Prerequisites

-   PHP 8.3+
-   Composer
-   MySQL

### Setup Steps

```bash
# 1. Clone the repository
git clone https://github.com/Dev-Ahmed-Alaa/content-scheduling
cd content-scheduling

# 2. Install dependencies
composer install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=content_scheduling
DB_USERNAME=root
DB_PASSWORD=

# 5. Run migrations and seeders
php artisan migrate:fresh --seed

# 6. Start the server
php artisan serve
```

The API will be available at `http://localhost:8000/api`

---

## Database

### Migrations

| Migration                    | Table           | Description                                                                 |
| ---------------------------- | --------------- | --------------------------------------------------------------------------- |
| `create_users_table`         | `users`         | User accounts (id, name, email, password)                                   |
| `create_platforms_table`     | `platforms`     | Social media platforms (id, name, type, character_limit)                    |
| `create_posts_table`         | `posts`         | User posts (id, title, content, image_url, scheduled_time, status, user_id) |
| `create_post_platform_table` | `post_platform` | Pivot: post-platform relationship with per-platform status                  |
| `create_user_platform_table` | `user_platform` | Pivot: which platforms each user has activated                              |

### Schema Overview

```
users
├── id, name, email, password, timestamps

platforms
├── id, name, type (x/instagram/linkedin/facebook), character_limit, is_active

posts
├── id, user_id (FK), title, content, image_url, scheduled_time, status, published_at, timestamps, soft_deletes

post_platform (pivot)
├── post_id (FK), platform_id (FK), platform_status (pending/published/failed), published_at, error_message

user_platform (pivot)
├── user_id (FK), platform_id (FK), is_active
```

### Seeders

Run `php artisan db:seed` to populate the database with:

**PlatformSeeder** - Creates 4 platforms:

| Platform    | Type      | Character Limit |
| ----------- | --------- | --------------- |
| X (Twitter) | x         | 280             |
| Instagram   | instagram | 2,200           |
| LinkedIn    | linkedin  | 3,000           |
| Facebook    | facebook  | 63,206          |

**UserSeeder** - Creates 4 test users (password: `password`):

| Email             | Active Platforms                                                |
| ----------------- | --------------------------------------------------------------- |
| test@example.com  | X, Instagram                                                    |
| demo@example.com  | All 4 platforms                                                 |
| power@example.com | LinkedIn, Facebook (8 posts scheduled - for rate limit testing) |
| admin@example.com | All 4 platforms                                                 |

**DemoDataSeeder** - Creates sample posts:

-   Draft posts
-   Scheduled posts (future dates)
-   Published posts with platform statuses
-   Historical data for analytics

---

## API Endpoints

### Authentication

| Method | Endpoint        | Description            |
| ------ | --------------- | ---------------------- |
| POST   | `/api/register` | Register new user      |
| POST   | `/api/login`    | Login, returns token   |
| POST   | `/api/logout`   | Logout (requires auth) |

### User Profile

| Method | Endpoint    | Description      |
| ------ | ----------- | ---------------- |
| GET    | `/api/user` | Get current user |
| PUT    | `/api/user` | Update profile   |

### Platforms

| Method | Endpoint                     | Description              |
| ------ | ---------------------------- | ------------------------ |
| GET    | `/api/platforms`             | List all platforms       |
| POST   | `/api/platforms/{id}/toggle` | Toggle platform for user |

### Posts

| Method | Endpoint          | Description                                      |
| ------ | ----------------- | ------------------------------------------------ |
| GET    | `/api/posts`      | List posts (filters: status, date_from, date_to) |
| POST   | `/api/posts`      | Create post                                      |
| GET    | `/api/posts/{id}` | Get post                                         |
| PUT    | `/api/posts/{id}` | Update post                                      |
| DELETE | `/api/posts/{id}` | Delete post                                      |

### Analytics

| Method | Endpoint                   | Description                               |
| ------ | -------------------------- | ----------------------------------------- |
| GET    | `/api/analytics/overview`  | Total posts, drafts, scheduled, published |
| GET    | `/api/analytics/platforms` | Posts per platform, success rates         |
| GET    | `/api/analytics/timeline`  | Daily scheduled vs published counts       |

---

## Postman Collection

The Postman collection is included in the project directory:

```
postman/Content-Scheduling-API.postman_collection.json
```

> **Note:** The collection is also attached in the submission email.

**How to use:**

1. Import the collection into Postman
2. Run **Login** request (token auto-saves)
3. Test any endpoint

---

## Running the Application

### Development Server

```bash
php artisan serve
```

### Queue Worker (for publishing posts)

```bash
php artisan queue:work
```

### Process Scheduled Posts

```bash
# Manual run
php artisan posts:publish-due

# Or via scheduler (add to crontab)
* * * * * cd /path-to-project && php artisan schedule:run
```

---

## Approach & Architecture

### Project Structure

```
app/
├── Console/Commands/       # Artisan commands (ProcessScheduledPostsCommand)
├── Contracts/              # Interfaces (Repository, Validator)
├── Enums/                  # PostStatus, PlatformStatus, PlatformType
├── Exceptions/             # Custom exceptions
├── Http/
│   ├── Controllers/Api/    # API controllers
│   ├── Requests/           # Form request validation
│   └── Resources/          # JSON response transformers
├── Jobs/                   # PublishPostJob (queue)
├── Models/                 # Eloquent models
├── Policies/               # Authorization (PostPolicy)
├── Repositories/           # Data access layer
├── Services/               # Business logic layer
└── Validators/Platforms/   # Platform-specific validators
```

### Design Patterns

| Pattern           | Implementation                                                |
| ----------------- | ------------------------------------------------------------- |
| **Repository**    | Abstracts database operations (`EloquentPostRepository`)      |
| **Service Layer** | Business logic in services (`PostService`, `PlatformService`) |
| **Strategy**      | Platform validators (`XValidator`, `InstagramValidator`)      |
| **Factory**       | Validator creation via IoC container                          |

### SOLID Principles

| Principle                 | How Applied                                               |
| ------------------------- | --------------------------------------------------------- |
| **Single Responsibility** | Controllers → HTTP, Services → Logic, Repositories → Data |
| **Open/Closed**           | Add new platforms without modifying existing code         |
| **Liskov Substitution**   | Repositories implement interfaces, swappable              |
| **Interface Segregation** | Separate interfaces for different concerns                |
| **Dependency Inversion**  | Services depend on interfaces, not implementations        |

### Key Features

1. **Platform-Specific Validation** - Each platform has character limits enforced via Strategy pattern
2. **Rate Limiting** - Max 10 scheduled posts per day per user
3. **Queue-Based Publishing** - Posts publish asynchronously via jobs
4. **Per-Platform Status** - Track success/failure for each platform independently
5. **Analytics** - Overview stats, platform performance, timeline data

---

## Trade-offs & Decisions

| Decision                          | Rationale                                         |
| --------------------------------- | ------------------------------------------------- |
| **Soft deletes for posts**        | Maintains audit trail, analytics remain accurate  |
| **Published posts are immutable** | Prevents inconsistency after social media publish |
| **Pivot table for PostPlatform**  | One post can succeed on X but fail on Instagram   |
| **Mock publishing (80% success)** | Simulates real API failures for testing           |
| **Queue-based publishing**        | Scalable, non-blocking                            |
| **Cache analytics (5 min)**       | Reduces database load                             |
| **10 posts/day rate limit**       | Prevents abuse                                    |

### Scalability Considerations

-   **Database indexes** on `scheduled_time`, `status`, `user_id`
-   **Queue workers** can scale horizontally
-   **Cache locks** prevent duplicate job processing
-   **Retry logic** with exponential backoff for failed publishes

---

## License

MIT License
