<x-guest-layout>
    <form method="POST" action="{{ route('player-team.store') }}">
        @csrf

        <!-- Team Name -->
        <div>
            <x-input-label for="name" :value="__('Dai un nome alla tua squadra')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Crea Squadra') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
