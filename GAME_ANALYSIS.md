# Fantaciclismo - Analisi Completa del Gioco

**Data Analisi:** 1 Febbraio 2026
**Versione:** 1.0
**Repository:** fantaciclismo

---

## 1. Panoramica del Gioco

**Fantaciclismo** è un **Fantasy Cycling League Manager** - un gioco multiplayer competitivo dove i giocatori costruiscono e gestiscono squadre virtuali di ciclismo professionistico. I giocatori acquistano ciclisti reali tramite aste, gestiscono i roster, scambiano corridori e competono in base alle prestazioni reali nelle gare.

### Caratteristiche Principali
- Sistema di aste per l'acquisto dei corridori
- Mercato di scambio tra squadre
- Sistema di contro-offerte per negoziazioni
- Dashboard statistiche della squadra
- Pannello di amministrazione (Filament)

---

## 2. Meccaniche di Gioco

### 2.1 Creazione della Squadra
- Ogni utente può creare **una sola squadra**
- Budget iniziale: **700 fantamilioni**
- Massimo **45 corridori** per squadra
- I corridori sono divisi per categorie con limiti specifici

### 2.2 Categorie Corridori

| Categoria | Descrizione | Max Corridori |
|-----------|-------------|---------------|
| **GC** | Classificatori Generali | 8 |
| **Puncher** | Specialisti delle corse dure | 8 |
| **Pavé** | Specialisti del pavé | 5 |
| **Velocisti** | Sprinter | 7 |
| **Cronomen** | Specialisti a cronometro | 3 |
| **Gregari** | Domestiques/Supporto | 6 |
| **Next Gen** | Giovani promesse | 8 |

**Totale massimo roster:** 45 corridori

### 2.3 Sistema di Acquisto

#### Asta Iniziale
- Acquisto di corridori non assegnati al valore iniziale
- Il budget deve essere sufficiente
- I limiti di categoria devono essere rispettati

#### Rilascio Corridori
- **Pre-stagione:** Recupero del **100%** del prezzo di acquisto
- **Mid-season:** Recupero del **50%** del prezzo di acquisto

### 2.4 Sistema di Scambi

1. **Proposta di Scambio**
   - Seleziona corridori da offrire
   - Seleziona corridori da richiedere
   - Aggiungi aggiustamento monetario opzionale

2. **Stati dello Scambio**
   - `pending` - In attesa di risposta
   - `accepted` - Accettato
   - `rejected` - Rifiutato
   - `cancelled` - Annullato

3. **Contro-offerte**
   - La squadra ricevente può fare una contro-proposta
   - Crea una catena di negoziazione tracciata

---

## 3. Struttura del Database

### Tabelle Principali

```
users
├── id, name, email, password
└── Autenticazione utenti

player_teams (squadre fantasy)
├── id, user_id, name, balance
└── Una squadra per utente

riders (corridori)
├── id, name, team_name, initial_value
├── rider_category_id, player_team_id
└── Ciclisti acquistabili

rider_categories (categorie)
├── id, name, description
└── Tipi di corridori

trades (scambi)
├── id, offering_team_id, receiving_team_id
├── money_adjustment, status, parent_trade_id
└── Proposte di scambio

rider_trade (pivot scambi)
├── trade_id, rider_id, direction
└── Corridori negli scambi

settings (impostazioni)
├── key, value
└── Regole del gioco configurabili
```

### Relazioni
- `User` → `PlayerTeam` (1:1)
- `PlayerTeam` → `Riders` (1:N)
- `PlayerTeam` → `Trades` (come squadra offerente o ricevente)
- `Trade` ↔ `Riders` (N:N con attributo direction)
- `Rider` → `RiderCategory` (N:1)

---

## 4. Architettura Tecnica

### Stack Tecnologico
- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Blade, Alpine.js, Tailwind CSS
- **Database:** SQLite (sviluppo), MySQL/PostgreSQL (produzione)
- **Admin Panel:** Filament 3
- **Build:** Vite

### Struttura File Principali

```
/app
├── Http/Controllers/
│   └── PlayerTeamController.php  # Logica principale del gioco
├── Services/
│   └── SettingManager.php        # Gestione impostazioni
├── Models/                        # Modelli Eloquent
├── Middleware/                    # Middleware personalizzati
└── Filament/Resources/           # Pannello admin

/routes
└── web.php                        # Definizione route

/resources/views
├── auction/                       # Viste asta
├── market/                        # Viste mercato
├── player-team/                   # Viste squadra
├── statistics/                    # Viste statistiche
└── dashboard.blade.php            # Dashboard principale

/database
├── migrations/                    # Schema database
└── seeders/                       # Dati iniziali
```

---

## 5. Impostazioni di Gioco Configurabili

