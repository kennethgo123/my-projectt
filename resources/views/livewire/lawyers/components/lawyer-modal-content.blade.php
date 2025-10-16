<!-- Profile header -->
<div x-init="console.log('Modal content initialized', {
    'modal_type': lawyerType,
    'has_user': !!lawyerDetail.user,
    'user_id': lawyerDetail.user?.id,
    'rating_raw': lawyerType === 'lawFirm' ? lawyerDetail.user?.law_firm_average_rating : lawyerDetail.user?.average_rating,
    'office_address': lawyerDetail.office_address
})"></div>

<div class="flex flex-col md:flex-row md:items-start space-y-6 md:space-y-0 md:space-x-6 mb-10 bg-emerald-50 p-6 rounded-2xl border border-emerald-100 shadow-md">
    <!-- Profile photo -->
    <div class="flex-shrink-0 flex justify-center">
        <div class="h-32 w-32 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-emerald-600 border-4 border-emerald-400 overflow-hidden shadow-lg">
            <template x-if="lawyerDetail.photo_path">
                <img class="h-full w-full object-cover" :src="`/storage/${lawyerDetail.photo_path}`" alt="Profile photo">
            </template>
            <template x-if="!lawyerDetail.photo_path">
                <svg class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                </svg>
            </template>
        </div>
    </div>
    
    <!-- Basic info -->
    <div class="flex-1 space-y-4">
        <div>
            <div class="flex items-center">
                <h3 class="text-2xl font-bold font-raleway text-emerald-800 mb-2" x-text="lawyerType === 'lawFirm' ? lawyerDetail.firm_name : `${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h3>
            </div>
            
            <div class="flex items-center mt-1 text-sm text-gray-500">
                <!-- Direct star rating display using the values from the card -->
                <template x-if="lawyerDetail.user">
                    <div class="flex items-center">
                        <!-- Full stars -->
                        <template x-for="i in Math.floor(getAverageRating())">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </template>
                        
                        <!-- Half star -->
                        <template x-if="getAverageRating() % 1 >= 0.5">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <defs>
                                    <linearGradient :id="`halfStarModal${lawyerDetail.id || 'default'}`" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="50%" stop-color="currentColor"></stop>
                                        <stop offset="50%" stop-color="#D1D5DB"></stop>
                                    </linearGradient>
                                </defs>
                                <path :fill="`url(#halfStarModal${lawyerDetail.id || 'default'})`" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </template>
                        
                        <!-- Empty stars -->
                        <template x-for="i in Math.floor(5 - getAverageRating() - (getAverageRating() % 1 >= 0.5 ? 1 : 0))">
                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </template>
                        
                        <!-- Rating value -->
                        <span class="ml-1.5 mr-2">
                            <template x-if="getAverageRating() > 0">
                                <span x-text="getAverageRating().toFixed(1)"></span>
                            </template>
                            <template x-if="getAverageRating() <= 0">
                                <span>New</span>
                            </template>
                            <template x-if="getRatingCount() > 0">
                                <span x-text="'(' + getRatingCount() + ')'"></span>
                            </template>
                        </span>
                    </div>
                </template>
                <span class="text-gray-400 ml-2">•</span>
                <span class="ml-2" x-text="lawyerDetail.city"></span>
            </div>
            
            <div class="mt-3 flex items-center text-sm text-gray-700">
                <template x-if="lawyerType !== 'lawFirm'">
                    <div>
                        <template x-if="!lawyerDetail.law_firm_id">
                            <span class="font-medium">Solo Practitioner</span>
                        </template>
                        <template x-if="lawyerDetail.law_firm_id && lawyerDetail.lawFirm">
                            <span>Associated with <span class="font-medium" x-text="lawyerDetail.lawFirm.firm_name"></span></span>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Office Address Section - Link uses coordinates if available, otherwise section is hidden -->
            <template x-if="lawyerDetail.office_address && lawyerDetail.latitude && lawyerDetail.longitude">
                <div class="mt-3 flex items-center text-sm text-gray-700">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <a 
                       :href="`https://www.openstreetmap.org/?mlat=${lawyerDetail.latitude}&mlon=${lawyerDetail.longitude}#map=16/${lawyerDetail.latitude}/${lawyerDetail.longitude}`" 
                       target="_blank" 
                       class="text-emerald-600 hover:text-emerald-800 hover:underline transition-colors"
                       title="View on OpenStreetMap"
                    >
                        <span x-text="lawyerDetail.office_address"></span>
                        <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </template>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <!-- Book Consultation link with separate URL generation -->
            @php
                $baseBookUrl = route('client.book-consultation', ['lawyer_id' => 'PLACEHOLDER_ID']);
                $baseBookUrl = str_replace('PLACEHOLDER_ID', '', $baseBookUrl);
            @endphp
            <a :href="lawyerDetail && lawyerDetail.user ? '{{ $baseBookUrl }}' + lawyerDetail.user.id : '#'" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
                Book a Consultation
            </a>
            <a
                :href="lawyerDetail && lawyerDetail.user ? `/messages/${lawyerDetail.user.id}` : '#'"
                class="inline-flex items-center px-6 py-3 border border-emerald-600 text-base font-semibold rounded-lg shadow-md text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway transition-all duration-300"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                </svg>
                Message
            </a>
            <!-- View Full Profile Button -->
            <template x-if="lawyerType === 'lawFirm'">
                <a :href="lawyerDetail && lawyerDetail.user ? `/law-firm/${lawyerDetail.user.id}` : '#'" 
                   class="inline-flex items-center px-6 py-3 border border-indigo-600 text-base font-semibold rounded-lg shadow-md text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    View Full Profile
                </a>
            </template>
            <template x-if="lawyerType !== 'lawFirm'">
                <a :href="lawyerDetail && lawyerDetail.user ? `/lawyer/${lawyerDetail.user.id}` : '#'" 
                   class="inline-flex items-center px-6 py-3 border border-indigo-600 text-base font-semibold rounded-lg shadow-md text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    View Full Profile
                </a>
            </template>
        </div>
    </div>
