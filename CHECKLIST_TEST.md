# CHECKLIST TEST PRE-MIGRAZIONE
## Fantaciclismo - Verifica Funzionalità

**Data test:** _______________
**Tester:** _______________

---

## 1. SETUP INIZIALE

| # | Azione | Esito | Note |
|---|--------|-------|------|
| 1.1 | `git pull` eseguito | [ ] OK  [ ] KO | |
| 1.2 | `php artisan migrate` eseguito | [ ] OK  [ ] KO | |
| 1.3 | `php artisan serve` avviato | [ ] OK  [ ] KO | |
| 1.4 | Accesso a http://localhost:8000 | [ ] OK  [ ] KO | |

---

## 2. PANNELLO ADMIN (/admin)

### 2.1 Impostazioni Lega
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.1.1 | Accesso pannello admin | [ ] OK  [ ] KO | |
| 2.1.2 | Modifica budget iniziale | [ ] OK  [ ] KO | |
| 2.1.3 | Modifica max corridori per categoria | [ ] OK  [ ] KO | |
| 2.1.4 | Modifica durata contratto asta iniziale | [ ] OK  [ ] KO | |
| 2.1.5 | Modifica durata contratto asta riparazione | [ ] OK  [ ] KO | |
| 2.1.6 | Modifica % svalutazione annuale | [ ] OK  [ ] KO | |
| 2.1.7 | Modifica % stipendio | [ ] OK  [ ] KO | |

### 2.2 Categorie Corridori
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.2.1 | Creazione categoria (es. GC, Sprinter) | [ ] OK  [ ] KO | |
| 2.2.2 | Modifica categoria esistente | [ ] OK  [ ] KO | |
| 2.2.3 | Eliminazione categoria vuota | [ ] OK  [ ] KO | |
| 2.2.4 | Blocco eliminazione categoria con corridori | [ ] OK  [ ] KO | |

### 2.3 Corridori
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.3.1 | Creazione corridore manuale | [ ] OK  [ ] KO | |
| 2.3.2 | Import corridori da CSV | [ ] OK  [ ] KO | |
| 2.3.3 | Modifica valore corrente (current_value) | [ ] OK  [ ] KO | |
| 2.3.4 | Visualizzazione colore valore (verde/rosso) | [ ] OK  [ ] KO | |
| 2.3.5 | Modifica contratto corridore | [ ] OK  [ ] KO | |

### 2.4 Squadre Fantasy
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.4.1 | Creazione squadra | [ ] OK  [ ] KO | |
| 2.4.2 | Assegnazione utente a squadra | [ ] OK  [ ] KO | |
| 2.4.3 | Modifica budget squadra | [ ] OK  [ ] KO | |

### 2.5 Aste
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.5.1 | Creazione asta iniziale | [ ] OK  [ ] KO | |
| 2.5.2 | Creazione asta riparazione | [ ] OK  [ ] KO | |
| 2.5.3 | Attivazione asta | [ ] OK  [ ] KO | |
| 2.5.4 | Chiusura asta | [ ] OK  [ ] KO | |

### 2.6 Gare
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.6.1 | Creazione gara | [ ] OK  [ ] KO | |
| 2.6.2 | Import risultati CSV | [ ] OK  [ ] KO | |
| 2.6.3 | Calcolo punteggi | [ ] OK  [ ] KO | |

### 2.7 Gestione Lega
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.7.1 | Visualizzazione statistiche lega | [ ] OK  [ ] KO | |
| 2.7.2 | Reset budget squadre | [ ] OK  [ ] KO | |
| 2.7.3 | Decrementa contratti | [ ] OK  [ ] KO | |
| 2.7.4 | Svincola contratti scaduti | [ ] OK  [ ] KO | |
| 2.7.5 | Applica svalutazione | [ ] OK  [ ] KO | |
| 2.7.6 | Deduci stipendi | [ ] OK  [ ] KO | |
| 2.7.7 | Fine stagione completa | [ ] OK  [ ] KO | |
| 2.7.8 | Reset completo lega | [ ] OK  [ ] KO | |

### 2.8 Scambi (visualizzazione admin)
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 2.8.1 | Visualizzazione tutti gli scambi | [ ] OK  [ ] KO | |
| 2.8.2 | Filtro per stato | [ ] OK  [ ] KO | |

---

## 3. LATO GIOCATORE

