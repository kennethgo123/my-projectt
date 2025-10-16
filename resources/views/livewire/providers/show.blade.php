<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <!-- Provider Header -->
            <div class="border-b border-gray-200 pb-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        @if($provider->role->name === 'lawyer')
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $provider->lawyerProfile->first_name }}
                                {{ $provider->lawyerProfile->middle_name }}
                                {{ $provider->lawyerProfile->last_name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">{{ $provider->lawyerProfile->city }}</p>
                        @else
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $provider->lawFirmProfile->firm_name }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">{{ $provider->lawFirmProfile->city }}</p>
                        @endif
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= ($provider->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                            <span class="ml-2 text-sm text-gray-500">
                                {{ number_format($provider->rating ?? 0, 1) }} 
                                ({{ $provider->reviews_count ?? 0 }} reviews)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column - Contact Info -->
                <div class="md:col-span-1">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $provider->email }}</dd>
                        </div>
                        @if($provider->role->name === 'lawyer')
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $provider->lawyerProfile->contact_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $provider->lawyerProfile->address }}</dd>
                            </div>
                        @else
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $provider->lawFirmProfile->contact_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $provider->lawFirmProfile->address }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Middle Column - Services -->
                <div class="md:col-span-2">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Services Offered</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($provider->services as $service)
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $service->name }}
                            </span>
                        @endforeach
                    </div>

                    @if($provider->role->name === 'lawyer' && $provider->lawyerProfile->bio)
                        <div class="mt-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">About</h2>
                            <p class="text-sm text-gray-600">{{ $provider->lawyerProfile->bio }}</p>
                        </div>
                    @endif

                    @if($provider->role->name === 'law_firm' && $provider->lawFirmProfile->description)
                        <div class="mt-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">About the Firm</h2>
                            <p class="text-sm text-gray-600">{{ $provider->lawFirmProfile->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @auth
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('messages') }}" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send Message
                    </a>
                </div>
            @else
                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ route('login') }}" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Login to Contact
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div> 