<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Profile Header -->
                    <div class="md:col-span-2">
                        <div class="flex items-start space-x-4">
                            <!-- Profile Photo -->
                            <div class="flex-shrink-0">
                                @if($lawyer->photo_path)
                                    <img class="h-24 w-24 rounded-full object-cover border-2 border-indigo-500" 
                                        src="{{ Storage::url($lawyer->photo_path) }}" 
                                        alt="{{ $lawyer->full_name }}">
                                @elseif($lawyer->user && $lawyer->user->profile_photo_path)
                                    <img class="h-24 w-24 rounded-full object-cover border-2 border-indigo-500" 
                                        src="{{ Storage::url($lawyer->user->profile_photo_path) }}" 
                                        alt="{{ $lawyer->full_name }}">
                                @else
                                    <div class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 border-2 border-indigo-500">
                                        <svg class="h-12 w-12" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ $lawyer->full_name }}</h1>
                                <p class="mt-2 text-gray-600">{{ $lawyer->city }}</p>
                                
                                @if($lawyer->offers_online_consultation || $lawyer->offers_inhouse_consultation)
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @if($lawyer->offers_online_consultation)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Online Consultation
                                            </span>
                                        @endif
                                        @if($lawyer->offers_inhouse_consultation)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                In-House Consultation
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if(session()->has('message'))
                            <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
                                {{ session('message') }}
                            </div>
                        @endif
                    </div>

                    <!-- Contact Button -->
                    <div class="flex justify-end items-start">
                        @auth
                            @if(auth()->user()->isClient())
                                <button wire:click="startChat" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                    </svg>
                                    Message
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- Message Form -->
                @if(session()->has('error'))
                    <div class="mt-6 p-4 bg-red-50 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Profile Content -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="md:col-span-2 space-y-8">
                        @if($bio->about)
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">About</h2>
                                <p class="mt-4 text-gray-600">{{ $bio->about }}</p>
                            </div>
                        @endif

                        @if($bio->experience)
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Experience</h2>
                                <div class="mt-4 text-gray-600">{!! nl2br(e($bio->experience)) !!}</div>
                            </div>
                        @endif

                        @if($bio->education)
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Education</h2>
                                <div class="mt-4 text-gray-600">{!! nl2br(e($bio->education)) !!}</div>
                            </div>
                        @endif

                        @if($bio->achievements)
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Achievements</h2>
                                <div class="mt-4 text-gray-600">{!! nl2br(e($bio->achievements)) !!}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-gray-900">Professional Fee Range</h3>
                            <p class="mt-2 text-gray-600">
                                ₱{{ number_format($lawyer->min_budget) }} - ₱{{ number_format($lawyer->max_budget) }}
                            </p>
                        </div>

                        @if($bio->specializations)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-900">Specializations</h3>
                                <div class="mt-2 text-gray-600">{!! nl2br(e($bio->specializations)) !!}</div>
                            </div>
                        @endif

                        @if($bio->languages)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-900">Languages</h3>
                                <div class="mt-2 text-gray-600">{!! nl2br(e($bio->languages)) !!}</div>
                            </div>
                        @endif

                        @if($bio->website || $bio->linkedin)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-gray-900">Connect</h3>
                                <div class="mt-2 space-y-2">
                                    @if($bio->website)
                                        <a href="{{ $bio->website }}" target="_blank" class="text-blue-600 hover:underline block">
                                            Website
                                        </a>
                                    @endif
                                    @if($bio->linkedin)
                                        <a href="{{ $bio->linkedin }}" target="_blank" class="text-blue-600 hover:underline block">
                                            LinkedIn
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 