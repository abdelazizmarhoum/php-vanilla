# Team Workspace

A lightweight multi-tenant workspace application inspired by Notion.

## Stack

- PHP 8.2+
- PostgreSQL with PDO
- Vanilla JavaScript
- Bootstrap 5 and marked via CDN
- CommonMark for safe server-side Markdown
- No framework or ORM

## Features

Authentication, email verification, password reset, remember-me sessions,
workspaces and roles, invitations, documents and version history, comments,
activity feeds, full-text search, Markdown rendering, CSRF protection, and
login rate limiting.

## Local development

Requirements: PHP 8.2+, PostgreSQL, Composer, and the required PHP PDO
extensions.

1. Run `composer install`.
2. Copy `.env.example` to `.env` and fill in database credentials.
3. Create the PostgreSQL database and user.
4. Run `php migrate.php`.
5. Start the server with `php -S localhost:8000 -t public`.
6. Visit <http://localhost:8000>.

For demo data, run `php bin/seed.php`. The demo login is
`demo@example.com` / `demo1234`.

## Testing and maintenance

Run `php bin/cleanup.php` periodically to remove expired authentication
tokens and old login-attempt records.

## Project structure

- `public/` — front controller and browser assets
- `src/Core/` — application primitives
- `src/Auth/` — authentication and security services
- `src/Controllers/` — HTTP controllers
- `src/Models/` — PostgreSQL data access
- `src/Middleware/` — request authorization
- `views/` — PHP views and layouts
- `migrations/` — ordered database migrations
- `config/` — environment-backed configuration
- `storage/logs/` — local application and mail logs

## Design decisions

- No framework or ORM: the application stays explicit and easy to inspect.
- Tokens are stored as SHA-256 hashes so database disclosure does not expose
  usable credentials.
- CSRF protects every state-changing request.
- Workspace-scoped queries and middleware prevent cross-tenant access.
- Server-side Markdown escapes embedded HTML before rendering.

Personal project — no license.
