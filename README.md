# Portfolio — WordPress (Bedrock + Sage + Blade + Vue 3)

A production-ready WordPress project built on the Roots ecosystem: **Bedrock** for WordPress application structure, **Sage** as the theme (Blade templates + Vite build), and **Vue 3** used as progressive-enhancement "islands" for interactive UI. The primary rendering system is WordPress + Sage + Blade — Vue never takes over the page.

## Architecture

```
                        Docker Compose
                              │
              ┌───────────────┼────────────────┐
              │               │                │
           web (nginx)     app (PHP-FPM)     node (Vite)
              │               │                │
              └──────┬────────┘                │
                     │                         │
                  Bedrock  ──────────────────────
                     │
                   Sage (Acorn / Blade)
                     │
              ┌──────┴──────┐
              │             │
            Blade         Vue 3
        server render   interactive islands
```

- **Bedrock** manages WordPress core and PHP dependencies via Composer. WordPress is never committed to the repo — it's installed by `composer install`.
- **Sage** is the WordPress theme, built on **Acorn** (Laravel components in WordPress) with **Blade** templates.
- **Vue 3** (Composition API, SFCs) is mounted only on specific `[data-vue-component]` elements inside Blade-rendered HTML — search/filter widgets, counters, REST-powered UI. The rest of the site is plain server-rendered HTML.
- **Vite** compiles the theme's CSS/JS (and Vue SFCs) for both dev (HMR) and production.
- **Yarn** (pinned via Corepack) is the only frontend package manager.
- **Docker Compose** provides a reproducible local dev environment: nginx, PHP-FPM, MariaDB, and a Node/Yarn/Vite container.

## Technology stack

