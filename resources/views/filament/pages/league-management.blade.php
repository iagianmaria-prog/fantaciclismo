<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Statistiche Lega --}}
        <x-filament::section>
            <x-slot name="heading">
                Statistiche Lega
            </x-slot>

            @php $stats = $this->getStats(); @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-primary-600">{{ $stats['teams'] }}</div>
                    <div class="text-sm text-gray-500">Squadre</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-success-600">{{ $stats['riders_assigned'] }}</div>
                    <div class="text-sm text-gray-500">Corridori Assegnati</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-warning-600">{{ $stats['riders_free'] }}</div>
                    <div class="text-sm text-gray-500">Corridori Liberi</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-info-600">{{ $stats['races'] }}</div>
                    <div class="text-sm text-gray-500">Gare Totali</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-success-600">{{ $stats['races_completed'] }}</div>
                    <div class="text-sm text-gray-500">Gare Completate</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-primary-600">{{ $stats['lineups'] }}</div>
                    <div class="text-sm text-gray-500">Formazioni Schierate</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-primary-600">{{ $stats['results'] }}</div>
                    <div class="text-sm text-gray-500">Risultati Inseriti</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                    <div class="text-2xl font-bold text-warning-600">{{ $stats['trades_total'] }}</div>
                    <div class="text-sm text-gray-500">Scambi Totali</div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-yellow-600">{{ $stats['trades_pending'] }}</div>
                    <div class="text-sm text-gray-500">Scambi In Attesa</div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-green-600">{{ $stats['trades_accepted'] }}</div>
                    <div class="text-sm text-gray-500">Scambi Accettati</div>
                </div>

                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-orange-600">{{ $stats['expiring_contracts'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Contratti in Scadenza (≤1 anno)</div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-red-600">{{ $stats['expired_contracts'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Contratti Scaduti</div>
                </div>
            </div>
        </x-filament::section>

        {{-- Gestione Contratti e Fine Stagione --}}
        <x-filament::section>
            <x-slot name="heading">
                Gestione Contratti e Fine Stagione
            </x-slot>

            {{-- Pulsante principale Fine Stagione --}}
            <div class="mb-6 border-2 border-blue-300 dark:border-blue-700 rounded-lg p-6 bg-blue-50 dark:bg-blue-900/20">
                <h3 class="font-bold text-lg mb-2 text-blue-800 dark:text-blue-300">🏁 Fine Stagione Completa</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Esegue tutte le operazioni di fine stagione in ordine:
                </p>
                <ol class="text-sm text-gray-600 dark:text-gray-400 mb-4 list-decimal list-inside space-y-1">
                    <li><strong>Deduce stipendi</strong> dal budget delle squadre ({{ \App\Services\SettingManager::get('salary_percentage', 20) }}% del valore corridori)</li>
                    <li><strong>Applica svalutazione</strong> ai corridori ({{ \App\Services\SettingManager::get('annual_devaluation_percentage', 20) }}% del valore)</li>
                    <li><strong>Decrementa contratti</strong> di 1 anno</li>
                    <li><strong>Svincola corridori</strong> con contratto scaduto</li>
                    <li><strong>Resetta formazioni</strong> delle gare</li>
                </ol>
                <x-filament::button
                    wire:click="endSeason"
                    wire:confirm="ATTENZIONE: Questa azione eseguirà TUTTE le operazioni di fine stagione (stipendi, svalutazione, contratti, svincoli). Sei sicuro?"
                    color="primary"
                    icon="heroicon-o-calendar"
                    size="lg"
                >
                    Esegui Fine Stagione Completa
                </x-filament::button>
            </div>

            <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Operazioni Singole</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">💰 Deduce Stipendi</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Deduce gli stipendi ({{ \App\Services\SettingManager::get('salary_percentage', 20) }}% del valore corridori) dal budget di ogni squadra.
                    </p>
                    <x-filament::button
                        wire:click="deductSalaries"
                        wire:confirm="Sei sicuro di voler detrarre gli stipendi da tutte le squadre?"
                        color="warning"
                        icon="heroicon-o-banknotes"
                    >
                        Deduce Stipendi
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">📉 Applica Svalutazione</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Riduce il valore di tutti i corridori del {{ \App\Services\SettingManager::get('annual_devaluation_percentage', 20) }}%.
                    </p>
                    <x-filament::button
                        wire:click="applyDevaluation"
                        wire:confirm="Sei sicuro di voler applicare la svalutazione a tutti i corridori?"
                        color="warning"
                        icon="heroicon-o-arrow-trending-down"
                    >
                        Applica Svalutazione
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">📅 Decrementa Contratti</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Decrementa di 1 anno tutti i contratti attivi. Non svincola automaticamente i corridori.
                    </p>
                    <x-filament::button
                        wire:click="decrementContracts"
                        wire:confirm="Sei sicuro di voler decrementare tutti i contratti di 1 anno?"
                        color="warning"
                        icon="heroicon-o-minus-circle"
                    >
                        Decrementa Contratti
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">🚪 Svincola Contratti Scaduti</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Svincola solo i corridori con contratto scaduto (0 anni rimanenti).
                    </p>
                    <x-filament::button
                        wire:click="releaseExpiredContracts"
                        wire:confirm="Sei sicuro di voler svincolare tutti i corridori con contratto scaduto?"
                        color="danger"
                        icon="heroicon-o-user-minus"
                    >
                        Svincola Scaduti
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Azioni Moderate --}}
        <x-filament::section>
            <x-slot name="heading">
                Azioni Moderate
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">Reset Budget Squadre</h3>
                    <p class="text-sm text-gray-500 mb-3">Resetta il budget di tutte le squadre al valore iniziale. I corridori rimarranno assegnati.</p>
                    <x-filament::button
                        wire:click="resetTeamsBudget"
                        wire:confirm="Sei sicuro di voler resettare il budget di tutte le squadre?"
                        color="warning"
                        icon="heroicon-o-banknotes"
                    >
                        Reset Budget
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">Svincola Tutti i Corridori</h3>
                    <p class="text-sm text-gray-500 mb-3">Rimuove tutti i corridori dalle squadre. Torneranno disponibili all'asta.</p>
                    <x-filament::button
                        wire:click="releaseAllRiders"
                        wire:confirm="Sei sicuro di voler svincolare TUTTI i corridori?"
                        color="warning"
                        icon="heroicon-o-user-minus"
                    >
                        Svincola Corridori
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">Elimina Tutti gli Scambi</h3>
                    <p class="text-sm text-gray-500 mb-3">Cancella tutto lo storico degli scambi tra squadre.</p>
                    <x-filament::button
                        wire:click="deleteAllTrades"
                        wire:confirm="Sei sicuro di voler eliminare TUTTI gli scambi?"
                        color="danger"
                        icon="heroicon-o-arrows-right-left"
                    >
                        Elimina Scambi
                    </x-filament::button>
                </div>

                <div class="border rounded-lg p-4 dark:border-gray-700">
                    <h3 class="font-semibold mb-2">Elimina Risultati e Formazioni</h3>
                    <p class="text-sm text-gray-500 mb-3">Cancella risultati gare e formazioni schierate. Le gare rimarranno.</p>
                    <x-filament::button
                        wire:click="deleteAllRaceData"
                        wire:confirm="Sei sicuro di voler eliminare TUTTI i risultati e le formazioni?"
                        color="danger"
                        icon="heroicon-o-flag"
                    >
                        Elimina Dati Gare
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Azioni Distruttive --}}
        <x-filament::section>
            <x-slot name="heading">
                Azioni Distruttive
            </x-slot>

            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                <p class="text-sm text-red-600 dark:text-red-400">
                    <strong>Attenzione!</strong> Queste azioni sono irreversibili e hanno un impatto significativo sulla lega.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="border border-red-300 dark:border-red-700 rounded-lg p-4 bg-red-50/50 dark:bg-red-900/10">
                    <h3 class="font-semibold mb-2 text-red-700 dark:text-red-400">RESET COMPLETO LEGA</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Elimina: tutti gli scambi, risultati, formazioni. Svincola tutti i corridori e resetta il budget.
                        Le squadre, le gare e i corridori rimarranno. <strong>Ideale per iniziare una nuova stagione.</strong>
                    </p>
                    <x-filament::button
                        wire:click="fullReset"
                        wire:confirm="ATTENZIONE: Questa azione resetterà TUTTA la lega. Sei assolutamente sicuro?"
                        color="danger"
                        icon="heroicon-o-exclamation-triangle"
                    >
                        RESET COMPLETO LEGA
                    </x-filament::button>
                </div>

                <div class="border border-red-300 dark:border-red-700 rounded-lg p-4 bg-red-50/50 dark:bg-red-900/10">
                    <h3 class="font-semibold mb-2 text-red-700 dark:text-red-400">ELIMINA TUTTE LE SQUADRE</h3>
                    <p class="text-sm text-gray-500 mb-3">
                        Elimina TUTTE le squadre e tutti i dati correlati. Gli utenti e i corridori rimarranno.
                        <strong>Gli utenti dovranno ricreare le squadre da zero.</strong>
                    </p>
                    <x-filament::button
                        wire:click="deleteAllTeams"
                        wire:confirm="ATTENZIONE ESTREMA: Questa azione eliminerà TUTTE le squadre. Sei assolutamente sicuro?"
                        color="danger"
                        icon="heroicon-o-trash"
                    >
                        ELIMINA TUTTE LE SQUADRE
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
