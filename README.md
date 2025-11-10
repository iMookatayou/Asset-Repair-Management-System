<p align="center">
  <img src="imagesREADME/PPK.png" width="200" alt="PPK Logo">
</p>

## Run with Docker (local)

Quick start:

1) Copy env and set app key (inside container after up)

2) Start stack

3) Generate key and migrate

4) Open app and Vite

Commands (PowerShell):

```powershell
# 0) Prepare env (first time)
Copy-Item .env.docker.example .env -Force

# 1) Start containers
docker compose up -d

# 2) App key and DB migrations (first time)
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# Optional: run tests
docker compose exec app php artisan test -q

#Request Me if you want .env.docker.example 
```

URLs:
- App: http://localhost:8080
- Vite Dev (hot reload): http://localhost:5173
- DB: host=localhost port=3307 db=arm user=arm pass=arm

Services:
- app (php-fpm), web (nginx), node (vite), db (mariadb), redis

Notes:
- Change ports in `docker-compose.yml` if they conflict locally.
- For production build, run `npm run build` in the node container or GitHub Actions and serve `public/build`.
 - A root `Dockerfile` is provided for building a production PHP-FPM image (multi-stage). The compose stack uses `./.docker/Dockerfile` for local dev.
## Run locally without Redis (Windows/macOS/Linux)

Redis is optional. The default `.env` already uses database + file drivers so the app runs even if the PHP Redis extension is missing.

Minimal `.env` (excerpt) for a Redis-free setup:

```env
CACHE_STORE=database        # database cache + rate limiter
SESSION_DRIVER=file         # file-based sessions
QUEUE_CONNECTION=database   # uses jobs table (run migrations) OR set sync
# QUEUE_CONNECTION=sync

# These can be commented out if you prefer (unused unless you switch drivers)
# REDIS_CLIENT=phpredis
# REDIS_HOST=127.0.0.1
# REDIS_PORT=6379
```

Details:
- Health endpoint skips Redis when the extension/class isn’t present.
- Rate limiting (login, etc.) uses the configured cache store; with `database` it writes to the `cache` table.
- To enable Redis later: install server + PHP extension, then set `CACHE_STORE=redis` and (optionally) `QUEUE_CONNECTION=redis`.
- If you keep `QUEUE_CONNECTION=database`, run a worker: `php artisan queue:work`.


## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
