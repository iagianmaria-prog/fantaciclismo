<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gare
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Gare con formazioni aperte --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Gare in programma</h3>

                @forelse($upcomingRaces as $race)
                    <div class="border border-gray-200 rounded-lg p-4 mb-4 {{ $race->isLineupOpen() ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $race->name }}</h4>
                                <p class="text-sm text-gray-600">
                                    {{ $race->date->format('d/m/Y') }} - {{ $race->type_label }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Corridori schierabili: {{ $race->lineup_size }}
                                </p>
                                @if($race->lineup_deadline)
                                    <p class="text-xs text-gray-500">
                                        Deadline formazione: {{ $race->lineup_deadline->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $race->status === 'lineup_open' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $race->status_label }}
                                </span>

                                @php
                                    $myLineup = $race->getLineupForTeam($team);
                                @endphp

                                @if($myLineup)
                                    <p class="text-xs text-green-600 mt-2">
                                        Formazione: {{ $myLineup->getRidersCount() }}/{{ $race->lineup_size }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('races.show', $race) }}"
                               class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">
                                Dettagli
                            </a>
                            @if($race->isLineupOpen())
                                <a href="{{ route('races.lineup', $race) }}"
                                   class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                    {{ $myLineup ? 'Modifica Formazione' : 'Schiera Formazione' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">Nessuna gara in programma.</p>
                @endforelse
            </div>

            {{-- Gare completate --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Gare completate</h3>

                @forelse($completedRaces as $race)
                    <div class="border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">{{ $race->name }}</h4>
                                <p class="text-sm text-gray-600">
                                    {{ $race->date->format('d/m/Y') }} - {{ $race->type_label }}
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                Completata
                            </span>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('races.show', $race) }}"
                               class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700">
                                Risultati
                            </a>
                            <a href="{{ route('races.standings', $race) }}"
                               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                Classifica
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">Nessuna gara completata.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
