<div class="flex items-center">
    <div class="flex-shrink-0 h-10 w-10">
        @if($consultation->lawyerUser && $consultation->lawyerUser->lawFirmLawyer && $consultation->lawyerUser->lawFirmLawyer->photo_path)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $consultation->lawyerUser->lawFirmLawyer->photo_path) }}" alt="{{ $consultation->lawyerUser->name }}">
        @elseif($consultation->lawyerUser && $consultation->lawyerUser->lawyerProfile && $consultation->lawyerUser->lawyerProfile->photo_path)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $consultation->lawyerUser->lawyerProfile->photo_path) }}" alt="{{ $consultation->lawyerUser->name }}">
        @elseif($consultation->lawyer && $consultation->lawyer->lawFirmLawyer && $consultation->lawyer->lawFirmLawyer->photo_path)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $consultation->lawyer->lawFirmLawyer->photo_path) }}" alt="{{ $consultation->lawyer->name }}">
        @elseif($consultation->lawyer && $consultation->lawyer->lawyerProfile && $consultation->lawyer->lawyerProfile->photo_path)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $consultation->lawyer->lawyerProfile->photo_path) }}" alt="{{ $consultation->lawyer->name }}">
        @elseif($consultation->lawyerUser)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ $consultation->lawyerUser->profile_photo_url }}" alt="{{ $consultation->lawyerUser->name }}">
        @elseif($consultation->lawyer)
            <img class="h-10 w-10 rounded-full object-cover" src="{{ $consultation->lawyer->profile_photo_url }}" alt="{{ $consultation->lawyer->name }}">
        @else
            <div class="h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                </svg>
            </div>
        @endif
    </div>
    <div class="ml-4">
        <h3 class="text-sm font-medium text-gray-900">
            @if($consultation->lawyer)
                {{ $consultation->lawyer->name }}
            @elseif($consultation->lawyerUser)
                {{ $consultation->lawyerUser->lawFirmLawyer ? 
                    $consultation->lawyerUser->lawFirmLawyer->first_name . ' ' . substr($consultation->lawyerUser->lawFirmLawyer->last_name, 0, 1) . '.' : 
                    ($consultation->lawyerUser->lawyerProfile ? 
                        $consultation->lawyerUser->lawyerProfile->first_name . ' ' . substr($consultation->lawyerUser->lawyerProfile->last_name, 0, 1) . '.' : 
                        $consultation->lawyerUser->name) }}
            @else
                Unknown Lawyer
            @endif
        </h3>
    </div>
</div> 