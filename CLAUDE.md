# CLAUDE.md — Fantaciclismo

This file provides essential context for AI assistants working on this codebase.

## Project Overview

**Fantaciclismo** is a fantasy cycling league manager — a multiplayer web application where players build virtual professional cycling teams, participate in auctions, trade cyclists, and compete based on real-world race performances.

- **Framework:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade + Alpine.js + Tailwind CSS 3
- **Realtime UI:** Livewire 3
- **Admin panel:** Filament 3.2
- **Database:** SQLite (dev) / MySQL or PostgreSQL (prod)
- **Build tool:** Vite 7

---

## Essential Commands

### Initial Setup
```bash
composer run setup
```
Installs PHP + Node dependencies, generates APP_KEY, runs migrations, and builds assets.

### Development Server
```bash
composer run dev
```
Starts concurrently: Laravel server (`:8000`), queue listener, Pail log viewer, and Vite watcher.

### Run Tests
```bash
composer run test
```
Clears config cache then runs PHPUnit. Tests use an in-memory SQLite database.

### Production Build
```bash
npm run build
```

### Useful Artisan Commands
```bash
php artisan migrate              # Run pending migrations
php artisan migrate:fresh --seed # Reset DB and seed
php artisan tinker               # REPL
php artisan pint                 # Lint PHP code (Laravel Pint)
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PlayerTeamController.php   # Core game logic (auctions, roster, trades)
│   │   ├── RaceController.php         # Race management and lineups
│   │   └── ProfileController.php
│   └── Middleware/
│       ├── EnsureUserHasTeam.php      # Redirects to team creation if team missing
│       └── EnsureSettingsExist.php    # Bootstraps game settings on first run
├── Models/                            # 13 Eloquent models (see section below)
├── Services/
│   └── SettingManager.php             # Centralized, cached game rule config
├── Filament/Resources/                # Admin CRUD for riders, auctions, races, settings
├── Livewire/
│   └── CreateTradeForm.php            # Interactive trade proposal component
└── View/Components/

database/
├── migrations/    # 20 migration files
└── seeders/
    ├── DatabaseSeeder.php
    ├── RiderCategorySeeder.php
    ├── AdminUserSeeder.php
    ├── SettingSeeder.php
    └── RaceCreditRuleSeeder.php

resources/views/
├── layouts/       # app.blade.php, guest.blade.php, navigation.blade.php
├── auction/       # Auction interface
├── market/        # Trade/transfer market
├── player-team/   # Team management
├── races/         # Race listings, lineup editor, standings
├── statistics/    # Team stats
├── livewire/      # Livewire component views
└── filament/      # Admin panel overrides

routes/
├── web.php        # All application routes
└── auth.php       # Breeze authentication routes
```

---

## Key Models and Relationships

| Model | Description |
|---|---|
| `User` | Auth user; admin check via hardcoded `admin@test.com` |
| `PlayerTeam` | User's fantasy team (1:1 with User) |
| `Rider` | A cyclist; belongs to `PlayerTeam` and `RiderCategory` |
| `RiderCategory` | Cyclist role: GC, Puncher, Pavé, Velocisti, Cronomen, Gregari, Next Gen |
| `Trade` | Trade proposal between teams; uses `rider_trade` pivot; supports counter-offers via `parent_trade_id` |
| `Auction` | Auction event for buying riders |
| `Race` | A cycling race event |
| `RaceLineup` | A team's submitted rider lineup for a race |
| `RaceResult` | Individual rider's result in a race |
| `RaceCreditRule` | Rules for awarding credits based on results |
| `Setting` | Key-value game configuration |
| `Roster` | Roster snapshot / historical entry |

---

## Routes Reference

All routes require `auth` middleware. Routes marked with `has.team` also require `EnsureUserHasTeam`.

| Method | Route | Middleware | Action |
|---|---|---|---|
| GET | `/` | — | Welcome page |
| GET | `/dashboard` | auth, verified, has.team | Team dashboard |
| GET/POST | `/create-team` | auth | Create team |
| GET | `/auction` | auth | Show auction |
| POST | `/auction/buy/{rider}` | auth | Purchase rider |
| POST | `/roster/release/{rider}` | auth | Release rider |
| GET | `/market` | auth, has.team | Trade market |
| POST | `/market/accept/{trade}` | auth, has.team | Accept trade |
| POST | `/market/reject/{trade}` | auth, has.team | Reject trade |
| POST | `/market/cancel/{trade}` | auth, has.team | Cancel trade |
| GET | `/market/history` | auth, has.team | Trade history |
| GET | `/statistics` | auth, has.team | Team statistics |
| GET | `/races` | auth, has.team | Race list |
| GET | `/races/{race}` | auth, has.team | Race detail |
| GET/POST | `/races/{race}/lineup` | auth, has.team | Lineup editor |
| GET | `/races/{race}/standings` | auth, has.team | Race standings |
| — | `/admin/*` | Filament auth | Admin panel |

