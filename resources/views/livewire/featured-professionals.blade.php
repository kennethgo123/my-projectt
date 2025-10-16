<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">Featured Legal Professionals</h2>
            <p class="mt-3 max-w-2xl mx-auto text-gray-500 sm:mt-4">
                These professionals are top-rated and highly recommended in their practice areas.
            </p>
        </div>
        
        @if($featuredSlots->count() > 0)
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($featuredSlots as $slot)
                    @php
                        $user = $slot->user;
                        $profile = null;
                        $profileType = 'lawyer';
                        
                        if ($user->isLawyer() && $user->lawyerProfile) {
                            $profile = $user->lawyerProfile;
                        } elseif ($user->isLawFirm() && $user->lawFirmProfile) {
                            $profile = $user->lawFirmProfile;
                            $profileType = 'law_firm';
                        } elseif ($user->lawFirmLawyer) {
                            $profile = $user->lawFirmLawyer;
                            $profileType = 'firm_lawyer';
                        }
                        
                        if (!$profile) {
                            continue;
                        }
                        
                        // Determine name and photo
                        $name = '';
                        $photoUrl = '';
                        
                        if ($profileType === 'lawyer') {
                            $name = $profile->first_name . ' ' . $profile->last_name;
                            $photoUrl = $profile->photo_path ? asset('storage/' . $profile->photo_path) : asset('img/default-avatar.png');
                        } elseif ($profileType === 'law_firm') {
                            $name = $profile->firm_name;
                            $photoUrl = $profile->logo_path ? asset('storage/' . $profile->logo_path) : asset('img/default-logo.png');
                        } elseif ($profileType === 'firm_lawyer') {
                            $name = $profile->first_name . ' ' . $profile->last_name;
                            $photoUrl = $profile->photo_path ? asset('storage/' . $profile->photo_path) : asset('img/default-avatar.png');
                        }
                        
                        // Calculate route
                        $route = $profileType === 'law_firm' 
                            ? route('law-firms.show', $profile->id)
                            : route('providers.show', $user->id);
                    @endphp
                    
                    <div class="flex flex-col bg-white rounded-lg shadow-lg overflow-hidden border-2 border-indigo-100 hover:border-indigo-300 transition-all">
                        <div class="relative flex-shrink-0 h-48 bg-indigo-100">
                            <img class="absolute inset-0 h-full w-full object-cover" src="{{ $photoUrl }}" alt="{{ $name }}">
                            
                            <!-- Premium badge -->
                            <div class="absolute top-0 right-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-3 py-1 rounded-bl text-xs font-bold flex items-center">
                                <svg class="h-4 w-4 text-yellow-300 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 15.654l-7.347 3.867 1.403-8.178L.111 7.217l8.186-1.19L10 0l1.703 6.027 8.186 1.19-3.945 3.846 1.403 8.178L10 15.654z" clip-rule="evenodd" />
                                </svg>
                                MAX TIER
                            </div>
                        </div>
                        
                        <div class="flex-1 p-6 flex flex-col justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ $profileType === 'law_firm' ? 'Law Firm' : 'Lawyer' }}
                                </p>
                                <a href="{{ $route }}" class="block mt-2">
                                    <p class="text-xl font-semibold text-gray-900">{{ $name }}</p>
                                    <p class="mt-3 text-base text-gray-500 line-clamp-3">
                                        @if($profileType === 'lawyer' || $profileType === 'firm_lawyer')
                                            {{ $profile->practice_description ?? 'Experienced legal professional ready to help with your case.' }}
                                        @else
                                            {{ $profile->about ?? 'Leading law firm providing comprehensive legal services.' }}
                                        @endif
                                    </p>
                                </a>
                            </div>
                            
                            <div class="mt-6 flex items-center">
                                <div class="flex items-center text-yellow-400">
                                    @php
                                        $rating = 0;
                                        $ratingCount = 0;
                                        
                                        if ($profileType === 'lawyer' || $profileType === 'firm_lawyer') {
                                            if ($user->receivedRatings && $user->receivedRatings->count() > 0) {
                                                $rating = $user->getAverageRatingAttribute();
                                                $ratingCount = $user->getRatingCountAttribute();
                                            }
                                        } elseif ($profileType === 'law_firm') {
                                            if ($user->receivedLawFirmRatings && $user->receivedLawFirmRatings->count() > 0) {
                                                $rating = $user->getLawFirmAverageRatingAttribute();
                                                $ratingCount = $user->getLawFirmRatingCountAttribute();
                                            }
                                        }
                                    @endphp
                                    
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $rating)
                                            <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                        @elseif($i - 0.5 <= $rating)
                                            <svg class="h-5 w-5 fill-current" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 fill-current text-gray-300" viewBox="0 0 20 20">
                                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                            </svg>
                                        @endif
                                    @endfor
                                    
                                    <span class="ml-2 text-gray-600 text-sm">
                                        {{ $rating > 0 ? number_format($rating, 1) . ' (' . $ratingCount . ' reviews)' : 'No reviews yet' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ $route }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full justify-center">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">No featured professionals at the moment.</p>
            </div>
        @endif
    </div>
</div>
