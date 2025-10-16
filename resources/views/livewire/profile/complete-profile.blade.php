<div class="min-h-screen bg-gray-100">
    <nav class="bg-white border-b border-gray-100">
        <!-- Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-sm text-gray-700 hover:text-gray-900">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Complete Your Profile</h2>
                
                <!-- Global Error Messages -->
                @if (session()->has('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (session()->has('upload_error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">File Upload Error: {{ session('upload_error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(auth()->user()->role_id == 2)
                    <!-- Client Profile Form -->
                    <form wire:submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" wire:model="first_name" id="first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" wire:model="middle_name" id="middle_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('middle_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" wire:model="last_name" id="last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Client Contact Number -->
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                    +63
                                </span>
                                <input type="text" wire:model.lazy="contact_number" id="contact_number" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" placeholder="9xxxxxxxxx">
                            </div>
                            @error('contact_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            <p class="mt-1 text-xs text-gray-500">Enter 10 digits after +63.</p>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" wire:model="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <select wire:model="city" id="city" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select City</option>
                                <option value="Bacoor">Bacoor</option>
                                <option value="Cavite City">Cavite City</option>
                                <option value="Dasmariñas">Dasmariñas</option>
                                <option value="General Trias">General Trias</option>
                                <option value="Imus">Imus</option>
                                <option value="Tagaytay">Tagaytay</option>
                                <option value="Trece Martires">Trece Martires</option>
                            </select>
                            @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="valid_id_type" class="block text-sm font-medium text-gray-700">Valid ID Type</label>
                            <select wire:model="valid_id_type" id="valid_id_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Type</option>
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
                            @error('valid_id_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="valid_id_file" class="block text-sm font-medium text-gray-700">Upload Valid ID</label>
                            <!-- Simplified file input structure -->
                            <input 
                                type="file" 
                                wire:model="valid_id_file" 
                                id="valid_id_file" 
                                wire:key="valid_id_file_client" 
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            
                            <!-- Loading indicator (optional, can be added if needed) -->
                            <div wire:loading wire:target="valid_id_file" class="text-xs text-gray-500 mt-1">Uploading...</div>

                            @error('valid_id_file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            <p class="mt-1 text-xs text-gray-500">Accepted file types: PDF, JPG, PNG, DOCX. Max size: 8MB.</p>
                        </div>

                        <!-- Profile Photo Upload -->
                        <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700">Profile Photo (Optional)</label>
                            <div class="mt-1 flex items-center space-x-5">
                                <div class="flex-shrink-0">
                                    @if($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Profile photo preview" class="h-16 w-16 rounded-full object-cover">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                            <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input 
                                        type="file" 
                                        wire:model="photo" 
                                        id="photo" 
                                        wire:key="photo_client" 
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    
                                    <div wire:loading wire:target="photo" class="text-xs text-gray-500 mt-1">Uploading...</div>
                                    @error('photo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    <p class="mt-1 text-xs text-gray-500">Accepted file types: JPG, PNG. Max size: 2MB.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Submit
                            </button>
                        </div>
                    </form>
                @elseif(auth()->user()->role_id == 3)
                    <!-- Lawyer Profile Form -->
                    <form wire:submit.prevent="submit" class="space-y-6">
                        <!-- Personal Information Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                    <input type="text" wire:model="first_name" id="first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                                    <input type="text" wire:model="middle_name" id="middle_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('middle_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                    <input type="text" wire:model="last_name" id="last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Lawyer Contact Number -->
                                <div>
                                    <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                            +63
                                        </span>
                                        <input type="text" wire:model.lazy="contact_number" id="contact_number" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" placeholder="9xxxxxxxxx">
                                    </div>
                                    @error('contact_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="mt-1 text-xs text-gray-500">Enter 10 digits after +63.</p>
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" wire:model="address" id="address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                                <select wire:model="city" id="city" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select City</option>
                                    <option value="Bacoor">Bacoor</option>
                                    <option value="Cavite City">Cavite City</option>
                                    <option value="Dasmariñas">Dasmariñas</option>
                                    <option value="General Trias">General Trias</option>
                                    <option value="Imus">Imus</option>
                                    <option value="Tagaytay">Tagaytay</option>
                                    <option value="Trece Martires">Trece Martires</option>
                                </select>
                                @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Required Documents</h3>
                            
                            <div>
                                <label for="valid_id_type" class="block text-sm font-medium text-gray-700">Valid ID Type</label>
                                <select wire:model="valid_id_type" id="valid_id_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Type</option>
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
                                @error('valid_id_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="valid_id_file" class="block text-sm font-medium text-gray-700">Upload Valid ID</label>
                                <!-- Simplified file input structure -->
                                <input 
                                    type="file" 
                                    wire:model="valid_id_file" 
                                    id="valid_id_file" 
                                    wire:key="valid_id_file_lawyer" 
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                
                                <!-- Loading indicator -->
                                <div wire:loading wire:target="valid_id_file" class="text-xs text-gray-500 mt-1">Uploading...</div>
                                
                                @error('valid_id_file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">Accepted file types: PDF, JPG, PNG, DOCX. Max size: 8MB.</p>
                            </div>

                            <div>
                                <label for="bar_admission_type" class="block text-sm font-medium text-gray-700">Bar Admission Type</label>
                                <select wire:model="bar_admission_type" id="bar_admission_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Type</option>
                                    <option value="Philippine Bar">IBP ID</option>
                                    <option value="Other">IBP Certificate of Admission</option> 
                                </select>
                                @error('bar_admission_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="bar_admission_file" class="block text-sm font-medium text-gray-700">Upload Bar Admission Document</label>
                                <input 
                                    type="file" 
                                    wire:model="bar_admission_file" 
                                    id="bar_admission_file" 
                                    wire:key="bar_admission_file_lawyer" 
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                
                                <!-- Loading indicator -->
                                <div wire:loading wire:target="bar_admission_file" class="text-xs text-gray-500 mt-1">Uploading...</div>
                                
                                @error('bar_admission_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">Accepted file types: PDF, JPG, PNG, DOCX. Max size: 8MB.</p>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Services Offered</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($services as $service)
                                    <label class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" 
                                                   wire:model="selectedServices" 
                                                   value="{{ $service->id }}"
                                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="font-medium text-gray-700">{{ $service->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('selectedServices') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Budget Section -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Professional Fee Range</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="min_budget" class="block text-sm font-medium text-gray-700">Minimum Professional Fee (PHP)</label>
                                    <input type="number" wire:model.lazy="min_budget" id="min_budget" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g., 5000">
                                    @error('min_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="max_budget" class="block text-sm font-medium text-gray-700">Maximum Professional Fee (PHP)</label>
                                    <input type="number" wire:model.lazy="max_budget" id="max_budget" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g., 10000">
                                    @error('max_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="pricing_description" class="block text-sm font-medium text-gray-700">Pricing Description</label>
                                <textarea wire:model.lazy="pricing_description" id="pricing_description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Briefly describe your pricing structure or typical fees..."></textarea>
                                @error('pricing_description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Submit
                            </button>
                        </div>
                    </form>
                @elseif(auth()->user()->role_id == 4)
                    <!-- Law Firm Profile Form -->
                    <form wire:submit.prevent="submit" class="space-y-6">
                        @if (session()->has('message'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('message') }}</span>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                        @endif

                        <!-- Firm Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">Firm Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="firm_name" class="block text-sm font-medium text-gray-700">Firm Name</label>
                                    <input type="text" wire:model.defer="firm_name" id="firm_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('firm_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Law Firm Contact Number -->
                                <div>
                                    <label for="firm_contact_number" class="block text-sm font-medium text-gray-700">Firm Contact Number</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                            +63
                                        </span>
                                        <input type="text" wire:model.lazy="firm_contact_number" id="firm_contact_number" class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300" placeholder="9xxxxxxxxx">
                                    </div>
                                    @error('firm_contact_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="mt-1 text-xs text-gray-500">Enter 10 digits after +63.</p>
                                </div>
                            </div>

                            <div>
                                <label for="firm_address" class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" wire:model.defer="firm_address" id="firm_address" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('firm_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="firm_city" class="block text-sm font-medium text-gray-700">City</label>
                                <select wire:model.defer="firm_city" id="firm_city" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select City</option>
                                    <option value="Bacoor">Bacoor</option>
                                    <option value="Cavite City">Cavite City</option>
                                    <option value="Dasmarinas">Dasmarinas</option>
                                    <option value="General Trias">General Trias</option>
                                    <option value="Imus">Imus</option>
                                    <option value="Tagaytay">Tagaytay</option>
                                    <option value="Trece Martires">Trece Martires</option>
                                </select>
                                @error('firm_city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="registration_type" class="block text-sm font-medium text-gray-700">Registration Type</label>
                                <select wire:model.defer="registration_type" id="registration_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Type</option>
                                    <option value="SEC Registration Certificate">SEC Registration Certificate</option>
                                    <option value="DTI Registration Certificate">DTI Registration Certificate</option>
                                </select>
                                @error('registration_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="registration_certificate_file" class="block text-sm font-medium text-gray-700">Upload Registration Certificate</label>
                                <input 
                                    type="file" 
                                    wire:model="registration_certificate_file" 
                                    id="registration_certificate_file" 
                                    wire:key="registration_certificate_file_firm" 
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                
                                <!-- Loading indicator -->
                                <div wire:loading wire:target="registration_certificate_file" class="text-xs text-gray-500 mt-1">Uploading...</div>
                                
                                @error('registration_certificate_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">Accepted file types: PDF, JPG, PNG, DOCX. Max size: 8MB.</p>
                            </div>

                            <div>
                                <label for="bir_certificate_file" class="block text-sm font-medium text-gray-700">Upload BIR Certificate</label>
                                <input 
                                    type="file" 
                                    wire:model="bir_certificate_file" 
                                    id="bir_certificate_file" 
                                    wire:key="bir_certificate_file_firm" 
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                
                                <!-- Loading indicator -->
                                <div wire:loading wire:target="bir_certificate_file" class="text-xs text-gray-500 mt-1">Uploading...</div>
                                
                                @error('bir_certificate_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">Accepted file types: PDF, JPG, PNG, DOCX. Max size: 8MB.</p>
                            </div>

                            <!-- Professional Fee Range -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="min_budget" class="block text-sm font-medium text-gray-700">Minimum Professional Fee (PHP)</label>
                                    <input type="number" wire:model.lazy="min_budget" id="min_budget" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g., 5000">
                                    @error('min_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="max_budget" class="block text-sm font-medium text-gray-700">Maximum Professional Fee (PHP)</label>
                                    <input type="number" wire:model.lazy="max_budget" id="max_budget" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="e.g., 10000">
                                    @error('max_budget') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Services -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Services Offered</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($services as $service)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model.defer="selectedServices" value="{{ $service->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2">{{ $service->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedServices') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-4">
                                <label for="pricing_description" class="block text-sm font-medium text-gray-700">Pricing Description</label>
                                <textarea wire:model.lazy="pricing_description" id="pricing_description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Briefly describe your firm's pricing structure or typical fees..."></textarea>
                                @error('pricing_description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Submit
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>