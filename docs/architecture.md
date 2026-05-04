Tentu, saya pastikan seluruh detail dari dokumentasi arsitektur **IRESS-APP** yang kamu berikan tercantum lengkap tanpa ada yang terlewat, mulai dari struktur folder hingga daftar kesalahan umum (_Common Issues_).

Berikut adalah versi lengkapnya dalam format Markdown:

# IRESS-APP Architecture Documentation

## 1. Overview

IRESS-APP adalah sistem _scraping insight_ Instagram berbasis arsitektur terpisah:

- **Laravel Backend** → API + Job Management
- **Python Worker** → Scraping & Processing
- **PostgreSQL** → Database
- **Docker** → Orkestrasi service

Sistem menggunakan pendekatan **polling job** dari database.

---

## 2. Architecture

**Frontend (Vue)**
↓
**Laravel API (Job Manager)**
↓
**Python Worker (Processor)**
↓
**Meta Graph API**
↓
**Laravel API (Store Result)**
↓
**Database**

---

## 3. Project Structure

### Laravel Backend

`laravel_backend/`

- `app/`
  - `Http/`
    - `Controllers/Api/InternalApiController.php`
    - `Middleware/InternalApiMiddleware.php`
  - `Models/`
    - `Account.php`
    - `ScrapingJob.php`
    - `WeeklyInsight.php`
- `routes/`
  - `api.php`
- `database/`
  - `migrations/`

### Python Worker

`python_app/`

- `app/`
  - `clients/`
    - `laravel_client.py`
    - `meta_clients.py`
  - `services/`
    - `scrapping_services.py`
    - `retry_services.py`
  - `workers/`
    - `job_worker.py`
  - `core/`
    - `config.py`
    - `logger.py`
    - `exceptions.py`
- `main.py`

### Root / Docker

`root/`

- `docker-compose.yml`
- `.env`
- `laravel_backend/`
- `python_app/`

---

## 4. API Contract

Semua endpoint berada di: `/api/internal/*`

| Method | Endpoint                       |
| :----- | :----------------------------- |
| GET    | `/internal/jobs/pending`       |
| GET    | `/internal/accounts/{id}`      |
| POST   | `/internal/insights`           |
| POST   | `/internal/jobs/{id}/success`  |
| POST   | `/internal/jobs/{id}/failed`   |
| POST   | `/internal/jobs/recover-stuck` |

---

## 5. Authentication

Header yang digunakan:
`X-INTERNAL-KEY: supersecretkey`
_Harus konsisten antara Laravel dan Python._

---

## 6. System Flow

1.  **Step 1: Get Pending Job** → `GET /api/internal/jobs/pending`
2.  **Step 2: Get Account Credential** → `GET /api/internal/accounts/{id}`
3.  **Step 3: Fetch Meta API** → `GET https://graph.facebook.com/v24.0/{ig_id}/insights`
4.  **Step 4: Transform Data** → Function: `aggregate_weekly()`
5.  **Step 5: Save Insights** → `POST /api/internal/insights`
6.  **Step 6: Mark Success** → `POST /api/internal/jobs/{id}/success`

---

## 7. Worker Flow

```python
loop:
    job = get_pending_job()
    if job empty:
        sleep
        continue

    credentials = get_account_credentials()
    data = fetch_meta()
    result = transform_data()

    save_insights(result)
    mark_success(job_id)
```

---

## 8. Database Schema

### scraping_jobs

| Field         | Type                                    |
| :------------ | :-------------------------------------- |
| id            | int                                     |
| account_id    | int                                     |
| status        | pending / processing / success / failed |
| attempt_count | int                                     |

### weekly_insights

| Field         | Type |
| :------------ | :--- |
| account_id    | int  |
| since_date    | date |
| until_date    | date |
| reach         | int  |
| profile_views | int  |

---

## 9. Docker Setup

Docker digunakan untuk menjalankan semua service dalam environment terisolasi. Docker Compose menghubungkan service Laravel, Python, dan database dalam satu network.

### docker-compose.yml (Simplified)

```yaml
version: "3.8"
services:
  laravel_backend:
    build: ./laravel_backend
    container_name: laravel_backend
    ports:
      - "9000:9000"
    volumes:
      - ./laravel_backend:/var/www
    depends_on:
      - db
  python_app:
    build: ./python_app
    container_name: python_app
    depends_on:
      - laravel_backend
  db:
    image: postgres:15
    container_name: postgres_db
    environment:
      POSTGRES_DB: iress
      POSTGRES_USER: user
      POSTGRES_PASSWORD: password
    volumes:
      - postgres_data:/var/lib/postgresql/data
volumes:
  postgres_data:
```

---

## 10. Dockerfile

### Laravel Dockerfile

```dockerfile
FROM php:8.2-fpm
WORKDIR /var/www
RUN apt-get update && apt-get install -y \
    git curl libpq-dev zip unzip
RUN docker-php-ext-install pdo pdo_pgsql
COPY . .
CMD ["php-fpm"]
```

### Python Worker Dockerfile

```dockerfile
FROM python:3.10-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .
CMD ["python", "main.py"]
```

---

## 11. Common Issues

- **Endpoint mismatch:** `/internal/insight` (wrong) vs `/internal/insights` (correct)
- **Missing ID:** `/internal/jobs/success` (wrong) vs `/internal/jobs/{id}/success` (correct)
- **Wrong method:** `GET /internal/insights` (wrong) vs `POST /internal/insights` (correct)
- **Header mismatch:** `INTERNAL_API_KEY` (wrong) vs `X-INTERNAL-KEY` (correct)
- **Non-existent endpoint:** `/internal/accounts/{id}/refresh` (not defined)

---

## 12. Debugging

- **Python:**
  - `print("METHOD:", method)`
  - `print("URL:", url)`
- **Laravel:**
  - `tail -f storage/logs/laravel.log`
- **Check route:**
  - `php artisan route:list`

---

## 13. Best Practices

- **Base URL:** `http://laravel_backend:9000/api`
- **Endpoint:** `/internal/...`
- **Jangan gunakan:** `/api/internal/...` (double prefix)

---

## 14. Conclusion

Sistem bekerja sebagai pipeline:
**Laravel (Job Queue) → Python Worker (Processing) → Meta API (Source) → Laravel (Store Result) → Database**

Kunci stabilitas:

- Endpoint harus konsisten
- Header harus sama
- Method harus sesuai
- Tidak memanggil endpoint yang tidak ada
