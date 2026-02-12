<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Formazione - {{ $race->name }}
            </h2>
            <a href="{{ route('races.show', $race) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                ← Torna alla Gara
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            @if ($errors->any())
                <div class="p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900">{{ $race->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $race->date->format('d/m/Y') }} - {{ $race->type_label }}</p>
                    @if($race->lineup_deadline)
                        <p class="text-sm text-red-600 mt-1">
                            Deadline: {{ $race->lineup_deadline->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>

                <form method="POST" action="{{ route('races.lineup.save', $race) }}" id="lineupForm">
                    @csrf

                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            Seleziona <strong>{{ $maxRiders }} corridori</strong> dal tuo roster.
                            <span id="selectedCount" class="font-bold">{{ count($selectedRiderIds) }}</span>/{{ $maxRiders }} selezionati.
                        </p>
                    </div>

                    <div class="space-y-2 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
                        @forelse($availableRiders as $rider)
                            <label class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 cursor-pointer border border-transparent has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           name="riders[]"
                                           value="{{ $rider->id }}"
                                           {{ in_array($rider->id, $selectedRiderIds) ? 'checked' : '' }}
                                           class="rider-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-900">{{ $rider->name }}</span>
                                </div>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $rider->category->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-4">Non hai corridori nel roster.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150">
                            Salva Formazione
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxRiders = {{ $maxRiders }};
            const checkboxes = document.querySelectorAll('.rider-checkbox');
            const countDisplay = document.getElementById('selectedCount');

            function updateCount() {
                const checked = document.querySelectorAll('.rider-checkbox:checked').length;
                countDisplay.textContent = checked;

                // Disabilita i non selezionati se si raggiunge il max
                checkboxes.forEach(cb => {
                    if (!cb.checked && checked >= maxRiders) {
                        cb.disabled = true;
                        cb.closest('label').classList.add('opacity-50');
                    } else {
                        cb.disabled = false;
                        cb.closest('label').classList.remove('opacity-50');
                    }
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCount);
            });

            updateCount();
        });
    </script>
</x-app-layout>
