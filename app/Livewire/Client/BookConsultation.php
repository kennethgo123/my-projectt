<?php

namespace App\Livewire\Client;

use App\Models\Consultation;
use App\Models\User;
use App\Models\LawFirmLawyer;
use App\Models\BlockedTimeSlot;
use App\Models\Invoice;
use App\Services\NotificationService;
use App\Services\PayMongoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookConsultation extends Component
{
    use WithFileUploads;

    public $lawyer_id;
    public $consultation_type;
    public $description;
    public $documents = [];
    public $preferred_dates = [];
    
    // For displaying lawyer info
    public $lawyer;
    public $lawyerName;
    public $lawyerEmail;
    
    // For lawyer availability selection
    public $selectedDay = null;
    public $availableDays = [];
    public $availableTimeSlots = [];
    public $selectedTimeSlot = null;
    public $useAvailability = false; // Flag to switch between custom dates and lawyer availability
    
    // For calendar functionality
    public $selectedDate = null;
    public $currentCalendarMonth = null;
    public $calendarDays = [];
    
    // Reservation payment confirmation (₱500)
    public $reservationPaid = false;
    public $showPaymentModal = false;
    
    // For law firm lawyer selection
    public $isLawFirm = false;
    public $firmLawyers = [];
    public $selectedLawyerId = null; // null means "Let the firm decide"
    
    // Add a property to store the reservation invoice id
    public $reservationInvoiceId = null;
    
    // Add a property to store the draft consultation id
    public $draftConsultationId = null;
    
    // Constants for lawyer selection
    const SELECT_FIRM_DECIDE = "__default__"; // Let the firm decide
    const SELECT_FIRM_ENTITY = "__firm__";    // The law firm as an entity
    
    protected $rules = [
        'consultation_type' => 'required|in:Online Consultation,In-House Consultation',
        'description' => 'required|min:10',
        'documents.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
    ];

    public function mount($lawyer_id)
    {
        $this->lawyer_id = $lawyer_id;
        $this->lawyer = User::with(['lawyerProfile', 'lawFirmProfile', 'role'])->findOrFail($lawyer_id);
        
        // Check if this is a law firm - use the isLawFirm method if available
        $this->isLawFirm = $this->lawyer->isLawFirm();
        
        // Set lawyer info based on whether it's an individual lawyer or law firm
        if ($this->lawyer->isLawyer()) {
            // Add null check before accessing lawyerProfile properties
            if ($this->lawyer->lawyerProfile) {
                $this->lawyerName = $this->lawyer->lawyerProfile->first_name . ' ' . $this->lawyer->lawyerProfile->last_name;
            } else {
                // Fallback to user's name if profile is missing
                $this->lawyerName = $this->lawyer->name;
            }
        } else {
            // Add null check before accessing lawFirmProfile properties
            if ($this->lawyer->lawFirmProfile) {
                $this->lawyerName = $this->lawyer->lawFirmProfile->firm_name;
            } else {
                // Fallback to user's name if profile is missing
                $this->lawyerName = $this->lawyer->name;
            }
            
            // Load lawyers for this firm if it's a law firm
            if ($this->isLawFirm) {
                $this->loadFirmLawyers();
                // Set "Let the firm decide" as the default selection
                $this->selectedLawyerId = "__default__";
                
                // Check if the law firm has any availability records
                $lawFirmHasAvailability = \App\Models\LawyerAvailability::where('user_id', $this->lawyer_id)->exists();
                
                // If not, create a default availability
                if (!$lawFirmHasAvailability) {
                    // Create default availability for Monday-Friday, 9-5
                    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                        \App\Models\LawyerAvailability::create([
                            'user_id' => $this->lawyer_id,
                            'day_of_week' => $day,
                            'start_time' => '09:00',
                            'end_time' => '17:00',
                            'is_available' => true,
                        ]);
                    }
                }
            }
        }
        $this->lawyerEmail = $this->lawyer->email;
        
        // Load available days for consultation
        $this->loadAvailableDays();
        
        // Check for consultation offerings
        $this->loadConsultationTypes();
        
        // Set useAvailability default to true if there are available days
        if (!empty($this->availableDays)) {
            $this->useAvailability = true;
        }
        
        // Initialize calendar
        $this->currentCalendarMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->generateCalendarDays();
        
        // Persist reservation payment across navigation
        $this->reservationPaid = (bool) session('reservation_paid_for_booking', false);
        // Check if there is a reservation invoice and if it is paid
        $invoiceId = session('reservation_invoice_id');
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice && $invoice->status === Invoice::STATUS_PAID) {
                $this->reservationPaid = true;
            }
        }
        
        // Restore saved form data from draft consultation if it exists
        $this->restoreFormDataFromDraft();
    }
    
    /**
     * Save form data to draft consultation in database before payment redirect
     */
    private function saveFormDataToDraft()
    {
        // Store uploaded documents temporarily before payment
        $documentPaths = [];
        foreach ($this->documents as $document) {
            if ($document) {
                $path = $document->store('temp-consultation-documents', 'public');
                $documentPaths[] = $path;
            }
        }
        
        // Prepare consultation data
        $preferredDates = [];
        $selectedStartTime = null;
        $selectedEndTime = null;
        
        // If using lawyer availability and a time slot is selected
        if ($this->useAvailability && $this->selectedTimeSlot) {
            foreach ($this->availableTimeSlots as $slot) {
                if (isset($slot['datetime']) && $slot['datetime'] === $this->selectedTimeSlot) {
                    $preferredDates = [$slot['datetime']];
                    $selectedStartTime = $slot['datetime'];
                    $selectedEndTime = $slot['end_datetime'];
                    break;
                }
            }
        }
        
        // Determine which lawyer ID to use
        $targetLawyerId = $this->lawyer_id;
        $specificLawyer = null;
        $assignAsEntity = false;
        
        if ($this->isLawFirm && $this->selectedLawyerId === self::SELECT_FIRM_ENTITY) {
            $assignAsEntity = true;
        } else if ($this->isLawFirm && $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
            $specificLawyer = (int) $this->selectedLawyerId;
        }
        
        // Find or create draft consultation
        $draft = Consultation::where('client_id', Auth::id())
            ->where('lawyer_id', $targetLawyerId)
            ->where('status', 'draft')
            ->latest()
            ->first();
        
        if ($draft) {
            // Update existing draft
            $draft->update([
                'consultation_type' => $this->consultation_type,
                'description' => $this->description,
                'preferred_dates' => json_encode($preferredDates),
                'start_time' => $selectedStartTime,
                'end_time' => $selectedEndTime,
                'documents' => json_encode($documentPaths),
                'specific_lawyer_id' => $specificLawyer,
                'assign_as_entity' => $assignAsEntity,
                'selected_date' => $this->selectedDate ? Carbon::parse($this->selectedDate) : null,
            ]);
            $this->draftConsultationId = $draft->id;
        } else {
            // Create new draft
            $draft = Consultation::create([
                'client_id' => Auth::id(),
                'lawyer_id' => $targetLawyerId,
                'status' => 'draft',
                'consultation_type' => $this->consultation_type,
                'description' => $this->description,
                'preferred_dates' => json_encode($preferredDates),
                'start_time' => $selectedStartTime,
                'end_time' => $selectedEndTime,
                'documents' => json_encode($documentPaths),
                'specific_lawyer_id' => $specificLawyer,
                'assign_as_entity' => $assignAsEntity,
                'selected_date' => $this->selectedDate ? Carbon::parse($this->selectedDate) : null,
            ]);
            $this->draftConsultationId = $draft->id;
        }
        
        // Store draft ID and form metadata in session for easy retrieval
        session([
            'consultation_draft_id_' . $this->lawyer_id => $this->draftConsultationId,
            'consultation_form_metadata_' . $this->lawyer_id => [
                'selectedDay' => $this->selectedDay,
                'selectedTimeSlot' => $this->selectedTimeSlot,
                'selectedDate' => $this->selectedDate,
                'useAvailability' => $this->useAvailability,
                'selectedLawyerId' => $this->selectedLawyerId,
                'currentCalendarMonth' => $this->currentCalendarMonth,
            ]
        ]);
    }
    
    /**
     * Restore form data from draft consultation after payment redirect
     */
    private function restoreFormDataFromDraft()
    {
        // Try to get draft ID from session first
        $draftId = session('consultation_draft_id_' . $this->lawyer_id);
        
        // If not in session, try to find the most recent draft for this client/lawyer
        if (!$draftId) {
            $draft = Consultation::where('client_id', Auth::id())
                ->where('lawyer_id', $this->lawyer_id)
                ->where('status', 'draft')
                ->latest()
                ->first();
            
            if ($draft) {
                $draftId = $draft->id;
                $this->draftConsultationId = $draftId;
            }
        } else {
            $draft = Consultation::find($draftId);
        }
        
        if ($draft && $draft->status === 'draft') {
            // Restore basic form fields from draft
            $this->consultation_type = $draft->consultation_type ?? $this->consultation_type;
            $this->description = $draft->description ?? $this->description;
            
            // Restore metadata from session first (this has the UI state)
            $metadata = session('consultation_form_metadata_' . $this->lawyer_id, []);
            if ($metadata) {
                $this->selectedDay = $metadata['selectedDay'] ?? $this->selectedDay;
                $this->selectedDate = $metadata['selectedDate'] ?? $this->selectedDate;
                $this->useAvailability = $metadata['useAvailability'] ?? $this->useAvailability;
                $this->selectedLawyerId = $metadata['selectedLawyerId'] ?? $this->selectedLawyerId;
                $this->currentCalendarMonth = $metadata['currentCalendarMonth'] ?? $this->currentCalendarMonth;
            }
            
            // Restore date from draft if not in metadata
            if (!$this->selectedDate && $draft->selected_date) {
                $this->selectedDate = Carbon::parse($draft->selected_date)->format('Y-m-d');
            }
            
            // Restore lawyer selection if it was changed
            if (isset($metadata['selectedLawyerId']) && $metadata['selectedLawyerId'] !== $this->selectedLawyerId) {
                $this->selectedLawyerId = $metadata['selectedLawyerId'];
                if ($this->selectedLawyerId && 
                    $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && 
                    $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
                    $this->loadAvailableDays($this->selectedLawyerId);
                } else {
                    $this->loadAvailableDays();
                }
            }
            
            // Regenerate calendar if needed
            if ($this->currentCalendarMonth) {
                $this->generateCalendarDays();
            }
            
            // Reload time slots if a day/date was selected (MUST happen before restoring selectedTimeSlot)
            if ($this->selectedDay) {
                $this->loadTimeSlots();
            } elseif ($this->selectedDate) {
                $this->loadTimeSlotsForDate();
            }
            
            // Now restore the selected time slot AFTER time slots are loaded
            if ($metadata && isset($metadata['selectedTimeSlot'])) {
                $savedTimeSlot = $metadata['selectedTimeSlot'];
                // Try to find matching slot in available slots
                foreach ($this->availableTimeSlots as $slot) {
                    if (isset($slot['datetime']) && $slot['datetime'] === $savedTimeSlot) {
                        $this->selectedTimeSlot = $slot['datetime'];
                        break;
                    }
                }
                // If not found, set it anyway (might be a different format)
                if (!$this->selectedTimeSlot) {
                    $this->selectedTimeSlot = $savedTimeSlot;
                }
            } elseif ($draft->start_time) {
                // Try to match the start_time from draft with available slots
                $draftStartTime = is_string($draft->start_time) ? $draft->start_time : Carbon::parse($draft->start_time)->format('Y-m-d H:i:s');
                foreach ($this->availableTimeSlots as $slot) {
                    if (isset($slot['datetime'])) {
                        $slotTime = is_string($slot['datetime']) ? $slot['datetime'] : Carbon::parse($slot['datetime'])->format('Y-m-d H:i:s');
                        if ($slotTime === $draftStartTime || $slot['datetime'] === $draftStartTime) {
                            $this->selectedTimeSlot = $slot['datetime'];
                            break;
                        }
                    }
                }
                // If not found in available slots, set from draft anyway
                if (!$this->selectedTimeSlot) {
                    $this->selectedTimeSlot = $draftStartTime;
                }
            }
            
            // Store document paths from draft for later retrieval
            if ($draft->documents) {
                $docPaths = is_array($draft->documents) ? $draft->documents : json_decode($draft->documents, true);
                if ($docPaths) {
                    session(['consultation_temp_documents_' . $this->lawyer_id => $docPaths]);
                }
            }
        }
    }
    
    /**
     * Clear saved form data from session and delete draft
     */
    private function clearFormDataFromSession()
    {
        session()->forget([
            'consultation_form_data_' . $this->lawyer_id,
            'consultation_draft_id_' . $this->lawyer_id,
            'consultation_form_metadata_' . $this->lawyer_id,
            'consultation_temp_documents_' . $this->lawyer_id
        ]);
        
        // Delete draft consultation if it exists
        if ($this->draftConsultationId) {
            $draft = Consultation::find($this->draftConsultationId);
            if ($draft && $draft->status === 'draft') {
                // Delete associated temp documents
                if ($draft->documents) {
                    $docPaths = is_array($draft->documents) ? $draft->documents : json_decode($draft->documents, true);
                    if ($docPaths) {
                        foreach ($docPaths as $path) {
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }
                    }
                }
                $draft->delete();
            }
        }
    }
    
    /**
     * Load available consultation types based on the profile
     */
    public function loadConsultationTypes()
    {
        // Check if online consultation is offered (with null safety)
        $offersOnline = false;
        $offersInHouse = false;
        
        // First check the main lawyer/firm profile
        if ($this->lawyer->lawyerProfile) {
            $offersOnline = $this->lawyer->lawyerProfile->offers_online_consultation;
            $offersInHouse = $this->lawyer->lawyerProfile->offers_inhouse_consultation;
        } elseif ($this->lawyer->lawFirmProfile) {
            $offersOnline = $this->lawyer->lawFirmProfile->offers_online_consultation;
            $offersInHouse = $this->lawyer->lawFirmProfile->offers_inhouse_consultation;
        }
        
        // If this is a lawyer under a law firm, check their LawFirmLawyer record
        if ($this->lawyer->isLawyer() && $this->lawyer->firm_id) {
            $lawFirmLawyer = \App\Models\LawFirmLawyer::where('user_id', $this->lawyer->id)->first();
            if ($lawFirmLawyer) {
                $offersOnline = $lawFirmLawyer->offers_online_consultation;
                $offersInHouse = $lawFirmLawyer->offers_inhouse_consultation;
            }
        }
        
        // Set the default consultation type based on what's offered
        if ($offersOnline) {
            $this->consultation_type = 'Online Consultation';
        } elseif ($offersInHouse) {
            $this->consultation_type = 'In-House Consultation';
        }
    }
    
    /**
     * Load lawyers belonging to this law firm
     */
    public function loadFirmLawyers()
    {
        // Make sure the law firm profile exists
        if (!$this->lawyer->lawFirmProfile) {
            $this->firmLawyers = [];
            return;
        }
        
        // Get all lawyers that belong to this law firm
        $lawFirmLawyers = LawFirmLawyer::where('law_firm_profile_id', $this->lawyer->lawFirmProfile->id)
            ->with('user')
            ->where('status', 'active')
            ->get();
            
        // If no lawyers found in LawFirmLawyer, try using the User model relationship
        if ($lawFirmLawyers->isEmpty()) {
            // Find lawyers with firm_id equal to the law firm user ID
            $firmLawyers = User::where('firm_id', $this->lawyer_id)
                ->where('status', 'approved')
                ->whereHas('role', function($query) {
                    $query->where('name', 'lawyer');
                })
                ->with('lawyerProfile')
                ->get();
                
            $this->firmLawyers = $firmLawyers->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->lawyerProfile ? 
                        ($user->lawyerProfile->first_name . ' ' . $user->lawyerProfile->last_name) : 
                        $user->name,
                    'photo' => $user->lawyerProfile ? $user->lawyerProfile->photo_path : null
                ];
            })->toArray();
            
            return;
        }
        
        $this->firmLawyers = $lawFirmLawyers->map(function ($lawFirmLawyer) {
            return [
                'id' => $lawFirmLawyer->user_id,
                'name' => $lawFirmLawyer->first_name . ' ' . $lawFirmLawyer->last_name,
                'photo' => $lawFirmLawyer->photo_path
            ];
        })->toArray();
    }
    
    /**
     * Handle lawyer selection change
     */
    public function updatedSelectedLawyerId()
    {
        // Check if "Let the firm decide" or "Law Firm as Entity" is selected
        if ($this->selectedLawyerId === self::SELECT_FIRM_DECIDE || $this->selectedLawyerId === null) {
            // Treat __default__ as null internally for consistency
            $this->selectedLawyerId = null;
            
            // For "Let the firm decide", use the firm's availability
            $this->loadAvailableDays();
        } else if ($this->selectedLawyerId === self::SELECT_FIRM_ENTITY) {
            // For "Law Firm as Entity", use the firm's availability
            $this->loadAvailableDays();
        } else {
            // A specific lawyer is selected, load their availability
            $this->loadAvailableDays($this->selectedLawyerId);
            
            // Also update consultation types if a specific lawyer is selected
            $this->loadLawyerConsultationTypes($this->selectedLawyerId);
        }
        
        $this->selectedDay = null;
        $this->selectedTimeSlot = null;
        $this->availableTimeSlots = [];
        $this->selectedDate = null;
        
        // Regenerate calendar for the new lawyer
        if ($this->useAvailability) {
            $this->generateCalendarDays();
        }
    }
    
    /**
     * Load consultation types for a specific lawyer in a law firm
     */
    private function loadLawyerConsultationTypes($lawyerId)
    {
        $lawFirmLawyer = \App\Models\LawFirmLawyer::where('user_id', $lawyerId)->first();
        
        if ($lawFirmLawyer) {
            // Reset consultation type based on this specific lawyer's offerings
            $offersOnline = $lawFirmLawyer->offers_online_consultation;
            $offersInHouse = $lawFirmLawyer->offers_inhouse_consultation;
            
            if ($offersOnline) {
                $this->consultation_type = 'Online Consultation';
            } elseif ($offersInHouse) {
                $this->consultation_type = 'In-House Consultation';
            } else {
                $this->consultation_type = null; // Reset if neither is offered
            }
        }
    }
    
    /**
     * Load days where lawyer has set availability
     */
    public function loadAvailableDays($specificLawyerId = null)
    {
        $lawyerId = $specificLawyerId ?? $this->lawyer_id;
        $this->availableDays = \App\Models\LawyerAvailability::getAvailableDays($lawyerId);
        
        // Set default selected day if available
        if (!empty($this->availableDays)) {
            $this->selectedDay = $this->availableDays[0];
            $this->loadTimeSlots();
        }
    }
    
    /**
     * Load available time slots for the selected day
     */
    public function loadTimeSlots()
    {
        if (!$this->selectedDay) {
            $this->availableTimeSlots = [];
            return;
        }
        
        // Use the selected lawyer's ID if one is chosen (not null/__default__/__firm__), otherwise use the firm's ID
        if ($this->selectedLawyerId && 
            $this->selectedLawyerId !== null && 
            $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && 
            $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
            $lawyerId = $this->selectedLawyerId;
        } else {
            $lawyerId = $this->lawyer_id;
        }
        
        // Get the next occurrence of the selected day
        $nextDate = $this->getNextDayOccurrence($this->selectedDay);
        
        // Get available time slots with lunch breaks and blocked slots excluded
        $timeSlots = \App\Models\LawyerAvailability::getAvailableTimeSlots($lawyerId, $this->selectedDay, $nextDate);
        
        $this->availableTimeSlots = $timeSlots;
    }
    
    /**
     * Get the next occurrence of a day of the week
     */
    private function getNextDayOccurrence($dayName)
    {
        $today = \Carbon\Carbon::today();
        
        // Map day names to Carbon day constants (1=Monday through 7=Sunday)
        $dayMap = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];
        
        $targetDayOfWeek = $dayMap[$dayName];
        
        // If today is the target day, return next week's occurrence
        if ($today->dayOfWeek === $targetDayOfWeek) {
            return $today->addWeek();
        }
        
        // Get next occurrence
        return $today->next($targetDayOfWeek);
    }
    
    /**
     * Handle consultation type change
     */
    public function updatedConsultationType()
    {
        // If consultation type is changed and available days exist, 
        // default to showing lawyer's availability
        if (!empty($this->availableDays)) {
            $this->useAvailability = true;
            $this->loadTimeSlots();
        }
    }
    
    /**
     * Handle day selection change
     */
    public function updatedSelectedDay()
    {
        $this->loadTimeSlots();
        $this->selectedTimeSlot = null;
    }
    
    /**
     * Handle switching between custom dates and lawyer availability
     */
    public function updatedUseAvailability()
    {
        if ($this->useAvailability) {
            // Switching to lawyer availability
            $this->generateCalendarDays();
            if ($this->selectedDate) {
                $this->loadTimeSlotsForDate();
            }
        } else {
            // Switching back to custom dates
            $this->selectedTimeSlot = null;
            $this->selectedDate = null;
        }
    }
    
    /**
     * Generate calendar days for the current month
     */
    public function generateCalendarDays()
    {
        $month = Carbon::parse($this->currentCalendarMonth);
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();
        
        // Get the first day of the calendar (start of week containing first day of month)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        // Get the last day of the calendar (end of week containing last day of month)
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        // Get available dates for this month
        $lawyerId = $this->getEffectiveLawyerId();
        $availableDates = \App\Models\LawyerAvailability::getAvailableDatesInMonth($lawyerId, $month);
        
        $this->calendarDays = [];
        $currentDate = $calendarStart->copy();
        
        while ($currentDate->lte($calendarEnd)) {
            $dateString = $currentDate->format('Y-m-d');
            $isCurrentMonth = $currentDate->month === $month->month;
            
            $this->calendarDays[] = [
                'date' => $dateString,
                'dayNumber' => $currentDate->day,
                'isCurrentMonth' => $isCurrentMonth,
                'hasAvailability' => in_array($dateString, $availableDates)
            ];
            
            $currentDate->addDay();
        }
    }
    
    /**
     * Get the effective lawyer ID for availability checks
     */
    private function getEffectiveLawyerId()
    {
        if ($this->selectedLawyerId && 
            $this->selectedLawyerId !== null && 
            $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && 
            $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
            return $this->selectedLawyerId;
        }
        
        return $this->lawyer_id;
    }
    
    /**
     * Navigate to previous month in calendar
     */
    public function previousMonth()
    {
        $this->currentCalendarMonth = Carbon::parse($this->currentCalendarMonth)
            ->subMonth()
            ->format('Y-m-d');
        $this->generateCalendarDays();
    }
    
    /**
     * Navigate to next month in calendar
     */
    public function nextMonth()
    {
        $this->currentCalendarMonth = Carbon::parse($this->currentCalendarMonth)
            ->addMonth()
            ->format('Y-m-d');
        $this->generateCalendarDays();
    }
    
    /**
     * Select a date from the calendar
     */
    public function selectDate($date)
    {
        $selectedDate = Carbon::parse($date);
        
        // Don't allow past dates
        if ($selectedDate->lt(Carbon::today())) {
            return;
        }
        
        // Check if the lawyer has availability on this date
        $lawyerId = $this->getEffectiveLawyerId();
        if (!\App\Models\LawyerAvailability::hasAvailabilityOnDate($lawyerId, $selectedDate)) {
            return;
        }
        
        $this->selectedDate = $date;
        $this->loadTimeSlotsForDate();
    }
    
    /**
     * Load available time slots for the selected date
     */
    public function loadTimeSlotsForDate()
    {
        if (!$this->selectedDate) {
            $this->availableTimeSlots = [];
            return;
        }
        
        $selectedDate = Carbon::parse($this->selectedDate);
        $lawyerId = $this->getEffectiveLawyerId();
        
        // Get available time slots for the specific date
        $slots = \App\Models\LawyerAvailability::getAvailableTimeSlotsForDate($lawyerId, $selectedDate);
        
        // Filter out past time slots if selected date is today (or earlier, though earlier should already be blocked)
        $now = Carbon::now();
        $this->availableTimeSlots = collect($slots)
            ->filter(function($slot) use ($now) {
                if (!isset($slot['datetime'])) return false;
                try {
                    $dt = Carbon::parse($slot['datetime']);
                } catch (\Throwable $e) { return false; }
                return $dt->greaterThan($now);
            })
            ->values()
            ->all();
        
        // Reset selected time slot
        $this->selectedTimeSlot = null;
    }

    /**
     * Find or create a reservation invoice for this client/lawyer
     */
    private function getOrCreateReservationInvoice()
    {
        $invoice = Invoice::whereNull('legal_case_id')
            ->where('client_id', Auth::id())
            ->where('lawyer_id', $this->lawyer_id)
            ->where('title', 'Consultation Reservation')
            ->where('status', Invoice::STATUS_PENDING)
            ->orderByDesc('id')
            ->first();
        if ($invoice) {
            $this->reservationInvoiceId = $invoice->id;
            return $invoice;
        }
        // Create a new invoice
        $invoice = Invoice::create([
            'client_id' => Auth::id(),
            'lawyer_id' => $this->lawyer_id,
            'invoice_number' => 'CONSULT-' . strtoupper(uniqid()),
            'title' => 'Consultation Reservation',
            'description' => 'Reservation fee for consultation booking',
            'subtotal' => 500,
            'tax' => 0,
            'discount' => 0,
            'total' => 500,
            'issue_date' => now(),
            'due_date' => now()->addDay(),
            'status' => Invoice::STATUS_PENDING,
            'payment_plan' => Invoice::PAYMENT_PLAN_FULL,
        ]);
        $this->reservationInvoiceId = $invoice->id;
        return $invoice;
    }

    public function submitConsultation()
    {
        // Always validate required fields
        $this->validate([
            'consultation_type' => 'required|in:Online Consultation,In-House Consultation',
            'description' => 'required|min:10',
            'documents.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
        ]);
        
        // Require reservation payment confirmation
        if (!$this->reservationPaid) {
            session()->flash('error', 'Reservation payment of ₱500 is required before booking.');
            return;
        }
        
        // Require both date and time slot selection when using availability system
        if ($this->useAvailability && !$this->selectedDate) {
            session()->flash('error', 'Please select a date for your consultation.');
            return;
        }
        
        if ($this->useAvailability && !$this->selectedTimeSlot) {
            session()->flash('error', 'Please select a time slot for your consultation.');
            return;
        }
        
        $preferredDates = [];
        $selectedStartTime = null;
        $selectedEndTime = null;
        
        // If using lawyer availability and a time slot is selected
        if ($this->useAvailability && $this->selectedTimeSlot) {
            foreach ($this->availableTimeSlots as $slot) {
                if (isset($slot['datetime']) && $slot['datetime'] === $this->selectedTimeSlot) {
                    // Use the selected time slot
                    $preferredDates = [$slot['datetime']];
                    $selectedStartTime = $slot['datetime'];
                    $selectedEndTime = $slot['end_datetime'];
                    break;
                }
            }
        } else {
            // Fallback for lawyers without availability system
            session()->flash('error', 'This lawyer has not set up their availability. Please contact them directly.');
            return;
        }

        // Check if we have a draft consultation to update
        $draft = null;
        if ($this->draftConsultationId) {
            $draft = Consultation::find($this->draftConsultationId);
        }
        
        // If no draft, try to find one from session
        if (!$draft) {
            $draftId = session('consultation_draft_id_' . $this->lawyer_id);
            if ($draftId) {
                $draft = Consultation::find($draftId);
            }
        }
        
        // Store documents if any
        $documentPaths = [];
        
        // First, check if there are documents from draft (after payment)
        if ($draft && $draft->documents) {
            $tempDocumentPaths = is_array($draft->documents) ? $draft->documents : json_decode($draft->documents, true);
            if ($tempDocumentPaths) {
                // Move temp documents to final location
                foreach ($tempDocumentPaths as $tempPath) {
                    $finalPath = str_replace('temp-consultation-documents/', 'consultation-documents/', $tempPath);
                    if (Storage::disk('public')->exists($tempPath)) {
                        Storage::disk('public')->move($tempPath, $finalPath);
                        $documentPaths[] = $finalPath;
                    }
                }
            }
        }
        
        // Also check session for temp documents (fallback)
        $tempDocumentPaths = session('consultation_temp_documents_' . $this->lawyer_id, []);
        if (!empty($tempDocumentPaths)) {
            foreach ($tempDocumentPaths as $tempPath) {
                $finalPath = str_replace('temp-consultation-documents/', 'consultation-documents/', $tempPath);
                if (Storage::disk('public')->exists($tempPath)) {
                    Storage::disk('public')->move($tempPath, $finalPath);
                    $documentPaths[] = $finalPath;
                }
            }
        }
        
        // Also handle any newly uploaded documents
        foreach ($this->documents as $document) {
            if ($document) {
                $path = $document->store('consultation-documents', 'public');
                $documentPaths[] = $path;
            }
        }

        // Determine which lawyer ID to use
        $targetLawyerId = $this->lawyer_id;
        $specificLawyer = null;
        $assignAsEntity = false;
        
        // If this is a law firm and the firm entity option was selected
        if ($this->isLawFirm && $this->selectedLawyerId === self::SELECT_FIRM_ENTITY) {
            $assignAsEntity = true;
        }
        // If this is a law firm and a specific lawyer was selected (not default or firm entity)
        else if ($this->isLawFirm && $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
            // Only set specificLawyer when a valid lawyer ID is selected
            $specificLawyer = (int) $this->selectedLawyerId;
        }

        // Update draft to pending status or create new consultation
        if ($draft && $draft->status === 'draft') {
            $consultation = $draft;
            $consultation->update([
                'status' => 'pending',
                'consultation_type' => $this->consultation_type,
                'description' => $this->description,
                'preferred_dates' => json_encode($preferredDates),
                'start_time' => $selectedStartTime,
                'end_time' => $selectedEndTime,
                'documents' => json_encode($documentPaths),
                'specific_lawyer_id' => $specificLawyer,
                'assign_as_entity' => $assignAsEntity,
            ]);
        } else {
            // Create new consultation
            $consultation = Consultation::create([
                'client_id' => Auth::id(),
                'lawyer_id' => $targetLawyerId,
                'status' => 'pending',
                'consultation_type' => $this->consultation_type,
                'description' => $this->description,
                'preferred_dates' => json_encode($preferredDates),
                'start_time' => $selectedStartTime,
                'end_time' => $selectedEndTime,
                'documents' => json_encode($documentPaths),
                'specific_lawyer_id' => $specificLawyer,
                'assign_as_entity' => $assignAsEntity,
            ]);
        }

        // Send notification to the lawyer
        NotificationService::newConsultationRequest($consultation);
        
        // Dispatch real-time notification event
        $this->dispatch('notification-received');

        session()->flash('message', 'Consultation request sent successfully! The lawyer will be notified.');
        
        // Redirect to consultations page instead of cases
        // Clear reservation paid session and saved form data so next booking requires payment
        session()->forget(['reservation_paid_for_booking', 'reservation_invoice_id']);
        $this->clearFormDataFromSession();
        return redirect()->route('client.consultations');
    }

    public function openPaymentModal()
    {
        // Validate required fields before saving draft
        $this->validate([
            'consultation_type' => 'required|in:Online Consultation,In-House Consultation',
            'description' => 'required|min:10',
        ]);
        
        // Require both date and time slot selection when using availability system
        if ($this->useAvailability && !$this->selectedDate) {
            session()->flash('error', 'Please select a date for your consultation.');
            return;
        }
        
        if ($this->useAvailability && !$this->selectedTimeSlot) {
            session()->flash('error', 'Please select a time slot for your consultation.');
            return;
        }
        
        // Save form data to draft consultation in database
        // This ensures data is preserved even if user navigates away
        $this->saveFormDataToDraft();
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }

    public function payWithGCash()
    {
        try {
            // Ensure draft is saved before redirecting to payment
            if (!$this->draftConsultationId) {
                $this->saveFormDataToDraft();
            }
            
            $invoice = $this->getOrCreateReservationInvoice();
            
            // Link draft to invoice
            if ($this->draftConsultationId) {
                $draft = Consultation::find($this->draftConsultationId);
                if ($draft) {
                    // Store invoice ID in a custom field or use a pivot table
                    // For now, we'll use session to link them
                    session(['draft_consultation_for_invoice_' . $invoice->id => $this->draftConsultationId]);
                }
            }
            
            $payMongo = new PayMongoService();
            $nextUrl = url('/client/book-consultation/' . $this->lawyer_id);
            $successUrl = route('payment.success', ['invoice' => $invoice->id]) . '?next=' . urlencode($nextUrl);
            $failedUrl = route('payment.failed', ['invoice' => $invoice->id]) . '?next=' . urlencode($nextUrl);
            $result = $payMongo->createSource($invoice, 'gcash', $successUrl, $failedUrl);
            if ($result['success'] && isset($result['checkout_url'])) {
                session(['reservation_invoice_id' => $invoice->id]);
                return redirect()->away($result['checkout_url']);
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to create GCash payment source.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'GCash payment failed. Please try again.');
        }
    }

    public function payWithCard()
    {
        try {
            // Ensure draft is saved before redirecting to payment
            if (!$this->draftConsultationId) {
                $this->saveFormDataToDraft();
            }
            
            $invoice = $this->getOrCreateReservationInvoice();
            
            // Link draft to invoice
            if ($this->draftConsultationId) {
                session(['draft_consultation_for_invoice_' . $invoice->id => $this->draftConsultationId]);
            }
            
            $payMongo = new PayMongoService();
            $nextUrl = url('/client/book-consultation/' . $this->lawyer_id);
            // Pass next param to card payment page
            return redirect()->route('client.payment.card', ['invoice' => $invoice->id, 'redirect' => $nextUrl]);
        } catch (\Throwable $e) {
            session()->flash('error', 'Card payment failed. Please try again.');
        }
    }

    private function handlePaymentSuccess(string $method)
    {
        $this->reservationPaid = true;
        session(['reservation_paid_for_booking' => true]);
        $this->showPaymentModal = false;
        session()->flash('message', 'Reservation paid successfully via ' . ($method === 'gcash' ? 'GCash' : 'Card') . '.');
    }

    public function render()
    {
        return view('livewire.client.book-consultation')
            ->layout('components.layouts.app', [
                'header' => 'Book a Consultation',
                'title' => 'Book a Consultation'
            ]);
    }
} 