---

## Game Rules & Configuration

All configurable game rules live in `app/Services/SettingManager.php` and are persisted in the `settings` table. Values are cached forever (invalidate manually when changed).

| Setting Key | Default | Description |
|---|---|---|
| `initial_budget` | 700 | Starting budget (fantamilioni) |
| `team_size` | 45 | Max cyclists per team |
| `max_gc` | 8 | Max GC riders |
| `max_puncher` | 8 | Max Puncher riders |
| `max_pave` | 5 | Max Pavé riders |
| `max_velocisti` | 7 | Max Sprinters |
| `max_cronomen` | 3 | Max Time trialists |
| `max_gregari` | 6 | Max Domestiques |
| `max_next_gen` | 8 | Max Next Gen riders |
| `release_recovery_percentage_pre_season` | 100 | % budget back when releasing pre-season |
| `release_recovery_percentage_mid_season` | 50 | % budget back mid-season |
| `annual_devaluation_percentage` | 20 | Annual rider value devaluation % |
| `salary_percentage` | 20 | Salary as % of purchase price |
| `rebuy_penalty_amount` | 25 | Fine for re-buying a just-released rider |
| `max_trades_per_team` | 5 | Max team-to-team trades |

Read a setting: `SettingManager::get('initial_budget')`

---

## Admin Panel

- URL: `/admin`
- Credentials seeded by `AdminUserSeeder`: `admin@test.com` (check seeder for password)
- Admin check in `User` model uses hardcoded email comparison
- Manage: Users, Riders, RiderCategories, Auctions, Races, Settings, RaceCreditRules

---

## Code Conventions

- **Language:** Italian is used throughout for comments, route comments, and user-facing strings. Code identifiers follow English Laravel conventions.
- **Naming:**
  - Models: singular PascalCase (`PlayerTeam`, `RiderCategory`)
  - Tables: plural snake_case (`player_teams`, `rider_categories`)
  - Controllers: PascalCase with `Controller` suffix
  - Blade views: kebab-case (`player-team/show.blade.php`)
  - Methods: camelCase
- **Database integrity:** Wrap multi-step operations (trades, purchases) in `DB::transaction()`.
- **Settings cache:** After modifying a `Setting` record, clear its cache key: `Cache::forget('setting.'.$key)`.
- **Middleware:** Register custom middleware in `bootstrap/app.php` (Laravel 12 style, not `Kernel.php`).
- **Livewire:** Interactive forms use Livewire components in `app/Livewire/`; views in `resources/views/livewire/`.
- **Filament resources:** Located in `app/Filament/Resources/`; each has its own `Pages/` subdirectory.

---

## Testing

Tests use PHPUnit with an in-memory SQLite database (configured in `phpunit.xml`).

```
tests/
├── Feature/    # Integration tests (auth flows, profile management)
└── Unit/       # Unit tests (currently minimal)
```

When adding tests:
- Extend `Tests\TestCase`
- Use `RefreshDatabase` trait for database isolation
- Use `UserFactory` for test users

Current test coverage is focused on authentication/profile. Game logic (auctions, trades, races) needs more tests.

---

## Environment Variables

Copy `.env.example` to `.env` and run `php artisan key:generate`. Key variables:

```dotenv
APP_NAME=Fantaciclismo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite          # Use mysql/pgsql for production

APP_LOCALE=it                 # Italian locale
APP_FALLBACK_LOCALE=en

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log               # Emails go to log in development
```

---

## Known Issues / Areas of Attention

- **Counter-offer system:** The `parent_trade_id` column on `trades` exists but counter-offer logic was simplified. Marked as "da fixare" in git history.
- **Test coverage:** Game core logic (auction, trade, race scoring) lacks feature tests.
- **Admin auth:** The admin check uses a hardcoded email in `User::isAdmin()` — not suitable for multi-admin production setups.
- **Settings cache invalidation:** `SettingManager::get()` caches forever. Admin changes to settings via Filament need manual cache clearing or a cache-busting observer.

---

## Related Documentation

- `GAME_ANALYSIS.md` — Comprehensive Italian-language game design document covering mechanics, scoring, feature checklist, and improvement proposals.
- `README.md` — Default Laravel README (not project-specific).