### 3.1 Accesso e Dashboard
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 3.1.1 | Login giocatore | [ ] OK  [ ] KO | |
| 3.1.2 | Visualizzazione dashboard | [ ] OK  [ ] KO | |
| 3.1.3 | Visualizzazione rosa | [ ] OK  [ ] KO | |
| 3.1.4 | Visualizzazione budget | [ ] OK  [ ] KO | |
| 3.1.5 | Visualizzazione valore corridori | [ ] OK  [ ] KO | |
| 3.1.6 | Visualizzazione anni contratto | [ ] OK  [ ] KO | |
| 3.1.7 | Visualizzazione anni rimanenti | [ ] OK  [ ] KO | |

### 3.2 Asta
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 3.2.1 | Accesso pagina asta | [ ] OK  [ ] KO | |
| 3.2.2 | Visualizzazione tipo asta (iniziale/riparazione) | [ ] OK  [ ] KO | |
| 3.2.3 | Visualizzazione durata contratto | [ ] OK  [ ] KO | |
| 3.2.4 | Acquisto corridore | [ ] OK  [ ] KO | |
| 3.2.5 | Verifica decremento budget | [ ] OK  [ ] KO | |
| 3.2.6 | Verifica assegnazione contratto corretto | [ ] OK  [ ] KO | |
| 3.2.7 | Blocco se budget insufficiente | [ ] OK  [ ] KO | |
| 3.2.8 | Blocco se max categoria raggiunto | [ ] OK  [ ] KO | |

### 3.3 Scambi
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 3.3.1 | Proposta scambio | [ ] OK  [ ] KO | |
| 3.3.2 | Visualizzazione scambi ricevuti | [ ] OK  [ ] KO | |
| 3.3.3 | Accettazione scambio | [ ] OK  [ ] KO | |
| 3.3.4 | Rifiuto scambio con motivazione | [ ] OK  [ ] KO | |
| 3.3.5 | Blocco se viola limite categoria | [ ] OK  [ ] KO | |
| 3.3.6 | Trasferimento corridori corretto | [ ] OK  [ ] KO | |

### 3.4 Formazioni
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 3.4.1 | Inserimento formazione gara | [ ] OK  [ ] KO | |
| 3.4.2 | Modifica formazione | [ ] OK  [ ] KO | |
| 3.4.3 | Blocco dopo deadline | [ ] OK  [ ] KO | |

### 3.5 Svincolo
| # | Azione | Esito | Note |
|---|--------|-------|------|
| 3.5.1 | Svincolo corridore | [ ] OK  [ ] KO | |
| 3.5.2 | Corridore torna svincolato | [ ] OK  [ ] KO | |

---

## 4. TEST FLUSSO COMPLETO

| # | Scenario | Esito | Note |
|---|----------|-------|------|
| 4.1 | Creo lega → categorie → corridori → squadre | [ ] OK  [ ] KO | |
| 4.2 | Asta iniziale: giocatore compra 5 corridori | [ ] OK  [ ] KO | |
| 4.3 | Scambio tra 2 squadre completato | [ ] OK  [ ] KO | |
| 4.4 | Gara: formazioni → risultati → punteggi | [ ] OK  [ ] KO | |
| 4.5 | Fine stagione: svalutazione + stipendi + contratti | [ ] OK  [ ] KO | |
| 4.6 | Asta riparazione: contratto diverso da iniziale | [ ] OK  [ ] KO | |

---

## 5. RIEPILOGO

| Sezione | Passati | Falliti |
|---------|---------|---------|
| Setup | /4 | |
| Admin - Impostazioni | /7 | |
| Admin - Categorie | /4 | |
| Admin - Corridori | /5 | |
| Admin - Squadre | /3 | |
| Admin - Aste | /4 | |
| Admin - Gare | /3 | |
| Admin - Gestione Lega | /8 | |
| Admin - Scambi | /2 | |
| Giocatore - Dashboard | /7 | |
| Giocatore - Asta | /8 | |
| Giocatore - Scambi | /6 | |
| Giocatore - Formazioni | /3 | |
| Giocatore - Svincolo | /2 | |
| Flusso Completo | /6 | |
| **TOTALE** | **/72** | |

---

## 6. BUG TROVATI

| # | Descrizione | Gravità | Risolto |
|---|-------------|---------|---------|
| 1 | | [ ] Alta [ ] Media [ ] Bassa | [ ] |
| 2 | | [ ] Alta [ ] Media [ ] Bassa | [ ] |
| 3 | | [ ] Alta [ ] Media [ ] Bassa | [ ] |
| 4 | | [ ] Alta [ ] Media [ ] Bassa | [ ] |
| 5 | | [ ] Alta [ ] Media [ ] Bassa | [ ] |

---

## 7. NOTE FINALI

**Pronto per migrazione:** [ ] SI  [ ] NO

**Firma tester:** _______________

**Data:** _______________
