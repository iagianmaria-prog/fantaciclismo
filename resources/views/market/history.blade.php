<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📜 Storico Scambi
            </h2>
            <a href="{{ route('market.show') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna al Mercato
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">
                    Tutti gli Scambi Completati, Rifiutati e Cancellati
                </h3>

                @forelse($trades as $trade)
                    <div class="border border-gray-200 rounded-lg p-6 mb-4 bg-gray-50">
                        
                        {{-- Header con squadre e status --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Proponente</p>
                                    <p class="font-semibold text-gray-900">
                                        {{ $trade->offeringTeam->name }}
                                        @if($trade->offeringTeam->id === $myTeam->id)
                                            <span class="text-xs text-indigo-600">(Tu)</span>
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="text-2xl text-gray-400">⇄</div>
                                
                                <div class="text-center">
                                    <p class="text-xs text-gray-500">Destinatario</p>
                                    <p class="font-semibold text-gray-900">
                                        {{ $trade->receivingTeam->name }}
                                        @if($trade->receivingTeam->id === $myTeam->id)
                                            <span class="text-xs text-indigo-600">(Tu)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Badge Status --}}
                            @php
                                $statusConfig = [
                                    'accepted' => ['text' => 'Accettata', 'icon' => '✅', 'color' => 'green'],
                                    'rejected' => ['text' => 'Rifiutata', 'icon' => '❌', 'color' => 'red'],
                                    'cancelled' => ['text' => 'Cancellata', 'icon' => '🗑️', 'color' => 'gray'],
                                ];
                                $config = $statusConfig[$trade->status] ?? ['text' => $trade->status, 'icon' => '❓', 'color' => 'gray'];
                            @endphp
                            
                            <div class="text-right">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                    {{ $config['icon'] }} {{ $config['text'] }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $trade->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        {{-- Dettagli Scambio --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Corridori offerti --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">
                                    {{ $trade->offeringTeam->name }} ha ceduto:
                                </h4>
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

                            {{-- Corridori ricevuti --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">
                                    {{ $trade->receivingTeam->name }} ha ceduto:
                                </h4>
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

                        {{-- Money Adjustment --}}
                        @if($trade->money_adjustment != 0)
                            <div class="mt-4 p-3 bg-blue-50 rounded-md border border-blue-200">
                                <p class="text-sm font-medium text-blue-900">
                                    💰 Movimento monetario: 
                                    @if($trade->money_adjustment > 0)
                                        <strong>{{ $trade->offeringTeam->name }}</strong> ha dato 
                                        <span class="text-green-600 font-semibold">{{ $trade->money_adjustment }}M</span> 
                                        a <strong>{{ $trade->receivingTeam->name }}</strong>
                                    @else
                                        <strong>{{ $trade->receivingTeam->name }}</strong> ha dato 
                                        <span class="text-green-600 font-semibold">{{ abs($trade->money_adjustment) }}M</span> 
                                        a <strong>{{ $trade->offeringTeam->name }}</strong>
                                    @endif
                                </p>
                            </div>
                        @endif
                        
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg">📭 Nessuno scambio nello storico</p>
                        <p class="text-gray-400 text-sm mt-2">Gli scambi completati, rifiutati o cancellati appariranno qui</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>