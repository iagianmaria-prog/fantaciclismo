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

            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-yellow-600">{{ $stats['trades_pending'] }}</div>
                    <div class="text-sm text-gray-500">Scambi In Attesa</div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="text-xl font-bold text-green-600">{{ $stats['trades_accepted'] }}</div>
                    <div class="text-sm text-gray-500">Scambi Accettati</div>
                </div>
            </div>
        </x-filament::section>

        {{-- Azioni --}}
        <x-filament::section>
            <x-slot name="heading">
                Azioni di Gestione
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p class="text-sm text-gray-500">
                    Usa i pulsanti in alto a destra per gestire la lega. Le azioni sono ordinate dalla meno distruttiva alla più distruttiva.
                </p>

                <ul class="text-sm mt-4 space-y-2">
                    <li><strong>Reset Budget Squadre:</strong> Resetta il budget di tutte le squadre al valore iniziale.</li>
                    <li><strong>Svincola Tutti i Corridori:</strong> Rimuove tutti i corridori dalle squadre.</li>
                    <li><strong>Elimina Tutti gli Scambi:</strong> Cancella lo storico degli scambi.</li>
                    <li><strong>Elimina Risultati e Formazioni:</strong> Cancella risultati gare e formazioni schierate.</li>
                    <li class="text-danger-600"><strong>RESET COMPLETO LEGA:</strong> Combina tutte le azioni sopra. Utile per iniziare una nuova stagione.</li>
                    <li class="text-danger-600"><strong>ELIMINA TUTTE LE SQUADRE:</strong> Elimina completamente tutte le squadre. Gli utenti dovranno ricrearne di nuove.</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
