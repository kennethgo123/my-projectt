<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    {{-- Direct law firms to dashboard, others to home --}}
                    <a href="{{ auth()->check() && auth()->user()->role->name === 'law_firm' ? route('law-firm.dashboard') : route('home') }}">
                            <x-application-logo class="block h-9 w-auto" />
                        </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        {{-- Links for AUTHENTICATED users --}}
                        @if(auth()->user()->role->name === 'lawyer')
                            <x-nav-link href="{{ route('lawyer.welcome') }}" :active="request()->routeIs('lawyer.welcome')">
                                {{ __('Home') }}
                            </x-nav-link>
                            {{-- Only show Optimize Profile link when not on cases or consultations pages --}}
                            @if(!request()->routeIs('lawyer.cases') && !request()->routeIs('lawyer.consultations'))
                                <x-nav-link href="{{ route('profile.optimize') }}" :active="request()->routeIs('profile.optimize')">
                                    {{ __('Optimize Your Profile') }}
                                </x-nav-link>
                            @endif
                            <x-nav-link href="{{ route('lawyer.cases') }}" :active="request()->routeIs('lawyer.cases')">
                                {{ __('Manage Your Cases') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('lawyer.consultations') }}" :active="request()->routeIs('lawyer.consultations')" class="relative">
                                {{ __('Consultations') }}
                                <span id="lawyer-notification-indicator" class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500 hidden"></span>
                            </x-nav-link>
                            <x-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">
                                {{ __('Messages') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role->name === 'client')
                            <x-nav-link href="{{ route('client.welcome') }}" :active="request()->routeIs('client.welcome')">
                                {{ __('Home') }}
                            </x-nav-link>
                            
                            {{-- Find Legal Help Dropdown (Using navbarrr) - ONLY FOR CLIENTS --}}
                            <div class="relative inline-block text-left" data-dropdown-toggle="dropdown">
                                <x-navbarrr align="left" width="48">
                                    <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Find Legal Help</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                                    </x-slot>
                                    <x-slot name="content">
                                       {{-- Dropdown content... --}}
                                    </x-slot>
                                </x-navbarrr>
                            </div>
                            
                            <x-nav-link href="{{ route('client.cases') }}" :active="request()->routeIs('client.cases')">
                                {{ __('My Cases') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">
                                {{ __('Messages') }}
                            </x-nav-link>
                            <x-nav-link href="{{ route('client.consultations') }}" :active="request()->routeIs('client.consultations')" class="relative">
                                {{ __('My Consultations') }}
                                <span id="notification-indicator" class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500 hidden"></span>
                            </x-nav-link>
                            <x-nav-link href="{{ route('client.invoices') }}" :active="request()->routeIs('client.invoices')">
                                {{ __('My Invoices') }}
                            </x-nav-link>
                        @elseif(auth()->user()->role->name === 'law_firm')
                            {{-- Law Firm Links --}}
                             <x-nav-link href="{{ route('law-firm.dashboard') }}" :active="request()->routeIs('law-firm.dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            @if(!auth()->user()->lawFirmProfile)
                                <x-nav-link href="{{ route('profile.complete') }}" :active="request()->routeIs('profile.complete')">
                                    {{ __('Complete Profile') }}
                                </x-nav-link>
                            @elseif(auth()->user()->status === 'approved')
                                {{-- Remove "Find Legal Help" dropdown for law firms completely --}}
                                <x-nav-link href="{{ route('law-firm.lawyers') }}" :active="request()->routeIs('law-firm.lawyers')">
                                    {{ __('Manage Lawyers') }}
                                </x-nav-link>
                                <x-nav-link href="{{ route('law-firm.cases') }}" :active="request()->routeIs('law-firm.cases')">
                                    {{ __('Manage My Cases') }}
                                </x-nav-link>
                                <x-nav-link href="{{ route('law-firm.consultations') }}" :active="request()->routeIs('law-firm.consultations')" class="relative">
                                    {{ __('Manage My Consultations') }}
                                </x-nav-link>
                                <x-nav-link href="{{ route('law-firm.invoices') }}" :active="request()->routeIs('law-firm.invoices')">
                                    {{ __('Invoices') }}
                                </x-nav-link>
                                <x-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">
                                    {{ __('Messages') }}
                                </x-nav-link>
                            @else
                                {{-- Add Lawyer disabled link --}}
                                <div class="group relative inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-400 cursor-not-allowed">
                                        {{ __('Add Lawyer') }}
                                    <div class="opacity-0 w-48 bg-black text-white text-xs rounded py-1 px-2 absolute z-10 bottom-0 left-1/2 transform -translate-x-1/2 translate-y-full mb-1 group-hover:opacity-100 transition-opacity duration-300">
                                        Kindly wait for your account approval to use this feature.
                                    </div>
                                </div>
                            @endif
                         @else {{-- Default Dashboard for other roles if any --}}
                             <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                                 {{ __('Dashboard') }}
                             </x-nav-link>
                         @endif
                    @else
                        {{-- Find Legal Help Dropdown for Guests --}}
                        {{-- Links for GUESTS --}}
                         {{-- Find Legal Help Dropdown for Guests (Using navbarrr) --}}
                        <div class="relative inline-flex items-center px-1 pt-1">
                             <x-navbarrr align="left" width="64" contentClasses="p-4 bg-white">
                                 <x-slot name="trigger">
                                     <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                         <div>{{ __('Find Legal Help') }}</div>
                                         <div class="ml-1">
                                             <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                 <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                             </svg>
                                         </div>
                                     </button>
                                 </x-slot>
                                 <x-slot name="content">
                                    {{-- Updated dropdown content for guests with new implementation --}}
                                     <div>
                                         <h3 class="text-base font-medium text-gray-900 mb-2">Find Legal Help</h3>
                                         <p class="text-sm text-gray-600 mb-4">Find experienced legal professionals near you.</p>
                                         
                                         <div class="space-y-2 mb-3">
                                             <div class="flex items-center">
                                                 <svg class="h-5 w-5 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                     <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                 </svg>
                                                 <a href="{{ route('client.nearby-lawyers') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                                     Search by Location
                                                 </a>
                                             </div>
                                             
                                             <div class="flex items-center">
                                                 <svg class="h-5 w-5 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                     <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                                 </svg>
                                                 <a href="{{ route('home') }}#services" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                                     Browse by Service
                                                 </a>
                                             </div>
                                         </div>
                                         
                                         <a href="{{ route('client.nearby-lawyers') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 text-center">
                                             View All Lawyers
                                         </a>
                                         <p class="text-xs text-gray-500 mt-3">Login or register to manage cases and message lawyers directly.</p>
                                     </div>
                                 </x-slot>
                             </x-navbarrr>
                         </div>
                    @endauth
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:ml-auto">
                @guest
                    <div class="flex items-center space-x-6">
                        <x-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')" class="font-medium text-gray-500 hover:text-gray-700">
                            {{ __('Log in') }}
                        </x-nav-link>
                        @if (Route::has('register'))
                            <x-nav-link href="{{ route('register') }}" :active="request()->routeIs('register')" class="font-medium text-gray-500 hover:text-gray-700">
                                {{ __('Register') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endguest
                
                @auth
                    {{-- Search Bar for Clients --}}
                    @if(auth()->user()->role->name === 'client')
                        <div class="w-64 mr-4">
                            @livewire('navbar-search')
                        </div>
                        {{-- Notification Dropdown for Clients --}}
                        @livewire('components.notification-dropdown')
                    @elseif(auth()->user()->role->name === 'lawyer')
                        {{-- Notification Dropdown for Lawyers --}}
                        @livewire('components.notification-dropdown')
                    @endif
                
                    {{-- Teams Dropdown --}}
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        {{-- Teams dropdown code... --}}
                    @endif

                    {{-- Settings Dropdown --}}
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                    </button>
                                @else
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                            @php 
                                                $user = Auth::user();
                                                $displayName = $user->name; // Default to user's name
                                                if ($user->isLawFirm() && $user->lawFirmProfile) {
                                                    $displayName = $user->lawFirmProfile->firm_name;
                                                } elseif ($user->isLawyer()) {
                                                    if ($user->lawFirmLawyer && $user->lawFirmLawyer->first_name && $user->lawFirmLawyer->last_name) {
                                                        $displayName = $user->lawFirmLawyer->first_name . ' ' . $user->lawFirmLawyer->last_name;
                                                    } elseif ($user->lawyerProfile && $user->lawyerProfile->first_name && $user->lawyerProfile->last_name) {
                                                        $displayName = $user->lawyerProfile->first_name . ' ' . $user->lawyerProfile->last_name;
                                                    }
                                                }
                                            @endphp
                                            {{ $displayName }}
                                            <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                        </button>
                                    </span>
                                @endif
                            </x-slot>

                            <x-slot name="content">
                                <!-- Account Management -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Manage Account') }}
                                </div>

                                <x-dropdown-link href="{{ auth()->user()->role->name === 'law_firm' ? route('law-firm.optimize-profile') : route('profile.show') }}">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @if(auth()->user()->isSuperAdmin())
                                    <x-dropdown-link href="{{ route('super-admin.dashboard') }}">
                                        {{ __('Super Admin Dashboard') }}
                                    </x-dropdown-link>
                                @endif

                                @if(auth()->user()->role->name === 'law_firm' || (auth()->user()->role->name === 'lawyer' && !auth()->user()->belongsToLawFirm()))
                                    <x-dropdown-link href="{{ route('account.subscription') }}">
                                        {{ __('Manage Subscription') }}
                                    </x-dropdown-link>
                                @elseif(auth()->user()->role->name === 'lawyer' && auth()->user()->belongsToLawFirm() && auth()->user()->firm)
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Your law firm\'s subscription applies to you') }}
                                    </div>
                                @endif

                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                        {{ __('API Tokens') }}
                                    </x-dropdown-link>
                                @endif

                                <div class="border-t border-gray-200"></div>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}" x-data>
                                    @csrf

                                    <x-dropdown-link href="{{ route('logout') }}"
                                                 @click.prevent="$root.submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth
            {{-- Responsive Links for AUTHENTICATED users --}}
        <div class="pt-2 pb-3 space-y-1">
                @if(auth()->user()->role->name === 'lawyer')
                    <x-responsive-nav-link href="{{ route('lawyer.welcome') }}" :active="request()->routeIs('lawyer.welcome')">{{ __('Home') }}</x-responsive-nav-link>
                    {{-- Only show Optimize Profile link when not on cases or consultations pages --}}
                    @if(!request()->routeIs('lawyer.cases') && !request()->routeIs('lawyer.consultations'))
                        <x-responsive-nav-link href="{{ route('profile.optimize') }}" :active="request()->routeIs('profile.optimize')">{{ __('Optimize Profile') }}</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link href="{{ route('lawyer.cases') }}" :active="request()->routeIs('lawyer.cases')">{{ __('Manage Cases') }}</x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('lawyer.consultations') }}" :active="request()->routeIs('lawyer.consultations')" class="relative">
                        {{ __('Consultations') }}
                        <span id="lawyer-notification-indicator" class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500 hidden"></span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">{{ __('Messages') }}</x-responsive-nav-link>
                    {{-- Mobile Notifications for Lawyers --}}
                    <div class="px-4 py-2 flex items-center justify-between">
                        <span class="font-medium text-base text-gray-800">Notifications</span>
                        @livewire('components.notification-dropdown')
                    </div>
                @elseif(auth()->user()->role->name === 'client')
                    {{-- Mobile Search Bar for Clients --}}
                    <div class="px-4 py-2"> @livewire('navbar-search') </div>
                    {{-- Mobile Notifications for Clients --}}
                    <div class="px-4 py-2 flex items-center justify-between">
                        <span class="font-medium text-base text-gray-800">Notifications</span>
                        @livewire('components.notification-dropdown')
                    </div>
                    <x-responsive-nav-link href="{{ route('client.welcome') }}" :active="request()->routeIs('client.welcome')">{{ __('Home') }}</x-responsive-nav-link>
                    {{-- Mobile Find Legal Help Dropdowns --}}
                    <div class="px-4 py-2">
                        <div class="font-medium text-base text-gray-800 mb-1">Find Legal Help</div>
                        <div class="relative ml-1 mt-1">
                            <x-navbarrr align="left" width="full" contentClasses="p-2 bg-white">
                                <x-slot name="trigger">
                                    <button class="flex items-center w-full py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md">
                                <span>Browse By Services</span>
                                        <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="space-y-1 p-2">
                                        @php $services = \App\Models\LegalService::active()->orderBy('name')->get(); @endphp
                                @foreach($services->take(8) as $service)
                                            <a href="{{ route('client.nearby-lawyers', ['selectedService' => $service->id]) }}" class="block py-1 text-sm text-gray-600 hover:text-gray-900">{{ $service->name }}</a>
                                @endforeach
                                        <a href="{{ route('client.nearby-lawyers') }}" class="block py-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">View All Services →</a>
                            </div>
                                </x-slot>
                            </x-navbarrr>
                        </div>
                         <div class="relative ml-1 mt-1">
                            <x-navbarrr align="left" width="full" contentClasses="p-2 bg-white">
                                <x-slot name="trigger">
                                    <button class="flex items-center w-full py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md">
                                <span>Browse By City</span>
                                        <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                            </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="space-y-1 p-2">
                                        @php $lawyerCities = \App\Models\LawyerProfile::distinct()->whereNotNull('city')->whereHas('user', fn($q) => $q->where('status', 'approved'))->pluck('city')->toArray(); $lawFirmCities = \App\Models\LawFirmProfile::distinct()->whereNotNull('city')->whereHas('user', fn($q) => $q->where('status', 'approved'))->pluck('city')->toArray(); $allCities = array_unique(array_merge($lawyerCities, $lawFirmCities)); sort($allCities); @endphp
                                @foreach(array_slice($allCities, 0, 8) as $city)
                                            <a href="{{ route('client.nearby-lawyers', ['selectedCity' => $city]) }}" class="block py-1 text-sm text-gray-600 hover:text-gray-900">{{ $city }}</a>
                                @endforeach
                                        <a href="{{ route('client.nearby-lawyers') }}" class="block py-1 text-sm font-medium text-indigo-600 hover:text-indigo-700">View All Cities →</a>
                            </div>
                                </x-slot>
                            </x-navbarrr>
                        </div>
                    </div>
                    <x-responsive-nav-link href="{{ route('client.cases') }}" :active="request()->routeIs('client.cases')">{{ __('Manage My Cases') }}</x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">{{ __('Messages') }}</x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('client.consultations') }}" :active="request()->routeIs('client.consultations')">{{ __('My Consultations') }}</x-responsive-nav-link>
                    <x-responsive-nav-link href="{{ route('client.invoices') }}" :active="request()->routeIs('client.invoices')">{{ __('My Invoices') }}</x-responsive-nav-link>
                 @elseif(auth()->user()->role->name === 'law_firm')
                    {{-- Law Firm Responsive Links --}}
                     <x-responsive-nav-link href="{{ route('law-firm.dashboard') }}" :active="request()->routeIs('law-firm.dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                    @if(!auth()->user()->lawFirmProfile)
                        <x-responsive-nav-link href="{{ route('profile.complete') }}" :active="request()->routeIs('profile.complete')">{{ __('Complete Profile') }}</x-responsive-nav-link>
                    @elseif(auth()->user()->status === 'approved')
                        {{-- Remove "Find Legal Help" dropdown for law firms completely --}}
                        <x-responsive-nav-link href="{{ route('law-firm.lawyers') }}" :active="request()->routeIs('law-firm.lawyers')">{{ __('Manage Lawyers') }}</x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('law-firm.cases') }}" :active="request()->routeIs('law-firm.cases')">{{ __('Manage My Cases') }}</x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('law-firm.consultations') }}" :active="request()->routeIs('law-firm.consultations')">{{ __('Manage My Consultations') }}</x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('law-firm.invoices') }}" :active="request()->routeIs('law-firm.invoices')">{{ __('Invoices') }}</x-responsive-nav-link>
                        <x-responsive-nav-link href="{{ route('messages') }}" :active="request()->routeIs('messages')">{{ __('Messages') }}</x-responsive-nav-link>
                    @else
                        <div class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-400">{{ __('Add Lawyer (Pending Approval)') }}</div>
                    @endif
                @else {{-- Default Dashboard for other roles --}}
                    <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                @endif
        </div>

            {{-- Responsive Settings Options --}}
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-gray-800">
                        @php 
                            $user = Auth::user();
                            $displayName = $user->name; // Default to user's name
                            if ($user->isLawFirm() && $user->lawFirmProfile) {
                                $displayName = $user->lawFirmProfile->firm_name;
                            } elseif ($user->isLawyer()) {
                                if ($user->lawFirmLawyer && $user->lawFirmLawyer->first_name && $user->lawFirmLawyer->last_name) {
                                    $displayName = $user->lawFirmLawyer->first_name . ' ' . $user->lawFirmLawyer->last_name;
                                } elseif ($user->lawyerProfile && $user->lawyerProfile->first_name && $user->lawyerProfile->last_name) {
                                    $displayName = $user->lawyerProfile->first_name . ' ' . $user->lawyerProfile->last_name;
                                }
                            }
                        @endphp
                        {{ $displayName }}
                    </div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                    {{-- Account Management Links --}}
                    <div class="block px-4 py-2 text-sm">
                         <span class="text-xs font-semibold">Status:</span>
                          @if(auth()->user()->status === 'approved') <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                          @elseif(auth()->user()->status === 'pending') <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                          @elseif(auth()->user()->status === 'rejected') <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                          @elseif(auth()->user()->status === 'deactivated') <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800">Deactivated</span> @endif
                    </div>
                    
                    <x-responsive-nav-link href="{{ auth()->user()->role->name === 'law_firm' ? route('law-firm.optimize-profile') : route('profile.show') }}">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    
                    @if(auth()->user()->isSuperAdmin())
                        <x-responsive-nav-link href="{{ route('super-admin.dashboard') }}">
                            {{ __('Super Admin Dashboard') }}
                        </x-responsive-nav-link>
                    @endif
                    
                    @if(auth()->user()->role->name === 'law_firm' || (auth()->user()->role->name === 'lawyer' && !auth()->user()->belongsToLawFirm()))
                        <x-responsive-nav-link href="{{ route('account.subscription') }}">
                            {{ __('Manage Subscription') }}
                        </x-responsive-nav-link>
                    @elseif(auth()->user()->role->name === 'lawyer' && auth()->user()->belongsToLawFirm() && auth()->user()->firm)
                        <div class="block px-4 py-2 text-xs text-gray-500">
                            {{ __('Your law firm\'s subscription applies to you') }}
                        </div>
                    @endif
                    
                    @if(auth()->user()->role->name === 'lawyer') <x-responsive-nav-link href="{{ route('profile.optimize') }}" :active="request()->routeIs('profile.optimize')">{{ __('Optimize Profile') }}</x-responsive-nav-link>
                    @elseif(auth()->user()->role->name === 'law_firm') <x-responsive-nav-link href="{{ route('law-firm.optimize-profile') }}" :active="request()->routeIs('law-firm.optimize-profile')">{{ __('Optimize Profile') }}</x-responsive-nav-link> @endif
                    {{-- Authentication --}}
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                        <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
                    {{-- Team Management --}}
                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures()) {{-- Team management links... --}} @endif
                </div>
            </div>
        @else
             {{-- Responsive Links for GUESTS --}}
            <div class="pt-2 pb-3 space-y-1">
                 {{-- Find Legal Help Dropdown for Guests --}}
                 <div class="px-4 py-2">
                     <div class="font-medium text-base text-gray-800 mb-1">Find Legal Help</div>
                     <div class="relative ml-1 mt-1">
                         <x-navbarrr align="left" width="full" contentClasses="p-2 bg-white">
                             <x-slot name="trigger">
                                 <button class="flex items-center w-full py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md">
                                     <span>Explore Options</span>
                                     <svg class="ml-auto w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                 </button>
                             </x-slot>
                             <x-slot name="content">
                                <div class="space-y-2 p-2">
                                     <a href="{{ route('client.nearby-lawyers') }}" class="flex items-center py-1 text-sm text-gray-600 hover:text-gray-900">
                                         <svg class="h-4 w-4 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                             <path d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" />
                                         </svg>
                                         Search by Location
                                     </a>
                                     <a href="{{ route('home') }}#services" class="flex items-center py-1 text-sm text-gray-600 hover:text-gray-900">
                                         <svg class="h-4 w-4 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                             <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                         </svg>
                                         Browse by Service
                                     </a>
                                     <a href="{{ route('client.nearby-lawyers') }}" class="flex items-center py-1 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                         <svg class="h-4 w-4 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                             <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                         </svg>
                                         View All Lawyers
                                     </a>
                                 </div>
                             </x-slot>
                         </x-navbarrr>
                    </div>
                        </div>
                 <x-responsive-nav-link href="{{ route('login') }}" :active="request()->routeIs('login')">{{ __('Log in') }}</x-responsive-nav-link>
                @if (Route::has('register'))
                    <x-responsive-nav-link href="{{ route('register') }}" :active="request()->routeIs('register')">{{ __('Register') }}</x-responsive-nav-link>
                @endif
            </div>
        @endauth
    </div>
</nav>
