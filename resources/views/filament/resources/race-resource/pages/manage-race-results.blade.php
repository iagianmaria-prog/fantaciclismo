<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info Gara --}}
        <x-filament::section>
            <x-slot name="heading">
                Informazioni Gara
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Data:</span>
                    <span class="font-medium">{{ $this->record->date->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Tipo:</span>
                    <span class="font-medium">{{ $this->record->type_label }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Stato:</span>
                    <x-filament::badge
                        :color="match($this->record->status) {
                            'upcoming' => 'warning',
                            'lineup_open' => 'success',
                            'in_progress' => 'info',
                            'completed' => 'gray',
                            default => 'gray',
                        }"
                    >
                        {{ $this->record->status_label }}
                    </x-filament::badge>
                </div>
                <div>
                    <span class="text-gray-500">Formazioni:</span>
                    <span class="font-medium">{{ $this->record->lineups()->count() }}</span>
                </div>
            </div>
        </x-filament::section>

        {{-- Import CSV --}}
        <x-filament::section>
            <x-slot name="heading">
                Import Risultati da CSV
            </x-slot>

            <form wire:submit="importCsv" class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500 mb-2">
                        Formato CSV: <code>nome_corridore, posizione, crediti</code>
                    </p>
                    <input
                        type="file"
                        wire:model="csvFile"
                        accept=".csv,.txt"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    >
                    @error('csvFile')
                        <span class="text-sm text-danger-600">{{ $message }}</span>
                    @enderror
                </div>

                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Importa CSV</span>
                    <span wire:loading>Importando...</span>
                </x-filament::button>
            </form>
        </x-filament::section>

        {{-- Tabella Risultati --}}
        <x-filament::section>
            <x-slot name="heading">
                Risultati
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