| Layer | Technology | Version installed |
|---|---|---|
| CMS | WordPress | 7.0.3 (via `roots/wordpress`, Composer-managed) |
| App structure | Roots Bedrock | latest (`main`, no tagged release) |
| Theme | Roots Sage | 11.2.1 |
| Backend framework | Roots Acorn (Laravel components) | ^6.0 (6.2.0 installed) |
| Templating | Blade | via Acorn |
| Frontend framework | Vue 3 (Composition API, SFCs) | ^3.5.0 |
| Build tool | Vite | ^8.0.0 (via `laravel-vite-plugin` + `@roots/vite-plugin`) |
| CSS | Tailwind CSS | ^4.0.0 |
| Package manager | Yarn (Berry, via Corepack) | 4.18.0, pinned in `package.json#packageManager` |
| Runtime | Node.js | ^20.19.0 \|\| >=22.12.0 (Docker image: 22 LTS) |
| Language | PHP | >=8.3 required by Bedrock/Sage; **Docker images use 8.4** (see [Warnings](#warnings--deviations)) |
| Database | MariaDB | 12 |
| Containers | Docker / Docker Compose | any recent Docker Engine + Compose v2 |

## Requirements

- Docker Desktop / Docker Engine with Compose v2 (`docker compose`, not `docker-compose`)
- PHP 8.3+ and [Composer](https://getcomposer.org/) if you want to run things outside Docker
- Node.js 22 LTS and [Corepack](https://nodejs.org/api/corepack.html) enabled (`corepack enable`) if working on the theme outside Docker

## Project structure

```
├── config/                      # Bedrock application config (env-driven)
├── docker/
│   ├── php/Dockerfile           # PHP-FPM dev image (Composer, WP-CLI, extensions)
│   ├── php/php.ini
│   ├── node/Dockerfile          # Node + Corepack/Yarn dev image
│   └── nginx/nginx.conf
├── web/
│   ├── index.php, wp-config.php
│   ├── wp/                      # WordPress core (Composer-managed, gitignored)
│   └── app/                     # wp-content equivalent
│       ├── mu-plugins/
│       ├── plugins/
│       ├── uploads/
│       └── themes/
│           └── sage/
│               ├── app/                       # PHP: ThemeServiceProvider, View Composers, setup.php, vue.php
│               ├── resources/
│               │   ├── css/app.css            # Tailwind entry
│               │   ├── js/
│               │   │   ├── app.js             # Vite/Vue entry point
│               │   │   ├── config.js          # reads #theme-config (restUrl, nonce, locale)
│               │   │   ├── vue/mount.js        # auto-mounts [data-vue-component] islands
│               │   │   └── components/vue/     # Example.vue, Search.vue
│               │   └── views/                 # Blade templates (front-page.blade.php, layouts/, partials/…)
│               ├── public/build/              # Vite output (gitignored)
│               ├── composer.json               # roots/acorn
│               ├── package.json                # yarn, vue, vite, tailwind
│               ├── vite.config.js
│               └── .yarnrc.yml, .node-version
├── composer.json                # roots/bedrock, roots/wordpress
├── docker-compose.yml           # dev stack: app, web, database, node
├── Makefile                     # shortcuts for the commands below
├── .env.example
└── .gitignore
```

## Docker architecture (development)

`docker-compose.yml` defines four services:

| Service | Purpose | Image / build | Port (host) |
|---|---|---|---|
| `web` | Nginx, serves `web/` and proxies PHP to `app` | `nginx:alpine` | `8754` → 80 |
| `app` | PHP-FPM 8.4, Composer, WP-CLI | `docker/php/Dockerfile` | *(internal only, 9000)* |
| `database` | MariaDB | `mariadb:12` | `3309` → 3306 |
| `node` | Vite dev server (HMR) + Yarn | `docker/node/Dockerfile` | `5173` → 5173 |

- `app` and `web` share the whole repo as a bind mount so PHP/Blade edits are live.
- `node` bind-mounts only `web/app/themes/sage`, with `node_modules` kept in a **named volume** (`sage_node_modules`) so the bind mount never hides installed dependencies.
- `database` uses a named volume (`db_data`) for persistence and is **not exposed publicly**.
- Both `app` and `web` have healthchecks; `web` waits on `app`, `app` waits on `database`.

## Environment variables

Copy `.env.example` to `.env` and fill in real values (`.env` is gitignored, never committed):

| Variable | Purpose |
|---|---|
| `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`, `DB_PREFIX` | Database connection (must match the `database` service) |
| `WP_ENV` | `development` / `staging` / `production` |
| `WP_HOME`, `WP_SITEURL` | Site URL and WordPress install path |
| `AUTH_KEY` … `NONCE_SALT` | WordPress security salts — generate real values, never keep `generateme` |
| `VITE_DOCKER`, `VITE_ORIGIN`, `VITE_HMR_HOST` | Tell Vite to bind `0.0.0.0` and fix the HMR client origin when running inside Docker |

**Never** put secrets behind a `VITE_*` variable — anything with that prefix is bundled into browser-side JavaScript by Vite. Only non-sensitive frontend config is exposed, and it's exposed through WordPress (`app/vue.php`, see [Vue integration](#vue-integration)), not through `VITE_*` env vars.

## Installation

```bash
cp .env.example .env
# then edit .env: set real DB credentials and generate real salts, e.g.
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"   # run 8x, one per *_KEY / *_SALT
```

## Starting Docker

```bash
docker compose up -d
docker compose ps
# or: make up / make ps
```

## WordPress setup

```bash
docker compose exec app composer install

# first time only — install WordPress itself
docker compose exec app wp core install \
  --url="http://localhost:8754" \
  --title="Portfolio" \
  --admin_user="admin" \
  --admin_password="<choose one>" \
  --admin_email="you@example.com"

docker compose exec app wp theme activate sage
```

Or with the shortcuts in the [Makefile](#makefile-shortcuts):

```bash
make composer-install
make wp-install WP_ADMIN_PASSWORD="<choose one>"
make theme-activate
```

Visit `http://localhost:8754`.

## Sage / Vue / Vite development

```bash
docker compose exec node yarn install
docker compose exec node yarn dev     # already the default container command
# or: make yarn-install / make dev
```

The `node` container runs `yarn dev` on start, listening on `0.0.0.0:5173` (via `VITE_DOCKER=true` in `vite.config.js`). Acorn's Vite integration (`@roots/vite-plugin`) detects the running dev server and swaps Blade's `@vite(...)` output to point at `http://localhost:5173` automatically — no manual toggling between dev/build.

For production **assets** (still deployed to shared hosting, see below):

```bash
docker compose exec node yarn build
# or: make vite-build
```

### HMR

Vite HMR works through Docker because:
- `vite.config.js` sets `server.host: '0.0.0.0'` and `server.watch.usePolling: true` when `VITE_DOCKER=true` (bind mounts don't emit native fs events reliably).
- `server.hmr.host` and `server.origin` are pinned to `localhost` / `http://localhost:5173` so the browser (outside Docker) reaches the WebSocket correctly, while the container itself listens on all interfaces.

Edit any file under `resources/`, save, and the browser updates without a full reload.

## Vue integration

Vue is mounted as independent "islands", not a SPA:

- `resources/js/vue/mount.js` scans the page for `[data-vue-component="name"]` elements and mounts the matching component from a small registry — multiple islands, same or different components, can coexist on one page.
- `resources/js/app.js` registers `example` → `Example.vue` and `search` → `Search.vue`, then calls `mountVueApps(...)`.
- Blade passes initial data via a `data-props` attribute, JSON-encoded with `wp_json_encode(..., JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)` (see `resources/views/front-page.blade.php`) so it's safe inside an HTML attribute regardless of quoting.
- Non-sensitive WordPress config (REST base URL, a `wp_rest` nonce, locale, current user ID) is exposed via a `<script type="application/json" id="theme-config">` tag printed on `wp_footer` by `app/vue.php`, and read client-side by `resources/js/config.js`. **No secrets, DB credentials, or salts are ever put in that tag.**
- `Search.vue` demonstrates a real REST call: it queries `wp/v2/posts?search=...` with the `X-WP-Nonce` header, with loading/error states.

## Makefile shortcuts

`make help` lists every target. These wrap the `docker compose exec ...` commands used throughout this README:

| Target | Equivalent |
|---|---|
| `make up` / `make down` / `make restart` / `make ps` | `docker compose up -d` / `down` / `restart` / `ps` |
| `make build` | `docker compose build` |
| `make install` | `composer install` (root + theme) + `yarn install` |
| `make composer-install` / `make theme-composer-install` | `composer install` at root / in `web/app/themes/sage` |
| `make yarn-install` | `yarn install` in the theme |
| `make dev` | `docker compose up -d node` (Vite dev server) |
| `make vite-build` | `yarn build` (production assets) |
| `make wp-install` | `wp core install` (override with `WP_ADMIN_PASSWORD=...`, etc.) |
| `make theme-activate` | `wp theme activate sage` |
| `make shell-app` / `make shell-node` | shell into the `app` / `node` container |
| `make db-shell` / `make db-export` | `wp db cli` / `wp db export` |
| `make logs s=node` | `docker compose logs -f node` |
| `make clean` | `docker compose down -v` — **drops the database volume** |

## Yarn commands

Run from `web/app/themes/sage/` (or via `docker compose exec node <cmd>`):

| Command | Purpose |
|---|---|
| `yarn install` | Install frontend dependencies |
| `yarn dev` | Start the Vite dev server with HMR |
| `yarn build` | Production build (`public/build/`) |
| `yarn translate` | Regenerate `.pot`/`.po` translation files |

There is no `yarn lint` / `yarn test` script — none is configured in this theme. Add one (e.g. ESLint, Vitest) only if the project actually needs it.

## Composer commands

| Command | Purpose |
|---|---|
| `docker compose exec app composer install` | Install/update root (Bedrock) PHP dependencies |
| `docker compose exec app composer install --working-dir=web/app/themes/sage` | Install the theme's own dependencies (Acorn, Pint) |
| `docker compose exec app composer lint` / `lint:fix` | Laravel Pint (root project) |
| `docker compose exec app composer test` | Pest (root project) |

## Database management

- Data persists in the `db_data` named volume.
- `docker compose exec app wp db ...` runs any `wp-cli` DB subcommand (export, query, etc.) against the `database` service.
- The database port is published to the host at `3309` (mapped to the container's `3306`) so a local GUI client (TablePlus, Sequel Ace, etc.) can connect directly — use `DB_HOST=127.0.0.1`, `DB_PORT=3309` from the host, or `database`/`3306` from inside another container.

## Production build & deployment (shared hosting)

This project is deployed to **shared hosting**, not via Docker in production — the Docker Compose setup here is dev-only. To ship a release:

```bash
# 1. PHP dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

cd web/app/themes/sage
composer install --no-dev --optimize-autoloader

# 2. Frontend assets
corepack yarn install --immutable
corepack yarn build
cd ../../../..

# 3. Upload everything except the excluded paths below to shared hosting
#    (rsync, SFTP, or your host's Git deploy):
#    - .git/, .env, node_modules/, tests/, docker/, docker-compose.yml
#    - web/app/themes/sage/node_modules/
```

On the host:

- Point the webserver's document root at `web/`.
- Create `.env` on the server with production `WP_ENV=production`, real DB credentials, and real salts (never reuse dev salts).
- Most shared hosts run PHP-FPM/CGI already, so no PHP-FPM container is needed — just make sure the host's PHP version is 8.3+ and has the `mysqli`/`pdo_mysql`, `intl`, and `zip` extensions enabled.
- `public/build/manifest.json` must exist (produced by `yarn build`) or Blade's `@vite(...)` directive will throw `Vite manifest not found`.

## Troubleshooting

- **`Vite manifest not found`**: run `yarn build` (or start `yarn dev` locally) inside `web/app/themes/sage`.
- **Blank page / 500 on shared hosting**: check `WP_DEBUG_LOG` path and PHP error log; verify `.env` exists and `DB_*` values are correct.
- **Node container exits immediately**: it runs `yarn dev` on start, which needs `node_modules` already installed in the `sage_node_modules` volume. Run `docker compose run --rm node yarn install` once, then `docker compose up -d node`.
- **HMR not updating in the browser**: confirm `VITE_DOCKER=true`, `VITE_ORIGIN`, and `VITE_HMR_HOST` are set for the `node` service and match the port you're browsing on.
- **`composer install` fails on PHP version**: see [Warnings](#warnings--deviations) below — the PHP runtime must be 8.4+, not just the documented 8.3 minimum.

## Security

- `.env` is gitignored; only `.env.example` (with placeholder values) is committed.
- WordPress salts are unique per environment — regenerate them for staging/production, don't copy from `.env.example` or from dev.
- The `database` service's port (`3309`) is only published for local developer convenience (GUI DB clients); do not publish database ports on any shared/production host.
- Vue islands never receive PHP secrets — only `restUrl`, a `wp_rest` nonce, `locale`, and (when logged in) the current user ID are exposed, via `app/vue.php`.
- All data passed from Blade to Vue is JSON-encoded with `JSON_HEX_*` flags before being placed in an HTML attribute, preventing attribute-breakout/XSS.
- REST requests from `Search.vue` send the `X-WP-Nonce` header so WordPress can validate the request came from a logged-in session when applicable; the endpoint used (`wp/v2/posts`) only ever returns already-public data.
- Least-privilege DB credentials: the `database` service creates a dedicated `wordpress` user/database, not root, for the application's `DB_USER`.

## Warnings / deviations

- **PHP 8.4, not 8.3, in the dev containers.** Bedrock and Sage both declare `"php": ">=8.3"`. However, `composer.lock` (resolved against Composer's `require-dev` chain — `pestphp/pest` ^4.0 pulling in `symfony/console` 8.1) requires PHP **8.4.1+** on some dev-only packages. Running `composer install` against a PHP 8.3 runtime fails. Since 8.3 is a floor, not a ceiling, `docker/php/Dockerfile` uses `php:8.4-fpm-alpine` — fully within what both projects support, and it's what the lockfile actually needs. If you need to target exactly PHP 8.3 (e.g. because your shared host is pinned to it), regenerate `composer.lock` with `composer update --with=php:8.3.*` or drop `pestphp/pest` from `require-dev`.
- **Sage 11 ships no Vue by default.** As of this Sage version, the build tool is Vite (replacing the older Bud-based Sage 10), and `package.json` has no frontend framework preinstalled. `vue` and `@vitejs/plugin-vue` were added explicitly; `@vitejs/plugin-vue@^6.0.0` was required (not `^5.x`) because Sage 11's `vite@^8.0.0` isn't in `@vitejs/plugin-vue@5`'s peer range.
- **Directory naming follows current Sage 11, not older Sage tutorials.** Assets live in `resources/js/` and `resources/css/` (not `resources/scripts/`/`resources/styles/`), and the theme has no `config/` directory of its own — configuration is PHP files under `app/`. The Vue-specific additions (`resources/js/components/vue/`, `resources/js/vue/mount.js`, `resources/js/config.js`, `app/vue.php`) are additions on top of the stock Sage 11 layout, not replacements.
- **Bedrock's real content path is `web/app/`, not a top-level `app/`.** There is no separate root-level `app/` directory alongside `web/` — Bedrock's `wp-content` equivalent (`mu-plugins/`, `plugins/`, `themes/`, `uploads/`) lives entirely under `web/app/`.
- **No production Docker image.** Production deploys to shared hosting (manual `composer install --no-dev` + `yarn build`, then upload), so this repo intentionally has no multi-stage production `Dockerfile` or `docker-compose.prod.yml` — `docker-compose.yml` here is dev-only.
- **Bedrock has no tagged release**; the installed version tracks the `roots/wordpress` core pin (`7.0.3`) and whatever `main` resolved to at install time (composer.lock is committed, so this is reproducible).
