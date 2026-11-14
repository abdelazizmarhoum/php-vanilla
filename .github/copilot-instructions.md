# Project Brief — Team Workspace (Vanilla PHP + PostgreSQL)

## What we are building

A multi-tenant team workspace application — a "mini Notion-lite" — where users
create workspaces, invite teammates, and collaborate on documents with version
history, comments, activity feeds, and full-text search.

This is NOT a demo CRUD app. It is a real application with authentication,
authorization, role-based access, and features users actually ask for.

## Stack — hard constraints

Backend:
- PHP 8.2 or newer. Strict types. PSR-4 autoloading via Composer.
- PostgreSQL via PDO (pgsql driver). Prepared statements only.
- No frameworks. Do not use Laravel, Symfony, Slim, CodeIgniter, Yii,
  CakePHP, Laminas, or any MVC framework.
- No ORMs. No Doctrine, no Eloquent, no Cycle, no RedBean.
- Composer is allowed ONLY for: autoloading, dotenv, and small utility
  libraries (e.g. ramsey/uuid if needed). Nothing that provides routing,
  templating, or database abstraction.

Frontend:
- Server-rendered HTML from plain PHP view files.
- Vanilla JavaScript only. No React, Vue, Svelte, Angular, Alpine, htmx,
  jQuery, Stimulus, Livewire, Inertia, or any SPA framework.
- Bootstrap 5 via CDN for UI. Tailwind is also acceptable but pick one;
  do not mix both.
- Markdown rendering via the `marked` library loaded from CDN. No other
  frontend libraries unless explicitly approved.
- No build tools. No webpack, vite, esbuild, rollup, or npm scripts.
  What is written is what ships.

## Architecture — one shape, always

Request lifecycle:
1. All traffic enters through `public/index.php` (front controller).
2. `src/bootstrap.php` loads config, starts the session, initialises the
   database, builds the router, and dispatches.
3. The router matches method + path, applies middleware, and calls a
   controller method.
4. The controller reads input via a Request object, calls Model classes
   for data, and returns via a Response object or renders a View.
5. Models are plain classes with static methods that execute SQL through
   a single PDO-based Database helper.

Directory layout (do not invent new top-level directories):
- `public/` — the only web-exposed folder. Front controller + assets.
- `src/Core/` — Router, Request, Response, Session, CSRF, View,
  Validator, Database, RateLimiter, Middleware.
- `src/Auth/` — Auth, PasswordReset, EmailVerification, RememberMe.
- `src/Controllers/` — one class per resource.
- `src/Models/` — one class per table or aggregate.
- `src/Middleware/` — AuthMiddleware, WorkspaceMiddleware, CsrfMiddleware.
- `src/routes.php` — all route registrations in one file.
- `views/` — plain PHP templates. `layouts/`, `partials/`, and one
  folder per feature area.
- `migrations/` — numbered `.sql` files. No migration framework.
- `config/` — `config.php` returning an array; secrets from env.
- `storage/` — logs and any ephemeral files. Never web-accessible.
- `migrate.php` — a CLI script that applies migration files in order.

## Request, Response, Middleware — pinned contracts

- Controllers receive a Request object and must return through a
  Response object or a View render. Never `echo` from a controller.
- Request exposes: method, path, params (route parameters), query,
  body, headers, current user, current workspace, current membership.
- Response exposes: `json($data, $status)`, `redirect($url, $status)`,
  `html($string, $status)`, `setHeader($k, $v)`, `setStatus($code)`.
- Middleware signature: receives Request and a `$next` callable.
  Returns Response. Registers as arrays of class names on routes.
- Route definition looks like: method, path with `{param}` placeholders,
  controller `[Class, method]`, and an array of middleware class names.
  Do not invent alternative route syntaxes.

## Authentication — the required behaviours

- Registration: email, name, password. Password hashed with
  `password_hash` using Argon2id if available, otherwise PASSWORD_DEFAULT.
- Email verification: token generated with `random_bytes(32)`, stored
  hashed with sha256, expires in 24 hours, single use. Emails are
  written to `storage/logs/mail.log` — no SMTP in this project.
- Login: verify password, regenerate session id, record the attempt.
- Rate limiting: failed logins counted per (email, IP) in the last
  15 minutes. Lock out after 5 failures. Data lives in `login_attempts`.
- Remember me: persistent cookie in `selector:validator` form. Store
  the selector and a sha256 hash of the validator. Rotate validator on
  every use. Expire after 30 days.
- Password reset: token generated, stored hashed, expires in 1 hour,
  single use. Reset page requires a valid token and CSRF.
- Logout: destroy session, delete remember-me token, rotate session id.

## Authorization — the required behaviours

- Every workspace action is scoped to a workspace and a membership.
- Roles are an ordered enum: owner > admin > member > viewer.
- A role grants every capability of the roles below it.
- Viewers may read. Members may read and write documents and comments.
  Admins may also invite, remove members, and change roles below owner.
  Owners may do everything, including deleting the workspace.
- `WorkspaceMiddleware` resolves the workspace from the route parameter,
  loads the membership for the current user, and attaches both to the
  Request. It rejects with 403 if the user is not a member, or if the
  action's required role is not satisfied.
- Every SQL query that reads or writes workspace-scoped data filters
  by `workspace_id`. Never trust the client to send a valid scope.

## Invitations

- Invited by email + role. Token stored hashed, expires in 7 days,
  single use, logged to mail.log.
- Accepting an invitation requires the invited user to be logged in
  with the matching email (existing account) or to register first
  (auto-verified, since the token proves email ownership).
- Invitations may be revoked before acceptance.

## Documents

- Belong to a workspace. Author, title, content (markdown source),
  timestamps.
