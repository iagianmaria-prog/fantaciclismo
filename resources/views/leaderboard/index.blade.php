<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Classifica Generale
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Statistiche Globali --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">La Tua Posizione</div>
                    <div class="text-3xl font-bold text-indigo-600">
                        @if($myPosition <= 3)
                            @if($myPosition == 1) @endif
                            @if($myPosition == 2) @endif
                            @if($myPosition == 3) @endif
                        @endif
                        {{ $myPosition }}°
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Gare Completate</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $completedRaces }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Crediti Totali Distribuiti</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($totalCreditsDistributed) }} M</div>
                </div>
            </div>

            {{-- Classifica --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Classifica Squadre</h3>

                    @if($teams->isEmpty())
                        <p class="text-gray-500 text-center py-8">Nessuna squadra in classifica. Le gare non sono ancora state completate.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Squadra</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Gare</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Corridori</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Crediti</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Dettagli</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($teams as $index => $item)
                                        @php
                                            $position = $index + 1;
                                            $isMyTeam = $item['team']->id === $myTeam->id;
                                        @endphp
                                        <tr class="{{ $isMyTeam ? 'bg-indigo-50' : '' }} hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($position == 1)
                                                        <span class="text-2xl mr-2">🥇</span>
                                                    @elseif($position == 2)
                                                        <span class="text-2xl mr-2">🥈</span>
                                                    @elseif($position == 3)
                                                        <span class="text-2xl mr-2">🥉</span>
                                                    @else
                                                        <span class="text-lg font-bold text-gray-400 mr-2">{{ $position }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item['team']->name }}
                                                    @if($isMyTeam)
                                                        <span class="ml-2 px-2 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-full">Tu</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $item['team']->user->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm text-gray-900">{{ $item['races_participated'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="text-sm text-gray-900">{{ $item['riders_count'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-bold {{ $item['total_credits'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                                    {{ number_format($item['total_credits']) }} M
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('leaderboard.team', $item['team']) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    Vedi Storico
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
