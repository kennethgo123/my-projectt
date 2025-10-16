# Solutions

## UI/UX Solutions

1. **Modal Implementation Pattern**
   - **Problem**: Modals triggered via event dispatching (`$dispatch('open-modal', 'modal-name')`) were not reliably opening.
   - **Solution**: Use direct Alpine.js state management with `x-data="{ open: false }"` and `@click="open = true"` to control modal visibility.
   - **Implementation**:
     ```html
     <div x-data="{ open: false }">
         <button @click="open = true">Open Modal</button>
         
         <div x-show="open" class="modal">
             <!-- Modal content -->
             <button @click="open = false">Close</button>
         </div>
     </div>
     ```
   - **Components Fixed**: CasePhaseTracker
   - **Date Implemented**: Current day

---

20. Livewire actions not firing on `/lawyer/consultations` (buttons unresponsive)
   - **Problem**: Livewire click handlers (e.g., "Complete", "View Contract") did not trigger. Root cause was missing CSRF meta tag in the custom layout used by the consultations page, causing Livewire requests to miss the CSRF header.
   - **Solution**: Added CSRF meta tag to the layout so Livewire can attach the `X-CSRF-TOKEN` header.
   - **Implementation Details**:
     - In `resources/views/components/layouts/app.blade.php`, added:
       ```html
       <meta name="csrf-token" content="{{ csrf_token() }}">
       ```
   - **Files Modified**:
     - `resources/views/components/layouts/app.blade.php`
   - **Date Implemented**: Current day

2. **Consultation Type Handling**
   - **Problem**: Inconsistent values for consultation types in the database making conditional displays unreliable.
   - **Solution**: Use multiple OR conditions in blade templates to check for all possible values.
   - **Implementation**:
     ```php
     @if(($consultation->consultation_type === 'in_house' || 
          $consultation->consultation_type === 'inhouse' || 
          $consultation->consultation_type === 'In-House Consultation'))
         <!-- Show in-house specific content -->
     @endif
     ```
   - **Components Fixed**: ManageConsultations
   - **Date Implemented**: Recent

3. **Invoice Management Form Simplification**
   - **Problem**: The invoice creation form required a case selection which was causing validation errors.
   - **Solution**: 
     1. Removed the case selection field from the form
     2. Created a migration to make `legal_case_id` nullable in the invoices table
     3. Updated the validation rules to make case selection optional
   - **Implementation**:
     ```php
     // Migration to make legal_case_id nullable
     Schema::table('invoices', function (Blueprint $table) {
         $table->dropForeign(['legal_case_id']);
         $table->foreignId('legal_case_id')->nullable()->change();
         $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('set null');
     });
     
     // Updated validation rules
     'selectedCase' => 'nullable', // Changed from 'required_if:editMode,false'
     ```
   - **Components Fixed**: InvoiceManagement (Livewire component and blade view)
   - **Date Implemented**: 2025-05-11

## Data Handling Solutions

1. **Consultation Type Standardization**
   - **Problem**: Multiple different values representing the same consultation type.
   - **Long-term Solution**: Database migration to standardize all values (pending).
   - **Interim Solution**: Use conditional checks in templates and controllers.

## Code Organization Solutions

1. **Component Structure for Reusability**
   - **Problem**: Duplicated code across different views for similar functionality.
   - **Solution**: Extract common functionality into shared Livewire components.
   - **Example**: CasePhaseTracker component reused across lawyer and client interfaces.

7. **Fixed Close Case Modal Issue**
   - **Problem**: The close case modal was appearing even after a case had been closed, causing an error when attempting to close an already closed case.
   - **Solution**: Modified the Close Case button display condition in case-setup.blade.php to check the case status and not show the button if the case is already completed.
   - **Implementation**:
     ```php
     x-show="isLastPhase && !{{ $isReadOnly ? 'true' : 'false' }} && '{{ $case->status }}' !== 'completed'"
     ```
   - **Files Modified**: 
     - `resources/views/livewire/lawyer/case-setup.blade.php`
     - `app/Livewire/Components/CasePhaseTracker.php`
   - **Date Implemented**: Current day

8. **Refactor CasePhaseTracker and Modal Logic for Case Statuses**
   - **Problem**: `PublicPropertyNotFoundException` in `CasePhaseTracker` and inconsistent handling of 'closed' vs 'completed' case statuses for modal display.
   - **Solution**:
     1. Modified `CasePhaseTracker` to accept `caseId` in its `mount` method and fetch the `LegalCase` model internally. This simplifies data passing from the parent `CaseSetup` component and potentially avoids hydration issues with complex case objects.
     2. Updated `CasePhaseTracker::checkIfLastPhase()` and the `x-show` condition in `case-setup.blade.php` for the "Close Case" button to correctly check for both `LegalCase::STATUS_COMPLETED` ('completed') and `LegalCase::STATUS_CLOSED` ('closed') statuses, ensuring the modal does not appear if the case is already in either of these states.
   - **Implementation Details**:
     - `CasePhaseTracker.php` `mount` method changed from `mount(LegalCase $case, ...)` to `mount($caseId, ...)` with internal `LegalCase::findOrFail($caseId)`.
     - `case-setup.blade.php` changed calls from `<livewire:components.case-phase-tracker :case="$case" ...>` to `<livewire:components.case-phase-tracker :caseId="$case->id" ...>`.
     - Conditional checks for status now include `|| $this->case->status === LegalCase::STATUS_CLOSED` in PHP and `&& '$case->status' !== 'closed'` in Blade's `x-show`.
   - **Files Modified**:
     - `app/Livewire/Components/CasePhaseTracker.php`
     - `resources/views/livewire/lawyer/case-setup.blade.php`
   - **Date Implemented**: Current day 

