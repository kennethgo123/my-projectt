<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Law Firm Dashboard') }}
        </h2>
    </x-slot>

    @livewire('law-firm.dashboard')

</x-app-layout> 