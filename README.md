# YoPrint Challenge

A Laravel 10 application using Livewire 3, Tailwind CSS 4.1, and Redis (via Docker) for queue processing.
This development being done in Windows, so unable to use Horizon

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

Then create the SQLite file manually:
```bash
touch database/database.sqlite  # or use a text editor to create an empty file
```

### 3. Update PHP Configuration (for file uploads and timezone)

#### Option A: Edit `php.ini` directly (Windows)
- Open `php.ini` and modify/add:
  ```ini
  upload_max_filesize=50M
  post_max_size=55M
  date.timezone=Asia/Kuala_Lumpur
  ```

#### Option B: Use override config file (Linux/macOS or Docker)
- Create a file named `99-php.ini` in your `conf.d` or custom config directory:
  ```bash
  sudo nano 99-php.ini
  ```
  With content:
  ```ini
  upload_max_filesize=50M
  post_max_size=55M
  date.timezone=Asia/Kuala_Lumpur
  ```

### 4. Link Storage Folder
```bash
php artisan storage:link
```

### 5. Install PHP & JS dependencies
```bash
composer install
npm install
npm run dev
```

### 6. Run Migrations
```bash
php artisan migrate
```

### 7. Start Laravel Server
```bash
php artisan serve
```

### 8. Ensure Redis is Running
```bash
docker start redis  # or re-run the docker run command if container not yet created
```

### 9. Start Laravel Queue Worker
```bash
php artisan queue:work
```

---

## ✅ You're ready!
- Visit the Laravel app in your browser (usually http://127.0.0.1:8000)
- Upload your CSV files and monitor background processing with Redis queues

---

## 🧠 Notes
- Redis is required for queueing jobs — don't skip the Docker container
- Laravel Horizon is not used (not supported on Windows natively)
- Ensure your SQLite file exists before running migrations

Let us know if you need help extending this for production or real-time monitoring!
