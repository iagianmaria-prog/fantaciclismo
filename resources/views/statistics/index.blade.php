<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📊 Statistiche - {{ $myTeam->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- SEZIONE 1: OVERVIEW GENERALE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Panoramica Generale</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Card Valore Rosa --}}
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <p class="text-sm text-blue-600 font-medium">Valore Totale Rosa</p>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($totalValue, 0, ',', '.') }}M</p>
                    </div>
                    
                    {{-- Card Budget --}}
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-600 font-medium">Budget Disponibile</p>
                        <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($currentBudget, 0, ',', '.') }}M</p>
                    </div>
                    
                    {{-- Card Corridori --}}
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                        <p class="text-sm text-purple-600 font-medium">Corridori in Rosa</p>
                        <p class="text-3xl font-bold text-purple-900 mt-2">{{ $totalRiders }}</p>
                    </div>
                    
                    {{-- Card Media Valore --}}
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                        <p class="text-sm text-orange-600 font-medium">Valore Medio</p>
                        <p class="text-3xl font-bold text-orange-900 mt-2">{{ number_format($averageValue, 1, ',', '.') }}M</p>
                    </div>
                </div>
            </div>

            {{-- SEZIONE 2: CORRIDORI PER CATEGORIA --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🚴 Distribuzione per Categoria</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($ridersByCategory as $categoryName => $data)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-semibold text-gray-900">{{ $categoryName }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 rounded-full">
                                    {{ $data['count'] }} corridori
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Valore totale: <strong>{{ number_format($data['total_value'], 0, ',', '.') }}M</strong>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Media: {{ number_format($data['total_value'] / $data['count'], 1, ',', '.') }}M
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-3 text-center">Nessun corridore in rosa</p>
                    @endforelse
                </div>
            </div>

            {{-- SEZIONE 3: STATISTICHE SCAMBI --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔄 Statistiche Scambi</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                        <p class="text-3xl font-bold text-green-600">{{ $tradesAccepted }}</p>
                        <p class="text-sm text-gray-600 mt-1">✅ Accettati</p>
                    </div>
                    
                    <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-3xl font-bold text-red-600">{{ $tradesRejected }}</p>
                        <p class="text-sm text-gray-600 mt-1">❌ Rifiutati</p>
                    </div>
                    
                    <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-3xl font-bold text-gray-600">{{ $tradesCancelled }}</p>
                        <p class="text-sm text-gray-600 mt-1">🗑️ Cancellati</p>
                    </div>
                    
                    <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-3xl font-bold text-yellow-600">{{ $tradesPending }}</p>
                        <p class="text-sm text-gray-600 mt-1">⏳ In Attesa</p>
                    </div>
                </div>

                {{-- Bilancio Crediti Scambi --}}
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-semibold text-blue-900 mb-3">💰 Bilancio Crediti negli Scambi</h4>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm text-gray-600">Crediti Ricevuti</p>
                            <p class="text-2xl font-bold text-green-600">+{{ number_format($creditsReceived, 0, ',', '.') }}M</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Crediti Pagati</p>
                            <p class="text-2xl font-bold text-red-600">-{{ number_format($creditsPaid, 0, ',', '.') }}M</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Bilancio Netto</p>
                            <p class="text-2xl font-bold {{ $creditsBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $creditsBalance >= 0 ? '+' : '' }}{{ number_format($creditsBalance, 0, ',', '.') }}M
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEZIONE 4: STATISTICHE FINANZIARIE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💵 Situazione Finanziaria</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-gray-700">Budget Iniziale</span>
                        <span class="font-semibold text-gray-900">{{ number_format($initialBudget, 0, ',', '.') }}M</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <span class="text-gray-700">Spesa Totale (al netto scambi)</span>
                        <span class="font-semibold text-red-600">-{{ number_format($totalSpent, 0, ',', '.') }}M</span>
                    </div>
                    
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border-2 border-green-300">
                        <span class="text-gray-700 font-semibold">Budget Attuale</span>
                        <span class="font-bold text-green-600 text-xl">{{ number_format($currentBudget, 0, ',', '.') }}M</span>
                    </div>
                </div>
            </div>

            {{-- SEZIONE 5: CLASSIFICA SQUADRE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Classifica per Valore Rosa</h3>
                
                <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200 text-center">
                    <p class="text-sm text-indigo-600">La tua posizione</p>
                    <p class="text-4xl font-bold text-indigo-900">{{ $myPosition }}° / {{ $allTeams->count() }}</p>
                </div>
                
                <div class="space-y-2">
                    @foreach($allTeams as $index => $team)
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $team['id'] === $myTeam->id ? 'bg-indigo-100 border-2 border-indigo-400' : 'bg-gray-50' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold {{ $index < 3 ? 'text-yellow-600' : 'text-gray-500' }}">
                                    #{{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ $team['name'] }}
                                        @if($team['id'] === $myTeam->id)
                                            <span class="text-xs text-indigo-600">(Tu)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $team['riders_count'] }} corridori</p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-bold text-gray-900">{{ number_format($team['total_value'], 0, ',', '.') }}M</p>
                                <p class="text-xs text-gray-500">Budget: {{ number_format($team['balance'], 0, ',', '.') }}M</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- LINK RAPIDI --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Link Rapidi</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('dashboard') }}" class="block p-4 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition">
                        <p class="font-semibold text-blue-900">📋 Dashboard</p>
                        <p class="text-sm text-gray-600 mt-1">Visualizza il tuo roster</p>
                    </a>
                    
                    <a href="{{ route('market.show') }}" class="block p-4 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition">
                        <p class="font-semibold text-green-900">💼 Mercato</p>
                        <p class="text-sm text-gray-600 mt-1">Proponi e gestisci scambi</p>
                    </a>
                    
                    <a href="{{ route('market.history') }}" class="block p-4 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition">
                        <p class="font-semibold text-purple-900">📜 Storico</p>
                        <p class="text-sm text-gray-600 mt-1">Vedi tutti gli scambi</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>