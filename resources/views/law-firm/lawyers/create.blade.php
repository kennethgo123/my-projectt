<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Lawyer to Firm') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('law-firm.lawyers.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <x-label for="first_name" value="{{ __('First Name') }}" />
                            <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus />
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <x-label for="middle_name" value="{{ __('Middle Name') }}" />
                            <x-input id="middle_name" class="block mt-1 w-full" type="text" name="middle_name" :value="old('middle_name')" />
                            @error('middle_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <x-label for="last_name" value="{{ __('Last Name') }}" />
                            <x-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required />
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <x-label for="contact_number" value="{{ __('Contact Number (Format: +63 9xxxxxxxxx)') }}" />
                            <div class="flex items-center mt-1 border border-gray-300 rounded-md shadow-sm">
                                <span class="px-3 py-2 bg-gray-100 border-r border-gray-300 rounded-l-md">+63</span>
                                <x-input id="contact_number" class="block w-full border-none rounded-r-md focus:ring-0" type="tel" name="contact_number" :value="old('contact_number')" required placeholder="9xxxxxxxxx" pattern="^9[0-9]{9}$" title="Must be a 10-digit Philippine mobile number starting with 9." />
                            </div>
                            @error('contact_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <x-label for="email" value="{{ __('Email') }}" />
                            <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <x-label for="password" value="{{ __('Password') }}" />
                            <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                            <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        </div>

                        <!-- Address -->
                        <div>
                            <x-label for="address" value="{{ __('Address') }}" />
                            <x-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required />
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valid ID Type -->
                        <div>
                            <x-label for="valid_id_type" value="{{ __('Valid ID Type') }}" />
                            <select id="valid_id_type" name="valid_id_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select ID Type</option>
                                <option value="Philippine Passport">Philippine Passport</option>
                                <option value="PhilSys National ID">PhilSys National ID</option>
                                <option value="SSS ID">SSS ID</option>
                                <option value="GSIS ID">GSIS ID</option>
                                <option value="UMID">UMID</option>
                                <option value="Drivers License">Driver's License</option>
                                <option value="PRC ID">PRC ID</option>
                                <option value="Postal ID">Postal ID</option>
                                <option value="Voters ID">Voter's ID</option>
                                <option value="PhilHealth ID">PhilHealth ID</option>
                                <option value="NBI Clearance">NBI Clearance</option>
                            </select>
                            @error('valid_id_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Valid ID File -->
                        <div>
                            <x-label for="valid_id_file" value="{{ __('Valid ID File') }}" />
                            <input id="valid_id_file" type="file" name="valid_id_file" class="block mt-1 w-full" required />
                            @error('valid_id_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bar Admission Type -->
                        <div>
                            <x-label for="bar_admission_type" value="{{ __('Bar Admission Type') }}" />
                            <select id="bar_admission_type" name="bar_admission_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select Bar Admission Proof</option>
                                <option value="IBP Bar ID">IBP Bar ID</option>
                                <option value="IBP Certificate">IBP Certificate of Admission</option>
                            </select>
                            @error('bar_admission_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bar Admission File -->
                        <div>
                            <x-label for="bar_admission_file" value="{{ __('Bar Admission File') }}" />
                            <input id="bar_admission_file" type="file" name="bar_admission_file" class="block mt-1 w-full" required />
                            @error('bar_admission_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Services -->
                    <div class="mt-6">
                        <x-label value="{{ __('Services Offered') }}" />
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($services as $service)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2">{{ $service->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('services')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-button>
                            {{ __('Add Lawyer') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 