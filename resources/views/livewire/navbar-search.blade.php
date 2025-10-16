<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <form action="{{ route('client.nearby-lawyers') }}" method="GET" class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
            <button type="submit" class="p-1 focus:outline-none focus:shadow-outline">
                <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="text-gray-500 w-4 h-4">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </span>
        <input 
            name="search" 
            type="search" 
            class="w-full py-2 text-sm text-gray-900 rounded-xl pl-11 pr-4 bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-300" 
            placeholder="Search lawyers..." 
            autocomplete="off"
            value="{{ request()->input('search', '') }}">
    </form>
</div>