| Chiave | Valore Default | Descrizione |
|--------|----------------|-------------|
| `team_size` | 45 | Corridori totali per squadra |
| `initial_budget` | 700 | Budget iniziale (fantamilioni) |
| `max_gc` | 8 | Max corridori GC |
| `max_puncher` | 8 | Max corridori Puncher |
| `max_pave` | 5 | Max corridori Pavé |
| `max_velocisti` | 7 | Max velocisti |
| `max_cronomen` | 3 | Max cronomen |
| `max_gregari` | 6 | Max gregari |
| `max_next_gen` | 8 | Max Next Gen |
| `release_recovery_percentage_mid_season` | 50 | % recupero mid-season |
| `release_recovery_percentage_pre_season` | 100 | % recupero pre-season |
| `annual_devaluation` | 20 | % svalutazione annuale |
| `salary_percentage` | 20 | % stipendio del valore |

---

## 6. Funzionalità Implementate

### Stato Attuale (dal commit iniziale)

| Feature | Stato | Note |
|---------|-------|------|
| Creazione Squadra | Implementata | Funzionante |
| Sistema Aste | Implementato | Funzionante |
| Rilascio Corridori | Implementato | Funzionante |
| Mercato Scambi | Implementato | Funzionante |
| Contro-offerte | Implementato | Da fixare |
| Statistiche | Implementato | Funzionante |
| Pannello Admin | Implementato | Filament 3 |

### Funzionalità Future (Struttura Presente)
- Sistema Gare (`races`, `race_lineups`, `race_results`)
- Sistema di Punteggio
- Classifiche

---

## 7. Flusso di Gioco

```
┌─────────────────────────────────────────────────────────────┐
│                    REGISTRAZIONE                             │
│                         ↓                                    │
│              Creazione Squadra (nome + budget 700)           │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                    COSTRUZIONE ROSTER                        │
│                         ↓                                    │
│   ┌─────────────┐              ┌─────────────┐              │
│   │    ASTA     │              │   MERCATO   │              │
│   │ Acquista    │              │  Scambia    │              │
│   │ corridori   │              │  corridori  │              │
│   │ disponibili │              │  con altri  │              │
│   └─────────────┘              └─────────────┘              │
│          ↓                           ↓                       │
│   Budget - Costo              Negoziazione                   │
│   Verifica limiti             Contro-offerte                 │
│   categoria                   Accettazione                   │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                 GESTIONE SQUADRA                             │
│                         ↓                                    │
│   - Visualizza roster nella dashboard                        │
│   - Rilascia corridori (recupero %)                          │
│   - Monitora statistiche squadra                             │
│   - Controlla storico scambi                                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 8. API Routes

| Metodo | Route | Descrizione |
|--------|-------|-------------|
| GET | `/dashboard` | Dashboard squadra |
| GET | `/create-team` | Form creazione squadra |
| POST | `/create-team` | Salva nuova squadra |
| GET | `/auction` | Pagina aste |
| POST | `/auction/buy/{rider}` | Acquista corridore |
| POST | `/roster/release/{rider}` | Rilascia corridore |
| GET | `/market` | Mercato scambi |
| POST | `/market/accept/{trade}` | Accetta scambio |
| POST | `/market/reject/{trade}` | Rifiuta scambio |
| POST | `/market/cancel/{trade}` | Annulla scambio |
| GET | `/market/counter-offer/{trade}` | Form contro-offerta |
| POST | `/market/counter-offer/{trade}` | Invia contro-offerta |
| GET | `/market/history` | Storico scambi |
| GET | `/statistics` | Statistiche squadra |

---

## 9. Valutazione del Progetto

### Punti di Forza
- Architettura Laravel moderna e ben strutturata
- Separazione delle responsabilita (Controller, Service, Model)
- Sistema di middleware per autenticazione e validazione
- Pannello admin completo con Filament
- Impostazioni configurabili centralizzate
- Sistema di scambi completo con negoziazione

### Aree di Miglioramento
- Sistema contro-offerte da completare/fixare
- Sistema gare da implementare
- Sistema punteggio da implementare
- Test automatizzati da aggiungere
- Documentazione API da completare

### Valutazione Complessiva

| Aspetto | Voto | Note |
|---------|------|------|
| Architettura | 8/10 | Pulita e scalabile |
| Funzionalita | 7/10 | Core implementato, gare mancanti |
| UX/UI | 7/10 | Funzionale, migliorabile |
| Codice | 8/10 | Ben organizzato |
| Documentazione | 5/10 | Da migliorare |

**Voto Complessivo: 7/10**

---

## 10. Conclusioni

Fantaciclismo e un progetto fantasy cycling ben strutturato con le funzionalita core implementate. Il sistema di aste, gestione roster e mercato scambi funziona correttamente. Le aree principali da sviluppare sono:

1. **Fix sistema contro-offerte** (priorita alta)
2. **Implementazione sistema gare** (priorita media)
3. **Sistema di punteggio** (priorita media)
4. **Test automatizzati** (priorita bassa)
5. **Documentazione** (priorita bassa)

Il progetto ha una solida base tecnica e puo essere facilmente esteso per aggiungere nuove funzionalita.

---

*Documento generato automaticamente dall'analisi del repository*