</div>

<!-- Detailed sections -->
<div class="space-y-8">
    <!-- Pricing Description -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
            </svg>
            PRICING INFORMATION
        </h4>
        <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed">
            <template x-if="lawyerType === 'lawFirm'">
                <div x-html="lawyerDetail.pricing_description || 'Pricing information not available.'"></div>
            </template>
            <template x-if="lawyerType !== 'lawFirm'">
                <div>
                    <template x-if="lawyerDetail.law_firm_id && lawyerDetail.lawFirm">
                        <!-- Show law firm's pricing description for associated lawyers -->
                        <div x-html="lawyerDetail.lawFirm.pricing_description || 'Pricing information not available.'"></div>
                    </template>
                    <template x-if="!lawyerDetail.law_firm_id">
                        <!-- Show lawyer's own pricing description for independent lawyers -->
                        <div x-html="lawyerDetail.pricing_description || 'Pricing information not available.'"></div>
                    </template>
                </div>
            </template>
            <div class="mt-4 text-sm text-gray-600">
                <template x-if="lawyerType !== 'lawFirm' && lawyerDetail.law_firm_id && lawyerDetail.lawFirm">
                    <!-- Show law firm's budget range for associated lawyers -->
                    <template x-if="lawyerDetail.lawFirm.min_budget && lawyerDetail.lawFirm.max_budget">
                        <p>Professional Fee Range: ₱<span x-text="lawyerDetail.lawFirm.min_budget.toLocaleString()"></span> - ₱<span x-text="lawyerDetail.lawFirm.max_budget.toLocaleString()"></span></p>
                    </template>
                </template>
                <template x-if="(!lawyerDetail.law_firm_id || lawyerType === 'lawFirm') && lawyerDetail.min_budget && lawyerDetail.max_budget">
                    <!-- Show individual budget range for independent lawyers or law firms -->
                    <p>Professional Fee Range: ₱<span x-text="lawyerDetail.min_budget.toLocaleString()"></span> - ₱<span x-text="lawyerDetail.max_budget.toLocaleString()"></span></p>
                </template>
            </div>
        </div>
    </div>

    <!-- About section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            ABOUT
        </h4>
        <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed" x-html="lawyerDetail.about || lawyerDetail.description || 'No description available.'"></div>
    </div>
    
    <!-- Education section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300" x-show="lawyerType !== 'lawFirm'">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
            </svg>
            EDUCATION
        </h4>
        <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed" x-html="lawyerDetail.education || 'No education information available.'"></div>
    </div>
    
    <!-- Professional Experience section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-3.76 0-7.26-.84-10-2.308z" />
            </svg>
            PROFESSIONAL EXPERIENCE
        </h4>
        <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed" x-html="lawyerType === 'lawFirm' ? lawyerDetail.experience : (lawyerDetail.experience || 'No professional experience information available.')"></div>
    </div>
    
    <!-- Services section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
            </svg>
            SERVICES
        </h4>
        <div class="flex flex-wrap gap-2">
            <template x-for="service in lawyerDetail.services" :key="service.id">
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 font-open-sans border border-emerald-200">
                    <span x-text="service.name"></span>
                </span>
            </template>
            <template x-if="!lawyerDetail.services || lawyerDetail.services.length === 0">
                <p class="text-gray-600 font-open-sans">No services listed.</p>
            </template>
        </div>
    </div>
    
    <!-- Client Reviews Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
        <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L9.612 9.04a1 1 0 00.788 0l7-3a1 1 0 000-1.84l-7-3zM3.4 7.36a1 1 0 00-.8 1.4l1.041 2.36A1 1 0 004.77 11.6l6.87-6.87A1 1 0 0010.22 3.4L3.4 7.36z"></path>
                <path d="M5.2 10.02L6 11A1 1 0 007.4 10.594L5.2 10.02zM14.8 9a1 1 0 00-.8-1.4l-1.041-2.36A1 1 0 0011.82 4.6L5 11l1.6 1.6L15.18 5.2l-1.041-2.3A1 1 0 0015.6 3.8l.8 1.2 3 7a1 1 0 01-.364 1.118l-1.4 1.4a1 1 0 01-1.416 0l-3.6-3.6a1 1 0 010-1.416l1.4-1.4a1 1 0 011.364-.366l.16.076a1 1 0 00.416.096h.1z"></path>
            </svg>
            CLIENT REVIEWS
        </h4>
        
        <!-- Individual Reviews -->
        <div>
            @include('livewire.lawyers.components.reviews')
        </div>
    </div>
</div> 