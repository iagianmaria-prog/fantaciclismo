# GUIDA COMPLETA — FANTACICLISMO

## Indice
1. [Avvio Rapido](#1-avvio-rapido)
2. [Account e Accessi](#2-account-e-accessi)
3. [Pannello Admin](#3-pannello-admin)
4. [Lato Giocatore](#4-lato-giocatore)
5. [Workflow Tipico di una Stagione](#5-workflow-tipico-di-una-stagione)
6. [Formato File CSV](#6-formato-file-csv)
7. [Impostazioni di Gioco](#7-impostazioni-di-gioco)
8. [Risoluzione Problemi](#8-risoluzione-problemi)

---

## 1. Avvio Rapido

### Primo avvio (solo la prima volta)
```bash
cd ~/fantaciclismo
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
npm install && npm run build
php artisan serve
```

### Avvio normale (ogni volta)
```bash
cd ~/fantaciclismo
php artisan serve
```

### URL di accesso
| Pagina | URL |
|--------|-----|
| Home | http://localhost:8000 |
| Login | http://localhost:8000/login |
| Registrazione | http://localhost:8000/register |
| **Pannello Admin** | http://localhost:8000/admin |

---

## 2. Account e Accessi

### Account Admin Pre-configurato
| Campo | Valore |
|-------|--------|
| Email | `admin@test.com` |
| Password | `password` |

> **Nota:** Questo account viene creato automaticamente con `php artisan db:seed`. Ha accesso al pannello admin.

### Creare Nuovi Giocatori
I giocatori si registrano autonomamente da http://localhost:8000/register

Oppure l'admin può crearli dal pannello admin:
1. Vai a `/admin`
2. Clicca **Users** nel menu
3. Clicca **Create User**
4. Compila nome, email, password

---

## 3. Pannello Admin

Accesso: http://localhost:8000/admin

### 3.1 Dashboard
Statistiche generali della lega.

### 3.2 Categorie Corridori
**Menu:** Rider Categories

Categorie predefinite:
- GC (Classifica Generale)
- Velocista
- Scalatore
- Passista
- Altro

**Azioni:**
- ✅ Creare nuove categorie
- ✅ Modificare categorie esistenti
- ✅ Eliminare categorie (solo se non hanno corridori associati)

### 3.3 Corridori (Riders)
**Menu:** Riders

**Campi principali:**
| Campo | Descrizione |
|-------|-------------|
| Name | Nome del corridore |
| Real Team | Squadra reale (es. UAE, Jumbo) |
| Category | Categoria (GC, Velocista, ecc.) |
| Initial Value | Valore base in fantamilioni |
| Current Value | Valore attuale (modificabile manualmente) |
| Purchase Price | Prezzo pagato all'acquisto (automatico) |
| Contract Years | Durata contratto iniziale |
| Contract Remaining | Anni rimanenti |
| Player Team | Squadra fantasy proprietaria |

**Azioni:**
- ✅ Creare corridori manualmente
- ✅ Importare da CSV (vedi sezione 6)
- ✅ Modificare valore corrente (svalutazione/rivalutazione manuale)
- ✅ Assegnare/rimuovere da squadre

### 3.4 Squadre Fantasy (Player Teams)
**Menu:** Player Teams

**Azioni:**
- ✅ Vedere tutte le squadre
- ✅ Modificare budget
- ✅ Vedere rosa corridori

### 3.5 Aste (Auctions)
**Menu:** Auctions

**Tipi di asta:**
| Tipo | Descrizione | Durata contratto default |
|------|-------------|--------------------------|
| `initial` | Asta iniziale (inizio stagione) | 2 anni |
| `repair` | Asta di riparazione (durante stagione) | 1 anno |

**Stati dell'asta:**
| Stato | Descrizione |
|-------|-------------|
| `scheduled` | Programmata, non ancora iniziata |
| `open` | Aperta, i giocatori possono acquistare |
| `closed` | Chiusa |

> **Automatismo:** Le aste passano automaticamente da `scheduled` a `open` quando arriva l'ora di inizio, e da `open` a `closed` quando arriva l'ora di fine. Non devi cambiarle manualmente.

**Creare un'asta:**
1. Clicca **Create Auction**
2. Nome: es. "Asta Iniziale 2026"
3. Tipo: `initial` o `repair`
4. Starts At: data/ora inizio
5. Ends At: data/ora fine
6. Status: `scheduled`

### 3.6 Gare (Races)
**Menu:** Races

**Stati della gara:**
| Stato | Descrizione |
|-------|-------------|
| `upcoming` | Gara futura, formazioni non ancora aperte |
| `lineup_open` | Formazioni aperte |
| `in_progress` | Gara in corso |
| `completed` | Gara conclusa |

**Workflow gara:**
1. Crea gara con stato `upcoming`
2. Cambia a `lineup_open` quando vuoi aprire le formazioni
3. I giocatori inseriscono le formazioni
4. Dopo la deadline, cambia a `in_progress`
5. Inserisci i risultati (posizioni e crediti)
6. Cambia a `completed`

**Inserire risultati:**
1. Apri la gara
2. Clicca **Manage Results**
3. Per ogni corridore: posizione e crediti guadagnati
4. Oppure importa da CSV

### 3.7 Scambi (Trades)
**Menu:** Trades

Visualizza tutti gli scambi tra squadre:
- Squadra proponente e ricevente
- Corridori offerti e richiesti
- Compenso in crediti
- Stato (pending, accepted, rejected, cancelled)
- Motivazione rifiuto

### 3.8 Gestione Lega (League Management)
**Menu:** League Management

**Azioni disponibili:**

| Azione | Descrizione |
|--------|-------------|
| **Reset Budget** | Riporta tutte le squadre al budget iniziale |
| **Svincola Tutti i Corridori** | Libera tutti i corridori dalle squadre |
| **Elimina Tutti gli Scambi** | Cancella lo storico scambi |
| **Elimina Dati Gare** | Cancella formazioni e risultati |
| **Elimina Squadre** | Cancella tutte le squadre fantasy |
| **Reset Completo** | Tutto quanto sopra insieme |

**Operazioni Fine Stagione:**

| Azione | Descrizione |
|--------|-------------|
| **Decrementa Contratti** | -1 anno a tutti i contratti |
| **Svincola Scaduti** | Libera corridori con 0 anni rimanenti |
| **Applica Svalutazione** | Riduce il valore corrente (% da impostazioni) |
| **Deduci Stipendi** | Sottrae stipendi dal budget squadre |
| **Fine Stagione Completa** | Esegue tutto in ordine |

### 3.9 Impostazioni (Settings)
**Menu:** Settings

Tutte le regole di gioco modificabili (vedi sezione 7).

---

## 4. Lato Giocatore

### 4.1 Registrazione e Login
1. Vai a http://localhost:8000/register
2. Inserisci nome, email, password
3. Dopo il login, verrai reindirizzato alla creazione squadra

### 4.2 Creazione Squadra
- URL: http://localhost:8000/create-team
- Scegli un nome univoco
- Ricevi il budget iniziale (default: 700M)

### 4.3 Dashboard
- URL: http://localhost:8000/dashboard
- Vedi la tua rosa con:
  - Nome corridore
  - Categoria
  - Valore effettivo (con indicatore se diverso dal base)
  - Prezzo pagato
  - Anni contratto rimanenti
- Vedi il tuo budget

### 4.4 Asta
- URL: http://localhost:8000/auction
- Appare solo se c'è un'asta aperta
- Mostra:
  - Tipo asta (Iniziale = blu, Riparazione = arancione)
  - Durata contratto che verrà assegnato
  - Lista corridori disponibili
- Clicca **Acquista** per comprare un corridore
- Il sistema verifica automaticamente:
  - Budget sufficiente
  - Limite categoria non superato

### 4.5 Mercato Scambi
- URL: http://localhost:8000/market

**Proporre uno scambio:**
1. Seleziona la squadra avversaria
2. Seleziona corridori da offrire (tuoi)
3. Seleziona corridori da richiedere (loro)
4. Opzionale: aggiungi crediti da offrire o richiedere
5. Clicca **Proponi Scambio**

**Gestire scambi ricevuti:**
- **Accetta**: i corridori e crediti vengono scambiati
- **Rifiuta**: puoi aggiungere una motivazione

**Storico:**
- URL: http://localhost:8000/market/history

### 4.6 Svincolo Corridori
Dalla Dashboard:
1. Clicca **Svincola** accanto al corridore
2. Conferma
3. Recuperi una percentuale del valore (default: 50%)

### 4.7 Gare
- URL: http://localhost:8000/races

**Inserire formazione:**
1. Clicca su una gara con stato "Formazioni Aperte"
2. Seleziona i corridori (max indicato)
3. Clicca **Salva Formazione**

**Vedere risultati:**
- Clicca su una gara completata
- Vedi classifica e crediti guadagnati

### 4.8 Classifica Generale
- URL: http://localhost:8000/leaderboard
- Classifica squadre per crediti totali
- Clicca su una squadra per vedere il dettaglio gare

### 4.9 Statistiche
- URL: http://localhost:8000/statistics
- Rosa completa con valori
- Scambi effettuati
- Crediti guadagnati/spesi
- Posizione in classifica

---

## 5. Workflow Tipico di una Stagione

### Fase 1: Setup Iniziale (Admin)
1. Configura le impostazioni di gioco
2. Crea/importa le categorie corridori
3. Importa i corridori da CSV
4. Crea l'asta iniziale (tipo `initial`)

### Fase 2: Asta Iniziale (Giocatori)
1. I giocatori si registrano e creano le squadre
2. Quando l'asta si apre, acquistano i corridori
3. Contratti di 2 anni assegnati automaticamente

### Fase 3: Stagione (Admin + Giocatori)
1. **Admin:** Crea le gare
2. **Admin:** Apre le formazioni (status `lineup_open`)
3. **Giocatori:** Inseriscono le formazioni
4. **Giocatori:** Propongono/accettano scambi
5. **Admin:** Inserisce risultati gara
6. **Admin:** Se serve, apre asta riparazione

### Fase 4: Fine Stagione (Admin)
1. Vai a **League Management**
2. Clicca **Fine Stagione Completa**
   - Decrementa contratti (-1 anno)
   - Svincola corridori a contratto scaduto
   - Applica svalutazione (default -20%)
   - Deduci stipendi dai budget

### Fase 5: Nuova Stagione
1. Aggiorna valori corridori se necessario
2. Crea nuova asta iniziale per nuovi acquisti
3. Ripeti da Fase 2

---

## 6. Formato File CSV

### 6.1 Import Corridori

**Formato (una riga per corridore):**
```csv
name;category;initial_value;real_team
Tadej Pogacar;GC;120;UAE Team Emirates
Jonas Vingegaard;GC;115;Jumbo-Visma
Mathieu van der Poel;Puncher;100;Alpecin-Deceuninck
```

**Colonne:**
| Colonna | Obbligatoria | Descrizione |
|---------|--------------|-------------|
| name | Sì | Nome corridore |
| category | Sì | Nome categoria (deve esistere) |
| initial_value | Sì | Valore in fantamilioni |
| real_team | No | Squadra reale |

**Note:**
- Separatore: `;` (punto e virgola) o `,` (virgola) — rilevato automaticamente
- Prima riga: intestazioni
- Encoding: UTF-8

### 6.2 Import Risultati Gara

**Formato:**
```csv
rider_name;position;credits_earned
Tadej Pogacar;1;100
Jonas Vingegaard;2;80
Primoz Roglic;3;60
```

---

## 7. Impostazioni di Gioco

Accesso: **Admin → Settings**

### Rosa e Contratti
| Chiave | Default | Descrizione |
|--------|---------|-------------|
| team_size | 45 | Max corridori per squadra |
| contract_duration_initial | 2 | Anni contratto asta iniziale |
| contract_duration_repair | 1.5 | Anni contratto asta riparazione |

### Limiti per Categoria
| Chiave | Default | Descrizione |
|--------|---------|-------------|
| max_gc | 8 | Max corridori GC |
| max_puncher | 8 | Max Puncher |
| max_pave | 5 | Max Pavé |
| max_velocisti | 7 | Max Velocisti |
| max_cronomen | 3 | Max Cronomen |
| max_gregari | 6 | Max Gregari |
| max_next_gen | 8 | Max Next Gen |

### Budget
| Chiave | Default | Descrizione |
|--------|---------|-------------|
| initial_budget | 700 | Budget iniziale (milioni) |

### Svincoli
| Chiave | Default | Descrizione |
|--------|---------|-------------|
| release_recovery_percentage_pre_season | 100 | % recupero pre-stagione |
| release_recovery_percentage_mid_season | 50 | % recupero durante stagione |

### Fine Stagione
| Chiave | Default | Descrizione |
|--------|---------|-------------|
| annual_devaluation_percentage | 20 | % svalutazione annuale |
| salary_percentage | 20 | % valore per calcolo stipendio |
| min_salary_amount | 1 | Stipendio minimo garantito |

---

## 8. Risoluzione Problemi

### "Vite manifest not found"
```bash
npm install
npm run build
```

### "SQLSTATE: no such table"
```bash
php artisan migrate
```

### "No such column: real_team"
```bash
php artisan migrate
```

### Pagina bianca o errore 500
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Dimenticato password admin
```bash
php artisan tinker
```
Poi:
```php
User::where('email', 'admin@test.com')->update(['password' => Hash::make('nuovapassword')]);
```

### Reset completo database
```bash
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

---

## Contatti e Supporto

Per bug o richieste: https://github.com/iagianmaria-prog/fantaciclismo/issues
