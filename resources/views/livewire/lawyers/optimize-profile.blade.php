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
                        <x-label for="education" value="Education" />
                        <div class="mt-1">
                            <textarea id="education" wire:model="education" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="List your educational background, including law school, degrees, and certifications"></textarea>
                        </div>
                        @error('education') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Experience -->
                    <div>
                        <x-label for="experience" value="Professional Experience" />
                        <div class="mt-1">
                            <textarea id="experience" wire:model="experience" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="Detail your work experience, notable cases, and areas of practice"></textarea>
                        </div>
                        @error('experience') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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

                    <!-- Languages -->
                    <div>
                        <x-label for="languages" value="Languages" />
                        <div class="mt-2 space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_english" wire:model="languages" value="English" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_english" class="ml-2 text-sm text-gray-700">English</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_filipino" wire:model="languages" value="Filipino (Tagalog)" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_filipino" class="ml-2 text-sm text-gray-700">Filipino (Tagalog)</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_cebuano" wire:model="languages" value="Cebuano" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_cebuano" class="ml-2 text-sm text-gray-700">Cebuano</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_ilocano" wire:model="languages" value="Ilocano" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_ilocano" class="ml-2 text-sm text-gray-700">Ilocano</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_waray" wire:model="languages" value="Waray" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_waray" class="ml-2 text-sm text-gray-700">Waray</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_kapampangan" wire:model="languages" value="Kapampangan" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_kapampangan" class="ml-2 text-sm text-gray-700">Kapampangan</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="lang_pangasinan" wire:model="languages" value="Pangasinan" 
                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="lang_pangasinan" class="ml-2 text-sm text-gray-700">Pangasinan</label>
                                </div>
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
                        <div class="mt-1">
                            <textarea id="office_address" wire:model="office_address" rows="3" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="Enter your complete office address including building name, street, city, etc."></textarea>
                        </div>
                        @error('office_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

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