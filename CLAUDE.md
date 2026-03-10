# CLAUDE.md — Fantaciclismo

Questo file fornisce il contesto essenziale per gli assistenti AI che lavorano su questo progetto.

## Panoramica del Progetto

**Fantaciclismo** è un gestore di leghe di ciclismo fantasy — un'applicazione web multiplayer dove i giocatori costruiscono squadre ciclistiche virtuali, partecipano ad aste, scambiano corridori e competono in base alle prestazioni reali delle gare.

- **Framework:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade + Alpine.js + Tailwind CSS 3
- **UI reattiva:** Livewire 3
- **Pannello admin:** Filament 3.2
- **Database:** SQLite (sviluppo) / MySQL o PostgreSQL (produzione)
- **Build tool:** Vite 7

---

## Comandi Essenziali

### Setup Iniziale
```bash
composer run setup
```
Installa le dipendenze PHP e Node, genera l'APP_KEY, esegue le migration e compila gli asset.

### Server di Sviluppo
```bash
composer run dev
```
Avvia in parallelo: server Laravel (`:8000`), queue listener, Pail log viewer e Vite watcher.

### Eseguire i Test
```bash
composer run test
```
Svuota la cache di configurazione e poi esegue PHPUnit. I test usano un database SQLite in memoria.

### Build di Produzione
```bash
npm run build
```

### Comandi Artisan Utili
```bash
php artisan migrate              # Esegue le migration in sospeso
php artisan migrate:fresh --seed # Azzera il DB e lo risemina
php artisan tinker               # REPL interattivo
php artisan pint                 # Linting del codice PHP (Laravel Pint)
```

---

## Struttura del Progetto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PlayerTeamController.php   # Logica di gioco principale (aste, rosa, scambi)
│   │   ├── RaceController.php         # Gestione gare e formazioni
│   │   └── ProfileController.php
│   └── Middleware/
│       ├── EnsureUserHasTeam.php      # Reindirizza alla creazione squadra se mancante
│       └── EnsureSettingsExist.php    # Inizializza le impostazioni di gioco al primo avvio
├── Models/                            # 13 model Eloquent (vedi sezione dedicata)
├── Services/
│   └── SettingManager.php             # Configurazione centralizzata e cachata delle regole di gioco
├── Filament/Resources/                # CRUD admin per corridori, aste, gare, impostazioni
├── Livewire/
│   └── CreateTradeForm.php            # Componente interattivo per proporre scambi
└── View/Components/

database/
├── migrations/    # 20 file di migration
└── seeders/
    ├── DatabaseSeeder.php
    ├── RiderCategorySeeder.php
    ├── AdminUserSeeder.php
    ├── SettingSeeder.php
    └── RaceCreditRuleSeeder.php

resources/views/
├── layouts/       # app.blade.php, guest.blade.php, navigation.blade.php
├── auction/       # Interfaccia asta
├── market/        # Mercato degli scambi
├── player-team/   # Gestione squadra
├── races/         # Lista gare, editor formazione, classifiche
├── statistics/    # Statistiche squadra
├── livewire/      # View dei componenti Livewire
└── filament/      # Override del pannello admin

