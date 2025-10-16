<?php

namespace App\Livewire\Lawyers;

use App\Models\LawyerProfile;
use App\Models\LawFirmProfile;
use App\Models\LegalService;
use App\Models\LawFirmLawyer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

class NearbyLawyers extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    protected $perPage = 5;

    // Add page property for Livewire pagination
    public $page = 1;

    #[Url(as: 'search', history: true, keep: false)]
    public $search = '';
    
    #[Url(history: true)]
    public $selectedService = '';
    
    #[Url(history: true)]
    public $minBudget = null;
    
    #[Url(history: true)]
    public $maxBudget = null;
    
    #[Url(history: true)]
    public $selectedCity = 'All Cities';
    
    #[Url(history: true)]
    public $onlineConsultation = false;
    
    #[Url(history: true)]
    public $inhouseConsultation = false;
    
    #[Url(history: true)]
    public $selectedLanguage = '';

    #[Url(history: true)]
    public $minRating = 0;

    protected function queryString()
    {
        return array_merge([
            'page' => ['except' => 1],
            'search' => ['except' => ''],
            'selectedService' => ['except' => ''],
            'minBudget' => ['except' => null],
            'maxBudget' => ['except' => null],
            'selectedCity' => ['except' => 'All Cities'],
            'onlineConsultation' => ['except' => false],
            'inhouseConsultation' => ['except' => false],
            'selectedLanguage' => ['except' => ''],
            'minRating' => ['except' => 0]
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function updatedSelectedService()
    {
        $this->resetPage();
    }

    public function updatedMinBudget()
    {
        $this->resetPage();
    }

    public function updatedMaxBudget()
    {
        $this->resetPage();
    }

    public function updatedSelectedCity()
    {
        $this->resetPage();
    }

    public function updatedOnlineConsultation()
    {
        $this->resetPage();
    }

    public function updatedInhouseConsultation()
    {
        $this->resetPage();
    }

    public function updatedSelectedLanguage()
    {
        $this->resetPage();
    }

    public function updatedMinRating()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Set default city to All Cities instead of client's city to show all lawyers by default
        $this->selectedCity = request()->query('selectedCity', 'All Cities');
        $this->selectedService = request()->query('selectedService', '');
        $this->minBudget = request()->query('minBudget');
        $this->maxBudget = request()->query('maxBudget');
        $this->onlineConsultation = (bool)request()->query('onlineConsultation', false);
        $this->inhouseConsultation = (bool)request()->query('inhouseConsultation', false);
        $this->selectedLanguage = request()->query('selectedLanguage', '');
        $this->minRating = (int)request()->query('minRating', 0);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedService', 'minBudget', 'maxBudget', 'onlineConsultation', 'inhouseConsultation', 'selectedLanguage', 'minRating']);
        $this->selectedCity = 'All Cities';
        $this->resetPage();
    }

    public function render()
    {
        $services = LegalService::active()->orderBy('name')->get();
        
        // Get all unique cities for the dropdown
        $lawyerCities = LawyerProfile::select('city')
            ->distinct()
            ->whereNotNull('city')
            ->whereHas('user', function($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            })
            ->pluck('city')
            ->toArray();
            
        $lawFirmCities = LawFirmProfile::select('city')
            ->distinct()
            ->whereNotNull('city')
            ->whereHas('user', function($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            })
            ->pluck('city')
            ->toArray();
            
        $lawFirmLawyerCities = LawFirmLawyer::select('city')
            ->distinct()
            ->whereNotNull('city')
            ->pluck('city')
            ->toArray();
        
        $allCities = array_unique(array_merge($lawyerCities, $lawFirmCities, $lawFirmLawyerCities));
        sort($allCities);
        
        // ----------------------
        // LAWYER PROFILES QUERY
        // ----------------------
        $lawyersQuery = LawyerProfile::query()
            ->with(['user.activeSubscription.plan', 'services', 'lawFirm'])
            ->whereHas('user', function($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            });
        
        // Apply city filter for lawyers
        if (!empty($this->selectedCity) && $this->selectedCity !== 'All Cities') {
            $lawyersQuery->where('city', $this->selectedCity);
        }
        
        // Apply search filter
        if (!empty($this->search)) {
            $lawyersQuery->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply service filter
        if (!empty($this->selectedService)) {
            $lawyersQuery->whereHas('services', function ($query) {
                $query->where('legal_services.id', $this->selectedService);
            });
        }
        
        // Apply budget filters
        if (!is_null($this->minBudget) && $this->minBudget > 0) {
            $lawyersQuery->where('min_budget', '>=', $this->minBudget);
        }
        
        if (!is_null($this->maxBudget) && $this->maxBudget > 0) {
            $lawyersQuery->where('max_budget', '<=', $this->maxBudget);
        }
        
        // Apply consultation type filters
        if ($this->onlineConsultation) {
            $lawyersQuery->where('offers_online_consultation', true);
        }
        
        if ($this->inhouseConsultation) {
            $lawyersQuery->where('offers_inhouse_consultation', true);
        }
        
        // Apply language filter
        if (!empty($this->selectedLanguage)) {
            $lawyersQuery->whereJsonContains('languages', $this->selectedLanguage);
        }
        
        // Apply rating filter for lawyers
        if ($this->minRating > 0) {
            $lawyersQuery->whereHas('user', function($q) {
                $q->whereHas('receivedRatings', function($q) {
                    $q->select('lawyer_id')
                        ->where('is_visible', true)
                        ->groupBy('lawyer_id')
                        ->havingRaw('AVG(rating) >= ?', [$this->minRating]);
                });
            });
        }
        
        // ----------------------
        // LAW FIRM PROFILES QUERY
        // ----------------------
        $lawFirmsQuery = LawFirmProfile::query()
            ->with(['user.activeSubscription.plan', 'services'])
            ->whereHas('user', function($q) {
                $q->where('profile_completed', true)
                    ->where('status', 'approved');
            });
        
        // Apply city filter for law firms
        if (!empty($this->selectedCity) && $this->selectedCity !== 'All Cities') {
            $lawFirmsQuery->where('city', $this->selectedCity);
        }

        // Apply search filter
        if (!empty($this->search)) {
            $lawFirmsQuery->where('firm_name', 'like', '%' . $this->search . '%');
        }
        
        // Apply service filter
        if (!empty($this->selectedService)) {
            $lawFirmsQuery->whereHas('services', function ($query) {
                $query->where('legal_services.id', $this->selectedService);
            });
        }
        
        // Apply budget filters
        if (!is_null($this->minBudget) && $this->minBudget > 0) {
            $lawFirmsQuery->where('min_budget', '>=', $this->minBudget);
        }
        
        if (!is_null($this->maxBudget) && $this->maxBudget > 0) {
            $lawFirmsQuery->where('max_budget', '<=', $this->maxBudget);
        }
        
        // Apply consultation type filters
        if ($this->onlineConsultation) {
            $lawFirmsQuery->where('offers_online_consultation', true);
        }
        
        if ($this->inhouseConsultation) {
            $lawFirmsQuery->where('offers_inhouse_consultation', true);
        }
        
        // Apply language filter
        if (!empty($this->selectedLanguage)) {
            $lawFirmsQuery->whereJsonContains('languages', $this->selectedLanguage);
        }
        
        // Apply rating filter for law firms
        if ($this->minRating > 0) {
            $lawFirmsQuery->whereHas('user', function($q) {
                $q->whereHas('receivedLawFirmRatings', function($q) {
                    $q->select('law_firm_id')
                        ->where('is_visible', true)
                        ->groupBy('law_firm_id')
                        ->havingRaw('AVG(rating) >= ?', [$this->minRating]);
                });
            });
        }
        
        // ----------------------
        // LAW FIRM LAWYERS QUERY
        // ----------------------
        $lawFirmLawyersQuery = LawFirmLawyer::query()
            ->with(['user.activeSubscription.plan', 'services', 'lawFirm'])
            ->whereNotNull('user_id')
            ->whereHas('user', function($q) {
                $q->where('status', 'approved')
                  ->where('profile_completed', true);
            });

        // Apply city filter
        if (!empty($this->selectedCity) && $this->selectedCity !== 'All Cities') {
            $lawFirmLawyersQuery->where('city', $this->selectedCity);
        }

        // Apply search filter
        if (!empty($this->search)) {
            $lawFirmLawyersQuery->where(function($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }

        // Apply budget filters
        if (!is_null($this->minBudget) && $this->minBudget > 0) {
            $lawFirmLawyersQuery->where('min_budget', '>=', $this->minBudget);
        }
        
        if (!is_null($this->maxBudget) && $this->maxBudget > 0) {
            $lawFirmLawyersQuery->where('max_budget', '<=', $this->maxBudget);
        }
        
        // Apply consultation type filters
        if ($this->onlineConsultation) {
            $lawFirmLawyersQuery->where('offers_online_consultation', true);
        }
        
        if ($this->inhouseConsultation) {
            $lawFirmLawyersQuery->where('offers_inhouse_consultation', true);
        }

        // Apply language filter
        if (!empty($this->selectedLanguage)) {
            $lawFirmLawyersQuery->whereJsonContains('languages', $this->selectedLanguage);
        }

        // Apply service filter
        if (!empty($this->selectedService)) {
            $lawFirmLawyersQuery->whereHas('services', function ($query) {
                $query->where('legal_services.id', $this->selectedService);
            });
        }

        // Apply rating filter for law firm lawyers
        if ($this->minRating > 0) {
            $lawFirmLawyersQuery->whereHas('user', function($q) {
                $q->whereHas('receivedRatings', function($q) {
                    $q->select('lawyer_id')
                        ->where('is_visible', true)
                        ->groupBy('lawyer_id')
                        ->havingRaw('AVG(rating) >= ?', [$this->minRating]);
                });
            });
        }

        // Log the queries before subscription sorting
        \Log::debug('Lawyers Query Before Subscription Sorting:', [
            'sql' => $lawyersQuery->toSql(),
            'bindings' => $lawyersQuery->getBindings(),
            'filters' => [
                'selectedService' => $this->selectedService,
                'minBudget' => $this->minBudget,
                'maxBudget' => $this->maxBudget,
                'onlineConsultation' => $this->onlineConsultation,
                'inhouseConsultation' => $this->inhouseConsultation,
                'selectedLanguage' => $this->selectedLanguage,
                'minRating' => $this->minRating
            ]
        ]);
        
        // Execute the queries with filters
        $lawyersResults = $this->getResultsWithSubscriptionSorting($lawyersQuery, 'lawyer_profiles');
        $lawFirmsResults = $this->getResultsWithSubscriptionSorting($lawFirmsQuery, 'law_firm_profiles');
        $lawFirmLawyersResults = $lawFirmLawyersQuery->get();
        
        // Combine all results into a single collection
        $allResults = collect([])->concat($lawyersResults)->concat($lawFirmsResults)->concat($lawFirmLawyersResults);

        // Custom sorting function to properly prioritize by subscription tier, rating, and review count
        $getSortKeys = function($entity) {
            $priority = 3; // Default (Free/No subscription)
            $userForSubscription = $entity->user;
            $averageRating = 0;
            $ratingCount = 0;

            // Get subscription priority
            if ($userForSubscription && $userForSubscription->activeSubscription && $userForSubscription->activeSubscription->plan) {
                $planName = strtolower($userForSubscription->activeSubscription->plan->name);
                $priority = ($planName === 'max') ? 1 : (($planName === 'pro') ? 2 : 3);
            }

            // Get rating data
            if ($userForSubscription) {
                if ($entity instanceof \App\Models\LawFirmProfile) {
                    $ratings = $userForSubscription->receivedLawFirmRatings()->where('is_visible', true)->get();
                } else {
                    $ratings = $userForSubscription->receivedRatings()->where('is_visible', true)->get();
                }
                
                $ratingCount = $ratings->count();
                $averageRating = $ratingCount > 0 ? $ratings->avg('rating') : 0;
            }

            // Check for law firm subscription for lawyers
            if ($entity instanceof \App\Models\LawyerProfile || $entity instanceof \App\Models\LawFirmLawyer) {
                $firmToCheck = null;
                if ($entity instanceof \App\Models\LawyerProfile && $entity->law_firm_id && $entity->lawFirm) {
                    $firmToCheck = $entity->lawFirm;
                } elseif ($entity instanceof \App\Models\LawFirmLawyer && $entity->lawFirm) {
                    $firmToCheck = $entity->lawFirm;
                }

                if ($firmToCheck && $firmToCheck->user && $firmToCheck->user->activeSubscription && $firmToCheck->user->activeSubscription->plan) {
                    $firmPlanName = strtolower($firmToCheck->user->activeSubscription->plan->name);
                    $firmPriority = ($firmPlanName === 'max') ? 1 : (($firmPlanName === 'pro') ? 2 : 3);
                    $priority = min($priority, $firmPriority);
                }
            }
            
            $createdAtTimestamp = $entity->created_at instanceof \Carbon\Carbon ? $entity->created_at->timestamp : 0;
            
            // Return array of sort keys in priority order:
            // 1. Subscription priority (1=MAX, 2=PRO, 3=FREE)
            // 2. Average rating (negative to sort DESC)
            // 3. Rating count (negative to sort DESC)
            // 4. Creation timestamp (negative to sort DESC)
            return [
                $priority,
                -$averageRating,
                -$ratingCount,
                -$createdAtTimestamp
            ];
        };
        
        $sortedResults = $allResults->sortBy($getSortKeys)->values();
        
        // Paginate the sorted results
        $paginatedResults = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedResults->forPage($this->page, $this->perPage),
            $sortedResults->count(),
            $this->perPage,
            $this->page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );
        
        // Add debug logging to help identify issues
        \Log::debug('Filter Values:', [
            'selectedService' => $this->selectedService,
            'minBudget' => $this->minBudget,
            'maxBudget' => $this->maxBudget,
            'selectedCity' => $this->selectedCity,
            'onlineConsultation' => $this->onlineConsultation,
            'inhouseConsultation' => $this->inhouseConsultation,
            'selectedLanguage' => $this->selectedLanguage,
            'minRating' => $this->minRating,
            'resultCount' => $paginatedResults->count()
        ]);
        
        return view('livewire.lawyers.nearby-lawyers', [
            'results' => $paginatedResults,
            'services' => $services,
            'allCities' => array_merge(['All Cities'], $allCities),
            'currentCity' => $this->selectedCity
        ])->layout('layouts.app');
    }

    /**
     * Get results with subscription-based sorting
     * This method preserves the original query conditions
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tableName The base table name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getResultsWithSubscriptionSorting($query, $tableName = 'lawyer_profiles')
    {
        // First execute the query to get filtered results
        $filteredIds = $query->pluck('id')->toArray();
        
        if (empty($filteredIds)) {
            return collect([]);
        }
        
        // Then apply subscription sorting on only those results
        $results = DB::table($tableName)
            ->whereIn("$tableName.id", $filteredIds)
            ->leftJoin('users', "$tableName.user_id", '=', 'users.id')
            ->leftJoin('subscriptions', function($join) {
                $join->on('users.id', '=', 'subscriptions.user_id')
                    ->where('subscriptions.status', '=', 'active')
                    ->where(function($query) {
                        $query->whereDate('subscriptions.ends_at', '>', now())
                            ->orWhereNull('subscriptions.ends_at');
                    });
            })
            ->leftJoin('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->orderByRaw("CASE 
                WHEN subscription_plans.name = 'Max' THEN 1
                WHEN subscription_plans.name = 'Pro' THEN 2
                ELSE 3
            END")
            ->orderBy("$tableName.created_at", 'desc')
            ->select("$tableName.*")
            ->get();

        // Convert back to model instances with eager loading
        $modelClass = $tableName === 'lawyer_profiles' 
            ? \App\Models\LawyerProfile::class 
            : \App\Models\LawFirmProfile::class;
            
        return $modelClass::with(['user.activeSubscription.plan', 'services'])
            ->whereIn('id', $results->pluck('id')->toArray())
            ->get();
    }

    /**
     * This method is kept for backward compatibility but is no longer used
     */
    protected function applySubscriptionSorting($query, $tableName = 'lawyer_profiles')
    {
        return $this->getResultsWithSubscriptionSorting($query, $tableName);
    }
} 