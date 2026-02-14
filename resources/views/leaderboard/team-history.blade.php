<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Storico Crediti - {{ $team->name }}
            </h2>
            <a href="{{ route('leaderboard.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna alla Classifica
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Riepilogo --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Squadra</div>
                    <div class="text-xl font-bold text-gray-900">{{ $team->name }}</div>
                    <div class="text-sm text-gray-500">Manager: {{ $team->user->name }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Gare Partecipate</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $raceHistory->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Crediti Totali</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($totalCredits) }} M</div>
                </div>
            </div>

            {{-- Storico Gare --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Storico Gare</h3>

                    @if($raceHistory->isEmpty())
                        <p class="text-gray-500 text-center py-8">Questa squadra non ha ancora partecipato a gare completate.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($raceHistory as $item)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $item['race']->name }}</h4>
                                            <p class="text-sm text-gray-500">
                                                {{ $item['race']->date->format('d/m/Y') }} - {{ $item['race']->type_label ?? $item['race']->type }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-green-600">+{{ $item['total_credits'] }} M</div>
                                        </div>
                                    </div>

                                    @if(count($item['rider_results']) > 0)
                                        <div class="mt-3 border-t border-gray-100 pt-3">
                                            <p class="text-xs text-gray-500 mb-2">Corridori a punti:</p>
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                                @foreach($item['rider_results'] as $riderResult)
                                                    <div class="flex justify-between items-center bg-gray-50 rounded px-2 py-1">
                                                        <span class="text-sm text-gray-700">
                                                            <span class="font-medium">{{ $riderResult['position'] }}°</span>
                                                            {{ $riderResult['rider']->name }}
                                                        </span>
                                                        <span class="text-sm font-medium text-green-600">+{{ $riderResult['credits'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 italic mt-2">Nessun corridore a punti in questa gara.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
