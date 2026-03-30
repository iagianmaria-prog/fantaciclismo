<x-app-layout>
    <x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mercato Scambi
        </h2>
        <a href="{{ route('market.history') }}" 
           class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
            📜 Vedi Storico Scambi →
        </a>
    </div>
</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Sezione per mostrare messaggi di successo e errore --}}
            @if (session('status'))
                <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Sezione Offerte Ricevute --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Offerte Ricevute</h3>
                <div class="mt-4 space-y-4">
                    @forelse($receivedTrades as $trade)
                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Proposta da:</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $trade->offeringTeam->name }}</p>
                                </div>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    In Attesa
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                {{-- Corridori offerti dalla squadra proponente --}}
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Ti offrono:</h4>
                                    <div class="bg-white rounded-md p-3 border border-gray-200">
                                        @php
                                            $offeredRiders = $trade->riders()->wherePivot('direction', 'offering')->get();
                                        @endphp
                                        @forelse($offeredRiders as $rider)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                                <span class="text-sm text-gray-900">{{ $rider->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Corridori richiesti (dal tuo roster) --}}
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Ti chiedono:</h4>
                                    <div class="bg-white rounded-md p-3 border border-gray-200">
                                        @php
                                            $requestedRiders = $trade->riders()->wherePivot('direction', 'receiving')->get();
                                        @endphp
                                        @forelse($requestedRiders as $rider)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                                <span class="text-sm text-gray-900">{{ $rider->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Money adjustment --}}
                            @if($trade->money_adjustment != 0)
                                <div class="mb-4 p-3 bg-blue-50 rounded-md">
                                    <p class="text-sm font-medium text-blue-900">
                                        💰 Aggiustamento monetario:
                                        @if($trade->money_adjustment > 0)
                                            <span class="text-red-600">Tu paghi {{ $trade->money_adjustment }}M</span>
                                        @else
                                            <span class="text-green-600">Tu ricevi {{ abs($trade->money_adjustment) }}M</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            {{-- Bottoni Azione --}}
<div class="space-y-3">
    <div class="grid grid-cols-2 gap-3">
        <form method="POST" action="{{ route('market.accept', $trade) }}">
            @csrf
            <button type="submit"
                    class="w-full px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition duration-150">
                Accetta
            </button>
        </form>

        <button type="button"
                onclick="document.getElementById('reject-form-{{ $trade->id }}').classList.toggle('hidden')"
                class="w-full px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition duration-150">
            Rifiuta
        </button>
    </div>

    {{-- Form rifiuto con motivo --}}
    <form id="reject-form-{{ $trade->id }}" method="POST" action="{{ route('market.reject', $trade) }}" class="hidden">
        @csrf
        <div class="bg-red-50 border border-red-200 rounded-lg p-3 space-y-3">
            <label for="rejection_reason_{{ $trade->id }}" class="block text-sm font-medium text-red-700">
                Motivo del rifiuto (opzionale):
            </label>
            <textarea
                id="rejection_reason_{{ $trade->id }}"
                name="rejection_reason"
                rows="2"
                class="w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                placeholder="Es: Non mi interessa questo scambio, preferisco tenere il mio corridore..."
            ></textarea>
            <button type="submit"
                    class="w-full px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition duration-150">
                Conferma Rifiuto
            </button>
        </div>
    </form>
</div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500">📭 Nessuna offerta di scambio ricevuta al momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sezione Offerte Inviate --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Offerte Inviate</h3>
                <div class="mt-4 space-y-4">
                    @forelse($proposedTrades as $trade)
                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Proposta a:</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $trade->receivingTeam->name }}</p>
                                </div>
                                @php
                                    $statusConfig = [
                                        'pending' => ['text' => 'In Attesa', 'color' => 'yellow'],
                                        'accepted' => ['text' => 'Accettata ✅', 'color' => 'green'],
                                        'rejected' => ['text' => 'Rifiutata ❌', 'color' => 'red'],
                                        'cancelled' => ['text' => 'Cancellata', 'color' => 'gray'],
                                    ];
                                    $config = $statusConfig[$trade->status] ?? ['text' => $trade->status, 'color' => 'gray'];
                                @endphp
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                    {{ $config['text'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                {{-- Corridori che hai offerto --}}
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Hai offerto:</h4>
                                    <div class="bg-white rounded-md p-3 border border-gray-200">
                                        @php
                                            $offeredRiders = $trade->riders()->wherePivot('direction', 'offering')->get();
                                        @endphp
                                        @forelse($offeredRiders as $rider)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                                <span class="text-sm text-gray-900">{{ $rider->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Corridori che hai richiesto --}}
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Hai richiesto:</h4>
                                    <div class="bg-white rounded-md p-3 border border-gray-200">
                                        @php
                                            $requestedRiders = $trade->riders()->wherePivot('direction', 'receiving')->get();
                                        @endphp
                                        @forelse($requestedRiders as $rider)
                                            <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                                <span class="text-sm text-gray-900">{{ $rider->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 italic">Nessun corridore</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Money adjustment --}}
                            @if($trade->money_adjustment != 0)
                                <div class="mb-4 p-3 bg-blue-50 rounded-md">
                                    <p class="text-sm font-medium text-blue-900">
                                        💰 Aggiustamento monetario:
                                        @if($trade->money_adjustment > 0)
                                            <span class="text-green-600">Tu ricevi {{ $trade->money_adjustment }}M</span>
                                        @else
                                            <span class="text-red-600">Tu paghi {{ abs($trade->money_adjustment) }}M</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            {{-- Bottone Cancella (solo se pending) --}}
                            @if($trade->status === 'pending')
                                <form method="POST" action="{{ route('market.cancel', $trade) }}">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Sei sicuro di voler cancellare questa proposta?')"
                                            class="w-full px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition duration-150">
                                        🗑️ Cancella Proposta
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500">📤 Non hai inviato nessuna offerta di scambio.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sezione Nuova Offerta --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Crea una Nuova Offerta</h3>
                
                {{-- Carichiamo il nostro componente Livewire interattivo --}}
                @livewire('create-trade-form')

            </div>

        </div>
    </div>
</x-app-layout>