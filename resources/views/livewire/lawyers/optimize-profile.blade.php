@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.8.0/dist/geosearch.css"/>
<style>
    #map-preview { height: 300px; }
    #map-container { height: 400px; }
    .leaflet-control-geosearch { 
        margin-top: 70px !important;
    }
    .search-control { display: none; }
</style>
@endpush

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Status Banner -->
        @if(auth()->user()->status === 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Account Pending Approval</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Your account is currently awaiting approval. In the meantime, optimize your profile to showcase your expertise and credentials to potential clients.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Optimize Your Profile</h2>

                @if (session()->has('message'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit="save" class="space-y-6">
                    <!-- Professional Photo Upload -->
                    <div>
                        <x-label for="photo" value="Disclaimer: All information provided in this page will be displayed publicly." class="mb-1" />
                        <p class="text-sm text-gray-500 mb-4">Upload a professional photo</p>
                        
                        <div class="flex items-start space-x-6">
                            <!-- Preview -->
                            <div class="shrink-0">
                                <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200 overflow-hidden">
                                    @if($existingPhoto)
                                        <img src="{{ Storage::url($existingPhoto) }}" alt="Current profile photo" class="h-16 w-16 object-cover" id="preview-image">
                                    @elseif($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Profile photo preview" class="h-16 w-16 object-cover" id="preview-image">
                                    @else
                                        <svg class="h-6 w-6 text-gray-300" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="flex-grow">
                                <label class="block">
                                    <span class="sr-only">Choose profile photo</span>
                                    <input type="file" wire:model="photo" id="photo-upload"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        accept="image/*">
                                </label>
                                
                                @error('photo') 
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror

                                <p class="mt-2 text-xs text-gray-500">
                                    Recommended: Square image in JPG or PNG format
                                </p>
                                
                                <!-- Manually trigger modal -->
                                <button type="button" id="open-cropper-btn" class="mt-2 text-xs text-blue-600 hover:underline">
                                    Edit/crop photo
                                </button>
                            </div>
                        </div>

                        <!-- Simple Cropping Modal -->
                        <div id="cropper-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <!-- Modal backdrop -->
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

                                <div class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl relative z-50 max-w-lg w-full p-6">
                                    <div>
                                        <div class="text-center">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                                Preview profile picture
                                            </h3>
                                            <div class="mt-2">
                                                <div class="max-w-full mx-auto">
                                                    <div class="aspect-w-1 aspect-h-1 relative">
                                                        <img id="cropper-image" src="" alt="Image to crop" class="max-w-full">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
                                        <button type="button" 
                                                id="save-crop-btn"
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                            Save
                                        </button>
                                        <button type="button" 
                                                id="cancel-crop-btn"
                                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About -->
                    <div>
                        <x-label for="about" value="About You" />
                        <div class="mt-1">
                            <textarea id="about" wire:model="about" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="Share your background, philosophy, and approach to legal practice"></textarea>
                        </div>
                        @error('about') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Education -->
                    <div>
                        <x-label value="Education" />

                        <!-- University selection -->
                        <div class="mt-2">
                            <label for="selectedUniversity" class="block text-sm font-medium text-gray-700">Law School / University</label>
                            <select id="selectedUniversity" wire:model="selectedUniversity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select your law school</option>
                                <optgroup label="METRO MANILA — Quezon City">
                                    <option value="Ateneo de Manila University School of Law">Ateneo de Manila University School of Law</option>
                                    <option value="University of the Philippines College of Law (Diliman)">University of the Philippines College of Law (Diliman)</option>
                                    <option value="Far Eastern University Institute of Law">Far Eastern University Institute of Law</option>
                                    <option value="University of Santo Tomas Faculty of Civil Law">University of Santo Tomas Faculty of Civil Law</option>
                                    <option value="Arellano University School of Law">Arellano University School of Law</option>
                                    <option value="National University College of Law">National University College of Law</option>
                                    <option value="Philippine Christian University College of Law">Philippine Christian University College of Law</option>
                                    <option value="Trinity University of Asia College of Law">Trinity University of Asia College of Law</option>
                                </optgroup>
                                <optgroup label="METRO MANILA — Manila">
                                    <option value="De La Salle University College of Law (Taft Avenue)">De La Salle University College of Law (Taft Avenue)</option>
                                    <option value="Adamson University College of Law">Adamson University College of Law</option>
                                    <option value="Lyceum of the Philippines University College of Law">Lyceum of the Philippines University College of Law</option>
                                    <option value="Pamantasan ng Lungsod ng Maynila College of Law">Pamantasan ng Lungsod ng Maynila College of Law</option>
                                    <option value="San Beda University College of Law">San Beda University College of Law</option>
                                    <option value="University of the East College of Law">University of the East College of Law</option>
                                    <option value="Centro Escolar University School of Law and Jurisprudence">Centro Escolar University School of Law and Jurisprudence</option>
                                    <option value="Philippine Law School">Philippine Law School</option>
                                    <option value="Manuel L. Quezon University College of Law">Manuel L. Quezon University College of Law</option>
                                </optgroup>
                                <optgroup label="METRO MANILA — Makati City">
                                    <option value="Ateneo de Manila University School of Law (Rockwell Campus)">Ateneo de Manila University School of Law (Rockwell Campus)</option>
                                </optgroup>
                                <optgroup label="METRO MANILA — Pasay City">
                                    <option value="Lyceum of the Philippines University (Pasay Campus)">Lyceum of the Philippines University (Pasay Campus)</option>
                                </optgroup>
                                <optgroup label="METRO MANILA — Taguig City">
                                    <option value="University of Makati College of Law">University of Makati College of Law</option>
                                </optgroup>
                                <optgroup label="METRO MANILA — Mandaluyong City">
                                    <option value="San Beda University College of Law (San Beda-Mendiola moved here)">San Beda University College of Law (San Beda-Mendiola moved here)</option>
                                </optgroup>
                                <optgroup label="LUZON — Cavite">
                                    <option value="De La Salle University College of Law (Dasmariñas)">De La Salle University College of Law (Dasmariñas)</option>
                                    <option value="Cavite State University College of Law (Indang)">Cavite State University College of Law (Indang)</option>
                                </optgroup>
                                <optgroup label="LUZON — Laguna">
                                    <option value="Laguna State Polytechnic University College of Law">Laguna State Polytechnic University College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Batangas">
                                    <option value="Lyceum of the Philippines University - Batangas">Lyceum of the Philippines University - Batangas</option>
                                    <option value="University of Batangas College of Law">University of Batangas College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Pampanga">
                                    <option value="Holy Angel University School of Law">Holy Angel University School of Law</option>
                                    <option value="Pampanga State Agricultural University College of Law">Pampanga State Agricultural University College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Pangasinan">
                                    <option value="Pangasinan State University College of Law">Pangasinan State University College of Law</option>
                                    <option value="University of Luzon College of Law">University of Luzon College of Law</option>
                                    <option value="Virgen Milagrosa University Foundation College of Law">Virgen Milagrosa University Foundation College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — La Union / Baguio City">
                                    <option value="Saint Louis University School of Law (Baguio City)">Saint Louis University School of Law (Baguio City)</option>
                                    <option value="University of Baguio College of Law">University of Baguio College of Law</option>
                                    <option value="Saint Louis University School of Law">Saint Louis University School of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Ilocos Norte">
                                    <option value="Mariano Marcos State University College of Law">Mariano Marcos State University College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Cagayan / Isabela / Nueva Vizcaya">
                                    <option value="Saint Paul University Philippines College of Law (Tuguegarao)">Saint Paul University Philippines College of Law (Tuguegarao)</option>
                                    <option value="University of Cagayan Valley College of Law">University of Cagayan Valley College of Law</option>
                                    <option value="Nueva Vizcaya State University College of Law">Nueva Vizcaya State University College of Law</option>
                                    <option value="Isabela State University College of Law">Isabela State University College of Law</option>
                                </optgroup>
                                <optgroup label="LUZON — Albay / Camarines Sur / Sorsogon">
                                    <option value="Aquinas University of Legazpi College of Law">Aquinas University of Legazpi College of Law</option>
                                    <option value="Bicol University College of Law">Bicol University College of Law</option>
                                    <option value="Ateneo de Naga University College of Law">Ateneo de Naga University College of Law</option>
                                    <option value="Universidad de Santa Isabel College of Law">Universidad de Santa Isabel College of Law</option>
                                    <option value="Divine Word College of Legazpi Law School">Divine Word College of Legazpi Law School</option>
                                </optgroup>
                                <optgroup label="VISAYAS — Cebu">
                                    <option value="University of San Carlos School of Law and Governance">University of San Carlos School of Law and Governance</option>
                                    <option value="University of the Visayas College of Law">University of the Visayas College of Law</option>
                                    <option value="Southwestern University PHINMA School of Law">Southwestern University PHINMA School of Law</option>
                                    <option value="University of Cebu College of Law">University of Cebu College of Law</option>
                                    <option value="University of San Jose-Recoletos School of Law">University of San Jose-Recoletos School of Law</option>
                                </optgroup>
                                <optgroup label="VISAYAS — Iloilo / Bacolod / Dumaguete / Bohol / Leyte / Samar">
                                    <option value="Central Philippine University College of Law">Central Philippine University College of Law</option>
                                    <option value="University of San Agustin College of Law">University of San Agustin College of Law</option>
                                    <option value="West Visayas State University College of Law">West Visayas State University College of Law</option>
                                    <option value="Filamer Christian University College of Law">Filamer Christian University College of Law</option>
                                    <option value="University of St. La Salle College of Law">University of St. La Salle College of Law</option>
                                    <option value="University of Negros Occidental-Recoletos College of Law">University of Negros Occidental-Recoletos College of Law</option>
                                    <option value="Silliman University College of Law">Silliman University College of Law</option>
                                    <option value="Foundation University College of Law">Foundation University College of Law</option>
                                    <option value="Holy Name University College of Law">Holy Name University College of Law</option>
                                    <option value="Leyte State University College of Law">Leyte State University College of Law</option>
                                    <option value="University of San Carlos - Tacloban College of Law">University of San Carlos - Tacloban College of Law</option>
                                    <option value="Northwestern Samar State University College of Law">Northwestern Samar State University College of Law</option>
                                </optgroup>
                                <optgroup label="MINDANAO — Davao City / CDO / Zamboanga / GenSan / Cotabato / Iligan / Butuan / Surigao">
                                    <option value="Ateneo de Davao University School of Law">Ateneo de Davao University School of Law</option>
                                    <option value="University of Mindanao College of Law">University of Mindanao College of Law</option>
                                    <option value="San Pedro College of Law">San Pedro College of Law</option>
                                    <option value="Davao Doctors College">Davao Doctors College</option>
                                    <option value="Xavier University College of Law">Xavier University College of Law</option>
                                    <option value="Capitol University College of Law">Capitol University College of Law</option>
                                    <option value="Liceo de Cagayan University College of Law">Liceo de Cagayan University College of Law</option>
                                    <option value="Ateneo de Zamboanga University School of Law">Ateneo de Zamboanga University School of Law</option>
                                    <option value="Western Mindanao State University College of Law">Western Mindanao State University College of Law</option>
                                    <option value="Mindanao State University - General Santos College of Law">Mindanao State University - General Santos College of Law</option>
                                    <option value="Notre Dame University College of Law">Notre Dame University College of Law</option>
                                    <option value="Mindanao State University - Iligan Institute of Technology College of Law">Mindanao State University - Iligan Institute of Technology College of Law</option>
                                    <option value="Father Saturnino Urios University School of Law">Father Saturnino Urios University School of Law</option>
                                    <option value="Surigao State College of Technology College of Law">Surigao State College of Technology College of Law</option>
                                </optgroup>
                                <option value="OTHER">Other (type manually)</option>
                            </select>
                        </div>

                        <!-- Custom university input -->
                        @if($selectedUniversity === 'OTHER')
                        <div class="mt-2">
                            <label for="customUniversity" class="block text-sm font-medium text-gray-700">Enter your university</label>
                            <input type="text" id="customUniversity" wire:model="customUniversity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Type your university name">
                        </div>
                        @endif

                        <!-- Start/End year -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="startYear" class="block text-sm font-medium text-gray-700">Start Year</label>
                                <select id="startYear" wire:model="startYear" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select start year</option>
                                    @for($y = 2021; $y >= 1960; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="endYear" class="block text-sm font-medium text-gray-700">End Year</label>
                                <select id="endYear" wire:model="endYear" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select end year</option>
                                    @for($y = 2025; $y >= 1960; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Additional details (optional) -->
                        <div class="mt-4">
                            <label for="education" class="block text-sm font-medium text-gray-700">Additional details (optional)</label>
                            <textarea id="education" wire:model="education" rows="3" class="mt-1 shadow-sm block w-full border-gray-300 rounded-md" placeholder="Degrees, honors, bar review, certifications, etc."></textarea>
                        </div>
                        @error('education') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Professional Experience (Dynamic) -->
                    <div>
                        <x-label for="experiences" value="Professional Experience" />
                        <p class="text-sm text-gray-500 mb-2">Add your roles, firms, notable work, and durations</p>
                        <div class="mt-2 space-y-2">
                            @if(count($experiences) > 0)
                                <div class="space-y-2">
                                    @foreach($experiences as $index => $exp)
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700 truncate" title="{{ $exp }}">
                                                {{ $exp }}
                                            </div>
                                            <button type="button" wire:click="removeExperience({{ $index }})" 
                                                class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Remove
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="newExperience" 
                                       placeholder="e.g., Associate Lawyer at ABC Law (2019–2023)"
                                       class="flex-1 shadow-sm block border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       wire:keydown.enter.prevent="addExperience">
                                <button type="button" wire:click="addExperience" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Achievements -->
                    <div>
                        <x-label for="achievements" value="Achievements & Recognition" />
                        <div class="mt-1">
                            <textarea id="achievements" wire:model="achievements" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="List awards, publications, speaking engagements, and other professional achievements"></textarea>
                        </div>
                        @error('achievements') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Languages and Dialects -->
                    <div>
                        <x-label for="languages" value="Languages and Dialects" />
                        <p class="text-sm text-gray-500 mb-2">Add languages or dialects you speak</p>
                        <div class="mt-2 space-y-3">
                            <!-- Display existing languages -->
                            @if(count($languages) > 0)
                                <div class="space-y-2">
                                    @foreach($languages as $index => $language)
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-700">
                                                {{ $language }}
                                            </div>
                                            <button type="button" wire:click="removeLanguage({{ $index }})" 
                                                class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-md hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Remove
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Add new language input -->
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="newLanguage" 
                                    placeholder="Enter language or dialect"
                                    class="flex-1 shadow-sm block border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    wire:keydown.enter.prevent="addLanguage">
                                <button type="button" wire:click="addLanguage" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add
                                </button>
                            </div>
                        </div>
                        @error('languages') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Services Offered -->
                    <div>
                        <x-label for="selectedServices" value="Legal Services Offered" />
                        <p class="text-sm text-gray-500 mb-2">Select the legal services you provide</p>
                        
                        @if(count($availableServices) > 0)
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($availableServices as $service)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="service-{{ $service->id }}" type="checkbox" 
                                                wire:model="selectedServices" value="{{ $service->id }}"
                                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="service-{{ $service->id }}" class="font-medium text-gray-700">{{ $service->name }}</label>
                                            @if($service->description)
                                                <p class="text-gray-500">{{ $service->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-1 p-4 bg-yellow-50 border border-yellow-300 rounded-md text-yellow-700">
                                <p>No legal services are available for selection. Please contact support if you believe this is an error.</p>
                            </div>
                        @endif
                        @error('selectedServices') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Consultation Types -->
                    <div>
                        <x-label value="What Consultation Type do you offer?" />
                        <div class="mt-2 space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="online_consultation" wire:model="offersOnlineConsultation" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="online_consultation" class="ml-2 text-sm text-gray-700">Online Consultation</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="inhouse_consultation" wire:model="offersInhouseConsultation" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="inhouse_consultation" class="ml-2 text-sm text-gray-700">In-House Consultation</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Office Address Section -->
                    <div>
                        <x-label for="office_address" value="Office Address" />
                        <p class="text-sm text-gray-500 mb-2">This will be shown to clients when you accept their consultation request</p>
                        
                        <!-- City Dropdown -->
                        <div class="mt-4">
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <select id="city" wire:model.live="city" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select City</option>
                                <option value="Bacoor City">Bacoor City</option>
                                <option value="Cavite City">Cavite City</option>
                                <option value="Dasmariñas City">Dasmariñas City</option>
                                <option value="General Trias City">General Trias City</option>
                                <option value="Imus City">Imus City</option>
                                <option value="Tagaytay City">Tagaytay City</option>
                                <option value="Trece Martires City">Trece Martires City</option>
                            </select>
                            @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Barangay Dropdown -->
                        @if($city)
                        <div class="mt-4">
                            <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay</label>
                            <select id="barangay" wire:model="barangay" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Barangay</option>
                                @if($city === 'Bacoor City')
                                    @foreach(['Alima', 'Aniban I', 'Aniban II', 'Aniban III', 'Aniban IV', 'Aniban V', 'Bagong Sikat', 'Banalo', 'Bayanan', 'Campo Santo', 'Daang Bukid', 'Digman', 'Dulong Bayan', 'Habay I', 'Habay II', 'Kaingin', 'Ligas I', 'Ligas II', 'Ligas III', 'Mabolo I', 'Mabolo II', 'Mabolo III', 'Maliksi I', 'Maliksi II', 'Maliksi III', 'Mambog I', 'Mambog II', 'Mambog III', 'Mambog IV', 'Molino I', 'Molino II', 'Molino III', 'Molino IV', 'Molino V', 'Molino VI', 'Molino VII', 'Niog I', 'Niog II', 'Niog III', 'Panapaan I', 'Panapaan II', 'Panapaan III', 'Panapaan IV', 'Panapaan V', 'Panapaan VI', 'Panapaan VII', 'Panapaan VIII', 'Queens Row Central', 'Queens Row East', 'Queens Row West', 'Real I', 'Real II', 'Salinas I', 'Salinas II', 'Salinas III', 'Salinas IV', 'San Nicolas I', 'San Nicolas II', 'San Nicolas III', 'Springville', 'Tabing Dagat', 'Talaba I', 'Talaba II', 'Talaba III', 'Talaba IV', 'Talaba V', 'Talaba VI', 'Talaba VII', 'Zapote I', 'Zapote II', 'Zapote III', 'Zapote IV', 'Zapote V'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @elseif($city === 'Cavite City')
                                    @foreach(range(1, 84) as $num)
                                        <option value="Barangay {{ $num }}">Barangay {{ $num }}</option>
                                    @endforeach
                                    <option value="Barangay 2-A">Barangay 2-A</option>
                                @elseif($city === 'Dasmariñas City')
                                    @foreach(['Adobe', 'Amadeo', 'Bagong Bayan I', 'Bagong Bayan II', 'Burol I', 'Burol II', 'Burol III', 'Caapat', 'Calayo', 'Datu Esmael', 'Emmanuel Bergado I', 'Emmanuel Bergado II', 'Fatima I', 'Fatima II', 'Fatima III', 'H-2', 'Hercules', 'Langkaan I', 'Langkaan II', 'Luzviminda I', 'Luzviminda II', 'Malagasang I-A', 'Malagasang I-B', 'Malagasang I-C', 'Malagasang I-D', 'Malagasang I-E', 'Malagasang I-F', 'Malagasang I-G', 'Malagasang II-A', 'Malagasang II-B', 'Malagasang II-C', 'Malagasang II-D', 'Malagasang II-E', 'Malagasang II-F', 'Malagasang II-G', 'Malinta', 'Malibu I', 'Malibu II', 'Malibu III', 'Paliparan I', 'Paliparan II', 'Paliparan III', 'Palo-Alto', 'Sabang', 'Salawag', 'Salitran I', 'Salitran II', 'Salitran III', 'Salitran IV', 'Sampaloc I', 'Sampaloc II', 'Sampaloc III', 'Sampaloc IV', 'Sampaloc V', 'San Agustin I', 'San Agustin II', 'San Agustin III', 'San Andres I', 'San Andres II', 'San Antonio de Padua I', 'San Antonio de Padua II', 'San Dionisio', 'San Esteban', 'San Francisco I', 'San Francisco II', 'San Isidro Labrador I', 'San Isidro Labrador II', 'San Jose', 'San Juan I', 'San Juan II', 'San Lorenzo I', 'San Lorenzo II', 'San Luis I', 'San Luis II', 'San Manuel I', 'San Manuel II', 'San Mateo', 'San Miguel', 'San Miguel II', 'San Nicolas I', 'San Nicolas II', 'San Roque', 'San Simon', 'Santa Cristina I', 'Santa Cristina II', 'Santa Cruz I', 'Santa Cruz II', 'Santa Fe', 'Santa Lucia', 'Santa Maria', 'Santo Cristo', 'Santo Niño I', 'Santo Niño II', 'Santolan I', 'Santolan II', 'Victoria Reyes', 'Zone I-A', 'Zone I-B', 'Zone II-A', 'Zone II-B', 'Zone III', 'Zone IV-A', 'Zone IV-B'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @elseif($city === 'General Trias City')
                                    @foreach(['Alingaro', 'Arnaldo Poblacion', 'Bacao I', 'Bacao II', 'Bagumbayan Poblacion', 'Biclatan', 'Buenavista I', 'Buenavista II', 'Buenavista III', 'Corregidor Poblacion', 'Dulong Bayan Poblacion', 'Gov. Ferrer Poblacion', 'Javalera', 'Manggahan', 'Navarro', 'Ninety Sixth Poblacion', 'Panungyanan', 'Pasong Camachile I', 'Pasong Camachile II', 'Pasong Kawayan I', 'Pasong Kawayan II', 'Pinagtipunan', 'Prinza Poblacion', 'Sampalucan Poblacion', 'San Francisco', 'San Gabriel Poblacion', 'San Juan I', 'San Juan II', 'Santa Clara', 'Santiago', 'Tapia', 'Tejero', 'Vibora Poblacion'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @elseif($city === 'Imus City')
                                    @foreach(['Alapan I-A', 'Alapan I-B', 'Alapan I-C', 'Alapan II-A', 'Alapan II-B', 'Anabu I-A', 'Anabu I-B', 'Anabu I-C', 'Anabu I-D', 'Anabu I-E', 'Anabu I-F', 'Anabu I-G', 'Anabu II-A', 'Anabu II-B', 'Anabu II-C', 'Anabu II-D', 'Anabu II-E', 'Anabu II-F', 'Bagong Silang', 'Bayan Luma I', 'Bayan Luma II', 'Bayan Luma III', 'Bayan Luma IV', 'Bayan Luma V', 'Bayan Luma VI', 'Bayan Luma VII', 'Bayan Luma VIII', 'Bucandala I', 'Bucandala II', 'Bucandala III', 'Bucandala IV', 'Bucandala V', 'Buhay na Tubig', 'Carsadang Bago I', 'Carsadang Bago II', 'Lumang Bayan', 'Mabuhay', 'Maduya', 'Magdalo', 'Maharlika', 'Mahiwagang Dulang', 'Malagasang I-A', 'Malagasang I-B', 'Malagasang II-A', 'Malagasang II-B', 'Malhacan', 'Mariano Espeleta I', 'Mariano Espeleta II', 'Mariano Espeleta III', 'Medicion I-A', 'Medicion I-B', 'Medicion I-C', 'Medicion I-D', 'Medicion II-A', 'Medicion II-B', 'Medicion II-C', 'Medicion II-D', 'Medicion II-E', 'Medicion II-F', 'Pag-Asa I', 'Pag-Asa II', 'Pag-Asa III', 'Palico I', 'Palico II', 'Palico III', 'Palico IV', 'Pal-Palan I', 'Pal-Palan II', 'Pal-Palan III', 'Pasong Buaya I', 'Pasong Buaya II', 'Patindig Araw', 'Pinyahan', 'Poblacion I-A', 'Poblacion I-B', 'Poblacion I-C', 'Poblacion II-A', 'Poblacion II-B', 'Poblacion III-A', 'Poblacion III-B', 'Poblacion IV-A', 'Poblacion IV-B', 'Poblacion IV-C', 'Poblacion IV-D', 'Sapang I', 'Sapang II', 'Tabing Ilog', 'Tanzang Luma I', 'Tanzang Luma II', 'Tanzang Luma III', 'Tanzang Luma IV', 'Tanzang Luma V', 'Tanzang Luma VI', 'Tanzang Luma VII', 'Toclong I-A', 'Toclong I-B', 'Toclong I-C', 'Toclong II-A', 'Toclong II-B'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @elseif($city === 'Tagaytay City')
                                    @foreach(['Asisan', 'Bagong Tubig', 'Calabuso', 'Dapdap East', 'Dapdap West', 'Francisco', 'Guinhawa North', 'Guinhawa South', 'Iruhin Central', 'Iruhin East', 'Iruhin South', 'Iruhin West', 'Kaybagal Central', 'Kaybagal North', 'Kaybagal South', 'Maharlika East', 'Maharlika West', 'Maitim 2nd Central', 'Maitim 2nd East', 'Maitim 2nd West', 'Mendez Crossing East', 'Mendez Crossing West', 'Neogan', 'Patutong Malaki North', 'Patutong Malaki South', 'Sambong', 'San Jose', 'Silang Crossing North', 'Silang Crossing South', 'Sungay East', 'Sungay West', 'Tolentino East', 'Tolentino West', 'Zambal'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @elseif($city === 'Trece Martires City')
                                    @foreach(['Alulod', 'Cabezas (Luciano)', 'Cabuco', 'De Ocampo', 'Gregorio', 'Inocencio', 'Lapidario', 'Llavac', 'Osorio', 'Perez', 'Quintana (Barangay 5-A)', 'San Agustin (Poblacion)', 'Luciano'] as $barangay)
                                        <option value="{{ $barangay }}">{{ $barangay }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('barangay') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        
                        <div class="mt-4">
                            <label for="office_address" class="block text-sm font-medium text-gray-700">Street Address (Building, Street, etc.)</label>
                            <div class="mt-1">
                                <textarea id="office_address" wire:model="office_address" rows="3" 
                                    class="shadow-sm block w-full border-gray-300 rounded-md"
                                    placeholder="Enter your complete street address including building name, street, etc."></textarea>
                            </div>
                            @error('office_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-3 flex items-center">
                            <input type="checkbox" id="show_office_address" wire:model="show_office_address" 
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="show_office_address" class="ml-2 text-sm text-gray-700 font-medium">Share Your Office Address in your card?</label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 ml-6">
                            If checked, your office address will be displayed on your profile card and will be clickable, linking to your location on the map.
                        </p>
                    </div>

                    <!-- Map Selection -->
                    <div>
                        <x-label value="Office Location" />
                        <p class="text-sm text-gray-500 mb-2">Pin your exact office location on the map</p>
                        
                        <div class="mt-1 mb-3 bg-blue-50 p-4 rounded-md border border-blue-200">
                            <h4 class="text-sm font-semibold text-blue-800 mb-2">How to set your location:</h4>
                            <ol class="list-decimal text-sm text-blue-700 pl-5 space-y-1">
                                <li>Use the search box to find your general location</li>
                                <li>Drag the map to position it correctly</li>
                                <li>Click on the exact position of your office to drop a pin</li>
                                <li>You can drag the pin to adjust its position if needed</li>
                            </ol>
                        </div>
                        
                        <!-- Hidden inputs to store coordinates -->
                        <input type="hidden" id="lat" wire:model="lat">
                        <input type="hidden" id="lng" wire:model="lng">
                        
                        <!-- Search box -->
                        <div class="mb-4">
                            <div class="flex">
                                <input type="text" id="map-search" 
                                    class="shadow-sm block w-full border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Search for your location...">
                                <button type="button" id="search-button"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-r-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Search
                                </button>
                            </div>
                        </div>
                        
                        <!-- Map container -->
                        <div id="map-container" class="h-96 rounded-md overflow-hidden shadow-md border border-gray-300 relative"></div>
                        
                        <div class="flex items-center mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="accuracy-indicator" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <span id="accuracy-text" class="ml-2 text-xs text-gray-600">Zoom in for better accuracy</span>
                        </div>
                        
                        <div class="mt-2">
                            <p class="text-xs text-gray-500">
                                Your selected coordinates: 
                                <span id="coordinates-display" class="font-medium">Not set</span>
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-geosearch@3.8.0/dist/geosearch.umd.js"></script>
<script src="{{ asset('js/location-map.js') }}"></script>
<script>
    // OpenStreetMap integration - Global variables
    var map = null;
    var marker = null;
    var searchControl = null;
    
    // Function to initialize interactive location map
    function initializeLocationMap() {
        const mapContainer = document.getElementById('map-container');
        const searchInput = document.getElementById('map-search');
        const searchButton = document.getElementById('search-button');
        const latInput = document.getElementById('lat');
        const lngInput = document.getElementById('lng');
        const coordinatesDisplay = document.getElementById('coordinates-display');
        const accuracyIndicator = document.getElementById('accuracy-indicator');
        const accuracyText = document.getElementById('accuracy-text');
        
        if (!mapContainer) return;
        
        // Initialize map with default location (Philippines)
        const defaultLat = 14.5995;
        const defaultLng = 120.9842;
        const defaultZoom = 13;
        
        // Create map
        map = L.map('map-container').setView([defaultLat, defaultLng], defaultZoom);
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add scale control
        L.control.scale().addTo(map);
        
        // Setup GeoSearch provider for location search
        const provider = new GeoSearch.OpenStreetMapProvider();
        
        // Initialize search control
        searchControl = new GeoSearch.GeoSearchControl({
            provider: provider,
            style: 'bar',
            showMarker: false, // We'll add our own marker
            autoClose: true,
            searchLabel: 'Search for address',
            retainZoomLevel: false,
            animateZoom: true,
            autoComplete: true,
            autoCompleteDelay: 250,
            classNames: {
                container: 'search-control'
            }
        });
        
        map.addControl(searchControl);
        
        // Check if we already have stored coordinates
        if (latInput.value && lngInput.value) {
            const storedLat = parseFloat(latInput.value);
            const storedLng = parseFloat(lngInput.value);
            
            if (!isNaN(storedLat) && !isNaN(storedLng)) {
                // Center map on stored coordinates
                map.setView([storedLat, storedLng], 18);
                
                // Add marker at stored location
                marker = L.marker([storedLat, storedLng], {
                    draggable: true
                }).addTo(map);
                
                // Update display
                updateCoordinatesDisplay(storedLat, storedLng);
                updateAccuracyIndicator(map.getZoom());
                
                // Setup marker drag events
                setupMarkerEvents();
            }
        }
        
        // Function to update coordinates display
        function updateCoordinatesDisplay(lat, lng) {
            const formattedLat = lat.toFixed(6);
            const formattedLng = lng.toFixed(6);
            coordinatesDisplay.textContent = `${formattedLat}, ${formattedLng}`;
            
            // Update hidden inputs for Livewire
            latInput.value = lat;
            lngInput.value = lng;
            
            // Dispatch change events to update Livewire properties
            latInput.dispatchEvent(new Event('input', { bubbles: true }));
            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Function to update accuracy indicator based on zoom level
        function updateAccuracyIndicator(zoomLevel) {
            // Calculate accuracy percentage (zoom level 12-19)
            const minZoom = 12;
            const maxZoom = 19;
            const percentage = Math.min(100, Math.max(0, ((zoomLevel - minZoom) / (maxZoom - minZoom)) * 100));
            
            // Update indicator
            accuracyIndicator.style.width = `${percentage}%`;
            
            // Update text
            if (percentage < 30) {
                accuracyText.textContent = "Zoom in for better accuracy";
                accuracyIndicator.classList.remove('bg-green-600', 'bg-yellow-500');
                accuracyIndicator.classList.add('bg-blue-600');
            } else if (percentage < 70) {
                accuracyText.textContent = "Medium accuracy";
                accuracyIndicator.classList.remove('bg-blue-600', 'bg-green-600');
                accuracyIndicator.classList.add('bg-yellow-500');
            } else {
                accuracyText.textContent = "High accuracy";
                accuracyIndicator.classList.remove('bg-blue-600', 'bg-yellow-500');
                accuracyIndicator.classList.add('bg-green-600');
            }
        }
        
        // Setup marker drag events
        function setupMarkerEvents() {
            marker.on('dragend', function(event) {
                const position = marker.getLatLng();
                updateCoordinatesDisplay(position.lat, position.lng);
            });
        }
        
        // Handle map clicks to place/move marker
        map.on('click', function(e) {
            const clickedLat = e.latlng.lat;
            const clickedLng = e.latlng.lng;
            
            // If marker already exists, move it
            if (marker) {
                marker.setLatLng([clickedLat, clickedLng]);
            } else {
                // Otherwise create new marker
                marker = L.marker([clickedLat, clickedLng], {
                    draggable: true
                }).addTo(map);
                
                // Setup marker events
                setupMarkerEvents();
            }
            
            // Update display
            updateCoordinatesDisplay(clickedLat, clickedLng);
        });
        
        // Update accuracy indicator when zoom changes
        map.on('zoomend', function() {
            updateAccuracyIndicator(map.getZoom());
        });
        
        // Handle initial accuracy indicator
        updateAccuracyIndicator(map.getZoom());
        
        // Handle search button clicks
        searchButton.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                // Call the provider directly
                provider.search({ query: searchTerm }).then(results => {
                    if (results && results.length > 0) {
                        const result = results[0];
                        const searchLat = result.y;
                        const searchLng = result.x;
                        
                        // Center map on result
                        map.setView([searchLat, searchLng], 18);
                        
                        // If marker already exists, move it
                        if (marker) {
                            marker.setLatLng([searchLat, searchLng]);
                        } else {
                            // Otherwise create new marker
                            marker = L.marker([searchLat, searchLng], {
                                draggable: true
                            }).addTo(map);
                            
                            // Setup marker events
                            setupMarkerEvents();
                        }
                        
                        // Update display
                        updateCoordinatesDisplay(searchLat, searchLng);
                    } else {
                        alert('No results found for your search. Please try a different search term.');
                    }
                });
            }
        });
        
        // Handle Enter key in search input
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchButton.click();
            }
        });
    }

    // Profile Photo Handling and Cropping
    document.addEventListener('DOMContentLoaded', function() {
        const photoUpload = document.getElementById('photo-upload');
        const previewImage = document.getElementById('preview-image');
        const cropperModal = document.getElementById('cropper-modal');
        const cropperImage = document.getElementById('cropper-image');
        const openCropperBtn = document.getElementById('open-cropper-btn');
        const saveCropBtn = document.getElementById('save-crop-btn');
        const cancelCropBtn = document.getElementById('cancel-crop-btn');
        
        let cropper = null;
        
        // Helper function to scroll page to top
        function scrollPageToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        // Function to initialize cropper on an image
        function initCropper() {
            if (cropper) {
                cropper.destroy();
            }
            
            if (cropperImage && cropperImage.src) {
                console.log('Initializing cropper on image:', cropperImage.src);
                cropper = new Cropper(cropperImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    guides: true,
                    center: true,
                    highlight: false,
                    background: true,
                    responsive: true,
                });
            } else {
                console.error('Cropper image element not found or has no source');
            }
        }
        
        // Function to open cropper modal
        function openCropper() {
            if (!cropperModal) {
                console.error('Cropper modal not found');
                return;
            }
            
            // Check if we have an image to crop
            let imageSource = null;
            
            if (photoUpload && photoUpload.files && photoUpload.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    cropperImage.src = e.target.result;
                    cropperModal.classList.remove('hidden');
                    
                    // Initialize cropper after a small delay
                    setTimeout(initCropper, 300);
                };
                reader.readAsDataURL(photoUpload.files[0]);
            } else if (previewImage && previewImage.src) {
                // Use existing preview image
                cropperImage.src = previewImage.src;
                cropperModal.classList.remove('hidden');
                
                // Initialize cropper after a small delay
                setTimeout(initCropper, 300);
            } else {
                alert('Please select an image to crop first.');
            }
        }
        
        // Function to close cropper modal
        function closeCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            
            if (cropperModal) {
                cropperModal.classList.add('hidden');
            }
        }
        
        // Function to save cropped image
        function saveCroppedImage() {
            console.log('Save crop button clicked');
            
            if (!cropper) {
                console.error('Cropper not initialized');
                alert('Error: Cropper not initialized. Please try again.');
                return;
            }
            
            try {
                const canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                
                if (!canvas) {
                    console.error('Failed to create canvas');
                    alert('Error: Failed to create canvas. Please try again.');
                    return;
                }
                
                // Update preview image
                if (previewImage) {
                    previewImage.src = canvas.toDataURL('image/webp');
                }
                
                // Send data to Livewire component
                console.log('Sending cropped image to Livewire');
                
                // Use the Livewire JavaScript API to call the cropPhoto method
                @this.cropPhoto(canvas.toDataURL('image/webp'))
                    .then(() => {
                        console.log('Crop saved successfully');
                    })
                    .catch(error => {
                        console.error('Error sending cropped data to Livewire:', error);
                    });
                
                // Close modal
                closeCropper();
            } catch (error) {
                console.error('Error saving cropped image:', error);
                alert('An error occurred while saving the cropped image. Please try again.');
            }
        }
        
        // Register event listeners for cropper
        if (openCropperBtn) {
            openCropperBtn.addEventListener('click', openCropper);
        }
        
        if (saveCropBtn) {
            saveCropBtn.addEventListener('click', saveCroppedImage);
        }
        
        if (cancelCropBtn) {
            cancelCropBtn.addEventListener('click', closeCropper);
        }
        
        // Handle file input change - don't auto-open cropper to allow direct uploads
        if (photoUpload) {
            photoUpload.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    // Display the selected image in the preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (previewImage) {
                            previewImage.src = event.target.result;
                        }
                    };
                    reader.readAsDataURL(e.target.files[0]);
                    
                    console.log('Photo selected. Click "Edit/crop photo" to open the cropper, or save directly.');
                }
            });
        }
        
        // Multiple approaches to listen for events
        // 1. Livewire v3 style event listener
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized, setting up event listeners');
            
            Livewire.on('photo-selected', (event) => {
                console.log('Received photo-selected event:', event);
                
                // Check if we have photoUrl
                if (event.photoUrl && cropperImage) {
                    cropperImage.src = event.photoUrl;
                    cropperModal.classList.remove('hidden');
                    
                    // Initialize cropper after a small delay
                    setTimeout(initCropper, 300);
                }
            });
            
            // Listen for the profile-optimized event
            Livewire.on('profile-optimized', () => {
                console.log('Received profile-optimized event');
                scrollPageToTop();
            });
            
            // Listen for the alternative event
            Livewire.on('profile-optimized-js', (data) => {
                console.log('Received profile-optimized-js event');
                if (data && data.scrollToTop) {
                    scrollPageToTop();
                }
            });
        });
        
        // 2. Direct DOM event listener (fallback)
        window.addEventListener('profile-optimized', function() {
            console.log('Received DOM profile-optimized event');
            scrollPageToTop();
        });
        
        window.addEventListener('profile-optimized-js', function(e) {
            console.log('Received DOM profile-optimized-js event');
            scrollPageToTop();
        });
        
        // 3. Additional observer to watch for success message appearance
        // Setup a mutation observer to detect when the success message is added to the DOM
        const targetNode = document.querySelector('.max-w-7xl.mx-auto'); 
        if (targetNode) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Check if a success message appeared
                        const successMsg = document.querySelector('.bg-green-100.border.border-green-400');
                        if (successMsg && successMsg.textContent.includes('Profile optimized successfully')) {
                            console.log('Detected success message, scrolling to top');
                            scrollPageToTop();
                        }
                    }
                });
            });
            
            // Configure and start the observer
            observer.observe(targetNode, { childList: true, subtree: true });
        }
    });
</script>
@endpush 