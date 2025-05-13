# YoPrint Challenge

A Laravel 10 application using Livewire 3, Tailwind CSS 4.1, and Redis (via Docker) for queue processing.

---

## ✅ Environment Requirements

| Tool      | Version     |
|-----------|-------------|
| PHP       | 8.3.21      |
| Composer  | 2.8.8       |
| Node.js   | 22.15.0     |
| npm       | 10.9.2      |
| Laravel   | 10          |
| Livewire  | 3           |
| Tailwind  | 4.1         |
| Redis     | latest (Docker-based) |

---

## 🚀 Redis Setup (Docker on Windows)

### 1. Install Docker
- Download and install Docker Desktop: https://www.docker.com/products/docker-desktop

### 2. Start Redis in Docker
Open terminal and run:
```bash
docker run -d --name redis -p 6379:6379 redis
```

### 3. Use Redis CLI (if needed)
```bash
docker exec -it redis redis-cli
```

---

## 🔧 Application Setup

### 1. Clone the repository
```bash
git clone <repo-url>
cd <project-folder>
```

### 2. Set up `.env`
```bash
cp .env.example .env
```
Edit `.env` and change:
```
APP_KEY=            # run `php artisan key:generate` to set
DB_DATABASE=        # full absolute path to your SQLite file, e.g. C:/Users/you/project/database/database.sqlite
```

### 3. Install PHP & JS dependencies
```bash
composer install
npm install
npm run dev
```

### 4. Start Laravel Server
```bash
php artisan serve
```

### 5. Ensure Redis is Running
```bash
docker start redis  # or re-run the docker run command if container not yet created
```

### 6. Start Laravel Queue Worker
```bash
php artisan queue:work
```

### 7. Run Migrations
```bash
php artisan migrate
```

---

## ✅ You're ready!
- Visit the Laravel app in your browser (usually http://127.0.0.1:8000)
- Upload your CSV files and monitor background processing with Redis queues

---

## 🧠 Notes
- Redis is required for queueing jobs — don't skip the Docker container
- Laravel Horizon is not used (not supported on Windows natively)
- Ensure your SQLite file exists: `touch database/database.sqlite` (or create it manually)

Let us know if you need help extending this for production or real-time monitoring!
