<div x-data class="bg-gray-50 min-h-screen">
    @include('livewire.lawyers.components.subscription-badges')
    
    <!-- Alpine.js functions for reviews -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('lawyerReviews', () => ({
                show: false,
                lawyerDetail: null,
                lawyerType: null,
                
                calculateAverageRating(ratings) {
                    if (!ratings || ratings.length === 0) return 0;
                    
                    // Only include visible ratings
                    const visibleRatings = ratings.filter(rating => rating.is_visible);
                    if (visibleRatings.length === 0) return 0;
                    
                    const sum = visibleRatings.reduce((total, rating) => total + rating.rating, 0);
                    return (sum / visibleRatings.length).toFixed(1);
                },
                
                getRatingCount(ratings) {
                    if (!ratings) return 0;
                    return ratings.filter(rating => rating.is_visible).length;
                },
                
                formatDate(dateString) {
                    if (!dateString) return '';
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return new Date(dateString).toLocaleDateString(undefined, options);
                },
                
                getStarArray(rating) {
                    console.log('getStarArray called with rating:', rating);
                    
                    // Ensure rating is a number
                    const numericRating = parseFloat(rating) || 0;
                    const fullStars = Math.floor(numericRating);
                    const halfStar = numericRating - fullStars >= 0.5;
                    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
                    
                    console.log('Star calculation:', { fullStars, halfStar, emptyStars });
                    
                    return {
                        full: fullStars,
                        half: halfStar ? 1 : 0,
                        empty: emptyStars
                    };
                }
            }));
        });
    </script>

    <div class="py-12">
        <div class="container mx-auto px-4 max-w-7xl">
            <h1 class="text-4xl font-bold mb-8 font-raleway text-emerald-800 text-center">
                <span class="inline-block border-b-4 border-emerald-500 pb-2">FIND THE RIGHT LAW SERVICE PROVIDER</span>
            </h1>
            
            <!-- Quick Search Bar - Only visible on this page -->
            <div class="mb-8 max-w-3xl mx-auto lg:hidden">
                <form wire:submit.prevent="$refresh" class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="text-emerald-500 w-5 h-5">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input 
                        wire:model.live="search" 
                        type="search" 
                        class="w-full py-4 text-sm rounded-full pl-12 pr-4 border-2 border-emerald-300 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 font-open-sans text-gray-900" 
                        placeholder="Search lawyers by name..." 
                        autocomplete="off">
                </form>
            </div>
            
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar with filters -->
                @include('livewire.lawyers.components.lawyer-filters')
                
                <!-- Main Content Area -->
                <div class="w-full md:w-3/4">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                        @if ($results->isEmpty())
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No exact matches found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach ($results as $result)
                                    @if ($result instanceof \App\Models\LawFirmProfile)
                                        @livewire('lawyers.components.lawfirm-card', ['lawFirm' => $result], key('lawfirm-' . $result->id . '-' . microtime()))
                                    @elseif ($result instanceof \App\Models\LawyerProfile || $result instanceof \App\Models\LawFirmLawyer)
                                        @livewire('lawyers.components.lawyer-card', ['lawyer' => $result], key('lawyer-' . $result->id . '-' . microtime()))
                                    @endif
                                @endforeach
                            </div>
                            
                            <!-- Pagination Links -->
                            <div class="mt-8">
                                {{ $results->onEachSide(1)->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lawyer Detail Modal -->
    @include('livewire.lawyers.components.lawyer-modal')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap');
        
        .font-raleway {
            font-family: 'Raleway', sans-serif;
        }
        
        .font-open-sans {
            font-family: 'Open Sans', sans-serif;
        }
        
        .border-3 {
            border-width: 3px;
        }
        
        /* Add smooth scrolling to the modal */
        .overflow-y-auto {
            scroll-behavior: smooth;
        }
    </style>
    
    <!-- Debug script -->
    <script src="{{ asset('debug.js') }}"></script>
</div> 