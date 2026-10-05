# Self-hosting RepSilog on a Raspberry Pi

Runs the app on a Pi 4B, reachable only from your Tailscale tailnet. Nothing is
exposed to the internet and no ports are forwarded on your router. Same
pattern as [lettuce-eat](https://github.com/damoclescj/lettuce-eat) on the same
Pi, just on its own port so the two don't collide.

---

## What you end up with

```
your laptop / phone                    Raspberry Pi 4B
─────────────────────                  ─────────────────────────────────────
  Tailscale client                       tailscale serve  (TLS terminates here)
         │                                        │
         │  encrypted over your tailnet           │  plain HTTP, loopback only
         └───────────────────────────────────────►│
                                                  ▼
                                          127.0.0.1:8081
                                                  │
                                                  ▼
                                       Docker: repsilog
                                       FrankenPHP (Caddy + PHP)
                                                  │
                                                  ▼
                                       SQLite file on a bind mount
```

One container. No nginx, no php-fpm, no database server, no queue worker.
Port 8081 and tailscale-serve port 8444, not 8080/8443 — those already belong
to lettuce-eat on this Pi.

### Why these choices

**SQLite.** The app already defaults to it, and sessions, cache, and queue all
live in the database. For a single user that removes an entire container from
the stack. Set to WAL mode so reads don't block behind writes.

**One container, FrankenPHP.** FrankenPHP is Caddy with PHP embedded, so the
usual nginx + php-fpm pair collapses into a single process with no socket
wiring between them.

**No queue worker or scheduler.** There is no `app/Jobs` directory and nothing
dispatches jobs or schedules tasks. If that changes, this doc needs a worker.

**`tailscale serve`, never `tailscale funnel`.** `serve` publishes to your
tailnet only. `funnel` publishes to the public internet — that is the one
command to avoid here.

---

## Prerequisites

Same as lettuce-eat's — Docker and Tailscale are already installed on this
Pi. If starting from scratch:

**1. Confirm the Pi is running 64-bit OS.**

```bash
uname -m
```

Must print `aarch64`.

**2. Install Docker.**

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker "$USER"
```

**3. Install Tailscale and join your tailnet.**

```bash
curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up
```

**4. In the Tailscale admin console**, enable **MagicDNS** and **HTTPS
Certificates** under DNS settings.

**5. Give the Pi read access to the repo** (skip if the repo is public) via a
read-only deploy key, same as lettuce-eat's setup. See that app's deployment
doc for the full walkthrough if you need it set up from scratch.

---

## Deploy

```bash
git clone git@github.com:ubemacapuno/repsilog.git
cd repsilog

mkdir -p data/database data/storage

cp .env.production.example .env.production
```

Generate an app key and put it in `.env.production`:

```bash
echo "APP_KEY=base64:$(openssl rand -base64 32)"
```

Then edit `.env.production` and set:

- `APP_KEY` — the line you just generated
- `APP_URL` — your Tailscale hostname, e.g. `https://ube-pi.tail908b50.ts.net:8444`
  (note the `:8444` — this app isn't on the tailnet's default `:443`)

Build and start:

```bash
docker compose up -d --build
```

First build takes a while on a Pi 4B — it compiles the Svelte frontend
(including generating Wayfinder's typed routes, which needs PHP 8.4 available
during the Node build stage too) and installs PHP dependencies. Subsequent
builds are much faster thanks to layer caching.

Check it came up:

```bash
docker compose ps          # should show "healthy" after ~40s
curl -fsS localhost:8081/up
```

Migrations run automatically on every container start, so there is no separate
migrate step.

---

## Publish it to your tailnet

```bash
sudo tailscale serve --bg --https=8444 http://127.0.0.1:8081
sudo tailscale serve status
```

That's it. The app is now at `https://ube-pi.tail908b50.ts.net:8444` with a
real Let's Encrypt certificate, reachable from any device signed into your
tailnet and from nowhere else.

The container is bound to `127.0.0.1:8081` in `compose.yaml`, so it is not
reachable from your LAN either — only through Tailscale.

To stop publishing:

```bash
sudo tailscale serve --https=8444 off
```

---

## Create your account, then close the door

Visit `https://ube-pi.tail908b50.ts.net:8444/register` and create your user
(the demo seeder's `test@example.com` / `password` account only exists if you
ran `php artisan db:seed` — skip that in production).

Once you've registered, disable registration by commenting out
`Features::registration()` in `config/fortify.php`'s features array, commit,
and redeploy. Only your tailnet can reach the app, but there's no reason to
leave signup open.

---

## Updating

```bash
cd repsilog
./update.sh
```

Or manually:

```bash
git pull
docker compose up -d --build
```

Migrations run on boot. Your database and storage survive because they live in
`./data`, outside the image.

---

## Backups

The entire application state is one SQLite file. Back it up with SQLite's own
command — **never `cp`**, which can capture a torn file while a write is in
flight:

```bash
mkdir -p ~/backups
docker compose exec app sqlite3 /var/lib/repsilog/database.sqlite \
    ".backup '/var/lib/repsilog/backup.sqlite'"
mv data/database/backup.sqlite ~/backups/repsilog-$(date +%F).sqlite
```

As a nightly cron (`crontab -e`):

```cron
0 3 * * * cd /home/damoclescj/apps/repsilog && docker compose exec -T app sqlite3 /var/lib/repsilog/database.sqlite ".backup '/var/lib/repsilog/backup.sqlite'" && mv data/database/backup.sqlite /home/damoclescj/backups/repsilog-$(date +\%F).sqlite
```

Copy those off the Pi periodically. A backup that only exists on the machine
that might die is not a backup.

---

## Troubleshooting

**Redirect loop on login, or CSS loading over http on an https page.**
`APP_URL` in `.env.production` doesn't match your Tailscale hostname
(including the `:8444` port), or `bootstrap/app.php` is missing
`$middleware->trustProxies(at: '*')`. Fix it and `docker compose restart app`.

**Build fails with `RolldownError ... php: not found` or a "Please provide a
valid cache path" error from Wayfinder.** Wayfinder's Vite plugin shells out
to `php artisan wayfinder:generate` during the frontend build, so the Node
build stage needs a working PHP 8.4 CLI (installed from Sury's APT repo in the
Dockerfile, since Debian bookworm's own packages only go up to PHP 8.2) and
`storage/framework/{views,sessions,cache/data}` to already exist — those
directories don't exist in a fresh checkout and are normally created by
`docker/entrypoint.sh` at container boot, which is too late for a build-time
step. The Dockerfile's `assets` stage creates them explicitly before `npm run
build` runs.

**500 error on first request.** Almost always a missing or malformed `APP_KEY`.
Check `docker compose logs app`.

**`attempt to write a readonly database`.** The `data/` directories are owned by
the wrong user:

```bash
sudo chown -R "$USER:$USER" data
docker compose restart app
```

**Port already in use.** lettuce-eat owns `127.0.0.1:8080` and tailscale-serve
port `8443` on this Pi — this app uses `8081` / `8444` instead. If you see a
bind conflict, check `docker ps` and `sudo tailscale serve status` for what's
already claimed.

**`tailscale serve` says it can't get a certificate.** MagicDNS and HTTPS
Certificates both need to be enabled in the admin console DNS settings.

**Logs.**

```bash
docker compose logs -f app
```
