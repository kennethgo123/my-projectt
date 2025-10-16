<!-- Law Firm Reviews -->
<template x-if="lawyerType === 'lawFirm'">
    <div class="space-y-6">
        <div x-init="console.log('Law firm reviews:', lawyerDetail.user.received_law_firm_ratings)"></div>
        <template x-if="lawyerDetail.user && lawyerDetail.user.received_law_firm_ratings && lawyerDetail.user.received_law_firm_ratings.length > 0">
            <div>
                <template x-for="(review, index) in lawyerDetail.user.received_law_firm_ratings.filter(r => r.is_visible)" :key="index">
                    <div class="border-b border-gray-200 pb-5 mb-5 last:border-0 last:mb-0 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center">
                                    <template x-for="i in 5">
                                        <svg :class="{ 'text-yellow-400': i <= review.rating, 'text-gray-300': i > review.rating }" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </template>
                                </div>
                                <h5 class="font-medium text-gray-800 mt-2">Client Review</h5>
                            </div>
                            <span class="text-sm text-gray-500" x-text="formatDate(review.rated_at)"></span>
                        </div>
                        <div class="mt-3">
                            <p class="text-gray-700" x-text="review.feedback || 'No additional comments.'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </template>
        
        <template x-if="!lawyerDetail.user || !lawyerDetail.user.received_law_firm_ratings || lawyerDetail.user.received_law_firm_ratings.length === 0 || !lawyerDetail.user.received_law_firm_ratings.some(r => r.is_visible)">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 font-open-sans">No reviews yet. Be the first to rate.</p>
            </div>
        </template>
    </div>
</template>

<!-- Lawyer Reviews -->
<template x-if="lawyerType !== 'lawFirm'">
    <div class="space-y-6">
        <div x-init="console.log('Lawyer reviews:', lawyerDetail.user.received_ratings)"></div>
        <template x-if="lawyerDetail.user && lawyerDetail.user.received_ratings && lawyerDetail.user.received_ratings.length > 0">
            <div>
                <template x-for="(review, index) in lawyerDetail.user.received_ratings.filter(r => r.is_visible)" :key="index">
                    <div class="border-b border-gray-200 pb-5 mb-5 last:border-0 last:mb-0 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center">
                                    <template x-for="i in 5">
                                        <svg :class="{ 'text-yellow-400': i <= review.rating, 'text-gray-300': i > review.rating }" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </template>
                                </div>
                                <h5 class="font-medium text-gray-800 mt-2">Client Review</h5>
                            </div>
                            <span class="text-sm text-gray-500" x-text="formatDate(review.rated_at)"></span>
                        </div>
                        <div class="mt-3">
                            <p class="text-gray-700" x-text="review.feedback || 'No additional comments.'"></p>
                        </div>
                    </div>
                </template>
            </div>
        </template>
        
        <template x-if="!lawyerDetail.user || !lawyerDetail.user.received_ratings || lawyerDetail.user.received_ratings.length === 0 || !lawyerDetail.user.received_ratings.some(r => r.is_visible)">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-gray-600 font-open-sans">No reviews yet. Be the first to rate.</p>
            </div>
        </template>
    </div>
</template> 