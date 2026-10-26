# ServerDeck Hub

A read-only, opt-in overview of several [ServerDeck](https://github.com/onjoakimsmind/serverdeck)
hosts in one place. See [docs/design/hub.md](https://github.com/onjoakimsmind/serverdeck/blob/main/docs/design/hub.md)
in the main repository for the full design and the reasoning behind it.

**What this is:** a host running ServerDeck can optionally send a small,
fixed status snapshot here every few minutes — CPU/memory/disk, workload
counts, a health-check summary, pending updates. The Hub lists enrolled
hosts and their latest snapshot.

**What this is not:** a control plane. There is no path, anywhere in this
application, for the Hub to send anything back to a host or to act on one.
A host that stops reporting just goes stale here; nothing about that
affects the host itself.

## Stack

Laravel + Inertia + Vue + Tailwind, SQLite by default — the same stack as
ServerDeck itself, reusing its design tokens (`resources/css/app.css`) for
a consistent look.

## Running it

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan hub:create-admin you@example.com "Your Name"
composer run dev
```

There is no public registration — accounts are created with
`php artisan hub:create-admin <email> <name>`.

## Enrolling a host

1. Sign in to the Hub and click **Enroll a host**, giving it a name.
2. Copy the Hub URL and the enrollment token shown — the token is shown
   exactly once and cannot be retrieved again.
3. On the ServerDeck host, go to **Settings → Hub reporting** and paste
   both values in.

The host starts reporting on its own schedule (every few minutes by
default). Remove a host from the Hub at any time to revoke its token.

## API

`POST /api/ingest` — the only write path into this application. Requires
`Authorization: Bearer <enrollment token>`. See
[`app/Http/Controllers/Api/IngestController.php`](app/Http/Controllers/Api/IngestController.php)
for the exact payload shape and validation rules.

## Tests

```sh
php artisan test
vendor/bin/pint
```

## Deployment

```sh
docker build -t serverdeck-hub .
docker run -d -p 8080:8080 \
    -e APP_URL=https://hub.example.com \
    -v serverdeck-hub-data:/app/database \
    serverdeck-hub
```

Then run `php artisan hub:create-admin` once inside the container
(`docker exec -it <container> php artisan hub:create-admin you@example.com "Your Name"`).

Real environment variables passed to `docker run -e` always take priority
over the `.env` baked into the image at build time, so set `APP_URL` (and
anything else you want to override) that way rather than rebuilding.

This image uses `php artisan serve`, which is fine for the self-hosted,
single-admin scale this is designed for. If traffic ever justifies it, put
a real application server (php-fpm + nginx, FrankenPHP, etc.) in front
instead — nothing else in this app assumes `artisan serve` specifically.

