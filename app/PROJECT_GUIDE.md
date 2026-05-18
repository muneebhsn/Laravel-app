# PitCrew API - Project Technical Guide

This document provides a comprehensive technical overview of the PitCrew API system, its architecture, and the logic behind its configuration.

---

## 1. System Architecture (The "How it Works")

This project is a **Containerized Full-Stack API**. It uses a 3-tier architecture to ensure isolation and scalability.

### A. The Networking Flow
1. **The User** requests a URL (e.g., `http://localhost:8080`).
2. **Nginx** (The Web Server) receives the request.
3. **The Logic**:
   - If the request is for the root `/`, Nginx serves `dashboard.html`.
   - If the request starts with `/api`, Nginx forwards it to **PHP-FPM** (Laravel).
   - Laravel then queries **Postgres** for data and returns a JSON response.

---

## 2. Component Breakdown

### Docker (The Container)
*   **`docker-compose.yml`**: Orchestrates two containers: `pitcrew_api` and `pitcrew_postgres`. It links them via a private network called `pitcrew`.
*   **`Dockerfile`**: A multi-stage build. 
    *   **Builder Stage**: Installs PHP extensions and Composer dependencies.
    *   **Development Stage**: Adds Nginx and creates a lightweight environment for coding.

### Nginx (The Traffic Cop)
*   **File**: `docker/nginx.conf`
*   **Key Role**: It acts as the "entry point." We specifically configured it with `index dashboard.html index.php;`. This ensures that when you open the "Web Preview," you see the UI immediately instead of a Laravel "404" error.

### Laravel (The Engine)
*   **File**: `routes/api.php`
*   **Role**: Handles all business logic. It has no "Web" routes because it is designed to be "Headless"—meaning it only sends data (JSON), not full web pages. The `RouteServiceProvider.php` ensures all these routes are prefixed with `/api`.

### PostgreSQL (The Memory)
*   **Role**: Stores customer and repair order data.
*   **Initialization**: The file `pitcrew_migration_test.sql` is automatically executed when the database is created, ensuring you have test data immediately.

---

## 3. Key Files Reference

| File Path | Technical Purpose |
| :--- | :--- |
| `app/Providers/RouteServiceProvider.php` | Configures Laravel to treat the app as an API only. |
| `public/dashboard.html` | The "Frontend." A static JS application that monitors the API. |
| `public/index.php` | The gateway for all PHP-based API requests. |
| `docker/nginx.conf` | Routes traffic between the Dashboard and the API. |
| `.env` | Stores sensitive configuration (Database credentials, App keys). |

---

## 4. Operational Commands

### Checking Logs
If something goes wrong, check the container logs:
```bash
docker logs -f pitcrew_api
```
*Note: Laravel is configured to log to "stderr," which means its errors appear directly in the docker logs.*

### Database Management
To wipe the database and start fresh with sample data:
```bash
docker exec -it pitcrew_api php artisan migrate:fresh --seed
```

### Checking Routes
To see all available API endpoints:
```bash
docker exec -it pitcrew_api php artisan route:list
```

---

## 5. Technical Post-Mortem & Troubleshooting History

This section captures the specific "Why" behind the fixes applied during the setup phase.

### A. The "404 Not Found" on Web Preview
*   **Symptom**: Accessing port 8080 returned a 404 error.
*   **Discovery**: The `public/dashboard.html` file existed, but the Nginx configuration (`docker/nginx.conf`) only looked for `index.php` by default. Since the request for `/` was passed to PHP, and Laravel has no route defined for `/` (only `/api`), Laravel returned a 404.
*   **Resolution**: Updated Nginx's `index` directive to: `index dashboard.html index.php;`.
*   **Knowledge Point**: Nginx now checks for the static dashboard *before* trying to send the request to the PHP engine.

### B. Docker Command Conflicts
*   **Symptom**: `docker-compose up` failed with a "Version unsupported" error.
*   **Reason**: The environment has both the older Python-based `docker-compose` (v1) and the modern Go-based `docker compose` (v2). The `docker-compose.yml` uses features that v1 doesn't understand.
*   **Resolution**: Always use the space-separated command: `docker compose up`.

### C. Missing Migration Output
*   **Symptom**: Running `php artisan migrate:fresh` seemed to produce no output.
*   **Reason**: This happens if the database is already in sync or if the command is run without an interactive TTY (`-it`).
*   **Verification**: Always use `php artisan migrate:status` to confirm the current state of the database schema.

---

## 6. Deep Dive: Routing & Data

### Laravel Routing Logic
Unlike standard Laravel apps, this project **completely ignores** `routes/web.php`. 
*   All logic is in `routes/api.php`.
*   The `RouteServiceProvider` applies the `api` middleware, which ensures responses are formatted for JSON and handles stateless authentication.

### Database Initialization
The PostgreSQL container is "Self-Seeding."
*   When the container starts for the first time, it looks into `/docker-entrypoint-initdb.d/`.
*   We mapped `pitcrew_migration_test.sql` to this folder.
*   This means you don't actually *need* to run migrations to get started; the SQL file builds the world for you.

---

## 7. Summary of System State
*   **Container Status**: Healthy (check with `docker ps`).
*   **Port Mapping**: `8080 -> 8080` (External -> Internal).
*   **Default Page**: `dashboard.html`.
*   **API Base**: `/api/v1/`.
