<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Status Banner - Only show if account is pending -->
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
                            <p>Your law firm account is currently awaiting approval. In the meantime, optimize your profile to showcase your expertise and services to potential clients.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Optimize Your Law Firm Profile</h2>

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
                        <p class="text-sm text-gray-500 mb-4">Upload a professional firm logo or image</p>
                        
                        <div class="flex items-start space-x-6">
                            <!-- Preview -->
                            <div class="shrink-0">
                                <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200 overflow-hidden">
                                    @if($existingPhoto)
                                        <img src="{{ Storage::url($existingPhoto) }}" alt="Current firm photo" class="h-16 w-16 object-cover" id="preview-image">
                                    @elseif($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Firm photo preview" class="h-16 w-16 object-cover" id="preview-image">
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
                                    <span class="sr-only">Choose firm photo</span>
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
                            </div>
                        </div>

                        <!-- Cropping Modal -->
                        <div id="cropper-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
                            <div class="flex items-center justify-center min-h-screen p-4">
                                <!-- Modal backdrop -->
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

                                <div class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl relative z-50 max-w-lg w-full p-6">
                                    <div>
                                        <div class="text-center">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                                Preview firm photo
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
                                    <div id="cropper-status" class="text-center mt-3 hidden">
                                        <p class="text-sm text-gray-600">
                                            <span class="inline-block mr-2">
                                                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </span>
                                            Processing image...
                                        </p>
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
                        <x-label for="about" value="Firm Overview" />
                        <div class="mt-1">
                            <textarea id="about" wire:model="about" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="Share your firm's background, mission, values, and approach to legal practice"></textarea>
                        </div>
                        @error('about') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Experience -->
                    <div>
                        <x-label for="experience" value="Firm Experience" />
                        <div class="mt-1">
                            <textarea id="experience" wire:model="experience" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="Detail your firm's collective experience, notable cases, and areas of practice"></textarea>
                        </div>
                        @error('experience') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Achievements -->
                    <div>
                        <x-label for="achievements" value="Achievements & Recognition" />
                        <div class="mt-1">
                            <textarea id="achievements" wire:model="achievements" rows="4" 
                                class="shadow-sm block w-full border-gray-300 rounded-md"
                                placeholder="List awards, accolades, media mentions, and other professional recognitions"></textarea>
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

                    <div class="flex justify-end">
                        <x-button>
                            Save Profile
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-geosearch@3.8.0/dist/geosearch.umd.js"></script>

<script>
    // Location map functionality
    function initializeLocationMap() {
        // ... existing map code ... (leave unchanged)
    }

    // Photo processing and cropping functionality
    document.addEventListener('livewire:initialized', () => {
        const photoFileInput = document.getElementById('photo-upload');
        const cropperPreviewContainer = document.getElementById('cropper-modal');
        const cropperImage = document.getElementById('cropper-image');
        const openCropperBtn = document.getElementById('open-cropper-btn');
        const saveCropBtn = document.getElementById('save-crop-btn');
        const cancelCropBtn = document.getElementById('cancel-crop-btn');
        const previewImage = document.getElementById('preview-image');
        
        let cropper;
        
        if (!photoFileInput || !cropperPreviewContainer) return;
        
        // Handle opening the cropper modal
        if (openCropperBtn) {
            openCropperBtn.addEventListener('click', function() {
                if (previewImage && previewImage.src) {
                    // If we have a preview image, show it in the cropper
                    cropperImage.src = previewImage.src;
                    cropperPreviewContainer.classList.remove('hidden');
                    
                    // Initialize cropper
                    if (cropper) {
                        cropper.destroy();
                    }
                    
                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 1,
                        zoomable: true,
                        scalable: true,
                        movable: true
                    });
                } else {
                    alert('Please select an image to crop.');
                }
            });
        }
        
        // Handle file selection
        photoFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (previewImage) {
                        previewImage.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
                
                console.log('Photo selected. Click "Edit/crop photo" to open the cropper, or save directly.');
            }
        });
        
        // Handle save crop
        if (saveCropBtn) {
            saveCropBtn.addEventListener('click', function() {
                if (cropper) {
                    try {
                        const canvas = cropper.getCroppedCanvas({
                            width: 300,
                            height: 300
                        });
                        
                        if (canvas) {
                            // Update preview image
                            if (previewImage) {
                                previewImage.src = canvas.toDataURL('image/webp');
                            }
                            
                            // Send cropped data to Livewire
                            console.log('Sending cropped image to Livewire');
                            @this.cropPhoto(canvas.toDataURL('image/webp'))
                                .then(() => {
                                    console.log('Crop data sent to Livewire component');
                                })
                                .catch(error => {
                                    console.error('Error sending crop data:', error);
                                });
                            
                            // Close and destroy cropper
                            cropperPreviewContainer.classList.add('hidden');
                            cropper.destroy();
                            cropper = null;
                        }
                    } catch (error) {
                        console.error('Error saving cropped image:', error);
                        alert('An error occurred while saving the cropped image. Please try again.');
                    }
                }
            });
        }
        
        // Handle cancel crop
        if (cancelCropBtn) {
            cancelCropBtn.addEventListener('click', function() {
                if (cropper) {
                    cropperPreviewContainer.classList.add('hidden');
                    cropper.destroy();
                    cropper = null;
                }
            });
        }
        
        // ----- START: Initialize Map After Livewire -----
        // Ensure the map is initialized AFTER Livewire is ready
        // and after the cropper logic is set up.
        if (typeof initializeLocationMap === 'function') {
            console.log('Livewire initialized, attempting to initialize map...');
            try {
                initializeLocationMap();
                console.log('Map initialization function called.');
            } catch (error) {
                console.error('Error calling initializeLocationMap:', error);
            }
        } else {
            console.error('initializeLocationMap function not found when Livewire initialized.');
        }
        // ----- END: Initialize Map After Livewire -----
        
    });
</script>

<script src="{{ asset('js/location-map.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to scroll to top
        function scrollPageToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Find the form and add a submit listener
        const form = document.querySelector('form[wire\\:submit="save"]');
        if (form) {
            form.addEventListener('submit', function() {
                // Set a timeout to scroll after submission is likely processed
                setTimeout(function() {
                    if (document.querySelector('.bg-green-100')) {
                        scrollPageToTop();
                    }
                }, 500);
            });
        }

        // Listen for Livewire events
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('profile-optimized', () => {
                scrollPageToTop();
            });
            
            Livewire.on('profile-optimized-js', (data) => {
                if (data && data.scrollToTop) {
                    scrollPageToTop();
                }
            });
        });

        // Direct DOM event listeners (fallback)
        window.addEventListener('profile-optimized', function() {
            scrollPageToTop();
        });
        
        window.addEventListener('profile-optimized-js', function(e) {
            scrollPageToTop();
        });

        // Setup a mutation observer to detect when the success message is added to the DOM
        const targetNode = document.querySelector('.max-w-7xl.mx-auto');
        if (targetNode) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Check if a success message appeared
                        const successMsg = document.querySelector('.bg-green-100.border.border-green-400');
                        if (successMsg && successMsg.textContent.includes('Profile optimized successfully')) {
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
