# Blog CMS

A multi-author blog/CMS built on Laravel 13. Public Blade front-end for readers, a
[Filament](https://filamentphp.com) v5 admin panel at `/admin` for staff, SQLite for storage.

## Stack

- **Laravel 13** with the Livewire starter kit (Fortify auth: register, login, email
  verification, 2FA, passkeys)
- **Filament v5** admin panel (`/admin`)
- **Livewire 4** for the one interactive piece on the public site (comments)
- **Laravel Sanctum** for the token-authenticated parts of the JSON API (`/api/v1`)
- **SQLite**
- **Tailwind CSS v4** + `@tailwindcss/typography` for rendered post content

## Getting started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build   # or `npm run dev` while working on front-end assets
php artisan serve
```

Then visit `http://localhost:8000`.

### Seeded accounts

All seeded users share the password `password`.

| Email | Role |
|---|---|
| `admin@example.com` | Admin |
| `editor@example.com` | Editor |
| *(random)* | Author &times;2 |
| `reader@example.com` | Reader |

The seeder also creates 5 categories, 10 tags, 30 posts (a mix of published, scheduled, and
draft), and 40 comments (mostly approved, some pending).

## Roles

Roles are a plain `role` enum column on `users` (`App\Enums\Role`), enforced through Laravel
policies (`app/Policies/`) — there's no permissions table; three roles beyond admin didn't
justify one.

| | Admin | Editor | Author | Reader |
|---|---|---|---|---|
| Manage users | ✅ | | | |
| Manage categories/tags | ✅ | ✅ | | |
| Create/edit own posts | ✅ | ✅ | ✅ | |
| Edit/delete any post | ✅ | ✅ | | |
| Moderate comments | ✅ | ✅ | | |
| Post a comment | ✅ | ✅ | ✅ | ✅ (verified email) |

Filament reads the same policies, so authorization is defined once and enforced identically in
the admin panel and the public site. A reader gets a 403 at `/admin` even with a valid session —
`User::canAccessPanel()` checks the role directly, and the panel also carries the `verified`
middleware as a second, independent gate.

## Publishing model

There's no `scheduled` status and no cron job flipping posts live. A post is publicly visible
when `status = published AND published_at <= now()` (`Post::scopePublished()`). A future
`published_at` on a published post *is* the schedule — Filament shows a "Scheduled" badge for
those. Every public-facing query goes through `scopePublished()`, so a draft or not-yet-due post
is a genuine 404, not just hidden from a listing.

## Content security

Post bodies are Markdown. On save, `App\Support\Markdown::toSafeHtml()` renders them once into
`posts.body_html` with `html_input: strip` (raw HTML in the source is discarded) and
`allow_unsafe_links: false` (no `javascript:`/`data:` hrefs). `body_html` is the only value in
the app ever echoed unescaped (`{!! !!}`). Comment bodies are plain text, always escaped.

## API

A read-only JSON API mirrors the public site at `/api/v1`, plus one authenticated write
endpoint for posting comments. All read endpoints are open — no auth, no verification — since
they only ever expose what `Post::scopePublished()` already makes public.

| Method | Endpoint | Notes |
|---|---|---|
| GET | `/api/v1/posts` | Paginated (`simplePaginate`), summary shape (no body) |
| GET | `/api/v1/posts/{slug}` | Full detail, including rendered `body_html` |
| GET | `/api/v1/posts/{slug}/comments` | Approved comments only |
| POST | `/api/v1/posts/{slug}/comments` | Requires a token + verified email; rate-limited |
| GET | `/api/v1/categories`, `/api/v1/categories/{slug}` | List / archive |
| GET | `/api/v1/tags`, `/api/v1/tags/{slug}` | List / archive |
| GET | `/api/v1/search?q=` | Same title/excerpt `LIKE` search as the web UI |
| POST | `/api/v1/auth/tokens` | Exchange email + password for a bearer token |
| DELETE | `/api/v1/auth/tokens` | Revoke the token used on the request |

Get a token and post a comment:

```bash
curl -X POST http://localhost:8000/api/v1/auth/tokens \
  -d "email=reader@example.com" -d "password=password" -d "device_name=cli"
# => {"token": "1|...", "token_type": "Bearer"}

curl -X POST http://localhost:8000/api/v1/posts/{slug}/comments \
  -H "Authorization: Bearer 1|..." -d "body=Nice post!"
# => 201, comment stored as "pending" — same moderation queue as the web form
```

Every endpoint that also exists on the public site reuses the same model scopes and policies
(`Post::scopePublished()`, `CommentPolicy::create`), so a draft/scheduled post 404s here exactly
as it does on the web, and an unverified user gets a 403 posting a comment here exactly as they
would in the Livewire form. Three rate limiters guard the API (`app/Providers/AppServiceProvider.php`):
`api` (60/min, all endpoints), `comments` (5/min per user, shared with the Livewire form), and
`api-tokens` (5/min per IP, guards token issuance against password-guessing).

## Tests

```bash
php artisan test
```

72 Pest feature tests cover: draft/scheduled posts 404ing publicly (web and API), panel access
by role, authors editing only their own posts, markdown sanitization, comment moderation
(pending until approved, web and API), token issuance/revocation, and a query-count assertion
proving the public feed eager-loads without N+1s.

## CI

`.github/workflows/tests.yml` runs on every push to `main` and every PR: installs PHP 8.3 +
Node 22, then `composer ci:check` — Pint, Larastan (level 7), and the full Pest suite.

## Project structure

```
app/Enums/                    Role, PostStatus, CommentStatus
app/Models/                    Post, Category, Tag, Comment, User
app/Policies/                  One policy per model, read by both Laravel, Filament, and the API
app/Filament/                  Admin panel resources, widgets
app/Http/Controllers/          Public site controllers (thin — one query + one view each)
app/Http/Controllers/Api/V1/   JSON API controllers (thin — one query + one Resource each)
app/Http/Resources/            API response shapes (PostResource, PostSummaryResource, ...)
app/Support/                   Markdown rendering, cached nav data (SiteNav)
resources/views/
  layouts/public.blade.php   Public site chrome
  posts/, categories/, tags/, search/
  components/⚡comments.blade.php   The comment form/list (Livewire single-file component)
routes/api.php                 /api/v1/* — versioned, prefixed in-file
```
