<!-- Client Navigation Bar Component -->
<div class="hidden sm:flex sm:items-center space-x-4">
    <!-- LexCav Logo -->
    <a href="{{ route('client.welcome') }}" class="flex items-center">
        <span class="text-2xl font-bold text-green-600">Lex<span class="text-gray-800">cav</span></span>
    </a>

    <!-- Navigation Links -->
    <a href="{{ route('client.welcome') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.welcome') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
        Home
    </a>
    
    <!-- Find Legal Help Dropdown -->
    <x-find-legal-help-dropdown />
    
    <a href="{{ route('client.cases') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.cases*') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
        Manage My Cases
    </a>
    
    <a href="{{ route('client.consultations') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.consultations') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
        My Consultations
    </a>
    
    <a href="{{ route('messages') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('messages*') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
        Messages
    </a>
    
    <a href="{{ route('client.invoices') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.invoices') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
        My Invoices
    </a>
</div>

<!-- Mobile Navigation (Responsive) -->
<div class="sm:hidden">
    <div class="pt-2 pb-4 space-y-1">
        <a href="{{ route('client.welcome') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('client.welcome') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
            Home
        </a>
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex justify-between w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800">
                Find Legal Help
                <svg class="ml-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" class="px-2 py-2 bg-white rounded-md shadow-lg">
                <a href="{{ route('client.nearby-lawyers') }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                    Find Lawyers Near Me
                </a>
                <a href="{{ route('client.nearby-lawyers') }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                    Browse by Service
                </a>
            </div>
        </div>
        <a href="{{ route('client.cases') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('client.cases*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
            Manage My Cases
        </a>
        <a href="{{ route('client.consultations') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('client.consultations') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
            My Consultations
        </a>
        <a href="{{ route('messages') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('messages*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
            Messages
        </a>
        <a href="{{ route('client.invoices') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('client.invoices') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
            My Invoices
        </a>
    </div>
</div> 