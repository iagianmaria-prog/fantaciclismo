<div>
    <form wire:submit.prevent="submitTrade">
        <div class="space-y-4">
            
            <div>
                <label for="team" class="block text-sm font-medium text-gray-700">Seleziona una squadra con cui scambiare:</label>
                <select id="team" wire:model.live="selectedTeamId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Scegli una squadra...</option>
                    @foreach($otherTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedTeamId && $selectedTeamRoster)
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Il Tuo Roster</h4>
                        <p class="text-sm text-gray-500">Seleziona i corridori che vuoi offrire.</p>
                        <div class="mt-4 space-y-2 border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                            @forelse($myTeamRoster as $rider)
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" wire:model="offeredRiderIds" value="{{ $rider->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span>{{ $rider->name }} <span class="text-xs text-gray-500">({{ $rider->category->name }})</span></span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Non hai corridori nel roster.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-medium text-gray-900">Roster di {{ $otherTeams->find($selectedTeamId)->name }}</h4>
                        <p class="text-sm text-gray-500">Seleziona i corridori che vuoi richiedere.</p>
                        <div class="mt-4 space-y-2 border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                            @forelse($selectedTeamRoster as $rider)
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" wire:model="requestedRiderIds" value="{{ $rider->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span>{{ $rider->name }} <span class="text-xs text-gray-500">({{ $rider->category->name }})</span></span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">Questa squadra non ha corridori nel roster.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

              {{-- Campo Money Adjustment --}}
                <div class="mt-6">
                    <label for="money" class="block text-sm font-medium text-gray-700 mb-2">
                        💰 Aggiustamento Monetario (opzionale)
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="money" 
                               wire:model="moneyAdjustment" 
                               placeholder="0"
                               class="block w-full pl-3 pr-20 py-2 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Fantamilioni</span>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        <strong>Come funziona:</strong>
                    </p>
                    <ul class="mt-1 text-xs text-gray-500 list-disc list-inside space-y-1">
                        <li><strong>Valore positivo</strong> (es. +50): Tu RICEVI 50M dall'altra squadra</li>
                        <li><strong>Valore negativo</strong> (es. -30): Tu DAI 30M all'altra squadra</li>
                        <li><strong>Zero o vuoto</strong>: Nessun scambio di denaro</li>
                    </ul>
                    <div class="mt-3 p-3 bg-blue-50 rounded-md border border-blue-200">
                        <p class="text-xs text-blue-800">
                            💡 <strong>Esempi:</strong><br>
                            • Vuoi un corridore più forte? Offri denaro (valore negativo)<br>
                            • Cedi un corridore forte? Chiedi denaro (valore positivo)<br>
                            • Puoi anche fare uno scambio SOLO di denaro (senza corridori)
                        </p>
                    </div>
                </div>

                {{-- Bottone Invia --}}
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150">
                        📤 Invia Proposta di Scambio
                    </button>
                </div>
            @endif

        </div>
    </form>
</div>
