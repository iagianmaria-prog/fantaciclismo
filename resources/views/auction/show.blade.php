<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asta Pubblica
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Info Asta Attiva --}}
            @if(isset($activeAuction) && $activeAuction)
                <div class="mb-6 p-4 rounded-lg {{ $activeAuction->type === 'repair' ? 'bg-orange-100 border border-orange-300' : 'bg-blue-100 border border-blue-300' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-lg {{ $activeAuction->type === 'repair' ? 'text-orange-800' : 'text-blue-800' }}">
                                {{ $activeAuction->type === 'repair' ? '🔧 Asta di Riparazione' : '🏁 Asta Iniziale' }}
                            </h3>
                            <p class="text-sm {{ $activeAuction->type === 'repair' ? 'text-orange-700' : 'text-blue-700' }}">
                                {{ $activeAuction->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm {{ $activeAuction->type === 'repair' ? 'text-orange-700' : 'text-blue-700' }}">
                                Durata contratto: <strong>{{ $contractDuration }} {{ $contractDuration == 1 ? 'anno' : 'anni' }}</strong>
                            </p>
                            @if($activeAuction->ends_at)
                                <p class="text-xs {{ $activeAuction->type === 'repair' ? 'text-orange-600' : 'text-blue-600' }}">
                                    Termina: {{ \Carbon\Carbon::parse($activeAuction->ends_at)->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 rounded-lg bg-gray-100 border border-gray-300">
                    <p class="text-gray-600 text-center">
                        ⚠️ Nessuna asta attiva al momento. Contatta l'amministratore.
                    </p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Corridori Svincolati</h3>

                    {{-- Sezione per mostrare messaggi di successo o errore --}}
                    @if (session('status'))
                        <div class="mt-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mt-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nome
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoria
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valore
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contratto
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Azione</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($riders as $rider)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $rider->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $rider->category->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $rider->initial_value }}M
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($rider->contract_years)
                                                {{ $rider->contract_years }} anni
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form method="POST" action="{{ route('auction.buy', $rider) }}">
                                                @csrf
                                                <x-primary-button>Acquista</x-primary-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Nessun corridore svincolato disponibile.
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
