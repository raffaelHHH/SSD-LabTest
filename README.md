# SSD Test

Docker Compose stack with three parts: a WordPress site behind an HTTPS
reverse proxy, a self-hosted Gitea git server, and a small PHP
password-policy demo app for the SSD coursework assignment.

All commands below assume Docker Desktop is running and are run from
`wordpress-docker/`.

```
docker compose up -d
```

## Services & access points

| Service        | URL / Port                                | Notes |
|----------------|--------------------------------------------|-------|
| WordPress      | https://127.0.0.1/                        | Served through the nginx HTTPS proxy. Also reachable directly (bypassing the proxy) at http://localhost:8080 |
| Password app   | https://127.0.0.1/passwordapp/            | Also reachable directly at http://localhost:8081 |
| Gitea          | http://127.0.0.1:3001/                    | Git over SSH on port 2222 |
| MySQL (WordPress) | internal only (`db`, port 3306)        | Not exposed to the host |
| MySQL (password app) | internal only (`passwordapp-db`, port 3306) | Not exposed to the host |

nginx (`nginx/nginx.conf`) terminates TLS on 80/443 with a self-signed cert
(`nginx/certs/`) and reverse-proxies `/` to WordPress and `/passwordapp/`
to the password app, stripping the prefix before forwarding. Browsers will
show a certificate-trust warning for the self-signed cert — expected for
local dev.

## Credentials

All credentials/ports are defined in `wordpress-docker/.env` (not meant for
production use — passwords are dev-only placeholders except where noted).

- **WordPress admin**: `WORDPRESS_ADMIN_USER` / `WORDPRESS_ADMIN_PASSWORD` in `.env`
- **Gitea login account** (web UI + git push auth): username `Raffael-Davin-Harjanto`, password set during setup (see [Gitea](#gitea) below) — not stored in `.env`
- **Gitea container's OS-level git identity** (separate from the login account above; used if you `docker exec` into the `gitea` container and run `git` commands as the `git` user): written to `~git/.gitconfig` on startup by the `gitea-init` service, from `GIT_USER_NAME` / `GIT_USER_EMAIL` in `.env`
- **Password app**: no login credentials to know in advance — create an account via the registration page (see below)

## Password-policy demo app (`password-app/`)

Built for the SSD assignment: a registration form that enforces password
requirements from **OWASP Proactive Controls 2024, C7 (Secure Digital
Identities) → Level 1: Passwords**.

- **Home page** (`src/index.php`) — login form (username/password) + link to registration.
- **Register page** (`src/register.php`) — create an account. On submit:
  - fails validation → redirected back to the home page with an error message
  - passes validation → account is created and the welcome page is shown
- **Password checks** — deliberately *no* composition rules (no forced mix of
  upper/lower/digit/symbol), per OWASP/NIST guidance against them. Instead:
  - length between 8 and 64 characters
  - rejected if it appears in the `common_passwords` blocklist table
  - **Frontend** (`src/assets/password-check.js`): length check only, for UX. Never trusted as the security boundary.
  - **Backend** (`src/includes/password_policy.php`): length + blocklist check — the authoritative check.
- **Welcome page** (`src/welcome.php`) — shows the username and password in plaintext, plus a logout link. This is intentional per the assignment spec (the exercise is about password-strength validation, not secure storage/transmission) — not a pattern to reuse elsewhere.
- **Login** (`src/login.php`) — only confirms the username exists. Passwords are never stored (assignment requirement), so there's nothing to authenticate against; a real login would need a separately-scoped, securely-hashed credential store.
- **No MFA** — out of scope per the assignment.
- **Database** (`password-app/db/init/`): dedicated MySQL instance (`passwordapp-db`), auto-seeded on first boot via `docker-entrypoint-initdb.d`:
  - `common_passwords` — ~97.7k rows seeded from SecLists' [`100k-most-used-passwords-NCSC.txt`](https://github.com/danielmiessler/SecLists/blob/master/Passwords/Common-Credentials/100k-most-used-passwords-NCSC.txt)
  - `2402294` (table name = student ID) — logs only `username` + `created_at` for every account created, never the password

Runs on `php:8.2-apache` directly (`password-app/Dockerfile` adds the
`pdo_mysql` extension) rather than a separate nginx + php-fpm pair.

## CI (GitHub Actions)

`.github/workflows/ci.yml` runs on every push/PR, with two jobs:

- **`dependency-check`** — runs [OWASP Dependency-Check](https://github.com/dependency-check/Dependency-Check_Action) over the repo and uploads the HTML report as a build artifact.
- **`integration-and-ui-tests`** — builds and starts just `passwordapp` + `passwordapp-db`, then runs two suites over plain HTTP against the live containers:
  - **Integration tests** (`password-app/tests/integration.sh`) — curl-driven, covering the PHP app + MySQL round trip: common-password rejection, minimum-length rejection, successful registration + welcome page, and login recognising an existing user.
  - **UI tests** (`password-app/tests/ui/register.spec.js`, [Playwright](https://playwright.dev/)) — drives an actual headless browser against the home, register, and welcome pages.

Run the same suites locally:

```
# integration tests (app must already be running, e.g. docker compose up -d passwordapp-db passwordapp)
bash wordpress-docker/password-app/tests/integration.sh

# UI tests
npm install
npx playwright install --with-deps chromium
npx playwright test
```

## Gitea

Self-hosted git server (`gitea` + one-shot `gitea-init` service). Uses
SQLite (no extra DB container) with the install wizard pre-skipped via
`GITEA__security__INSTALL_LOCK`. The account was set up manually after
first boot (Gitea's own admin account isn't created automatically):

```
docker compose exec -u git gitea gitea admin user create \
  --username <user> --password <pass> --email <email> --admin
```

Username/email/password can be changed via Gitea's admin UI
(`Site Administration → User Accounts`) or, for email/password only, via
the `PATCH /api/v1/admin/users/{username}` API — note that endpoint does
**not** rename the actual account username, only a secondary
`login_name` metadata field.

This repo's remote is the `ssd-test` repo on that Gitea instance.

## Notes / gotchas hit during setup

- The original `wpcli` service's install command used a YAML folded
  scalar (`>`) wrapped in stray literal quotes, which silently stripped
  the shell command's line-continuation backslashes and broke the
  automated WordPress install. Fixed by switching to a literal block
  scalar (`|`).
- Gitea's docker image already runs its own OpenSSH daemon on port 22;
  additionally enabling `GITEA__server__START_SSH_SERVER` makes Gitea's
  built-in SSH server try to bind the same port and crash-loop. Left
  disabled (default).
- `nginx/certs/*.key` is gitignored — the private key isn't committed,
  only the cert. Regenerate with `openssl req -x509 -nodes -days 825
  -newkey rsa:2048 -keyout selfsigned.key -out selfsigned.crt -subj
  "/CN=127.0.0.1" -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"`
  if missing.
