<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $race->name }}
            </h2>
            <a href="{{ route('races.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna alle Gare
            </a>
        </div>
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

            {{-- Info Gara --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Gara</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Data:</dt>
                                <dd class="text-sm font-medium">{{ $race->date->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Tipo:</dt>
                                <dd class="text-sm font-medium">{{ $race->type_label }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Corridori schierabili:</dt>
                                <dd class="text-sm font-medium">{{ $race->lineup_size }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Stato:</dt>
                                <dd>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $race->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $race->status === 'lineup_open' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $race->status === 'upcoming' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $race->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ $race->status_label }}
                                    </span>
                                </dd>
                            </div>
                            @if($race->lineup_deadline)
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">Deadline formazione:</dt>
                                    <dd class="text-sm font-medium">{{ $race->lineup_deadline->format('d/m/Y H:i') }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if($race->description)
                            <p class="mt-4 text-sm text-gray-600">{{ $race->description }}</p>
                        @endif
                    </div>

                    {{-- La tua formazione --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">La tua Formazione</h3>

                        @if($lineup)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-2">
                                    Corridori schierati: {{ $lineup->getRidersCount() }}/{{ $race->lineup_size }}
                                </p>
                                <ul class="space-y-2">
                                    @foreach($lineup->riders as $rider)
                                        <li class="flex justify-between items-center py-1 border-b border-gray-200 last:border-0">
                                            <span class="text-sm">{{ $rider->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $rider->category->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if($race->hasResults())
                                    <div class="mt-4 p-3 bg-green-100 rounded-lg">
                                        <p class="text-sm font-semibold text-green-800">
                                            Crediti guadagnati: {{ $teamCredits }}M
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($race->isLineupOpen())
                                <a href="{{ route('races.lineup', $race) }}"
                                   class="mt-4 block w-full px-4 py-2 bg-indigo-600 text-white text-center text-sm rounded-lg hover:bg-indigo-700">
                                    Modifica Formazione
                                </a>
                            @endif
                        @else
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-sm text-yellow-800">Non hai ancora schierato una formazione.</p>

                                @if($race->isLineupOpen())
                                    <a href="{{ route('races.lineup', $race) }}"
                                       class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                        Schiera Formazione
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Risultati Gara --}}
            @if($race->hasResults())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Risultati</h3>
                        <a href="{{ route('races.standings', $race) }}"
                           class="text-sm text-indigo-600 hover:text-indigo-800">
                            Vedi Classifica Squadre →
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pos.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Corridore</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Crediti</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($results as $result)
                                    @php
                                        $isInMyLineup = $lineup && $lineup->hasRider($result->rider);
                                    @endphp
                                    <tr class="{{ $isInMyLineup ? 'bg-green-50' : '' }}">
                                        <td class="px-4 py-3 text-sm font-medium">
                                            @if($result->position <= 3)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full
                                                    {{ $result->position === 1 ? 'bg-yellow-400' : '' }}
                                                    {{ $result->position === 2 ? 'bg-gray-300' : '' }}
                                                    {{ $result->position === 3 ? 'bg-amber-600' : '' }}
                                                    text-white text-xs font-bold">
                                                    {{ $result->position }}
                                                </span>
                                            @else
                                                {{ $result->position }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $result->rider->name }}
                                            @if($isInMyLineup)
                                                <span class="ml-2 text-xs text-green-600">(tuo)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $result->rider->category->name }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-medium text-green-600">
                                            +{{ $result->credits_earned }}M
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