routes/
├── web.php        # Tutte le rotte dell'applicazione
└── auth.php       # Rotte di autenticazione (Breeze)
```

---

## Model Principali e Relazioni

| Model | Descrizione |
|---|---|
| `User` | Utente autenticato; controllo admin tramite email hardcoded `admin@test.com` |
| `PlayerTeam` | Squadra fantasy dell'utente (1:1 con User) |
| `Rider` | Un corridore; appartiene a `PlayerTeam` e `RiderCategory` |
| `RiderCategory` | Ruolo del corridore: GC, Puncher, Pavé, Velocisti, Cronomen, Gregari, Next Gen |
| `Trade` | Proposta di scambio tra squadre; usa la pivot `rider_trade`; supporta contro-offerte via `parent_trade_id` |
| `Auction` | Evento d'asta per l'acquisto di corridori |
| `Race` | Una gara ciclistica |
| `RaceLineup` | Formazione inviata da una squadra per una gara |
| `RaceResult` | Risultato individuale di un corridore in una gara |
| `RaceCreditRule` | Regole per l'assegnazione di crediti in base ai risultati |
| `Setting` | Configurazione di gioco chiave-valore |
| `Roster` | Snapshot storico della rosa |

---

## Riferimento Rotte

Tutte le rotte richiedono il middleware `auth`. Le rotte con `has.team` richiedono anche `EnsureUserHasTeam`.

| Metodo | Rotta | Middleware | Azione |
|---|---|---|---|
| GET | `/` | — | Pagina di benvenuto |
| GET | `/dashboard` | auth, verified, has.team | Dashboard squadra |
| GET/POST | `/create-team` | auth | Crea squadra |
| GET | `/auction` | auth | Mostra asta |
| POST | `/auction/buy/{rider}` | auth | Acquista corridore |
| POST | `/roster/release/{rider}` | auth | Svincola corridore |
| GET | `/market` | auth, has.team | Mercato scambi |
| POST | `/market/accept/{trade}` | auth, has.team | Accetta scambio |
| POST | `/market/reject/{trade}` | auth, has.team | Rifiuta scambio |
| POST | `/market/cancel/{trade}` | auth, has.team | Annulla scambio |
| GET | `/market/history` | auth, has.team | Storico scambi |
| GET | `/statistics` | auth, has.team | Statistiche squadra |
| GET | `/races` | auth, has.team | Lista gare |
| GET | `/races/{race}` | auth, has.team | Dettaglio gara |
| GET/POST | `/races/{race}/lineup` | auth, has.team | Editor formazione |
| GET | `/races/{race}/standings` | auth, has.team | Classifica gara |
| — | `/admin/*` | Filament auth | Pannello admin |

---

## Regole di Gioco e Configurazione

Tutte le regole di gioco configurabili si trovano in `app/Services/SettingManager.php` e vengono salvate nella tabella `settings`. I valori sono cachati a tempo indeterminato (svuotare manualmente la cache dopo ogni modifica).

| Chiave | Default | Descrizione |
|---|---|---|
| `initial_budget` | 700 | Budget iniziale (fantamilioni) |
| `team_size` | 45 | Numero massimo di corridori per squadra |
| `max_gc` | 8 | Massimo corridori GC |
| `max_puncher` | 8 | Massimo corridori Puncher |
| `max_pave` | 5 | Massimo corridori Pavé |
| `max_velocisti` | 7 | Massimo velocisti |
| `max_cronomen` | 3 | Massimo cronomen |
| `max_gregari` | 6 | Massimo gregari |
| `max_next_gen` | 8 | Massimo Next Gen |
| `release_recovery_percentage_pre_season` | 100 | % budget recuperato per svincoli pre-stagione |
| `release_recovery_percentage_mid_season` | 50 | % budget recuperato per svincoli in stagione |
| `annual_devaluation_percentage` | 20 | % di svalutazione annuale del cartellino |
| `salary_percentage` | 20 | % del valore d'acquisto per lo stipendio |
| `rebuy_penalty_amount` | 25 | Multa per riacquisto di un corridore appena svincolato |
| `max_trades_per_team` | 5 | Numero massimo di scambi per squadra |

Per leggere un'impostazione: `SettingManager::get('initial_budget')`

---

## Pannello Admin

- URL: `/admin`
- Credenziali create da `AdminUserSeeder`: `admin@test.com` (verificare la password nel seeder)
- Il controllo admin nel model `User` usa un confronto email hardcoded
- Gestisce: Utenti, Corridori, Categorie, Aste, Gare, Impostazioni, Regole Crediti Gara

---

## Convenzioni del Codice

- **Lingua:** I commenti, le note nelle rotte e le stringhe rivolte all'utente sono in italiano. Gli identificatori nel codice seguono le convenzioni Laravel in inglese.
- **Nomenclatura:**
  - Model: singolare PascalCase (`PlayerTeam`, `RiderCategory`)
  - Tabelle: plurale snake_case (`player_teams`, `rider_categories`)
  - Controller: PascalCase con suffisso `Controller`
  - View Blade: kebab-case (`player-team/show.blade.php`)
  - Metodi: camelCase
- **Integrità del database:** Avvolgere le operazioni multi-step (scambi, acquisti) in `DB::transaction()`.
- **Cache impostazioni:** Dopo aver modificato un record `Setting`, svuotare la sua chiave cache: `Cache::forget('setting.'.$key)`.
- **Middleware:** Registrare i middleware personalizzati in `bootstrap/app.php` (stile Laravel 12, non `Kernel.php`).
- **Livewire:** I form interattivi usano componenti Livewire in `app/Livewire/`; le view corrispondenti sono in `resources/views/livewire/`.
- **Risorse Filament:** Situate in `app/Filament/Resources/`; ciascuna ha la propria sottodirectory `Pages/`.

---

## Test

I test usano PHPUnit con un database SQLite in memoria (configurato in `phpunit.xml`).

```
tests/
├── Feature/    # Test di integrazione (autenticazione, gestione profilo)
└── Unit/       # Test unitari (attualmente minimali)
```

Quando si aggiungono test:
- Estendere `Tests\TestCase`
- Usare il trait `RefreshDatabase` per l'isolamento del database
- Usare `UserFactory` per creare utenti di test

La copertura attuale è focalizzata su autenticazione e profilo. La logica di gioco (aste, scambi, punteggi gare) necessita di più test.

---

## Variabili d'Ambiente

Copiare `.env.example` in `.env` e lanciare `php artisan key:generate`. Variabili principali:

```dotenv
APP_NAME=Fantaciclismo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite          # Usare mysql/pgsql in produzione

APP_LOCALE=it                 # Lingua italiana
APP_FALLBACK_LOCALE=en

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log               # Le email vanno nei log in sviluppo
```

---

## Problemi Noti / Aree di Attenzione

- **Sistema di contro-offerte:** La colonna `parent_trade_id` sulla tabella `trades` esiste ma la logica delle contro-offerte è stata semplificata. Segnalato come "da fixare" nella storia dei commit.
- **Copertura dei test:** La logica di gioco principale (aste, scambi, punteggi gare) non ha test di feature.
- **Autenticazione admin:** Il controllo admin usa un'email hardcoded in `User::isAdmin()` — non adatto per ambienti di produzione con più admin.
- **Invalidazione cache impostazioni:** `SettingManager::get()` cachà a tempo indeterminato. Le modifiche alle impostazioni tramite Filament richiedono svuotamento manuale della cache o un observer dedicato.

---

## Documentazione Correlata

- `GAME_ANALYSIS.md` — Documento di game design completo in italiano: meccaniche, punteggi, checklist delle funzionalità e proposte di miglioramento.
- `README.md` — README predefinito di Laravel (non specifico del progetto).
