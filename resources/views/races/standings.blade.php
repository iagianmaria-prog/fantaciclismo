<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Classifica - {{ $race->name }}
            </h2>
            <a href="{{ route('races.show', $race) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna alla Gara
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Classifica Squadre</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pos.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Squadra</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Corridori</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Crediti</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($teamStandings as $index => $standing)
                                @php
                                    $position = $index + 1;
                                    $isMyTeam = $standing['team']->id === auth()->user()->playerTeam->id;
                                @endphp
                                <tr class="{{ $isMyTeam ? 'bg-indigo-50' : '' }}">
                                    <td class="px-4 py-4 text-sm font-medium">
                                        @if($position <= 3)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                                {{ $position === 1 ? 'bg-yellow-400' : '' }}
                                                {{ $position === 2 ? 'bg-gray-300' : '' }}
                                                {{ $position === 3 ? 'bg-amber-600' : '' }}
                                                text-white text-sm font-bold">
                                                {{ $position }}
                                            </span>
                                        @else
                                            <span class="ml-2">{{ $position }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $standing['team']->name }}
                                            @if($isMyTeam)
                                                <span class="ml-2 text-xs text-indigo-600">(tu)</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $standing['team']->user->name }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center text-gray-500">
                                        {{ $standing['riders_count'] }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-right font-bold text-green-600">
                                        +{{ $standing['credits'] }}M
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        Nessuna squadra ha partecipato a questa gara.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