- Saving a document creates a row in `document_versions` capturing
  title and content at that moment. Autosave is debounced on the
  client; the server treats every save as a version.
- History page lists versions with editor and timestamp; a version
  can be previewed and restored (restore creates a new version, never
  overwrites history).
- Full-text search uses the generated `search_vector` column on
  `documents` with a GIN index and `websearch_to_tsquery`. Search is
  scoped to the workspaces the current user belongs to.

## Comments

- Attached to a document. Support one level of replies (parent_id).
- Author is always the current user. No anonymous comments.
- Comments are rendered server-side; new comments are submitted via
  fetch and the page refreshes the comment list.

## Activity feed

- Every meaningful write (document created/updated/restored, member
  invited/joined/removed, workspace renamed, comment added) appends
  a row to `activity_log` with action, subject, actor, and optional
  JSONB metadata.
- The workspace page shows a reverse-chronological feed. Client polls
  for new entries every few seconds while the tab is visible.

## Security rules — non-negotiable

- All SQL uses PDO prepared statements. Never concatenate user input
  into SQL. Never interpolate into identifiers.
- All HTML output of user data passes through an `e()` helper that
  calls `htmlspecialchars($s, ENT_QUOTES, 'UTF-8')`. No exceptions.
- Every state-changing request (POST, PUT, PATCH, DELETE) requires a
  valid CSRF token. Tokens are compared with `hash_equals`. JS clients
  read the token from a `<meta name="csrf-token">` tag and send it in
  an `X-CSRF-Token` header.
- All auth-related tokens (email verification, password reset,
  invitations, remember-me validators) are stored hashed. Raw tokens
  only ever exist in the URL or cookie sent to the user.
- Session cookies set `HttpOnly`, `SameSite=Lax`, and `Secure` when
  the site runs under HTTPS. Session ID is regenerated on login and
  privilege changes.
- Passwords are never logged. Emails and passwords are never
  interpolated into error messages returned to the client.
- Errors return generic messages to the client; details go to
  `storage/logs/app.log`.

## Frontend rules

- All UI is served by PHP views. JS enhances; it never replaces
  server rendering.
- One JS file (`public/assets/js/app.js`) is the entire client code.
  Keep it under roughly 500 lines, organised into small functions.
  No classes unless truly warranted. No bundler.
- A single `api(method, url, body)` helper wraps `fetch`, injects the
  CSRF header, parses JSON, and throws on non-2xx responses.
- Debounce user-input-driven requests (autosave, search) at ~500 to
  800 milliseconds.
- Polling for activity and comments is only active while the tab is
  visible (`document.visibilityState`).
- Use Bootstrap components (toasts, modals, dropdowns) instead of
  building custom equivalents.
- Forms are progressive: without JS, the same form submits and the
  controller redirects. With JS, fetch intercepts and updates the UI.

## Style and quality

- `declare(strict_types=1)` at the top of every PHP file.
- Type-hint everything: parameters, returns, properties. Use union
  types and readonly properties when they clarify intent.
- Classes are final unless designed to be extended.
- No global helper functions except `e()`, `config()`, and `url()`.
  Those three are enough.
- No facades, no service containers, no dependency injection
  autowiring. Construct what you need. Pass what you need.
- One controller method = one HTTP action. Small, obvious, boring.
- Models hold SQL. Controllers hold orchestration. Views hold
  presentation. Never leak SQL into controllers, HTML into models,
  or business rules into views.
- Prefer early returns and guard clauses over nested conditionals.
- Log meaningful events; do not log noise.

## What NOT to do — Copilot, read this twice

- Do not suggest installing a framework, an ORM, a templating engine,
  a router library, a validation library, an authentication library,
  or a session library. We do not need them; we are building them
  ourselves, deliberately, in a small number of well-understood files.
- Do not generate "helpers" that duplicate existing ones. If `e()`
  exists, use `e()`. If `api()` exists in JS, use `api()`.
- Do not invent new file paths or directory names. If the current
  layout is missing something, ask before creating it.
- Do not create a `vendor`-style abstraction layer around PDO. Use
  PDO directly through the Database helper.
- Do not add a `_construct` to Models, or make Models stateful. Models
  are static function collections over SQL.
- Do not use PHP attributes, annotations, reflection, or magic
  `__call`/`__get`. Explicit code only.
- Do not use `eval`, dynamic class names from user input, or any
  `include`/`require` of a path derived from request data.
- Do not use inline event handlers in HTML (`onclick=`). Attach
  listeners in `app.js`.
- Do not add npm, yarn, pnpm, or any JS package manager. Frontend
  dependencies come from CDN with pinned versions.

## Definition of done for a feature

A feature is complete when:
1. Database schema is in a numbered migration.
2. Model methods exist for every query the feature needs.
3. Controller methods are wired in `routes.php` with the correct
   middleware (`AuthMiddleware`, `WorkspaceMiddleware`, `CsrfMiddleware`).
4. Views escape all user data with `e()`.
5. CSRF is enforced on every state-changing endpoint.
6. Role checks reject unauthorised users before any write happens.
7. Activity log entries are emitted for meaningful changes.
8. Errors flow through a single handler and produce a sensible
   user-facing message plus a log entry.

## Tone for suggestions

When suggesting code:
- Prefer the boring solution. Prefer three clear functions over one
  clever one.
- Explain *why* in one sentence if the choice is non-obvious. Do not
  narrate obvious code.
- If a suggestion would require touching more than two files, say so
  first and sketch the files before writing code.
- If something is genuinely a bad idea in this stack, say it plainly
  instead of generating it.

The goal of this project is a working, secure, maintainable
application built entirely from PHP, PostgreSQL, HTML, CSS, and
JavaScript — with every line explainable by the person who wrote it.