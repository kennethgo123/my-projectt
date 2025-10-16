<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    {{-- Logo links to lawyer dashboard for lawyers --}}
                    <a href="{{ route('lawyer.welcome') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Lawyer Links Only - Home link always visible --}}
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
                    <x-nav-link href="{{ route('lawyer.invoices') }}" :active="request()->routeIs('lawyer.invoices')">
                        {{ __('Invoices') }}
                    </x-nav-link>
                    {{-- Intentionally no "Find Legal Help" or "Services" links here --}}
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- Notification Dropdown for Lawyers --}}
                @livewire('components.notification-dropdown')
                
                {{-- Settings Dropdown --}}
                <div class="ms-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->lawyerProfile->first_name ?? '' }} {{ Auth::user()->lawyerProfile->last_name ?? '' }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                        {{ Auth::user()->lawyerProfile->first_name ?? '' }} {{ Auth::user()->lawyerProfile->last_name ?? '' }}
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

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if(!Auth::user()->belongsToLawFirm())
                                <x-dropdown-link href="{{ route('account.subscription') }}">
                                    {{ __('Manage Subscription') }}
                                </x-dropdown-link>
                            @else
                                @if(Auth::user()->firm)
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Your law firm\'s subscription applies to you') }}
                                    </div>
                                @endif
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
        <div class="pt-2 pb-3 space-y-1">
            {{-- Lawyer-specific links for mobile - Home link always visible --}}
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
            <x-responsive-nav-link href="{{ route('lawyer.invoices') }}" :active="request()->routeIs('lawyer.invoices')">{{ __('Invoices') }}</x-responsive-nav-link>
            {{-- Mobile Notifications for Lawyers --}}
            <div class="px-4 py-2 flex items-center justify-between">
                <span class="font-medium text-base text-gray-800">Notifications</span>
                @livewire('components.notification-dropdown')
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->lawyerProfile->first_name ?? '' }} {{ Auth::user()->lawyerProfile->last_name ?? '' }}" />
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-gray-800">
                        @if(Auth::user()->lawyerProfile)
                            {{ Auth::user()->lawyerProfile->first_name ?? '' }} {{ Auth::user()->lawyerProfile->last_name ?? '' }}
                        @else
                            {{ Auth::user()->name }}
                        @endif
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
                <x-responsive-nav-link href="{{ route('profile.optimize') }}" :active="request()->routeIs('profile.optimize')">{{ __('Optimize Profile') }}</x-responsive-nav-link>
                
                @if(!Auth::user()->belongsToLawFirm())
                    <x-responsive-nav-link href="{{ route('account.subscription') }}" :active="request()->routeIs('account.subscription')">{{ __('Manage Subscription') }}</x-responsive-nav-link>
                @else
                    @if(Auth::user()->firm)
                        <div class="block px-4 py-2 text-xs text-gray-500">
                            {{ __('Your law firm\'s subscription applies to you') }}
                        </div>
                    @endif
                @endif
                
                {{-- Authentication --}}
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
                {{-- Team Management --}}
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures()) {{-- Team management links... --}} @endif
            </div>
        </div>
    </div>
</nav> 