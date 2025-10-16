<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Legal Case from Consultation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Consultation Details
                    </h3>
                    
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <p><strong>Client:</strong> {{ $consultation->client->name }}</p>
                        <p><strong>Type:</strong> {{ $consultation->consultation_type }}</p>
                        <p><strong>Date:</strong> {{ $consultation->selected_date?->format('F j, Y, g:i a') }}</p>
                        <p><strong>Description:</strong> {{ $consultation->description }}</p>
                        
                        @if($consultation->consultation_results)
                            <p class="mt-2"><strong>Results:</strong> {{ $consultation->consultation_results }}</p>
                        @endif
                    </div>
                    
                    <form action="{{ route('lawyer.consultations.create-case', $consultation) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <x-label for="title" :value="__('Case Title')" />
                            <x-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', 'Case for ' . $consultation->client->name)" required />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <x-label for="description" :value="__('Case Description')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $consultation->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <x-label for="case_type" :value="__('Case Type')" />
                            <select id="case_type" name="case_type" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Case Type</option>
                                <option value="civil">Civil</option>
                                <option value="criminal">Criminal</option>
                                <option value="corporate">Corporate</option>
                                <option value="family">Family</option>
                                <option value="immigration">Immigration</option>
                                <option value="intellectual_property">Intellectual Property</option>
                                <option value="real_estate">Real Estate</option>
                                <option value="tax">Tax</option>
                                <option value="other">Other</option>
                            </select>
                            @error('case_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-label for="priority" :value="__('Priority')" />
                                <select id="priority" name="priority" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <x-label for="deadline" :value="__('Deadline (optional)')" />
                                <x-input id="deadline" class="block mt-1 w-full" type="date" name="deadline" :value="old('deadline')" />
                                @error('deadline')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-label for="opposing_party" :value="__('Opposing Party (optional)')" />
                                <x-input id="opposing_party" class="block mt-1 w-full" type="text" name="opposing_party" :value="old('opposing_party')" />
                                @error('opposing_party')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <x-label for="opposing_counsel" :value="__('Opposing Counsel (optional)')" />
                                <x-input id="opposing_counsel" class="block mt-1 w-full" type="text" name="opposing_counsel" :value="old('opposing_counsel')" />
                                @error('opposing_counsel')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_confidential" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2">{{ __('Mark as Confidential') }}</span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('lawyer.consultations.show', $consultation) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            
                            <x-button>
                                {{ __('Create Legal Case') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 