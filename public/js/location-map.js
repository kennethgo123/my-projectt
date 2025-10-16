// OpenStreetMap integration for location picking
document.addEventListener('DOMContentLoaded', function() {
    initializeLocationMap();
});

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
    var map = L.map('map-container').setView([defaultLat, defaultLng], defaultZoom);
    
    // Add OpenStreetMap tile layer and listen for its load event to correct sizing
    var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    // When tiles finish loading, ensure the map container sizing is correct
    tileLayer.on('load', function() {
        map.invalidateSize();
    });
    
    // Add scale control
    L.control.scale().addTo(map);
    
    // Setup GeoSearch provider for location search
    const provider = new GeoSearch.OpenStreetMapProvider();
    
    // Initialize search control
    const searchControl = new GeoSearch.GeoSearchControl({
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
    
    // Variable to hold our marker
    var marker = null;
    
    // Check if we already have stored coordinates
    if (latInput && latInput.value && lngInput && lngInput.value) {
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
        if (!coordinatesDisplay || !latInput || !lngInput) return;
        
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
        if (!accuracyIndicator || !accuracyText) return;
        
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
    
    // --- START: Add invalidateSize after a short delay ---
    // Sometimes the container size isn't final right away.
    // Give the layout a moment to settle, then tell Leaflet to check again.
    setTimeout(function() {
        if (map) {
            console.log('Invalidating map size...');
            map.invalidateSize();
        }
    }, 100); // 100ms delay
    // --- END: Add invalidateSize after a short delay ---
    
    // Also correct map size when window resizes
    window.addEventListener('resize', function() {
        map.invalidateSize();
    });
    
    // Handle search button clicks
    if (searchButton && searchInput) {
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
} 