9. **Fix Close Case Modal Automatic Opening**
   - **Problem**: The close-case-modal was appearing automatically when opening any case, even if the case was already closed, and a PublicPropertyNotFoundException error was occurring.
   - **Solution**: Implemented a JavaScript solution that ensures the close-case-modal only opens when explicitly triggered by a user clicking the "Close Case" button.
   - **Implementation Details**:
     - Kept the modal definition in CasePhaseTracker component but added prevention mechanism in case-setup.blade.php
     - Used x-init to override the addEventListener method for 'open-modal' events
     - Added a custom flag (userInitiated) to events from user clicks
     - Only allowed close-case-modal to open if the event was userInitiated
     - This prevents the modal from appearing automatically when the page loads
   - **Files Modified**:
     - resources/views/livewire/lawyer/case-setup.blade.php`
     - `resources/views/livewire/components/case-phase-tracker.blade.php`
   - **Date Implemented**: Current day 

10. **Fixed Close Case Modal Auto-Opening and Livewire Property Error**
   - **Problems**:
     1. The close-case-modal was appearing automatically when opening any case, even if the button wasn't clicked
     2. A Livewire error: "Unable to set component data. Public property [$] not found on component: [components.case-phase-tracker]"
   - **Solution**:
     1. Modified the x-modal component to check for a userInitiated flag on open-modal events for the close-case-modal
     2. Changed the CasePhaseTracker component to accept a caseId parameter instead of a full case object
   - **Implementation Details**:
     - In modal.blade.php, added a condition to check for userInitiated flag:
       ```php
       $root.addEventListener('open-modal', event => {
           if (@json(isset($name)) && event.detail === '{{ $name }}') {
               // For close-case-modal, only open if userInitiated flag is true
               if ('{{ $name }}' === 'close-case-modal' && !event.userInitiated) {
                   // Don't auto-open the close case modal
                   return;
               }
               show = true;
           }
       });
       ```
     - In CasePhaseTracker.php, changed:
       ```php
       // From:
       public LegalCase $case;
       public function mount($case, $readOnly = false)
       
       // To:
       public $case;  // No longer type-hinted
       public function mount($caseId, $readOnly = false)
       {
           $this->case = LegalCase::findOrFail($caseId);
           // Rest of the method remains the same
       }
       ```
   - **Files Modified**:
     - `resources/views/components/modal.blade.php`
     - `app/Livewire/Components/CasePhaseTracker.php`
   - **Date Implemented**: Current day 

11. **Guard Entangle in Generic Modal Component**
   - **Problem**: The generic `<x-modal>` component blindly used `@entangle($attributes->wire('model')).live`, even when no `wire:model` attribute was present, causing Livewire to attempt binding to an empty property and throw a `PublicPropertyNotFoundException` for `[$]`.
   - **Solution**: Updated `resources/views/components/modal.blade.php` to conditionally apply `@entangle()` only when a `wire:model` attribute is given; otherwise default `show` to `false`.
   - **Implementation Details**:
     ```blade
     <!-- Before: -->
     x-data="{ show: @entangle($attributes->wire('model')).live, ... }"
     <!-- After: -->
     x-data="{ show: @if($attributes->has('wire:model')) @entangle($attributes->wire('model')).live @else false @endif, ... }"
     ```
   - **Files Modified**: `resources/views/components/modal.blade.php`
   - **Date Implemented**: Current day 

12. **Fixed Close Case Button and Modal Issues**
   - **Problem**: Two issues with the Close Case functionality:
     1. The Close Case button was appearing before the case was truly at its last phase (even when previous phases weren't completed)
     2. Clicking the Close Case button did not properly trigger the closeCase method in the Livewire component
   - **Solution**: 
     1. Modified the logic in `checkIfLastPhase()` to ensure the button only appears when all previous phases are completed
     2. Enhanced the form submission to properly trigger the Livewire action
   - **Implementation Details**:
     ```php
     // Before:
     $this->isLastPhase = ($currentPhaseOrder === $maxPhaseOrder);

     // After:
     if ($currentPhaseOrder === $maxPhaseOrder) {
        // Check if all previous phases are completed
        $allPreviousPhasesCompleted = $this->phases
            ->where('order', '<', $maxPhaseOrder)
            ->every(function($phase) {
                return $phase->is_completed;
            });
            
        $this->isLastPhase = $allPreviousPhasesCompleted;
     } else {
        $this->isLastPhase = false;
     }
     ```
     And in the modal:
     ```html
     <!-- Before -->
     <form wire:submit.prevent="closeCase">
       ...
       <x-danger-button type="submit" class="ml-3" wire:loading.attr="disabled">
     
     <!-- After -->
     <form wire:submit="closeCase">
       ...
       <x-danger-button type="submit" class="ml-3" wire:loading.attr="disabled" wire:click="closeCase">
     ```
   - **Files Modified**:
     - `app/Livewire/Components/CasePhaseTracker.php`
     - `resources/views/livewire/components/case-phase-tracker.blade.php`
   - **Date Implemented**: Current day

13. **"Setup Finished" Button Auto-hide on Click**
   - **Problem**: The "Setup Finished" button remained visible after clicking, until page refresh
   - **Solution**: Added Alpine.js state to track when the button is clicked and hide it immediately
   - **Implementation Details**:
     ```blade
     <!-- Before -->
     <div class="mt-8 border-t border-gray-200 pt-6 flex justify-center">
         <button wire:click="markSetupComplete" ... >
     
     <!-- After -->
     <div class="mt-8 border-t border-gray-200 pt-6 flex justify-center" x-data="{ setupButtonClicked: false }">
         <button x-show="!setupButtonClicked" @click="setupButtonClicked = true" wire:click="markSetupComplete" ... >
     ```
   - This provides better UX by immediately hiding the button rather than waiting for the server to respond and refresh the page

14. **Fixed Admin Dashboard Sales Panel Error**
   - **Problem**: The admin dashboard was throwing an "Undefined variable $salesStats" error because the sales panel section was moved to a dedicated page, but the HTML for the sales panel was still in the dashboard view referencing removed variables.
   - **Solution**: Removed the entire Sales Panel section from the dashboard blade template, while keeping the link to the dedicated sales panel page.
   - **Implementation Details**:
     - Removed all HTML for the sales statistics cards and invoice table from the dashboard view
     - Kept only the "View Sales Panel" button that links to the dedicated sales panel page
   - **Files Modified**:
     - `resources/views/livewire/admin/dashboard.blade.php`
   - **Date Implemented**: Current day

15. **Fixed Law Firm Lawyer Selection in Book Consultation**
   - **Problem**: When a client tried to book a consultation with a law firm, selecting a specific lawyer worked, but the "Let the firm decide" option was not selectable due to issues with Livewire binding to an empty string value.
   - **Solution**: Changed the value of the "Let the firm decide" radio button from `""` to `"__default__"` and updated the component logic to explicitly check for this value.
   - **Implementation Details**:
     ```blade
     <!-- Changed value in book-consultation.blade.php -->
     <input type="radio" 
            name="lawyer_selection" 
            value="__default__" 
            wire:model.live="selectedLawyerId"
            ...
     >
     ```
     ```php
     // Updated updatedSelectedLawyerId() method
     public function updatedSelectedLawyerId()
     {
         if ($this->selectedLawyerId === "__default__" || $this->selectedLawyerId === null) {
             $this->selectedLawyerId = null; // Treat __default__ as null internally
         }
         // ... (rest of the method remains the same)
     }
     ```
   - **Files Modified**:
     - `resources/views/livewire/book-consultation.blade.php`
     - `app/Livewire/BookConsultation.php`
   - **Date Implemented**: Current day

16. **Notification System Error: "Call to a member function create() on array"**
    - **Problem**: Persistent "Call to a member function create() on array" error originating from `NotificationService.php` when trying to send database notifications, specifically `User::routeNotificationForDatabase()` returning an array.
    - **Solution**:
        1.  In `app/Models/User.php`:
            *   Renamed `databaseNotifications()` (which used `App\Extensions\DatabaseNotification`) to `notifications()`.
            *   Renamed `unreadDatabaseNotifications()` to `unreadNotifications()`.
            *   Renamed the original `notifications()` method (which used `AppNotification`) to `appNotifications()`.
            *   Removed the problematic `routeNotificationForDatabase()` method. This allows Laravel's `Notifiable` trait to use the default `notifications()` relationship.
        2.  Updated `app/Livewire/Components/NotificationDropdown.php` to use the new `notifications()` and `unreadNotifications()` method names.
        3.  Updated `resources/views/livewire/components/notification-dropdown.blade.php` to access notification data via `$notification->data['property']` (e.g., `$notification->data['title']`) instead of directly (`$notification->title`), as `DatabaseNotification` stores custom data in a JSON `data` column. Also updated `markAsRead` in `NotificationDropdown.php` to use `$notification->data['action_url']`.
    - **Files Modified**:
        - `app/Models/User.php`
        - `app/Livewire/Components/NotificationDropdown.php`
        - `resources/views/livewire/components/notification-dropdown.blade.php`

---

19. Fix "Complete" button not responding on `/lawyer/consultations`
   - **Problem**: Clicking the "Complete" button did nothing due to an implicit submit inside surrounding forms interfering with the Livewire `wire:click` event.
   - **Solution**: Added `type="button"` to the Complete action button to ensure it doesn't submit a parent form and that the Livewire method `showCompleteForm` is invoked.
   - **Implementation Details**:
     - In `resources/views/livewire/lawyers/manage-consultations.blade.php`, updated the button:
       ```html
       <button type="button" wire:click="showCompleteForm({{ $consultation->id }})" ...>
       ```
   - **Files Modified**:
     - `resources/views/livewire/lawyers/manage-consultations.blade.php`
   - **Date Implemented**: Current day
        - `app/Services/NotificationService.php` (try-catch blocks and type-hinting added in earlier attempts)
    - **Date Implemented**: Current day

17. **Added "Start Case" Button for Lawyers after Completed Consultation**
    - **Feature**: Implemented a "Start Case" button on the `/lawyer/consultations` page. This button appears for consultations that have been marked as 'completed'.
    - **Implementation Details**:
        - The `app/Livewire/Lawyers/ManageConsultations.php` component already had:
            - A `showStartCaseModal` boolean property.
            - A `showStartCaseForm($consultationId)` method to prepare and show the modal.
            - A `startCase()` method to handle form submission (validation, contract upload, `LegalCase` creation with `contract_sent` status, and `caseStarted` notification).
        - The `completeConsultation()` method in the same component sets a `can_start_case` flag on the `Consultation` model.
        - Modified `resources/views/livewire/lawyers/manage-consultations.blade.php`:
            - Added a "Start Case" button within the `@if($consultation->status === 'completed' && $consultation->can_start_case)` block.
            - The button calls `wire:click="showStartCaseForm({{ $consultation->id }})"`.
            - The button includes an appropriate icon and styling (purple color).
    - **Files Modified**:
        - `resources/views/livewire/lawyers/manage-consultations.blade.php`
        - `app/Livewire/Lawyers/ManageConsultations.php` (verified existing logic was sufficient)
    - **Date Implemented**: Current day

18. **Changed "Start Case" to "Review Contract" when Contract is Sent**
    - **Feature**: On the `/lawyer/consultations` page, if a lawyer has already started a case from a completed consultation and a contract has been sent (or the case is in a subsequent state), the "Start Case" button now changes to "Review Contract".
    - **Implementation Details**:
        - **`app/Models/Consultation.php`**:
            - Verified the existing `case()` relationship (`hasOne(LegalCase::class)`).
        - **`app/Livewire/Lawyers/ManageConsultations.php`**:
            - Eager loaded the `case` relationship in the `render()` method: `->with([..., 'case'])`.
            - Added new public properties for the review modal: `showReviewContractModal`, `reviewCaseTitle`, `reviewCaseDescription`, `reviewContractPath`, `selectedConsultationForReview`.
            - Created `showReviewContractModal($consultationId)` method: Fetches consultation with its case and client profile, populates review properties, and sets `showReviewContractModal = true`.
        - **`resources/views/livewire/lawyers/manage-consultations.blade.php`**:
            - Conditionally displayed buttons: If `$consultation->case` exists (and its status indicates a started case like `contract_sent`, `active`, etc.), a "Review Contract" button (teal color, eye icon) is shown, calling `showReviewContractModal`.
            - Otherwise, the original "Start Case" button is shown.
            - Added a new modal for "Review Contract" (`z-index-20`): Displays case title, description (read-only), client info, and a link to view the contract document via `Storage::url($reviewContractPath)`. Includes a "Close" button.
    - **Files Modified**:
        - `app/Livewire/Lawyers/ManageConsultations.php`
        - `resources/views/livewire/lawyers/manage-consultations.blade.php`
        - `app/Models/Consultation.php` (verified relationship)
    - **Date Implemented**: Current day

19. **Added "Law Firm as Entity" Option in Book Consultation**
   - **Problem**: Clients could either select a specific lawyer or let the firm decide when booking a consultation with a law firm, but there was no option to explicitly assign the law firm as an entity.
   - **Solution**: Added a new radio button option below "Let the firm decide" that assigns the law firm as an entity on the consultation.
   - **Implementation Details**:
     1. Added new constants in the BookConsultation component for the selection options:
        ```php
        const SELECT_FIRM_DECIDE = "__default__"; // Let the firm decide
        const SELECT_FIRM_ENTITY = "__firm__";    // The law firm as an entity
        ```
     2. Updated the updatedSelectedLawyerId method to handle the new option:
        ```php
        if ($this->selectedLawyerId === self::SELECT_FIRM_DECIDE || $this->selectedLawyerId === null) {
            // Let the firm decide logic
        } else if ($this->selectedLawyerId === self::SELECT_FIRM_ENTITY) {
            // Law firm as entity logic - load the firm's availability
            $this->loadAvailableDays();
        } else {
            // Specific lawyer logic
        }
        ```
     3. Added a new check in submitConsultation to set the new assign_as_entity flag:
        ```php
        if ($this->isLawFirm && $this->selectedLawyerId === self::SELECT_FIRM_ENTITY) {
            $assignAsEntity = true;
        }
        ```
     4. Created a migration to add the necessary database fields:
        ```php
        Schema::table('consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('specific_lawyer_id')->nullable();
            $table->boolean('assign_as_entity')->default(false);
            
            $table->foreign('specific_lawyer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
        ```
     5. Added the new option in the blade template:
        ```blade
        <!-- Law Firm as Entity option -->
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="lawyer-select-firm" 
                    type="radio" 
                    name="lawyer_selection" 
                    value="__firm__" 
                    wire:model.live="selectedLawyerId"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
            </div>
            <div class="ml-3 text-sm">
                <label for="lawyer-select-firm" class="font-medium text-gray-700 cursor-pointer font-raleway">{{ $lawyerName }}</label>
                <p class="text-gray-500 font-open-sans">Assign the firm on your consultation as an entity</p>
            </div>
        </div>
        ```
   - **Key Benefits**:
     - Gives clients the explicit option to associate their consultation with the law firm as an entity
     - Provides flexibility in how consultations are assigned and displayed
     - Uses the law firm's availability schedule directly
   - **Files Modified**:
     - `app/Livewire/Client/BookConsultation.php`
     - resources/views/livewire/client/book-consultation.blade.php
     - Added migration: `database/migrations/2025_05_12_210202_add_assign_as_entity_to_consultations_table.php`
   - **Date Implemented**: Current day

20. **Fixed Law Firm Entity Time Slot Display in Book Consultation**
   - **Problem**: When selecting the "Law Firm as Entity" option in the book consultation form, available time slots were not being displayed.
   - **Solution**: Fixed the logic in the `loadTimeSlots()` method to properly handle the "__firm__" option value when retrieving available time slots.
   - **Root Cause Analysis**: The issue was in the conditional logic that determined which ID to use when loading time slots:
     ```php
     // Old code (problematic):
     $lawyerId = ($this->selectedLawyerId && $this->selectedLawyerId !== null) ? $this->selectedLawyerId : $this->lawyer_id;
     ```
     This code was checking if `selectedLawyerId` was truthy and not null, but it wasn't checking for the special value `__firm__`. When the law firm entity option was selected, `selectedLawyerId` was set to `__firm__`, which caused the system to look for a lawyer with ID `__firm__` instead of using the law firm's ID.
   - **Implementation Details**:
     ```php
     // New code (fixed):
     if ($this->selectedLawyerId && 
         $this->selectedLawyerId !== null && 
         $this->selectedLawyerId !== self::SELECT_FIRM_DECIDE && 
         $this->selectedLawyerId !== self::SELECT_FIRM_ENTITY) {
         $lawyerId = $this->selectedLawyerId;
     } else {
         $lawyerId = $this->lawyer_id;
     }
     ```
     The updated code explicitly checks for both special values (`__default__` and `__firm__`) and only uses `selectedLawyerId` if it's a regular lawyer ID.
   - **Additional Improvements**:
     - Added automatic creation of default availability for law firms without any available time slots
     - Added debugging information to help diagnose availability issues
   - **Files Modified**:
     - `app/Livewire/Client/BookConsultation.php`
   - **Date Implemented**: Current day

21. **Law Firm Consultation Management with Lawyer Assignment Feature**
   - **Problem**: Consultations sent to law firms weren't displaying in the law firm's consultation management view, especially when clients selected "Let the firm decide" or "Law Firm as Entity" options.
   - **Solution**: Updated the law firm's ManageConsultations component to properly display all consultations and added functionality for law firms to assign specific lawyers to consultations.
   - **Implementation Details**:
      1. Fixed the query in `LawFirm/ManageConsultations.php` to include consultations directed at the law firm itself:
         ```php
         $consultationsQuery = Consultation::where(function($query) use ($lawFirmId) {
             // Direct consultations to the law firm
             $query->where('lawyer_id', $lawFirmId)
                 // OR consultations for lawyers belonging to this firm
                 ->orWhereHas('lawyer', function ($subQuery) use ($lawFirmId) {
                     $subQuery->whereHas('lawyerProfile', function ($profileQuery) use ($lawFirmId) {
                         $profileQuery->where('law_firm_id', $lawFirmId);
                     });
                 });
         });
         ```
      2. Added lawyer assignment functionality with an assignment modal in the law firm view
      3. Updated the NotificationService to send notifications when lawyers are assigned to consultations
      4. Enhanced the UI to clearly show which consultations need lawyer assignment and which are assigned to the firm as an entity

22. **Fixed Lawyer Loading in Law Firm's Assign Lawyer Modal**
   - **Problem**: No lawyers were appearing in the "Select Lawyer" dropdown when a law firm tried to assign a lawyer to a consultation.
   - **Solution**: Updated the query that fetches lawyers associated with the firm to check all possible relationship paths in the database.
   - **Implementation Details**:
      1. Modified the `loadFirmLawyers` method in the `ManageConsultations` component to check multiple relationship paths:
         ```php
         $this->firmLawyers = User::where(function($query) use ($lawFirmId) {
             // Check for lawyers with law_firm_id in their profile
             $query->whereHas('lawyerProfile', function($profileQuery) use ($lawFirmId) {
                 $profileQuery->where('law_firm_id', $lawFirmId);
             })
             // OR check for lawyers with firm_id directly in users table
             ->orWhere('firm_id', $lawFirmId);
         })
         ->where('status', 'approved')
         ->whereHas('role', function($query) {
             $query->where('name', 'lawyer');
         })
         ->with('lawyerProfile')
         ->get();
         ```
      2. Added a fallback to the `LawFirmLawyer` model if no lawyers are found through the primary relationships
      3. Updated the Blade template to gracefully handle different types of lawyer profiles (regular profile, law firm lawyer, or just basic user data)

23. **Fixed Law Firm Consultation Assignment Issues**
   - **Problem**: Two issues with law firm consultation assignments:
     1. When a law firm assigned a lawyer to a consultation, the status remained "pending" instead of changing to "accepted"
     2. Assigned lawyers couldn't see consultations assigned to them through the specific_lawyer_id field in their own consultation management view
   - **Solution**:
     1. Updated the `assignLawyer` method in the LawFirm ManageConsultations component to change the consultation status to "accepted" when a lawyer is assigned
     2. Modified the Lawyers ManageConsultations component's query to include consultations where the lawyer is assigned via either the primary lawyer_id or specific_lawyer_id fields
   - **Implementation Details**:
     1. Updated the consultation status in the assignLawyer method:
        ```php
        $this->selectedConsultation->update([
            'specific_lawyer_id' => $this->assignedLawyerId,
            'assign_as_entity' => false,
            'status' => 'accepted', // Change status to accepted when assigned
        ]);
        ```
     2. Enhanced the lawyer's consultation query in the render method:
        ```php
        $consultations = Consultation::where(function($query) use ($userId) {
            // Directly assigned to the lawyer
            $query->where('lawyer_id', $userId)
            // OR specifically assigned to the lawyer by a law firm
            ->orWhere('specific_lawyer_id', $userId);
        })->latest()->paginate(10);
        ```
   - **Benefits**:
     1. Automatically marks consultations as accepted when a lawyer is assigned, improving the client experience
     2. Ensures assigned lawyers can see and manage consultations assigned to them by their law firm
     3. Creates a consistent experience between direct lawyer bookings and law firm assignments

16. **Fixed Law Firm Consultation Details Modal Error**
   - **Problem**: The law firm manage consultations page was throwing an "Undefined variable $consultationDetails" error because the property and methods needed for the consultation details modal were missing from the component class.
   - **Solution**: Added the missing properties and methods to the ManageConsultations Livewire component.
   - **Implementation Details**:
     - Added public properties for modal control and storing consultation details:
       ```php
       // Variables for consultation details modal
       public $showDetailsModal = false;
       public $consultationDetails = null;
       ```
     - Added method to fetch and display consultation details:
       ```php
       public function openDetailsModal($consultationId)
       {
           $this->consultationDetails = Consultation::with(['client.clientProfile'])->findOrFail($consultationId);
           $this->showDetailsModal = true;
       }
       ```
   - **Files Modified**:
     - `app/Livewire/LawFirm/ManageConsultations.php`
   - **Date Implemented**: [Current Date]

24. **Unified Law Firm Consultation Details and Assignment Interface**
   - **Problem**: The law firm consultation management had separate modals for viewing details and assigning lawyers, requiring multiple clicks and not allowing scheduling during assignment.
   - **Solution**: Combined the detail view and lawyer assignment into a single unified interface, allowing administrators to:
     1. View consultation details
     2. Select a lawyer
     3. Choose from client's preferred consultation times
     4. Accept and assign in one action
   - **Implementation Details**:
     ```php
     // Added new properties
     public $selectedDate = null;
     
     // New method to handle combined assignment with time selection
     public function assignLawyerWithTime()
     {
         // Validate selections
         if (!$this->selectedDate) {
             session()->flash('error', 'Please select a consultation time.');
             return;
         }
         
         // Update consultation with both lawyer and selected time
         $this->consultationDetails->update([
             'specific_lawyer_id' => $this->assignedLawyerId,
             'selected_date' => $this->selectedDate,
             'status' => 'accepted',
         ]);
         
         // Rest of method...
     }
     ```
     The interface combines:
     - Complete consultation details view
     - Lawyer selection dropdown
     - Radio buttons for preferred consultation times
     - Single "Assign & Accept" button
     
   - **UX Improvements**:
     - Changed "View Details" button to "Manage" for unassigned consultations
     - Removed separate "Assign Lawyer" button and modal
     - Added radio button selection for client's preferred dates
     - Streamlined the workflow to reduce clicks and improve efficiency
     
   - **Files Modified**:
     - `app/Livewire/LawFirm/ManageConsultations.php`
     - `resources/views/livewire/law-firm/manage-consultations.blade.php`
   - **Date Implemented**: [Current Date]

25. **Fixed Law Firm Consultation Assignment and Visibility Issues**
   - **Problem**: Two critical issues were identified:
     1. When a law firm assigned a lawyer to a consultation, the assignment wasn't persisting in the database
     2. Assigned consultations weren't showing up in the individual lawyer's consultation list
   - **Solution**:
     1. Added `specific_lawyer_id` and `assign_as_entity` to the `$fillable` array in the Consultation model
     2. Improved the Law Firm's ManageConsultations component:
        - Added better data refresh mechanisms
        - Used fresh model instances instead of stale ones
        - Added error checking and debugging information
     3. Enhanced the consultation query in the render method to include consultations assigned via `specific_lawyer_id`
   - **Implementation Details**:
     ```php
     // 1. Added to Consultation model's $fillable array:
     'specific_lawyer_id',
     'assign_as_entity'
     
     // 2. Improved assignment method:
     $consultation = Consultation::findOrFail($this->consultationDetails->id);
     $updated = $consultation->update([
         'specific_lawyer_id' => $this->assignedLawyerId,
         'selected_date' => $this->selectedDate,
         'assign_as_entity' => false,
         'status' => 'accepted',
     ]);
     
     // 3. Enhanced query in LawFirm ManageConsultations render():
     ->orWhereExists(function($subQuery) use ($lawFirmId) {
         $subQuery->select(\DB::raw(1))
             ->from('users')
             ->whereColumn('users.id', 'consultations.specific_lawyer_id')
             ->whereHas('lawyerProfile', function($profileQuery) use ($lawFirmId) {
                 $profileQuery->where('law_firm_id', $lawFirmId);
             });
     });
     ```
   - **Key Benefits**:
     - Consultations are now properly assigned to lawyers when done from the law firm interface
     - Assigned consultations now appear in both the law firm's view and the individual lawyer's view
     - Added error checks and debugging to prevent silent failures
   - **Files Modified**:
     - `app/Models/Consultation.php`
     - `app/Livewire/LawFirm/ManageConsultations.php`
   - **Date Implemented**: [Current Date]

26. **Added "View Signature" Button and Improved Case Management UI**
   - **Problem**: Two UI issues needed to be addressed:
     1. There was no easy way for lawyers to view client signatures directly from the case list
     2. The "Setup Case" button needed to show "Manage Case" when a case status is active, and "Finish Setup" text needed to be removed
   - **Solution**:
     1. Added a "View Signature" button in the case actions that opens signatures in a new tab
     2. Changed "Setup Case" to "Manage Case" when status=active in the lawyer interface
     3. Changed "Setup Finished" button text to "Manage Case" in the case setup interface
   - **Implementation Details**:
     - Added a view signature button in the manage-cases.blade.php:
       ```php
       @if($case->signature_path)
           <button wire:click="viewSignature({{ $case->id }})" class="text-blue-600 hover:text-blue-900">
               View Signature
           </button>
       @endif
       ```
     - Added the viewSignature method in the ManageCases component:
       ```php
       public function viewSignature($caseId)
       {
           $this->selectedCase = LegalCase::findOrFail($caseId);
           
           if (!$this->selectedCase->signature_path) {
               // Try to find a contract action with a signature
               $contractAction = $this->selectedCase->contractActions()
                   ->where('action_type', 'accepted')
                   ->whereNotNull('signature_path')
                   ->latest()
                   ->first();
                   
               if ($contractAction && $contractAction->signature_path) {
                   // Open signature in a new tab
                   $this->dispatchBrowserEvent('open-signature', [
                       'url' => Storage::url($contractAction->signature_path)
                   ]);
               } else {
                   session()->flash('error', 'No signature found for this case.');
               }
               return;
           }
           
           // Open signature in a new tab
           $this->dispatchBrowserEvent('open-signature', [
               'url' => Storage::url($this->selectedCase->signature_path)
           ]);
       }
       ```
     - Updated case action button text in manage-cases.blade.php:
       ```php
       @if($case->status === 'closed' || $case->closed_at)
           View Case
       @elseif($case->status === 'active')
           Manage Case
       @else
           Setup Case
       @endif
       ```
   - **Key Benefits**:
     - Lawyers can now quickly view client signatures directly from the case list
     - UI is more consistent when cases are active, showing "Manage Case" instead of "Setup Case"
     - Improves user experience with clearer action buttons and terminology
   - **Files Modified**:
     - resources/views/livewire/lawyer/manage-cases.blade.php
     - app/Livewire/Lawyer/ManageCases.php
     - resources/views/livewire/lawyers/manage-cases.blade.php
     - resources/views/livewire/lawyer/case-setup.blade.php
   - **Date Implemented**: [Current Date]

27. **Removed Manage Case Button and Enhanced Signature Viewing**
   - **Problem**: Two critical issues needed to be fixed:
     1. There was an unwanted "Manage Case" button at the bottom of the lawyer case setup interface
     2. The "View Signature" functionality wasn't properly finding and displaying signatures 
   - **Solution**:
     1. Completely removed the "Setup Finished/Manage Case" button from the case-setup.blade.php file
     2. Enhanced the viewSignature method in ManageCases.php to check multiple possible signature locations:
        - Direct signature_path on the legal case
        - 'accepted' contract actions with signatures 
        - 'contract_signed' contract actions with signatures
        - Any contract action with a signature as a last resort
     3. Added try/catch error handling with detailed logs
   - **Implementation Details**:
     - Removed the entire "Setup Finished Button" section from case-setup.blade.php
     - Enhanced viewSignature method with 4 different lookup strategies
     - Added try/catch error handling with detailed logs
   - **Benefits**:
     - Cleaner interface without duplicate/conflicting buttons
     - More robust signature viewing that works across different signature storage scenarios
     - Better error handling and diagnostics for troubleshooting
   - **Files Modified**:
     - resources/views/livewire/lawyer/case-setup.blade.php
     - app/Livewire/Lawyer/ManageCases.php
   - **Date Implemented**: Current day

28. **Removed Start Case Button from Lawyer Consultations**
   - **Problem**: The "Start Case" button was appearing after a lawyer submits a contract at the lawyer consultations page, which was causing confusion and unwanted behavior.
   - **Solution**: Removed the "Start Case" button from the completed consultation section in the manage-consultations.blade.php file, while keeping the "Message Client" button for communication.
   - **Implementation Details**:
     - Modified the resources/views/livewire/lawyers/manage-consultations.blade.php file
     - Removed the button that used the showStartCaseForm action
     - Did not alter any other functionality
   - **Benefits**:
     - Eliminates confusion in the contract submission workflow
     - Prevents accidentally starting multiple cases for the same consultation
     - Keeps the interface clean and focused on necessary actions
   - **Files Modified**:
     - resources/views/livewire/lawyers/manage-consultations.blade.php
   - **Date Implemented**: Current day

29. **Fixed Client Contract Signature Not Saving to Legal Cases Table**
   - **Problem**: When clients signed contracts at the contract review page, the signature was saved to the contract_actions table but not to the signature_path field in the legal_cases table, causing the "View Signature" button to fail.
   - **Solution**: 
     1. Modified the ContractReview component to ensure signatures are properly saved to both tables
     2. Updated the updateCaseStatus method to accept and save the signature path to the legal_cases table
     3. Added a failsafe fallback in the createFallbackNotification method that double-checks and retrieves the signature if needed
   - **Implementation Details**:
     - Changed submitSignature method to pass the signature path to updateCaseStatus
     - Updated updateCaseStatus to set signature_path in the legal_cases table
     - Added detailed logging to track signature path updates
     - Created a redundant signature path update mechanism in the fallback notification method
   - **Benefits**:
     - Signatures are now reliably saved to both tables
     - "View Signature" button works properly for lawyers
     - Added redundant checks to prevent signature loss
     - Improved error logging for better debugging
   - **Files Modified**:
     - app/Livewire/Client/ContractReview.php
   - **Date Implemented**: Current day

30. **Added Enhanced View Signature Modal with Acknowledgment Statement**
   - **Problem**: The View Signature feature needed improvements:
     1. It opened signatures in a new tab without any legal acknowledgment message
     2. It needed to be accessible beside the Setup Case button for better UX
   - **Solution**: 
     1. Created a dedicated signature modal with the required legal acknowledgment text
     2. Added a View Signature button beside the Setup Case button in the interface
     3. Modified the viewSignature method to display in a modal rather than a new tab
   - **Implementation Details**:
     - Created a new case-signature-modal.blade.php template with:
       - Signature display area
       - Legal acknowledgment message: "I acknowledge receipt of the client's electronic signature for this specific contract only..."
       - Acknowledgment checkbox for lawyers to confirm
     - Added a dedicated "View Signature" button beside the Setup Case button
     - Updated the ManageCases component to show the modal instead of opening in a new tab
   - **Benefits**:
     - Lawyers now see the required legal disclaimer with signatures
     - Interface is more intuitive with a clear button next to Setup Case
     - Better UX with the signature and acknowledgment in a modal rather than new tab
     - Enhanced security by requiring explicit acknowledgment of signature usage restrictions
   - **Files Modified**:
     - resources/views/livewire/lawyer/partials/case-signature-modal.blade.php (created)
     - resources/views/livewire/lawyers/manage-cases.blade.php
     - app/Livewire/Lawyer/ManageCases.php 
     - resources/views/livewire/lawyer/manage-cases.blade.php
   - **Date Implemented**: Current day

31. **Added Case Creation and Signature Functionality for Law Firms**
   - **Problem**: Law firms didn't have the ability to start cases from completed consultations or view/acknowledge client signatures
   - **Solution**: 
     1. Added case creation functionality for law firms that matches the lawyer's implementation
     2. Added signature acknowledgment functionality for law firms
   - **Implementation Details**:
     - Added methods to `ManageConsultations.php` for law firms:
       - showStartCaseForm() to display the case creation modal
       - startCase() to save the new case with contract document
     - Added methods to `ManageCases.php` for law firms:
       - viewContract() to display the uploaded contract
       - viewSignature() to show client signature
       - acknowledgeSignature() to fulfill legal requirements
     - Created UI components:
       - Start Case button in the consultations list
       - Start Case modal with title, description, and contract upload
       - Signature viewing modal with legal acknowledgment
       - Contract viewing modal
   - **Benefits**:
     - Law firms can now convert consultations to cases directly
     - Consistent experience between lawyers and law firms
     - Complete e-signature handling for legal compliance
   - **Files Modified**:
     - app/Livewire/LawFirm/ManageConsultations.php
     - resources/views/livewire/law-firm/manage-consultations.blade.php
     - app/Livewire/LawFirm/ManageCases.php
     - resources/views/livewire/law-firm/manage-cases.blade.php
   - **Date Implemented**: Current day

## Replacing Google Maps with OpenStreetMap

The lawyer and law firm profile optimization pages were updated to use OpenStreetMap instead of Google Maps for sharing location links:

1. Changed the UI to show instructions for creating OpenStreetMap location links
2. Updated the placeholder to show an example OpenStreetMap URL
3. Updated the field name from `google_maps_link` to `openstreetmap_link` in the Livewire components 
4. For backward compatibility, we still store the link in the `google_maps_link` column in the database
5. Updated descriptive text to refer to OpenStreetMap instead of Google Maps

### Enhanced OpenStreetMap Integration with Interactive Map Preview

We further improved the OpenStreetMap integration by adding an interactive map preview:

1. Added the Leaflet.js library for OpenStreetMap integration
2. Created a live map preview that shows the location when a valid OpenStreetMap URL is entered
3. The map updates in real-time as the user types or pastes an OpenStreetMap URL
4. The map preview supports multiple OpenStreetMap URL formats (both mlat/mlon format and the #map= format)
5. Added this feature to both the lawyer and law firm profile optimization pages
6. The map uses proper event handling to detect changes from both direct user input and Livewire updates

This enhancement provides visual confirmation that the location link is correct and improves the user experience when sharing office locations.

## Location Mapping with Interactive Pin Placement

We completely redesigned how location selection works in the lawyer and law firm profile optimization pages:

1. **Removed the OpenStreetMap link input field and replaced it with a direct map selection:**
   - Users can now pin their exact office location directly on an interactive map
   - The location coordinates (latitude/longitude) are stored directly in the database
   - Added new database fields `lat` and `lng` to both lawyer_profiles and law_firm_profiles tables

2. **Added advanced mapping features:**
   - Interactive map with click-to-place-pin functionality
   - Draggable pins for precise location adjustment
   - Search box to quickly find locations by address or name
   - Zoom-level accuracy indicator that shows when the map is zoomed enough for precise location picking
   - Coordinate display showing the current selected position

3. **Technical implementation:**
   - Used Leaflet.js as the map library
   - Integrated Leaflet-GeoSearch for address search functionality
   - Created a reusable JavaScript component in public/js/location-map.js
   - Added real-time data binding to Livewire with proper event handling

4. **User experience improvements:**
   - Visual confirmation of the selected location
   - Accuracy indicator to encourage proper zoom level
   - Search functionality to quickly find addresses
   - Simplified workflow - no need to copy/paste links

5. **Migration strategy:**
   - Added backward compatibility to continue supporting existing `google_maps_link` data
   - Added new database fields without breaking existing functionalities
   - Created migration with nullable fields to allow gradual transition

This enhancement creates a much more intuitive, user-friendly way for lawyers and law firms to specify their office location.

## OpenStreetMap Integration in Nearby Lawyers View

To improve the user experience when viewing lawyer locations, we updated the nearby-lawyers component to use OpenStreetMap for viewing lawyer locations:

1. **Replaced Google Maps links with OpenStreetMap links:**
   - Updated the office address links to generate OpenStreetMap URLs directly using stored latitude and longitude coordinates
   - Used the format `https://www.openstreetmap.org/?mlat={lat}&mlon={lng}&zoom=18` for optimal viewing experience
   - Implemented this for individual lawyers, law firm lawyers, and law firms in the listings

2. **Added backward compatibility:**
   - Implemented fallback to use the existing `google_maps_link` if coordinates (`lat` and `lng`) are not available
   - Used conditional logic to check for valid coordinates before generating OpenStreetMap links
   - This ensures a smooth transition as lawyer profiles are gradually updated with map coordinates

3. **Updated Alpine.js components:**
   - Modified the lawyer detail modal to use OpenStreetMap links as well
   - Implemented the same fallback mechanism in the detail view Alpine.js templates
   - Maintained consistent UI across all instances of address links

This enhancement completes the integration of OpenStreetMap throughout the platform, replacing Google Maps links with open-source, privacy-respecting OpenStreetMap links for all location features.

## Fixed Location Map Pinning in Law Firm Profile

Fixed an issue where the location map pinning functionality was not working in the law firm's optimize profile page:

1. **Identified the root cause:**
   - The law firm's profile page included all the necessary HTML elements and styles
   - The page was loading the location-map.js file correctly
   - The issue was that the initialization function (initializeLocationMap) wasn't being called

2. **Solution implemented:**
   - Added explicit initialization of the map in the law firm optimize profile blade view
   - Used a window.onload handler to ensure proper timing of initialization
   - Added a small timeout to ensure DOM elements are fully rendered before map initialization
   - Added console logging for debugging

3. **Technical details:**
   ```javascript
   window.onload = function() {
       if (typeof initializeLocationMap === 'function') {
           console.log('Initializing location map for law firm...');
           setTimeout(initializeLocationMap, 100);
       }
   };
   ```

4. **Verification:**
   - Confirmed that the law firm's optimize profile page now correctly shows the interactive map
   - Verified that users can search locations, place pins, and drag pins to adjust positions
   - Confirmed that coordinates are properly saved to the database

This fix ensures that law firms can specify their exact office location on the map, improving the overall user experience for both law firms and clients looking for nearby legal services.

## OpenStreetMap Integration in Nearby Lawyers View

To improve the user experience when viewing lawyer locations, we updated the nearby-lawyers component to use OpenStreetMap for viewing lawyer locations:

1. **Replaced Google Maps links with OpenStreetMap links:**
   - Updated the office address links to generate OpenStreetMap URLs directly using stored latitude and longitude coordinates
   - Used the format `https://www.openstreetmap.org/?mlat={lat}&mlon={lng}&zoom=18` for optimal viewing experience
   - Implemented this for individual lawyers, law firm lawyers, and law firms in the listings

2. **Added backward compatibility:**
   - Implemented fallback to use the existing `google_maps_link` if coordinates (`lat` and `lng`) are not available
   - Used conditional logic to check for the presence of coordinates before deciding which link to use

## Law Firm Location Mapping Fix

Fixed the law firm location mapping on the optimize-profile page:

1. **Fixed issue where the map wasn't loading on the law firm profile page:**
   - Removed the embedded map implementation and Javascript code from the law firm template
   - Added the exact same implementation from the lawyer's profile page, using the external location-map.js
   - Implemented proper event handlers to ensure map initialization
   - Connected the map correctly with Livewire bindings to store lat/lng values

2. **Approach:**
   - Used a consistent approach for both lawyers and law firms to maintain code reliability
   - Leveraged the existing location-map.js code to handle map initialization and interaction
   - Made sure the HTML structure matched exactly between the two implementations

31. **Added "Case Request Sent" Indicator for Law Firm Consultations**
   - **Problem**: After a law firm sends a contract to start a case, the "Start Case" button was still visible in the consultations list instead of showing a status indicator
   - **Solution**: Modified the law firm's manage-consultations.blade.php to check if a consultation already has an associated case and show "Case Request Sent" instead of the "Start Case" button
   - **Implementation Details**:
     - Added a conditional check in the actions column to display different UI based on whether a case exists
     - Used the same styled badge with checkmark icon as in the client view for consistency
     - Updated the component to eager load the case relationship for more efficient queries
   - **Key Benefits**:
     - Prevents accidental attempts to create multiple cases for the same consultation
     - Provides clear visual feedback about which consultations already have cases
     - Creates a consistent user experience between law firm and lawyer interfaces
   - **Files Modified**:
     - resources/views/livewire/law-firm/manage-consultations.blade.php
     - app/Livewire/LawFirm/ManageConsultations.php
   - **Date Implemented**: Current day

32. **Added Law Firm Case Details Feature**
   - **Problem**: Route `law-firm.case-details` was not defined, causing internal server error when law firms tried to view case details
   - **Solution**: Created a new CaseDetails component and view for law firms, modeled after the lawyer's implementation but simplified
   - **Implementation Details**:
     - Created new component `app/Livewire/LawFirm/CaseDetails.php`
     - Created view file `resources/views/livewire/law-firm/case-details.blade.php` 
     - Added route `/law-firm/case/details/{case}` with proper name in the web.php routes file
   - **Key Benefits**:
     - Law firms can now view detailed case information
     - Interface is consistent with the rest of the application
     - Security is maintained by checking authorization in the component
   - **Files Modified**:
     - app/Livewire/LawFirm/CaseDetails.php (created)
     - resources/views/livewire/law-firm/case-details.blade.php (created)
     - routes/web.php
   - **Date Implemented**: Current day

33. **Updated Client Cases Table to Display Accurate Lawyer/Law Firm Information**
   - **Problem**: The client case table was directly using the `first_name` and `last_name` fields from the `users` table for lawyers, instead of retrieving this information from the correct profile tables
   - **Solution**: Modified the client's manage-cases view to fetch lawyer names from the appropriate profile tables:
     - Individual lawyers: Getting first_name and last_name from lawyer_profiles table
     - Law firms: Getting firm_name from law_firm_profiles table
     - Law firm lawyers: Getting first_name and last_name from law_firm_lawyers table
   - **Implementation Details**:
     - Updated the Lawyer column in manage-cases.blade.php to check the user role and display appropriate name fields
     - Modified the ManageCases controller to eager load additional relationships (lawFirmProfile, lawFirmLawyer)
     - Updated the case details modal view to also display the correct lawyer information
     - Enhanced the viewDetails method to load all required relationships
   - **Key Benefits**:
     - Displays accurate lawyer/law firm information to clients
     - Maintains data consistency across the application
     - Improves user experience by showing the professional name as entered in profiles
   - **Files Modified**:
     - resources/views/livewire/client/manage-cases.blade.php
     - app/Livewire/Client/ManageCases.php
     - resources/views/livewire/client/partials/case-details-modal.blade.php
   - **Date Implemented**: Current day

34. **Added Case Setup and Management for Law Firms**
   - **Problem**: Law firms couldn't set up and manage cases like individual lawyers could
   - **Solution**: Implemented case setup and management functionality for law firms that matches the lawyer interface
   - **Implementation Details**:
     - Created LawFirm\CaseSetup component by extending the Lawyer\CaseSetup component
     - Added a route for law firm case setup at `/law-firm/case/setup/{case}`
     - Added "Setup Case" button in the law firm's case management interface
     - Modified the case authorization checks to allow law firms to manage cases assigned to both the firm and its lawyers
     - Implemented additional methods in ManageCases component for prioritization and label management
   - **Files Modified/Created**:
     - Created `/app/Livewire/LawFirm/CaseSetup.php`
     - Created `/resources/views/livewire/law-firm/case-setup.blade.php`
     - Updated `/app/Livewire/LawFirm/ManageCases.php`
     - Updated `/resources/views/livewire/law-firm/manage-cases.blade.php
     - Updated `/routes/web.php`
   - **Date Implemented**: Current day

35. **Enhanced Law Firm Case Management Capabilities**
   - **Problem**: Law firms needed the ability to view their lawyers' cases and manage them with the same privileges
   - **Solution**: Added comprehensive case management capabilities for law firms that match those available to lawyers
   - **Implementation Details**:
     - Updated the law firm's ManageCases component to display all cases from the firm's lawyers
     - Implemented proper name display fetching lawyer first_name and last_name from the lawyer_profiles table
     - Added case setup functionality for law firms to manage case phases, events, tasks, and documents
     - Allowed law firms to view and edit cases for any lawyer within their firm
     - Ensured that both the lawyer and their law firm have the same access privileges to cases
   - **Files Modified/Created**:
     - Updated `/app/Livewire/LawFirm/CaseSetup.php` with complete case management functionality
     - Updated `/app/Livewire/LawFirm/ManageCases.php` to display lawyer information from profiles
     - Enhanced `/resources/views/livewire/law-firm/manage-cases.blade.php` to show proper lawyer names and add Setup Case button
     - Added new route `/law-firm/case/setup/{case}` for law firm case setup

36. **Added Law Firm StartCase Component**
   - **Problem**: The law firm route `/law-firm/start-case` was defined but the component didn't exist, causing an Internal Server Error
   - **Solution**: Created a new StartCase component for law firms to start cases from consultations
   - **Implementation Details**:
     - Created a LawFirm\StartCase component similar to the Client\StartCase component but with added features
     - Added a contract file upload feature to allow law firms to directly send contracts when creating a case
     - Pre-filled the case form when creating from a consultation
     - Implemented proper status changes and notifications
     - Added relationship handling between consultations and cases
   - **Files Created**:
     - `/app/Livewire/LawFirm/StartCase.php`
     - `/resources/views/livewire/law-firm/start-case.blade.php`
   - **Date Implemented**: Current day

37. **Fixed Undefined $actionType Variable in Law Firm ManageCases Component**
   - **Problem**: The law firm's manage-cases.blade.php was including the lawyer's case-action-modal.blade.php partial, but the $actionType variable required by this partial was not defined in the LawFirm\ManageCases component
   - **Solution**: Added the missing properties and methods needed to handle case actions in the LawFirm\ManageCases component
   - **Implementation Details**:
     - Added the following properties to match the lawyer's implementation:
       ```php
       // Modal states
       public $showActionModal = false;
       public $showDetailsModal = false;
       
       // Action properties
       public $actionType = null; // 'accept', 'reject', 'upload_contract', 'add_update', 'mark_active'
       public $rejectionReason = '';
       public $contract = null;
       public $updateTitle = '';
       public $updateContent = '';
       public $updateVisibility = 'both';
       ```
     - Added the methods required for case actions:
       ```php
       public function showAction($caseId, $action)
       public function submitAction()
       protected function acceptCase()
       protected function rejectCase()
       protected function uploadContract()
       protected function addCaseUpdate()
       protected function markCaseActive()
       protected function resetAction()
       ```
   - **Files Modified**:
     - `/app/Livewire/LawFirm/ManageCases.php`
   - **Date Implemented**: Current day

38. **Fixed Multiple Root Elements in Law Firm ManageCases Component**
   - **Problem**: The law firm manage-cases.blade.php view had multiple root elements, causing Livewire to throw a MultipleRootElementsDetectedException: "Livewire only supports one HTML element per component"
   - **Solution**: Merged the two separate root `<div>` elements into a single root element
   - **Implementation Details**:
     - Original problematic code:
       ```html
       <div>
           {{-- Success is as dangerous as failure. --}}
       </div>
       
       <div>
           <div class="py-12">
               <!-- Rest of the content -->
           </div>
       </div>
       ```
     - Fixed code:
       ```html
       <div>
           {{-- Success is as dangerous as failure. --}}
           
           <div class="py-12">
               <!-- Rest of the content -->
           </div>
       </div>
       ```
   - **Files Modified**:
     - `/resources/views/livewire/law-firm/manage-cases.blade.php`
   - **Date Implemented**: Current day

39. **Added Finish Setup Button to Case Setup Pages**
   - **Problem**: Users needed a more prominent way to mark a case as active directly from the case setup page
   - **Solution**: Added a fixed "Finish Setup" button at the bottom of the case setup page for both lawyers and law firms
   - **Implementation Details**:
     - Added a prominent button that calls the markSetupComplete method
     - The button is only visible when the case is not in "active" or "closed" status and the user is not in read-only mode
     - The button is fixed at the bottom of the screen for high visibility
     - Added the button to both lawyer and law firm case setup pages
   - **Files Modified**:
     - `/resources/views/livewire/lawyer/case-setup.blade.php`
     - `/resources/views/livewire/law-firm/case-setup.blade.php`

40. **Fixed Multiple Root Elements in Case Setup Components**
   - **Problem**: The law-firm.case-setup component had multiple root elements due to the Finish Setup button being added as a separate element outside the main div, causing a Livewire error
   - **Solution**: Merged the Finish Setup button into the main component div for both lawyer and law firm case setup templates
   - **Implementation Details**:
     - Combined the two separate div elements into a single root div by moving the Finish Setup button inside the main div
     - Fixed the same issue in both lawyer and law firm components to maintain consistency
     - Error that was resolved: "Livewire only supports one HTML element per component. Multiple root elements detected for component"
   - **Files Modified**:
     - `/resources/views/livewire/lawyer/case-setup.blade.php`
     - `/resources/views/livewire/law-firm/case-setup.blade.php`

41. **Added Signature Acknowledgment Modal for Law Firms**
   - **Problem**: Law firms didn't have the same signature viewing and acknowledgment functionality that lawyers had, which is important for legal compliance
   - **Solution**: Implemented the same signature viewing and acknowledgment process for law firms that was already in place for lawyers
   - **Implementation Details**:
     - Created a new case-signature-modal.blade.php for law firms, based on the lawyer version
     - Enhanced the viewSignature method in the LawFirm ManageCases component to search for signatures in multiple places:
       - Direct signature_path on the legal case
       - Latest 'accepted' contract action with signature
       - Latest 'contract_signed' contract action with signature
       - Any contract action with signature as a last resort
     - Added the legal acknowledgment checkbox and statement
     - Updated the law firm's manage-cases.blade.php to include the new law firm signature modal
   - **Files Created/Modified**:
     - Created: `/resources/views/livewire/law-firm/partials/case-signature-modal.blade.php`
     - Modified: `/app/Livewire/LawFirm/ManageCases.php`
     - Modified: `/resources/views/livewire/law-firm/manage-cases.blade.php`

42. **Added Missing Close Case Modal**
   - **Problem**: The "Close Case" button in the lawyer's case setup page (/lawyer/case/setup/6) didn't work because the close-case-modal was missing
   - **Solution**: Added the missing modal and necessary form elements along with the corresponding component properties and validation
   - **Implementation Details**:
     - Added a close-case-modal with a form to confirm case closure and collect a closing note
     - Added caseCloseNote property to the CaseSetup Livewire component
     - Updated the validation rules for the closing note
     - Modified the closeCase method to validate the closing note and use it in the case update
     - The modal now properly opens when the Close Case button is clicked and submits the form with the wire:submit.prevent="closeCase" handler
   - **Files Modified**:
     - `/resources/views/livewire/lawyer/case-setup.blade.php`
     - `/app/Livewire/Lawyer/CaseSetup.php`

43. **Fixed Close Case Button in CasePhaseTracker Component**
   - **Problem**: The Close Case button in the case-phase-tracker component didn't work properly because of form submission issues
   - **Solution**: Fixed the form submission in the close-case-modal by updating the wire:submit directive and removing redundant wire:click attribute
   - **Implementation Details**:
     - Changed `wire:submit="closeCase"` to `wire:submit.prevent="closeCase"` to properly prevent the default form submission
     - Removed the redundant `wire:click="closeCase"` attribute from the submit button, as it was causing conflicting actions
     - This fixes the issue where clicking the Close Case button didn't properly trigger the form submission
   - **Files Modified**:
     - `/resources/views/livewire/components/case-phase-tracker.blade.php`
   - **Key Benefits**:
     - The Close Case button now properly triggers the form submission and validation
     - Prevents double-submission issues that could occur from having both wire:submit and wire:click handlers
     - Makes the code cleaner and follows Livewire best practices for form handling
   - **Date Implemented**: Current day

44. **Simplified Close Case Functionality in Lawyer and Law Firm Interfaces**
   - **Problem**: The Close Case button was not working reliably because it depended on complex phase tracking logic and conflicting modal implementations.
   - **Solution**: Completely redesigned the Close Case functionality to be simpler and more direct:
     1. Removed the dependency on the CasePhaseTracker component for closing cases
     2. Added a simple, always-visible Close Case button that appears when case is not already closed/completed
     3. Created a dedicated modal with a unique name ('simple-close-case-modal') to avoid conflicts
     4. Updated the closeCase methods in both Lawyer and LawFirm CaseSetup components to be more consistent
   - **Implementation Details**:
     - Removed complex phase-tracking logic that determined when the Close Case button should be visible
     - Simplified button display condition to `@if(!$isReadOnly && ($case->status !== 'completed' && $case->status !== 'closed'))`
     - Added direct modal in the CaseSetup components rather than relying on child component modals
     - Updated both component closeCase methods to validate the note, update case status, and redirect correctly
   - **Files Modified**:
     - `resources/views/livewire/lawyer/case-setup.blade.php`
     - `resources/views/livewire/law-firm/case-setup.blade.php`
     - `app/Livewire/Lawyer/CaseSetup.php`
     - `app/Livewire/LawFirm/CaseSetup.php`
   - **Key Benefits**:
     - More reliable Close Case functionality that doesn't depend on phase tracking
     - Cleaner, more straightforward implementation
     - Better user experience by making the button available whenever the case is not closed
     - Consistent behavior between lawyer and law firm interfaces
   - **Date Implemented**: Current day

45. **Refactor: Centralized Phase and Close Case UI/Logic in CasePhaseTracker**
   - All phase and close-case UI and modal logic is now handled exclusively by the CasePhaseTracker Livewire component.
   - Parent case-setup views for lawyers and law firms only render the tracker; they no longer contain any phase or close-case UI or modals.
   - All modal opening is now done via Livewire methods (using wire:click and dispatch), not Alpine.js.
   - The UI and user experience remain unchanged, but the code is now much simpler and more reliable.

46. **Fixed Non-Working Phase and Close Case Buttons**
   - **Problem**: In the CasePhaseTracker component, buttons for "Update Phase", "Edit Phase", and "Close Case" did not work, while the "+ Phase" button did work. This inconsistency was occurring because the working button used direct Alpine.js `@click` handling while the non-working buttons used `wire:click`.
   - **Solution**: 
     - Updated all non-working buttons to use Alpine.js `@click` with Livewire method calls:
     - Changed `wire:click="selectPhaseForUpdate({{ $currentPhase->id }})"` to `@click="$wire.selectPhaseForUpdate({{ $currentPhase->id }}).$nextTick(() => $dispatch('open-modal', 'update-phase-modal'))"`
     - Changed `wire:click="prepareEditPhase({{ $phase->id }})"` to `@click="$wire.prepareEditPhase({{ $phase->id }}).$nextTick(() => $dispatch('open-modal', 'edit-phase-modal'))"`
     - Changed `wire:click="openCloseCaseModal"` to `@click="$dispatch('open-modal', 'close-case-modal')"`
   - **Implementation Details**:
     - Only modified click handling in the blade templates
     - Maintained the same Livewire methods in the PHP component
     - Used Alpine.js's `$nextTick` to ensure the Livewire method completes before opening the modal
     - Added `x-data="{}"` to each button to ensure Alpine.js context is available

47. **Complete Rebuild of Case Phase System**
   - **Problem**: Multiple persistent issues with the Case Progression Monitor where buttons (Update Phase, Edit Phase, Close Case) didn't work properly. The system had inconsistent implementation with mixed use of Alpine.js and Livewire for interactions.
   - **Solution**: Complete rebuild of the Case Progression Monitor with a consistent, simpler approach:
     - Removed all custom modal implementations in favor of consistent x-modal components
     - Standardized all button interactions using a single approach with Alpine.js
     - Made all buttons trigger Livewire methods directly which then dispatch modal events
     - Streamlined the relationship between button clicks, Livewire methods, and modal displays
     - Added proper type="button" attributes to all buttons to prevent unintended form submissions
     - Ensured consistent styling and spacing across all modals
   - **Implementation Details**:
     - Button clicks now trigger Livewire methods with `@click="$wire.methodName(params)"`
     - Modal events are dispatched from the Livewire PHP component with `$this->dispatch('open-modal', 'modal-name')`
     - Modal forms maintain a connection to their respective Livewire methods via `wire:submit.prevent`
     - Added missing reset form methods to clear form data when needed
   - **Result**: A more reliable, consistent system where all buttons work as expected while maintaining the same UI appearance.

16. **Fixed Law Firm Access to Lawyers' Cases**
   - **Problem**: Law firms were receiving "You are not authorized to set up this case" errors (403) when trying to access or manage cases belonging to their lawyers.
   - **Root cause**: 
     1. ManageCases component was using `law_firm_id` column that didn't exist in the users table (actual column is `firm_id`)
     2. CaseSetup and CaseDetails components were checking for lawyer association using `law_firm_id` in lawyerProfile or inconsistent lookups
   - **Solution**:
     1. Updated ManageCases to use correct `firm_id` column in users table
     2. Modified all authorization checks in CaseSetup, CaseDetails, and CasePhaseTracker to use consistent DB queries
     3. Fixed the relationship checks to use the same approach across all components
   - **Implementation Details**:
     - In ManageCases.php:
       ```php
       // Before:
       $lawyerIds = \App\Models\User::where('law_firm_id', $lawFirmId)
           
       // After:
       $lawyerIds = \App\Models\User::whereHas('role', function($query) {
               $query->where('name', 'lawyer');
           })
           ->where('firm_id', $lawFirmId)
       ```
     - In CaseSetup.php, CaseDetails.php, and CasePhaseTracker.php:
       ```php
       // Before (inconsistent across components):
       $caseIsForLawFirm = $case->lawyer_id === $lawFirmId || 
           (($case->lawyer && $case->lawyer->lawyerProfile) ? 
               ($case->lawyer->lawyerProfile->law_firm_id === $lawFirmId) : false);
           
       // After (consistent across all components):
       $hasAccess = $lawyerId === $lawFirmId || // Direct ownership
           DB::table('users')->where('id', $lawyerId)
               ->where('firm_id', $lawFirmId)
               ->exists(); // Lawyer belongs to this law firm
       ```
   - **Files Modified**:
     - `app/Livewire/LawFirm/ManageCases.php`
     - `app/Livewire/LawFirm/CaseSetup.php`
     - `app/Livewire/LawFirm/CaseDetails.php`
     - `app/Livewire/Components/CasePhaseTracker.php`
   - **Date Implemented**: Current day

48. **Fixed Case Label Updating in Lawyer Interface**
   - **Problem**: Lawyers weren't able to save case labels when changing them in the case management interface. Law firms could update labels, but individual lawyers and lawyers under a firm couldn't.
   - **Root cause**: 
     1. Initial Issue: Event handling mismatch in Livewire 3. The JavaScript was using `@this.updateLabel()` directly, which is deprecated.
     2. Subsequent Issue: The Lawyer/ManageCases component had an overly restrictive database query that only loaded cases directly assigned to the current lawyer, while the LawFirm component properly loaded cases for all lawyers in the firm.
     3. Multiple authorization checks throughout the component methods were creating inconsistencies in permission handling.
   - **Solution**:
     1. Modified the Lawyer/ManageCases component's `render` method to load cases for the firm's lawyers if the current lawyer belongs to a firm, similar to the LawFirm/ManageCases component.
     2. Simplified authorization logic by removing explicit checks in all update methods and instead relying on the fact that the lawyer can only see and interact with cases they are authorized to access.
     3. Updated several methods to match this simplified approach:
        - `updateLabel`: Removed authorization check
        - `updatePriority`: Removed authorization check  
        - `finishSetup`: Removed authorization check
        - `startCaseFromConsultation`: Modified to allow both direct and firm-related access
   - **Key Benefits**:
     - Individual lawyers can update labels for their own cases
     - Lawyers within a firm can update labels for cases managed by any lawyer in their firm
     - Maintains consistent behavior between law firms and their lawyers
     - Simplified and consistent authorization model across the application
   - **Files Modified**:
     - `/app/Livewire/Lawyer/ManageCases.php` (updated query and removed explicit auth checks)
   - **Date Implemented**: Current day

49. **Fixed Label Update Event Handling for Lawyers Under Firms**
   - **Problem**: Label updating still didn't work for lawyers under a firm, while it worked fine for the law firm itself
   - **Root cause**: 
     1. Inconsistent event handling between the law firm and lawyer components
     2. Law firm template used direct `@this.updateLabel()` method calls
     3. Lawyer template used `Livewire.dispatch('updateLabel')` creating an extra event layer
   - **Solution**:\n     1. Updated the lawyer's template to use the same direct `@this.updateLabel()` call as the law firm template\n     2. Added extensive debug logging to track the event flow and update process\n     3. Made both implementations identical to ensure consistent behavior\n   - **Key Benefits**:\n     - Lawyers under a firm can now successfully update labels just like the firm itself\n     - Consistent implementation between law firm and lawyer components\n     - More reliable event handling and data flow\n     - Added debugging helps identify any remaining issues\n   - **Files Modified**:\n     - `/resources/views/livewire/lawyer/manage-cases.blade.php` (updated JavaScript event handling)\n   - **Date Implemented**: Current day

50. **Created New Case Label System to Fix Label Update Issues**
   - **Problem**: The original case label update functionality wasn't working reliably for lawyers under a law firm, despite multiple attempts to fix it.
   - **Solution**: Created a completely new case label system with a dedicated database field and new methods:
     1. Added a new `case_label` field to the legal_cases table
     2. Created dedicated `updateCaseLabel` methods in both Lawyer and LawFirm components
     3. Updated blade templates to use the new field and methods directly
     4. Improved logging and error handling for better debugging
   - **Implementation Details**:
     - Created a migration to add a new dedicated field:
       ```php
       $table->enum('case_label', ['high_priority', 'medium_priority', 'low_priority'])->nullable();
       ```
     - Added constants to the LegalCase model:
       ```php
       const CASE_LABEL_HIGH_PRIORITY = 'high_priority';
       const CASE_LABEL_MEDIUM_PRIORITY = 'medium_priority';
       const CASE_LABEL_LOW_PRIORITY = 'low_priority';
       ```
     - Created new methods in both Lawyer and LawFirm ManageCases components:
       ```php
       public function updateCaseLabel($caseId, $label)
       {
           // Implementation with improved logging and error handling
       }
       ```
     - Updated blade templates to use a direct method call instead of event-based approach:
       ```javascript
       $wire.updateCaseLabel({{ $case->id }}, label).then(() => {
           // Handle UI updates after successful save
       });
       ```
   - **Files Modified**:
     - Created migration: `database/migrations/2025_05_14_225151_add_case_label_to_legal_cases.php`
     - Updated `app/Models/LegalCase.php`
     - Updated `app/Livewire/Lawyer/ManageCases.php`
     - Updated `app/Livewire/LawFirm/ManageCases.php`
     - Updated `resources/views/livewire/lawyer/manage-cases.blade.php`
     - Updated `resources/views/livewire/law-firm/manage-cases.blade.php
   - **Date Implemented**: Current day

51. **Fixed Case Label Update for Lawyers by Correcting Component Path**
   - **Problem**: Changes to case labels were not working for lawyers because we were editing the wrong component file.
   - **Solution**: Identified that the route `/lawyer/cases` was actually pointing to `App\Livewire\Lawyers\ManageCases` (plural) but we had been editing `App\Livewire\Lawyer\ManageCases` (singular).
   - **Implementation Details**:
     - Updated the correct component file at `app/Livewire/Lawyers/ManageCases.php` with our new `updateCaseLabel` method
     - Modified the corresponding view at `resources/views/livewire/lawyers/manage-cases.blade.php`
     - Changed the view to use `case_label` instead of `label`
     - Updated the UI to use direct wire method calls instead of event dispatching
     - Enhanced the code with better error handling and logging
     - Cleared all caches to ensure changes took effect immediately

52. **Removed Unused ManageCases Component File to Prevent Confusion**
   - **Problem**: Having both `app/Livewire/Lawyer/ManageCases.php` (singular) and `app/Livewire/Lawyers/ManageCases.php` (plural) was confusing, especially since only the plural version was actually being used by the routes.
   - **Solution**: Deleted the unused singular version (`app/Livewire/Lawyer/ManageCases.php`) to avoid future confusion and prevent developers from making changes to the wrong file.
   - **Implementation Details**:
     - Confirmed that the route `/lawyer/cases` points to `App\Livewire\Lawyers\ManageCases` (plural)
     - Deleted `app/Livewire/Lawyer/ManageCases.php` (singular) which wasn't being used
     - Kept all functionality in the correctly-used plural version `app/Livewire/Lawyers/ManageCases.php`
   - **Date Implemented**: Current day

53. **Fixed Calendar Links in Lawyer and Law Firm Dashboards**
   - **Problem**: When clicking on case events, deadlines, or tasks in the calendar on the lawyer dashboard, it would navigate to `/lawyer/cases/{case_id}` (details view) instead of `/lawyer/case/setup/{case_id}` (setup/management view)
   - **Solution**: Updated all calendar event URLs in both the lawyer and law firm dashboard components to point to the correct case setup/management routes
   - **Implementation Details**:
     - Modified `app/Livewire/Lawyer/Dashboard.php` to use `route('lawyer.case.setup', $case->id)` instead of `route('lawyer.cases.show', $case->id)`
     - Updated `app/Livewire/LawFirm/Dashboard.php` to use `route('law-firm.case.setup', $case->id)` instead of generic `route('law-firm.cases')`
     - Applied the changes to case deadlines, case events, and case tasks in both components
     - Added query parameter `?tab=tasks_management` for task links to automatically open the tasks tab
   - **Date Implemented**: Current day

54. **Added X (Close) Buttons to All Modals in Case Setup Pages**
   - **Problem**: Users needed a more intuitive way to close modals in case setup pages besides the Cancel button
   - **Solution**: Added X close buttons to all modals in both lawyer and law firm case setup pages
   - **Implementation Details**:
     - Added close (X) buttons to all custom modals in `resources/views/livewire/lawyer/case-setup.blade.php` and `resources/views/livewire/law-firm/case-setup.blade.php`
     - Updated all x-modal components in `resources/views/livewire/components/case-phase-tracker.blade.php` to include X buttons
     - Made all modals consistent with a unified close mechanism
     - Improved user experience by giving users multiple ways to close modals
   - **Date Implemented**: May 14, 2025

55. **Fixed "Unknown User" Issue When Lawyers Under a Law Firm Add Case Notes**
   - **Problem**: When a lawyer working under a law firm added case notes/updates, it displayed "Unknown User" instead of their name.
   - **Solution**: Updated the CaseUpdate model to properly fetch names from the law_firm_lawyers table when appropriate.
   - **Implementation Details**:
     - Modified the `getCreatorNameAttribute()` method in CaseUpdate model to check for lawyers under firms
     - Added logic to first check if the user has a firm_id and lawFirmLawyer relationship
     - Updated the User relationship to eager load lawFirmLawyer, clientProfile, and lawFirmProfile
     - Added better fallback options to always display a meaningful name
   - **Date Implemented**: May 14, 2025

56. **Fixed Invoice Number Generation for Lawyers Under Law Firms**
   - **Problem**: When a lawyer working under a law firm tried to create an invoice, they got an error due to duplicate invoice numbers. This happened because the invoice number generation logic didn't account for other invoices created by different lawyers in the same law firm.
   - **Solution**: Updated the invoice number generation logic to account for all invoices across the entire law firm, not just the current lawyer's invoices.
   - **Implementation Details**:
     - Modified the `generateInvoiceNumber()` method in `app/Livewire/Lawyer/CaseInvoices.php`
     - Added logic to include invoices from all lawyers in the same firm when finding the last used invoice number
     - Added a backup check that looks for any invoice with the same pattern to ensure unique numbers
     - Used the maximum sequence number from both queries to generate the next invoice number
   - **Date Implemented**: May 15, 2025

# Technical Solutions and Implementation Details

## Implementing Dynamic Lawyer Assignment for Law Firms (2024-06-27)

### Problem
Law firms needed a way to reassign cases between lawyers within their firm after cases were already created. Previously, the lawyer assignment was fixed when the case was created, requiring manual database changes to reassign cases.

### Solution
Implemented a complete lawyer reassignment functionality within the law firm's case management dashboard:

1. **Backend Changes**:
   - Created `showReassignLawyer()` and `reassignLawyer()` methods in the `LawFirm\ManageCases` component
   - Added logic to fetch all lawyers belonging to the law firm
   - Implemented proper notification system to inform all parties about reassignments

2. **Frontend Changes**:
   - Added a "Reassign" button in the case management table
   - Created a dedicated modal for lawyer selection
   - Displayed clear information about the current and new assignment

3. **Database Operations**:
   - Updates the lawyer_id field in the legal_cases table
   - Records the reassignment action in the case_updates table
   - Creates notification records for all relevant parties

### Implementation Details
When a law firm reassigns a case:
1. The case's lawyer_id is updated to the selected lawyer
2. The client is notified about the change
3. The newly assigned lawyer receives a notification about the new case
4. The previously assigned lawyer is notified that the case has been reassigned
5. A case update record is created to track the change

This implementation allows law firms to flexibly manage their resources as case requirements change, without requiring technical intervention.

## Fixed Case Updates Visibility for Law Firm Team Management (2024-06-27)

### Problem
When trying to add a lawyer to a case team, the system threw an error: "Failed to add lawyer to team: SQLSTATE[01000]: Warning: 1265 Data truncated for column 'visibility' at row 1". This occurred because the 'visibility' column in the case_updates table was an enum limited to only 'lawyer', 'client', and 'both' values, but our team management functionality was trying to use 'law_firm' as a visibility option.

### Solution
1. Created a migration to modify the visibility enum in the case_updates table:
   ```php
   DB::statement("ALTER TABLE case_updates MODIFY COLUMN visibility ENUM('lawyer', 'client', 'both', 'law_firm') NOT NULL DEFAULT 'both'");
   ```

2. Updated the CaseUpdate model's documentation to reflect the new valid option:
   ```php
   'visibility', // 'lawyer', 'client', 'both', 'law_firm'
   ```

### Implementation Details
- Created migration file: 2025_05_15_004108_add_law_firm_visibility_to_case_updates_table.php
- This solution allows case updates to have a visibility scope specifically for law firms, which is essential for team management notifications and updates that should only be visible to the law firm managing the case.
- The 'law_firm' visibility option enables updates about team management to be hidden from clients while still being accessible to all lawyers in the firm.

## Implementing Multiple Lawyer Assignment for Cases (2024-06-27)

### Problem
Law firms needed the ability to assign multiple lawyers to cases, creating legal teams with different roles and responsibilities. The previous system only allowed one lawyer per case, which was insufficient for complex legal matters requiring multiple specialists.

### Solution
Implemented a comprehensive multiple lawyer assignment system with these key components:

1. **Database Changes**:
   - Created a new `case_lawyer` pivot table with additional data fields:
     - `legal_case_id` and `user_id` for the relationship
     - `role` to define each lawyer's responsibility on the case
     - `notes` for additional context about the assignment
     - `is_primary` flag to track which lawyer is the primary
     - `assigned_by` to track who made the assignment
   - Maintained the original `lawyer_id` field in the `legal_cases` table for backwards compatibility

2. **Model Relationships**:
   - Added a many-to-many relationship in the LegalCase model (`assignedLawyers()`)
   - Created a corresponding relationship in the User model (`assignedCases()`)
   - Added a one-to-many relationship for CaseLawyer records (`caseLawyers()`)
   - Created a new CaseLawyer model to handle direct CRUD operations

3. **UI Implementation**:
   - Added a "Manage Team" button on the case management dashboard
   - Created a comprehensive team management modal with:
     - Current team members list showing roles and primary status
     - Form to add new team members with role and notes
     - Options to edit existing team members' roles
     - Ability to set any lawyer as the primary lawyer
     - Ability to remove lawyers from the case team

4. **Business Logic**:
   - Automatic primary lawyer assignment when the first lawyer is added
   - Changes to the primary lawyer update the main `lawyer_id` in the `legal_cases` table
   - Notifications to lawyers when they're added to or removed from a case team
   - Case update records to track team changes for audit purposes

### Implementation Details
The system integrates with the existing case management system while allowing much more flexible team structures. Key implementation highlights:

- Used Laravel's many-to-many relationship with pivot data (`withPivot`)
- Maintained backward compatibility with the existing `lawyer_id` field
- Provided clear UI for adding, removing, and changing lawyer roles
- Ensured there's always a designated primary lawyer for each case
- Added thorough logging and case updates to track all team changes

This implementation allows law firms to:
- Build specialized legal teams for complex cases
- Assign specific roles to each team member
- Track who made each assignment and when
- Keep a designated primary lawyer as the main point of contact
- Adjust team composition as case requirements evolve

22. **Fixed Lawyer Team Member Cases Display**
   - **Problem**: When a lawyer was assigned to a case as a team member by their law firm (via the case_lawyer pivot table), the case didn't appear in the lawyer's own case listing at `/lawyer/cases`.
   - **Solution**: Updated the query in the Lawyers\ManageCases component to include cases where the lawyer is either the primary lawyer or part of the team via the teamLawyers relationship.
   - **Implementation Details**:
     ```php
     // Before:
     $casesQuery = LegalCase::where('lawyer_id', Auth::id())
         ->with(['client'])
         // ... rest of query ...
         
     // After:
     $casesQuery = LegalCase::where(function($query) {
             $query->where('lawyer_id', Auth::id())
                   ->orWhereHas('teamLawyers', function($q) {
                       $q->where('user_id', Auth::id());
                   });
         })
         ->with(['client'])
         // ... rest of query ...
     ```
   - **Files Modified**:
     - `app/Livewire/Lawyers/ManageCases.php`
   - **Date Implemented**: Current day

57. **Fixed Missing teamLawyers() Relationship Method**
   - **Problem**: The Lawyers/ManageCases component was throwing an error "Call to undefined method App\Models\LegalCase::teamLawyers()" because it was trying to use a relationship that didn't exist in the LegalCase model.
   - **Solution**: Added a teamLawyers() relationship method to the LegalCase model that serves as an alias for the existing assignedLawyers() relationship.
   - **Implementation Details**:
     ```php
     /**
      * Get all team lawyers assigned to this case.
      * This is an alias for assignedLawyers() to maintain backward compatibility.
      */
     public function teamLawyers()
     {
         return $this->belongsToMany(User::class, 'case_lawyer', 'legal_case_id', 'user_id')
             ->withPivot('role', 'notes', 'is_primary', 'assigned_by')
             ->withTimestamps();
     }
     ```
   - **Files Modified**:
     - `app/Models/LegalCase.php`
   - **Date Implemented**: Current day

58. **Fixed Team Member Authorization for Case Management**
   - **Problem**: Lawyers who were added as team members to a case couldn't access the case setup/details pages, receiving a "403 - You are not authorized to set up this case" error. The system was only checking if the current user matched the primary lawyer (lawyer_id).
   - **Solution**: Updated the authorization checks in all case management components to check both the primary lawyer and team members through the teamLawyers relationship.
   - **Implementation Details**:
     1. Added an `isAuthorized()` helper method to standardize authorization checks:
        ```php
        private function isAuthorized()
        {
            $userId = Auth::id();
            return $this->case->lawyer_id === $userId || 
                   $this->case->teamLawyers()->where('user_id', $userId)->exists();
        }
        ```
     2. Updated the `mount()` method to use this broader authorization:
        ```php
        $userId = Auth::id();
        $isAuthorized = $case->lawyer_id === $userId || 
                         $case->teamLawyers()->where('user_id', $userId)->exists();
                         
        if (!$isAuthorized) {
            abort(403, 'You are not authorized to set up this case.');
        }
        ```
     3. Replaced all instances of `if ($this->case->lawyer_id !== Auth::id())` with `if (!$this->isAuthorized())`
   - **Files Modified**:
     - `app/Livewire/Lawyer/CaseSetup.php`
     - `app/Livewire/Lawyer/CaseDetails.php`
   - **Date Implemented**: Current day

59. **Restricted Invoice Creation to Primary Lawyers Only**
   - **Problem**: All team lawyers assigned to a case could create invoices, but the requirement was to only allow the primary lawyer (with is_primary=1) to have this capability.
   - **Solution**: Implemented a verification system that checks if the current user is the primary lawyer for a case before allowing invoice operations.
   - **Implementation Details**:
     1. Added an `isPrimaryLawyer` flag in the CaseInvoices component:
        ```php
        public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
        ```
     2. Created a helper method to check primary status:
        ```php
        private function checkIfPrimaryLawyer()
        {
            $userId = Auth::id();
            
            // Case 1: User is the primary lawyer in the lawyer_id field
            if ($this->case->lawyer_id === $userId) {
                return true;
            }
            
            // Case 2: User is marked as primary in the case_lawyer pivot table
            return $this->case->teamLawyers()
                ->where('user_id', $userId)
                ->where('is_primary', true)
                ->exists();
        }
        ```
     3. Added authorization checks to all invoice-related methods:
        ```php
        // Only allow primary lawyers to create invoices
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can create invoices for this case.", type: 'error');
            return;
        }
        ```
     4. Modified the UI to conditionally display invoice management buttons only to the primary lawyer.
   - **Files Modified**:
     - `app/Livewire/Lawyer/CaseInvoices.php`
     - `resources/views/livewire/lawyer/case-invoices.blade.php`
   - **Date Implemented**: Current day

59. **Restricted Case Closing to Primary Lawyers Only**
   - **Problem**: Any lawyer assigned to a case (including team members) could close a case, but the business requirement is to only allow the primary lawyer to close cases.
   - **Solution**: Implemented a verification system that checks if the current user is the primary lawyer for a case before allowing case closure.
   - **Implementation Details**:
     1. Added an `isPrimaryLawyer` flag in the case management components:
        ```php
        public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
        ```
     2. Created a helper method to check primary lawyer status:
        ```php
        private function checkIfPrimaryLawyer()
        {
            $userId = Auth::id();
            
            // Case 1: User is the primary lawyer in the lawyer_id field
            if ($this->case->lawyer_id === $userId) {
                return true;
            }
            
            // Case 2: User is marked as primary in the case_lawyer pivot table
            return $this->case->teamLawyers()
                ->where('user_id', $userId)
                ->where('is_primary', true)
                ->exists();
        }
        ```
     3. Updated the `closeCase` method to check for primary lawyer status:
        ```php
        public function closeCase()
        {
            // Only primary lawyers can close cases
            if (!$this->isPrimaryLawyer) {
                session()->flash('error', 'Only the primary lawyer can close this case.');
                return;
            }
            
            // Rest of the method...
        }
        ```
     4. Modified the UI to hide the close case button for non-primary lawyers and display an informational message instead:
        ```php
        @if($isPrimaryLawyer)
            <button @click="$dispatch('open-modal', 'close-case-modal')">
                Close Case
            </button>
        @else
            <span class="text-gray-600 bg-gray-100">
                Only the primary lawyer can close the case
            </span>
        @endif
        ```
   - **Components Updated**:
     - Lawyer/CaseSetup
     - LawFirm/CaseSetup
     - Components/CasePhaseTracker

60. **Allowed Team Lawyers to Manage Case Phases**
   - **Problem**: Only the primary lawyer could update case phases, move the Case Progression Monitor, and add phase updates. Team members assigned to a case needed the ability to manage phases as well.
   - **Solution**: Updated the CasePhaseTracker component to allow team members to manage phases while maintaining the restriction that only primary lawyers can close cases.
   - **Implementation Details**:
     ```php
     // Updated canManagePhases check in CasePhaseTracker.php
     $this->canManagePhases = !$this->readOnly && Auth::check() && (
         $user->id === $this->case->lawyer_id || // Direct lawyer
         $this->case->teamLawyers()->where('user_id', $userId)->exists() || // Team member
         ($user->isLawFirm() && DB::table('users')
             ->where('id', $this->case->lawyer_id)
             ->where('firm_id', $user->id)
             ->exists()) // Law firm of the lawyer
     );
     ```
   - The `isPrimaryLawyer` check remains intact to maintain the restriction that only primary lawyers can close cases.

61. **Fixed Missing CaseClosed Notification Class Error**
   - **Problem**: When trying to close a case, the system encountered an error: "Class 'App\Notifications\CaseClosed' not found" because the code was trying to use a notification class that didn't exist.
   - **Solution**: Modified both Lawyer and LawFirm CaseSetup components to use the existing NotificationService::caseClosed() method instead of the non-existent notification class.
   - **Implementation Details**:
     ```php
     // Changed from:
     if ($case->client) {
         $case->client->notify(new \App\Notifications\CaseClosed($case));
     }
     
     // To:
     try {
         \App\Services\NotificationService::caseClosed($case);
     } catch (\Exception $e) {
         \Illuminate\Support\Facades\Log::error('Error sending case closed notification: ' . $e->getMessage());
     }
     ```
   - This approach uses the existing notification service to handle case closing notifications, which is already properly implemented in the system.

62. **Automatically Move Completed Cases to Archived Section**
   - **Problem**: Cases with "Completed" status were still showing up in the active cases list for clients instead of being automatically moved to the archived section.
   - **Solution**: Modified the ManageCases component for clients to treat cases with status "Completed" the same as archived cases, automatically moving them to the archived section.
   - **Implementation Details**:
     ```php
     // When showing archived cases, also include completed cases
     ->when($this->showArchived, function ($query) {
         $query->where(function($q) {
             $q->where('archived', true)
               ->orWhere('status', LegalCase::STATUS_COMPLETED);
         });
     }, function ($query) {
         // For non-archived view, exclude both archived and completed cases
         $query->where(function ($q) {
             $q->where(function($subQ) {
                 $subQ->where('archived', false)
                      ->orWhereNull('archived');
             })
             ->where(function($subQ) {
                 $subQ->where('status', '!=', LegalCase::STATUS_COMPLETED)
                      ->orWhereNull('status');
             });
         });
     })
     ```
   - This change ensures that when a lawyer marks a case as "Completed", it will immediately appear in the client's archived cases section rather than still showing up in their active cases list.

## Client Multiple Lawyer Rating Feature

**Problem**: Clients could only rate the primary lawyer assigned to a case, but not the team members who also worked on it.

**Solution**: Created a new RateTeamLawyer component that allows clients to select and rate any lawyer assigned to a case, in addition to the existing functionality to rate the primary lawyer.

**Implementation Details**:
1. Created a new Livewire component `RateTeamLawyer` that:
   - Loads all lawyers assigned to a case through the case_lawyer pivot table
   - Provides a dropdown to select which lawyer to rate
   - Shows existing ratings if a client has already rated a particular lawyer
   - Notifies the rated lawyer through the NotificationService

2. Updated the ManageCases component to:
   - Add a new "Rate Team Lawyers" button next to the existing "Rate This Lawyer" button for closed/completed cases
   - Include the event dispatcher method `openRateTeamLawyerModal`
   - Include the RateTeamLawyer component in the view

3. The implementation leverages the existing LawyerRating model which already supports per-lawyer ratings for a specific case.

**Files Modified**:
- Created: 
  - `app/Livewire/Client/RateTeamLawyer.php`
  - `resources/views/livewire/client/rate-team-lawyer.blade.php`
- Modified:
  - `app/Livewire/Client/ManageCases.php`
  - `resources/views/livewire/client/manage-cases.blade.php`

**Date Implemented**: Current day

## Enhanced Lawyer and Law Firm Rating System

**Problem**: The existing case rating system had two issues:
1. It always showed both "Rate This Lawyer" and "Rate Team Lawyers" buttons regardless of how many lawyers were on the case
2. There was no way to rate a law firm when a case was handled by a law firm or a lawyer under a firm

**Solution**: Created an intelligent rating system that:
1. Shows only "Rate Lawyer" for cases with a single lawyer
2. Shows only "Rate Lawyers" for cases with multiple lawyers
3. Adds a "Rate Law Firm" button when a case is handled by a law firm or lawyer under a firm

**Implementation Details**:
1. Added helper methods to the ManageCases component:
   - `caseHasMultipleLawyers()` - Checks if a case has more than one lawyer assigned
   - `caseHasLawFirm()` - Checks if a case is handled by a law firm or lawyer under a firm
   - `openRateLawFirmModal()` - Dispatches the event to open the law firm rating modal

2. Updated the client's manage-cases.blade.php to conditionally show the appropriate rating buttons based on:
   - Whether the case has multiple lawyers
   - Whether the case is associated with a law firm

3. Created a new RateLawFirm component to handle law firm ratings:
   - Identifies the correct law firm to rate using multiple detection methods
   - Allows clients to rate and provide feedback for the law firm
   - Sends notifications to the law firm when rated
   - Displays the law firm name in the rating interface

4. The system now provides a more intuitive rating experience that matches the actual case structure.

**Files Modified**:
- `app/Livewire/Client/ManageCases.php`
- `resources/views/livewire/client/manage-cases.blade.php`
- Created: `app/Livewire/Client/RateLawFirm.php`
- Created: `resources/views/livewire/client/rate-law-firm.blade.php`

**Date Implemented**: [Current Date]

13. **Navigation Menu Overlap Issue on Client Welcome Page**
   - **Problem**: The "Find Legal Help" dropdown in the navigation menu on `/client/welcome` was overlapped by the welcome page content and could not be clicked.
   - **Solution**: Made the primary navigation menu sticky and gave it a high z-index so it stays above page content.
   - **Implementation**:
     ```diff
     // resources/views/navigation-menu.blade.php
     -<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
     +<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
     ```
   - **Date Implemented**: Current day

63. **Fixed Navigation Menu Dropdown Issue on Client Welcome Page**
   - **Problem**: The "Find Legal Help" dropdown in the navigation menu on `/client/welcome` page appeared elevated and couldn't be clicked, while it worked properly on other pages like `/client/cases`.
   - **Solution**: 
     1. Initially made the navigation bar sticky with a high z-index (`z-50`)
     2. Added additional fix for the welcome page by:
        - Setting a lower z-index (`z-0`) for the welcome page's main content div
        - Adding relative positioning to create a new stacking context
        - Adding top padding (`pt-16`) to prevent content from being hidden under the navbar
   - **Implementation Details**:
     ```diff
     // resources/views/navigation-menu.blade.php - First attempt
     -<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
     +<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
     
     // resources/views/client/welcome.blade.php - Second fix
     -<div class="min-h-screen bg-gray-50">
     +<div class="min-h-screen bg-gray-50 relative z-0 pt-16">
     ```
   - **Key Issues Fixed**:
     - Resolved z-index conflict between navbar and welcome page content
     - Fixed positioning to ensure navbar dropdowns work correctly
     - Maintained proper spacing so content isn't hidden under the navbar
   - **Date Implemented**: Current day

## Notification System Fix (May 17, 2025)

### Issue
The notification system was not working properly because there were two parallel notification systems:
1. Laravel's built-in notification system using `laravel_notifications` table
2. A custom notification system using `app_notifications` table

The Livewire components (`NotificationDropdown` and `AllNotifications`) were looking for notifications in Laravel's built-in system, but the `NotificationService` was creating notifications in the custom `AppNotification` model.

### Solution
1. Created `GenericNotification` class in `App\Notifications` namespace to handle Laravel's database notifications
2. Updated `NotificationService` to create both `AppNotification` and Laravel `DatabaseNotification` records
3. Added `data` column to `app_notifications` table to store additional notification data
4. Updated `AppNotification` model to include the data field and added a method to convert to Laravel notification format

The system now properly sends notifications to both notification systems, ensuring compatibility with the existing notification components.

## Fixed Notification System Error (May 17, 2025)

### Issue
The notification system was throwing an error: "Call to a member function create() on array" in NotificationService.php when trying to send notifications. This was happening because:

1. The code was trying to call `$client->notify()` without checking if `$client` was a valid Eloquent model instance
2. There was no proper error handling around notification creation and dispatch

### Solution
1. Added proper type checking with `instanceof \Illuminate\Database\Eloquent\Model` before calling notify()
2. Wrapped notification sending in try-catch blocks to prevent errors from breaking the application
3. Added detailed error logging to help identify issues
4. Improved the structure of notification methods to use consistent variable naming and error handling

The fix was applied to all notification methods in the NotificationService class:
- newConsultationRequest
- consultationAccepted
- consultationDeclined
- consultationLinkUpdated
- consultationCompleted
- consultationAssigned
- caseStarted
- createSystemNotification
- caseActivated
- caseClosed
- lawyerRated
- lawFirmRated
- newMessage

This ensures that the notification system is more robust and won't crash when encountering invalid user references or other issues.

16. **Fixed Notification Error: "Call to a member function create() on array"**
   - **Problem**: The notification system was throwing a "Call to a member function create() on array" error when attempting to send notifications in various methods of the NotificationService class. This occurred because the code was trying to call methods on variables that might not be Eloquent model instances.
   - **Solution**: Added proper type checking with `instanceof \Illuminate\Database\Eloquent\Model` before calling methods on user objects, and wrapped notification sending in try-catch blocks to prevent errors from breaking the application flow.
   - **Implementation Details**:
     - Added try-catch blocks around all notification methods
     - Added explicit checks for null users with early returns
     - Added proper type checking before calling notify() method
     - Added detailed error logging
     - Improved methods affected: consultationDeclined, consultationLinkUpdated, consultationAssigned
     ```php
     // Before
     $client->notify(new \App\Notifications\GenericNotification(...));
     
     // After
     if ($client instanceof \Illuminate\Database\Eloquent\Model) {
         try {
             $client->notify(new \App\Notifications\GenericNotification(...));
         } catch (\Exception $e) {
             Log::error('Error sending notification: ' . $e->getMessage());
         }
     }
     ```
   - **Files Modified**:
     - `app/Services/NotificationService.php`
   - **Date Implemented**: Current day

61. **Contract Negotiation Workflow: Client Rejection & Change Requests, Lawyer Revisions**
    - **Feature**: Implemented a comprehensive contract negotiation flow allowing clients to reject or request changes to contracts, and lawyers to upload revised contracts.
    - **Database Changes**:
        - Added `rejection_reason` (TEXT, nullable) and `requested_changes_details` (TEXT, nullable) to the `legal_cases` table.
        - Migration: `add_contract_feedback_fields_to_legal_cases_table` (Note: A schema check was added to the migration to prevent errors if fields already exist from a previous attempt).
    - **Model Updates (`LegalCase.php`)**:
        - Added `rejection_reason`, `requested_changes_details` to `$fillable`.
        - Added new status constants:
            - `STATUS_CONTRACT_REJECTED_BY_CLIENT = 'contract_rejected_by_client'`
            - `STATUS_CHANGES_REQUESTED_BY_CLIENT = 'changes_requested_by_client'`
            - `STATUS_CONTRACT_REVISED_SENT = 'contract_revised_sent'`
    - **Client-Side (`App\Livewire\Client\ContractReview.php` & Blade)**:
        - **Component**:
            - Added properties: `showRejectModal`, `rejectionReason`, `showRequestChangesModal`, `requestedChanges`.
            - New methods: `openRejectModal()`, `submitRejection()`, `openRequestChangesModal()`, `submitRequestedChanges()`.
            - `submitRejection()`: Validates reason, updates case status to `contract_rejected_by_client`, saves reason, creates `ContractAction`, notifies lawyer.
            - `submitRequestedChanges()`: Validates changes, updates case status to `changes_requested_by_client`, saves details, sets `lawyer_response_required = true`, creates `ContractAction`, notifies lawyer.
            - `mount()`: Checks if `case->status` is `contract_sent` or `contract_revised_sent` before allowing actions.
        - **Blade (`contract-review.blade.php`)**:
            - Added "Reject Contract" and "Request Changes" buttons (visible if status is `contract_sent` or `contract_revised_sent`).
            - Added modals for rejection and requesting changes with textareas for input.
    - **Notification Service (`App\Services\NotificationService.php`)**:
        - New methods: `contractRejectedByClient()`, `contractChangesRequestedByClient()`, `revisedContractUploaded()` to send `GenericNotification` to relevant parties.
    - **Lawyer-Side (`App\Livewire\Lawyers\ManageCases.php` & Blade)**:
        - **Component**:
            - Added properties: `showUploadRevisedContractModal`, `revisedContractDocument`, `selectedCaseForRevision`.
            - Updated `$statuses` array in `render()` for new case statuses.
            - New methods: `openUploadRevisedContractModal($caseId)`, `submitRevisedContract()`.
            - `openUploadRevisedContractModal()`: Checks authorization and if case status is `changes_requested_by_client`.
            - `submitRevisedContract()`: Validates file, stores new contract, updates case status to `contract_revised_sent`, sets `contract_status = 'revised_sent_to_client'`, clears old rejection/change details, creates `ContractAction`, notifies client.
        - **Blade (`manage-cases.blade.php`)**:
            - Displays new statuses (e.g., "Contract Rejected by Client") with reasons/requests.
            - Shows "Upload Revised Contract" button if status is `changes_requested_by_client`.
            - Added modal for uploading revised contract, showing client's requested changes.
    - **Files Modified/Created**:
        - `database/migrations/YYYY_MM_DD_HHMMSS_add_contract_feedback_fields_to_legal_cases_table.php`
        - `app/Models/LegalCase.php`
        - `app/Livewire/Client/ContractReview.php`
        - `resources/views/livewire/client/contract-review.blade.php`
        - `app/Services/NotificationService.php`
        - `app/Livewire/Lawyers/ManageCases.php`
        - `resources/views/livewire/lawyers/manage-cases.blade.php
    - **Date Implemented**: Current day

21. Stabilized Livewire interactions on `/lawyer/consultations`
- **Problem**: Action buttons (Complete, View Contract, Accept, Decline) intermittently did nothing due to DOM diffing and re-render collisions with modals.
- **Solution**:
  - Added `wire:key` on each consultation card container to stabilize DOM nodes.
  - Switched action buttons to `wire:click.prevent` to avoid default behaviors and ensure Livewire receives the event.
  - Marked modals with `wire:ignore.self` so they don't get torn down while visible.
- **Implementation Details**:
  - In `resources/views/livewire/lawyers/manage-consultations.blade.php`:
    - Wrapped each card with `wire:key="consultation-card-{{ $consultation->id }}"`.
    - Updated buttons to use `wire:click.prevent`.
    - Added `wire:ignore.self` to all modal root divs.
- **Files Modified**:
  - `resources/views/livewire/lawyers/manage-consultations.blade.php`
- **Date Implemented**: Current day

22. Rebuilt modals for Complete and View Contract on `/lawyer/consultations`
- **Problem**: Modals did not reliably open because they were conditionally rendered and sometimes destroyed during Livewire updates.
- **Solution**: Keep modals always in the DOM, toggle visibility via Alpine `x-show` bound to Livewire properties using `@entangle().live`, and close on overlay click/escape. Ensured buttons call Livewire to set state and Alpine reflects it immediately.
- **Implementation Details**:
  - Converted both modals to always-present blocks with:
    - `x-data="{ open: @entangle('showCompleteModal').live }"` and `x-data="{ open: @entangle('showReviewContractModal').live }"`
    - `x-show="open" x-cloak` and `wire:ignore.self` on the modal wrapper
    - Esc/overlay click closes and updates Livewire via `$wire.$set(...)`
  - Left all form bindings (`wire:model`) intact
- **Files Modified**:
  - `resources/views/livewire/lawyers/manage-consultations.blade.php`
- **Date Implemented**: Current day