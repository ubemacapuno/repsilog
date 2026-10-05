# RepSilog

A self-hosted workout log. Log a session, add the exercise movements you did, and record
every set. The plan is to run it on my Raspberry Pi 4B behind [Tailscale](https://tailscale.com/), so nothing is exposed to the public internet.

The name is a reference to [silog](https://en.wikipedia.org/wiki/Silog), the Filipino breakfast dish of garlic rice and a fried egg.

## Why I built this

I work out pretty regularly but have never tracked any of my sessions.
I also have been interested in building on a **Laravel + Inertia + Svelte** stack to get that SPA feel,
so this app was the perfect excuse to try it out, while solving a real problem I have.

## What it does

- **Workouts**: a date, an optional title, and notes. The title and date are edited in
  place on the workout page rather than on a separate edit screen.
- **Strength exercises**: log sets with reps and weight, and tick each one off as you
  finish it.
- **Cardio exercises**: log duration and distance instead of sets.
- **An exercise catalog**: movements are reusable across workouts. Renaming "Bench" to
  "Bench Press" renames it everywhere at once, and the name is case-insensitive, so
  "bench press" and "Bench Press" cannot both exist.
- **Exercise history**: open any exercise to see every session that used it, with each
  entry linking straight to that movement inside that workout.
- **A dashboard**: lifetime workouts, workouts in the last 7 days, sets completed, and
  7-day volume, plus your five most recent sessions (I'll build on this more later)

## Tech stack

| Layer                | Tech                                                                                                                          |
| -------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| **Backend**          | Laravel 13, PHP 8.4                                                                                                           |
| **Frontend**         | Svelte 5 (runes) over [Inertia](https://inertiajs.com/) v3, Tailwind CSS v4                                                   |
| **Auth**             | Laravel Fortify                                                                                                               |
| **Database**         | SQLite, one file                                                                                                              |
| **Typed routing**    | [Wayfinder](https://github.com/laravel/wayfinder), which generates TypeScript functions for every route and controller action |
| **Build**            | Vite 8, via `vite-plus`                                                                                                       |
| **Tests**            | Pest 5                                                                                                                        |
| **Static analysis**  | Larastan (PHPStan level 7) and Pint                                                                                           |
| **Host** _(planned)_ | Raspberry Pi behind Tailscale                                                                                                 |

There is no API layer and no client-side router. Controllers return
`Inertia::render(...)` with plain Eloquent models, Svelte components take those as
props, and Wayfinder means a renamed route breaks the TypeScript build instead of
breaking a link at runtime.

## Self-hosting

Runs as a single Docker container on a Raspberry Pi 4B, behind
[Tailscale](https://tailscale.com/) and nowhere else — same setup as my other
self-hosted app, [lettuce-eat](https://github.com/ubemacapuno/lettuce-eat), on the
same Pi. Full walkthrough in [docs/deployment.md](docs/deployment.md); the short
version:

```bash
cp .env.production.example .env.production   # fill in APP_KEY and APP_URL
docker compose up -d --build
sudo tailscale serve --bg --https=8444 http://127.0.0.1:8081
```

That publishes it at your Tailscale hostname on port 8444, reachable from any
device on my tailnet and from nowhere else.

## Getting started

```bash
git clone git@github.com:ubemacapuno/repsilog.git
cd repsilog

composer setup                 # install, .env, app key, migrate, npm install, build
php artisan migrate --seed     # optional: a demo user with a few weeks of workouts
composer run dev               # http://127.0.0.1:8000
```

The seeder creates `test@example.com` with the password `password`.

Checks:

```bash
php artisan test --compact     # Pest
composer ci:check              # everything CI runs: lint, types, Pint, PHPStan, tests
```
