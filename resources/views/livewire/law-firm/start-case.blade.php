<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Start a New Case') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif
                
                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <form wire:submit="createCase">
                    <div class="space-y-6">
                        <!-- Client Information (if pre-selected from consultation) -->
                        @if(isset($clientName))
                            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                                <h3 class="text-lg font-medium text-blue-900 mb-2">Selected Client</h3>
                                <p class="text-blue-700"><strong>Name:</strong> {{ $clientName }}</p>
                                <p class="text-blue-700"><strong>Email:</strong> {{ $clientEmail }}</p>
                                <input type="hidden" wire:model="client_id">
                            </div>
                        @else
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Please use the "Start Case" button from a completed consultation to create a case.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Case Title -->
                        <div>
                            <x-label for="title" value="{{ __('Case Title') }}" />
                            <x-input id="title" type="text" class="mt-1 block w-full" wire:model="title" placeholder="Enter a title for the case" />
                            @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Legal Service -->
                        <div>
                            <x-label for="service_id" value="{{ __('Legal Service Required') }}" />
                            <select id="service_id" wire:model="service_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                <option value="">Select a service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                            @error('service_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Case Description -->
                        <div>
                            <x-label for="description" value="{{ __('Description of the case') }}" />
                            <textarea id="description" rows="6" wire:model="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" placeholder="Please describe the legal issue in detail..."></textarea>
                            @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Contract Upload -->
                        <div>
                            <x-label for="contract" value="{{ __('Upload Contract (optional)') }}" />
                            <div class="mt-1 flex items-center">
                                <label class="block w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                    <span>{{ $contract ? 'Change Contract' : 'Upload Contract' }}</span>
                                    <input id="contract" type="file" wire:model="contract" class="sr-only" accept=".pdf,.doc,.docx">
                                </label>
                            </div>
                            <div class="mt-2">
                                @if($contract)
                                    <p class="text-sm text-gray-600">{{ $contract->getClientOriginalName() }}</p>
                                @else
                                    <p class="text-sm text-gray-500">PDF, DOC, or DOCX up to 10MB</p>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Uploading a contract will automatically set the case status to "Contract Sent" and notify the client to review and sign it.
                            </p>
                            @error('contract') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <a href="{{ route('law-firm.consultations') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                                {{ __('Cancel') }}
                            </a>
                            <x-button class="ml-4" type="submit" @if(!isset($clientName)) disabled @endif>
                                {{ __('Create Case') }}
                            </x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 