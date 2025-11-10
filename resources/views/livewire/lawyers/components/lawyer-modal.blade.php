<!-- Lawyer Detail Modal -->
<div 
    x-data="lawyerReviews()"
    x-on:show-lawyer-detail.window="
        console.log('Modal received event with data:', $event.detail);
        show = true; 
        lawyerDetail = $event.detail.lawyer; 
        lawyerType = $event.detail.type;
        
        // Log the parsed data and important properties
        console.log('Lawyer detail:', lawyerDetail);
        console.log('Lawyer type:', lawyerType);
        console.log('Has ratings?', lawyerDetail && lawyerDetail.user && 
            (lawyerType === 'lawFirm' 
                ? (lawyerDetail.user.received_law_firm_ratings && lawyerDetail.user.received_law_firm_ratings.length) 
                : (lawyerDetail.user.received_ratings && lawyerDetail.user.received_ratings.length)
            )
        );
        console.log('Has address?', lawyerDetail && lawyerDetail.office_address);
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
    
    <!-- Modal Panel - Sliding from right instead of left -->
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
                            <div class="flex items-center">
                                <h2 class="text-2xl font-bold font-raleway text-white" x-text="lawyerType === 'lawFirm' ? lawyerDetail.firm_name : `${lawyerDetail.first_name} ${lawyerDetail.last_name}`"></h2>
                                <template x-if="lawyerDetail.user && lawyerDetail.user.activeSubscription && lawyerDetail.user.activeSubscription.plan">
                                    <div class="ml-2">
                                        <template x-if="lawyerDetail.user.activeSubscription.plan.name === 'Max'">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gradient-to-r from-yellow-300 to-amber-500 text-white shadow-md border border-yellow-300">
                                                <svg class="h-3.5 w-3.5 mr-1 text-yellow-100" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <span class="font-bold">Featured Legal Professional</span>
                                            </span>
                                        </template>
                                        <template x-if="lawyerDetail.user.activeSubscription.plan.name === 'Pro'">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gradient-to-r from-gray-300 to-gray-500 text-white shadow-md border border-gray-300">
                                                <svg class="h-3.5 w-3.5 mr-1 text-gray-100" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                                </svg>
                                                <span class="font-bold">Certified Legal Professional</span>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
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
                            @include('livewire.lawyers.components.lawyer-modal-content')
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function lawyerReviews() {
        return {
            show: false,
            lawyerDetail: null,
            lawyerType: null,
            
            // Helper method to get the correct average rating
            getAverageRating() {
                if (!this.lawyerDetail || !this.lawyerDetail.user) return 0;
                
                if (this.lawyerType === 'lawFirm') {
                    return this.lawyerDetail.user.law_firm_average_rating || 0;
                } else {
                    return this.lawyerDetail.user.average_rating || 0;
                }
            },
            
            // Helper method to get the correct rating count
            getRatingCount() {
                if (!this.lawyerDetail || !this.lawyerDetail.user) return 0;
                
                if (this.lawyerType === 'lawFirm') {
                    return this.lawyerDetail.user.law_firm_rating_count || 0;
                } else {
                    return this.lawyerDetail.user.rating_count || 0;
                }
            }
        };
    }
</script> 