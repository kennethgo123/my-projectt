<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $lawFirm->firm_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Law Firm Details -->
                <div class="p-6">
                    <div class="flex flex-col md:flex-row">
                        <!-- Profile Picture -->
                        <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                            @if($lawFirm->user && $lawFirm->user->profile_photo_path)
                                <img class="h-32 w-32 rounded-lg object-cover" src="{{ Storage::url($lawFirm->user->profile_photo_path) }}" alt="{{ $lawFirm->firm_name }}">
                            @else
                                <div class="h-32 w-32 rounded-lg bg-gray-200 flex items-center justify-center text-gray-500">
                                    <svg class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Law Firm Information -->
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $lawFirm->firm_name }}</h1>
                            <p class="text-sm text-gray-600 mt-1">{{ $lawFirm->city }}</p>
                            
                            <div class="mt-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-gray-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $lawFirm->address }}</span>
                                </div>
                                
                                @if($lawFirm->contact_number)
                                <div class="flex items-center mt-1">
                                    <svg class="h-4 w-4 text-gray-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                    </svg>
                                    <span>{{ $lawFirm->contact_number }}</span>
                                </div>
                                @endif
                                
                                <div class="flex items-center mt-1">
                                    <svg class="h-4 w-4 text-gray-500 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                    <span>{{ $lawFirm->user->email }}</span>
                                </div>
                            </div>
                            
                            <!-- Budget range -->
                            <div class="mt-3">
                                <h3 class="text-sm font-medium text-gray-900">Professional Fee Range</h3>
                                <p class="text-sm text-gray-600">₱{{ number_format($lawFirm->min_budget) }} - ₱{{ number_format($lawFirm->max_budget) }}</p>
                            </div>
                            
                            <!-- Services -->
                            @if($lawFirm->services->count() > 0)
                                <div class="mt-4">
                                    <h3 class="text-sm font-medium text-gray-900">Legal Services</h3>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($lawFirm->services as $service)
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                                {{ $service->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Contact Button -->
                            <div class="mt-6">
                                <a href="{{ route('messages.chat', ['userId' => $lawFirm->user->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                    </svg>
                                    Message {{ $lawFirm->firm_name }}
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    @if($lawFirm->description)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-medium text-gray-900">About {{ $lawFirm->firm_name }}</h2>
                            <div class="mt-2 text-gray-600 space-y-4">
                                <p>{{ $lawFirm->description }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Specializations -->
                    @if($lawFirm->specializations)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-medium text-gray-900">Specializations</h2>
                            <div class="mt-2 text-gray-600">
                                <p>{{ $lawFirm->specializations }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Associated Lawyers -->
                    @if($lawyers->count() > 0)
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h2 class="text-lg font-medium text-gray-900">Lawyers at {{ $lawFirm->firm_name }}</h2>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($lawyers as $lawyer)
                                    <div class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 border border-emerald-100 transform hover:-translate-y-1 hover:scale-[1.02] hover:ring-2 hover:ring-emerald-100 hover:ring-opacity-50">
                                        <a href="javascript:void(0);" class="block" 
                                           @click.prevent="$dispatch('show-lawyer-detail', {lawyer: {{ json_encode($lawyer->load('user', 'lawFirm')) }}, type: 'firmLawyer'})">
                                            <div class="p-4">
                                                <div class="flex items-start">
                                                    <!-- Profile Photo -->
                                                    <div class="flex-shrink-0">
                                                        @if($lawyer->photo_path)
                                                            <img class="h-12 w-12 rounded-full object-cover" src="{{ Storage::url($lawyer->photo_path) }}" alt="{{ $lawyer->full_name }}">
                                                        @elseif($lawyer->user && $lawyer->user->profile_photo_path)
                                                            <img class="h-12 w-12 rounded-full object-cover" src="{{ Storage::url($lawyer->user->profile_photo_path) }}" alt="{{ $lawyer->full_name }}">
                                                        @else
                                                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Lawyer Info -->
                                                    <div class="ml-4 flex-1 min-w-0">
                                                        <h4 class="text-lg font-medium text-gray-900">{{ $lawyer->first_name }} {{ $lawyer->last_name }}</h4>
                                                        
                                                        <div class="mt-1 flex flex-wrap gap-2">
                                                            @foreach($lawyer->services->take(3) as $service)
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    {{ $service->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                        
                                                        <!-- Office Address (if shared) -->
                                                        @if($lawyer->show_office_address && $lawyer->office_address)
                                                            <div class="mt-2">
                                                                <a href="{{ $lawyer->google_maps_link }}" target="_blank" class="flex items-start text-sm text-gray-700 hover:text-green-700 transition-colors group">
                                                                    <svg class="h-5 w-5 mr-1.5 text-green-600 flex-shrink-0 mt-0.5 group-hover:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    </svg>
                                                                    <span class="leading-tight group-hover:underline">{{ $lawyer->office_address }}</span>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lawyer Detail Modal - Add the modal component here -->
    <div 
        x-data="{ show: false, lawyerDetail: null, lawyerType: null }" 
        x-on:show-lawyer-detail.window="
            show = true; 
            lawyerDetail = $event.detail.lawyer; 
            lawyerType = $event.detail.type;
        "
        x-show="show" 
        class="fixed inset-0 z-[1000] overflow-hidden" 
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="show" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-700 bg-opacity-80 backdrop-blur-sm transition-opacity"
            @click="show = false"
        ></div>
        
        <!-- Modal Panel - Sliding from right -->
        <div class="fixed inset-y-0 right-0 max-w-3xl w-full flex justify-end">
            <!-- Slide-in panel -->
            <div 
                x-show="show" 
                x-transition:enter="transform transition ease-in-out duration-500"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-500"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-full bg-white shadow-2xl"
            >
                <!-- Content -->
                <div class="h-full flex flex-col overflow-y-auto">
                    <!-- Header with close button -->
                    <div class="flex justify-between items-center px-8 py-5 border-b border-emerald-100 bg-gradient-to-r from-emerald-500 to-emerald-600 sticky top-0 z-10">
                        <div class="flex items-center space-x-3">
                            <template x-if="lawyerDetail">
                                <h2 class="text-2xl font-bold font-raleway text-white" x-text="`${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h2>
                            </template>
                        </div>
                        <button @click="show = false" class="p-2 rounded-full bg-white/20 hover:bg-white/30 transition-colors">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Lawyer profile content -->
                    <div class="flex-1 p-8">
                        <template x-if="lawyerDetail">
                            <div>
                                <!-- Profile header -->
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
                                            <h3 class="text-2xl font-bold text-emerald-800 mb-2" x-text="`${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h3>
                                            
                                            <div class="flex flex-col space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-gray-700" x-text="lawyerDetail.city"></span>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
                                                    </svg>
                                                    <span class="text-gray-700" x-text="lawyerDetail.specialization || 'Lawyer'"></span>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-gray-700 font-medium" x-text="`₱${Number(lawyerDetail.min_budget).toLocaleString()} - ₱${Number(lawyerDetail.max_budget).toLocaleString()}`"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Book consultation button -->
                                        <div class="mt-4 flex space-x-3">
                                            <a 
                                                :href="lawyerDetail && lawyerDetail.user ? `/client/book-consultation/${lawyerDetail.user.id}` : '#'" 
                                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300"
                                            >
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Book a Consultation
                                            </a>
                                            <a
                                                :href="lawyerDetail && lawyerDetail.user ? `/messages/${lawyerDetail.user.id}` : '#'"
                                                class="inline-flex items-center px-6 py-3 border border-emerald-600 text-base font-semibold rounded-lg shadow-md text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                                </svg>
                                                Message
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detailed sections -->
                                <div class="space-y-8">
                                    <!-- About section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            ABOUT
                                        </h4>
                                        <div class="prose max-w-none text-gray-700 leading-relaxed" x-html="lawyerDetail.about || lawyerDetail.description || 'No description available.'"></div>
                                    </div>
                                    
                                    <!-- Education section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                            </svg>
                                            EDUCATION
                                        </h4>
                                        <div class="prose max-w-none text-gray-700 leading-relaxed" x-html="lawyerDetail.education || 'No education information available.'"></div>
                                    </div>
                                    
                                    <!-- Services section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
                                            </svg>
                                            SERVICES
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="service in lawyerDetail.services" :key="service.id">
                                                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <span x-text="service.name"></span>
                                                </span>
                                            </template>
                                            <template x-if="!lawyerDetail.services || lawyerDetail.services.length === 0">
                                                <p class="text-gray-600">No services listed.</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lawyer Detail Modal - Add the modal component here -->
    <div 
        x-data="{ show: false, lawyerDetail: null, lawyerType: null }" 
        x-on:show-lawyer-detail.window="
            show = true; 
            lawyerDetail = $event.detail.lawyer; 
            lawyerType = $event.detail.type;
        "
        x-show="show" 
        class="fixed inset-0 z-[1000] overflow-hidden" 
        style="display: none;"
    >
        <!-- Backdrop -->
        <div 
            x-show="show" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-700 bg-opacity-80 backdrop-blur-sm transition-opacity"
            @click="show = false"
        ></div>
        
        <!-- Modal Panel - Sliding from right -->
        <div class="fixed inset-y-0 right-0 max-w-3xl w-full flex justify-end">
            <!-- Slide-in panel -->
            <div 
                x-show="show" 
                x-transition:enter="transform transition ease-in-out duration-500"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-500"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-full bg-white shadow-2xl"
            >
                <!-- Content -->
                <div class="h-full flex flex-col overflow-y-auto">
                    <!-- Header with close button -->
                    <div class="flex justify-between items-center px-8 py-5 border-b border-emerald-100 bg-gradient-to-r from-emerald-500 to-emerald-600 sticky top-0 z-10">
                        <div class="flex items-center space-x-3">
                            <template x-if="lawyerDetail">
                                <h2 class="text-2xl font-bold font-raleway text-white" x-text="`${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h2>
                            </template>
                        </div>
                        <button @click="show = false" class="p-2 rounded-full bg-white/20 hover:bg-white/30 transition-colors">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Lawyer profile content -->
                    <div class="flex-1 p-8">
                        <template x-if="lawyerDetail">
                            <div>
                                <!-- Profile header -->
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
                                            <h3 class="text-2xl font-bold text-emerald-800 mb-2" x-text="`${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h3>
                                            
                                            <div class="flex flex-col space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-gray-700" x-text="lawyerDetail.city"></span>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />
                                                    </svg>
                                                    <span class="text-gray-700" x-text="lawyerDetail.specialization || 'Lawyer'"></span>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="text-gray-700 font-medium" x-text="`₱${Number(lawyerDetail.min_budget).toLocaleString()} - ₱${Number(lawyerDetail.max_budget).toLocaleString()}`"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Book consultation button -->
                                        <div class="mt-4 flex space-x-3">
                                            <a 
                                                :href="lawyerDetail && lawyerDetail.user ? `/client/book-consultation/${lawyerDetail.user.id}` : '#'" 
                                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300"
                                            >
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Book a Consultation
                                            </a>
                                            <a
                                                :href="lawyerDetail && lawyerDetail.user ? `/messages/${lawyerDetail.user.id}` : '#'"
                                                class="inline-flex items-center px-6 py-3 border border-emerald-600 text-base font-semibold rounded-lg shadow-md text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                                </svg>
                                                Message
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detailed sections -->
                                <div class="space-y-8">
                                    <!-- About section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            ABOUT
                                        </h4>
                                        <div class="prose max-w-none text-gray-700 leading-relaxed" x-html="lawyerDetail.about || lawyerDetail.description || 'No description available.'"></div>
                                    </div>
                                    
                                    <!-- Education section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                            </svg>
                                            EDUCATION
                                        </h4>
                                        <div class="prose max-w-none text-gray-700 leading-relaxed" x-html="lawyerDetail.education || 'No education information available.'"></div>
                                    </div>
                                    
                                    <!-- Services section -->
                                    <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                                        <h4 class="text-lg font-semibold text-emerald-800 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
                                            </svg>
                                            SERVICES
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="service in lawyerDetail.services" :key="service.id">
                                                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <span x-text="service.name"></span>
                                                </span>
                                            </template>
                                            <template x-if="!lawyerDetail.services || lawyerDetail.services.length === 0">
                                                <p class="text-gray-600">No services listed.</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 