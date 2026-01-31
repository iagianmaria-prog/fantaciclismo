<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔄 Proponi Controfferta
            </h2>
            <a href="{{ route('market.show') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna al Mercato
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Messaggi --}}
            @if (session('error'))
                <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- SEZIONE: Offerta Originale --}}
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-4">📋 Offerta Originale da {{ $theirTeam->name }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Ti offrivano:</h4>
                        <div class="bg-white rounded-md p-3 border border-yellow-200">
                            @forelse($offeredRiders as $rider)
                                <div class="flex justify-between py-2 border-b last:border-b-0">
                                    <span class="text-sm">{{ $rider->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                            @endforelse
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Ti chiedevano:</h4>
                        <div class="bg-white rounded-md p-3 border border-yellow-200">
                            @forelse($requestedRiders as $rider)
                                <div class="flex justify-between py-2 border-b last:border-b-0">
                                    <span class="text-sm">{{ $rider->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($originalTrade->money_adjustment != 0)
                    <div class="mt-4 p-3 bg-yellow-100 rounded-md">
                        <p class="text-sm font-medium text-yellow-900">
                            💰 Crediti: {{ $originalTrade->money_adjustment > 0 ? '+' : '' }}{{ $originalTrade->money_adjustment }}M per te
                        </p>
                    </div>
                @endif

                <div class="mt-4 p-3 bg-red-50 rounded-md border border-red-200">
                    <p class="text-sm text-red-800">
                        ⚠️ <strong>Attenzione:</strong> Inviando una controfferta, rifiuterai automaticamente questa proposta originale.
                    </p>
                </div>
            </div>

            {{-- SEZIONE: Nuova Controfferta --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">✏️ Modifica la Proposta</h3>
                
                <form method="POST" action="{{ route('market.counter-offer.submit', $originalTrade) }}">
                    @csrf
                    <form method="POST" action="{{ route('market.counter-offer.submit', $originalTrade) }}">
    @csrf
    
    {{-- DEBUG: Mostra errori validazione --}}
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
            <strong>Errori di validazione:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        {{-- Il TUO Roster --}}
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-2">🚴 Corridori che OFFRI (dal tuo roster)</h4>
                            <p class="text-sm text-gray-600 mb-3">Seleziona i corridori che vuoi cedere</p>
                            
                            <div class="bg-gray-50 rounded-md p-4 border border-gray-200 max-h-96 overflow-y-auto">
                                @forelse($myRoster as $rider)
                                    <label class="flex items-center space-x-3 py-2 hover:bg-gray-100 rounded px-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="offered_riders[]" 
                                               value="{{ $rider->id }}"
                                               {{ $requestedRiders->contains($rider->id) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="flex-1 text-sm">{{ $rider->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500 italic">Nessun corridore disponibile</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Il LORO Roster --}}
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-2">🎯 Corridori che CHIEDI (dal loro roster)</h4>
                            <p class="text-sm text-gray-600 mb-3">Seleziona i corridori che vuoi ricevere</p>
                            
                            <div class="bg-gray-50 rounded-md p-4 border border-gray-200 max-h-96 overflow-y-auto">
                                @forelse($theirRoster as $rider)
                                    <label class="flex items-center space-x-3 py-2 hover:bg-gray-100 rounded px-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="requested_riders[]" 
                                               value="{{ $rider->id }}"
                                               {{ $offeredRiders->contains($rider->id) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="flex-1 text-sm">{{ $rider->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500 italic">Nessun corridore disponibile</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Campo Crediti --}}
                    <div class="mb-6">
                        <label for="money" class="block text-sm font-medium text-gray-700 mb-2">
                            💰 Aggiustamento Monetario (opzionale)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="money" 
                                   name="money_adjustment" 
                                   value="{{ -$originalTrade->money_adjustment }}"
                                   placeholder="0"
                                   class="block w-full pl-3 pr-20 py-2 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">Fantamilioni</span>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            <strong>Positivo:</strong> Tu ricevi denaro | <strong>Negativo:</strong> Tu paghi denaro
                        </p>
                    </div>

                    {{-- Bottoni --}}
                    <div class="flex gap-4">
                        <button type="submit" 
                                class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                            ✅ Invia Controfferta
                        </button>
                        <a href="{{ route('market.show') }}" 
                           class="flex-1 px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition text-center">
                            ❌ Annulla
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>