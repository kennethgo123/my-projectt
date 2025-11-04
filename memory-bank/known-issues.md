# Known Issues and Solutions

## Resolved Issues

1. **PaymentController Redirect Error**
   - **Issue**: The PaymentController was causing a "Missing required parameter for [Route: client.case.view]" error when processing payments for invoices without an associated legal case.
   - **Solution**: Modified the PaymentController to check if the invoice has a legal_case_id before trying to redirect to the case view. If no legal case is associated, it now redirects to the client.invoices route instead.
   - **Files Modified**: 
     - app/Http/Controllers/PaymentController.php

2. **Lawyer Rating Notification Error**
   - **Issue**: The RateLawyer component was trying to call a private method `createSystemNotification` in the NotificationService class.
   - **Solution**: Added a new public method `lawyerRated` to the NotificationService class and updated the RateLawyer component to use it.
   - **Files Modified**:
     - app/Services/NotificationService.php
     - app/Livewire/Client/RateLawyer.php

3. **Law Firm Ratings Table Issue**
   - **Issue**: The law_firm_ratings table was not properly created despite the migration being run, resulting in "Table 'lexcav.law_firm_ratings' doesn't exist" error.
   - **Solution**: Created and ran a fix migration (2025_05_15_fix_law_firm_ratings_table.php) that checks if the table exists properly with all required columns, drops it if it's incomplete, and recreates it with the correct structure.
   - **Files Added**:
     - database/migrations/2025_05_15_fix_law_firm_ratings_table.php
   - **Date Fixed**: Current day

4. **Lawyer Reviews Not Displaying in Nearby-Lawyers Page**
   - **Issue**: Lawyer reviews were not displaying in the nearby-lawyers page modal when viewing lawyer profiles, while law firm reviews were displaying correctly.
   - **Solution**: Added the missing implementation for lawyer reviews display in the nearby-lawyers blade template, replacing the placeholder comment with the actual review display code.
   - **Files Modified**:
     - resources/views/livewire/lawyers/nearby-lawyers.blade.php
   - **Date Fixed**: Current day

5. **Non-Functional Search Bar in Client Navbar**
   - **Issue**: The search bar in the client navbar was not functioning and not linked to the nearby-lawyers page.
   - **Solution**: Added the NavbarSearch Livewire component to the client navbar for both desktop and mobile views, which redirects search queries to the nearby-lawyers page.
   - **Files Modified**:
     - resources/views/components/client-navbar.blade.php
   - **Date Fixed**: Current day

6. **Duplicate Search Bar and Navigation Issues**
   - **Issue**: Search functionality was duplicated in both the client navbar and the main layout, with neither working correctly.
   - **Solution**: Removed the duplicate search bar from the client navbar and simplified the NavbarSearch component to directly redirect to nearby-lawyers. Streamlined the JavaScript event handling.
   - **Files Modified**:
     - resources/views/components/client-navbar.blade.php
     - app/Livewire/NavbarSearch.php
     - resources/views/livewire/navbar-search.blade.php
   - **Date Fixed**: Current day

7. **URL Generation Error in Lawyer Detail Modal**
   - **Issue**: "Missing required parameter for [Route: client.book-consultation] [URI: client/book-consultation/{lawyer_id}] [Missing parameter: lawyer_id]" error in the lawyer modal content.
   - **Solution**: Fixed the URL generation by properly formatting the route parameter in the lawyer modals and cards, using the named parameter format.
   - **Files Modified**:
     - resources/views/livewire/lawyers/components/lawyer-modal-content.blade.php
     - resources/views/livewire/lawyers/components/lawyer-card.blade.php
     - resources/views/livewire/lawyers/components/lawfirm-card.blade.php
   - **Date Fixed**: Current day

8. **PayMongo Credit Card Payment Error**
   - **Issue**: Credit card payment was failing with the error "The source_type passed card is invalid" when trying to pay invoices.
   - **Solution**: Fixed the `payWithCreditCard` method in `InvoiceManagement.php` to use `createPaymentIntent` instead of incorrectly using `createSource` with 'card' as the source type. Card payments should use the Payment Intents API, while other payment methods like GCash use the Sources API.
   - **Files Modified**:
     - app/Livewire/Client/InvoiceManagement.php
   - **Date Fixed**: Current day

9. **Invoice Quantity and Tax Fields Removed**
   - **Issue**: Request to remove quantity and tax fields from invoice creation forms.
   - **Solution**: 
     - Made the quantity column nullable in the database through a migration
     - Updated the InvoiceItem model to handle null quantities when calculating amounts
     - Updated the Invoice model's recalculateTotals method to set tax to 0
     - Removed the tax input fields from both invoice creation forms
     - Made the quantity field optional in both invoice creation forms
     - Updated validation rules in both Livewire components
   - **Files Modified**:
     - database/migrations/2025_05_17_210034_make_quantity_nullable_in_invoice_items.php (new)
     - app/Models/InvoiceItem.php
     - app/Models/Invoice.php
     - app/Livewire/Lawyer/CaseInvoices.php
     - app/Livewire/Lawyer/InvoiceManagement.php
     - resources/views/livewire/lawyer/case-invoices.blade.php
     - resources/views/livewire/lawyer/invoice-management.blade.php
   - **Date Fixed**: Current day

10. **Added Invoice Installment Payment Plans**
   - **Issue**: Need to allow lawyers to offer installment payment plans for invoices.
   - **Solution**: 
     - Added a payment_plan column to the invoices table with options: full, 3_months, 6_months, 1_year
     - Updated the Invoice model with constants for payment plans and added to fillable array
     - Added payment plan selection to both invoice creation forms
     - Updated Livewire components to handle payment plan selection
     - Added payment plan display to invoice view modals
   - **Files Modified**:
     - database/migrations/2025_05_17_211114_add_payment_plan_to_invoices.php (new)
     - app/Models/Invoice.php
     - app/Livewire/Lawyer/CaseInvoices.php
     - app/Livewire/Lawyer/InvoiceManagement.php
     - resources/views/livewire/lawyer/case-invoices.blade.php
     - resources/views/livewire/lawyer/invoice-management.blade.php
   - **Date Fixed**: Current day

## Current Issues

1. **Modal Event Handling Issues**
   - **Description**: Modals triggered via event dispatching (`$dispatch('open-modal', 'modal-name')`) may not reliably open in certain contexts.
   - **Status**: Identified. Workaround implemented using direct Alpine.js state management.
   - **Components Affected**: CasePhaseTracker, potentially other components using x-modal.
   - **Date Identified**: Current day (when fixing the Add Phase button).

2. **Close Case Modal Auto-Opening Issue**
   - **Description**: The close case modal appears automatically when opening any case, regardless of case status.
   - **Status**: Fixed. Modified the modal component's event listener to check for a userInitiated flag.
   - **Components Affected**: CasePhaseTracker, x-modal component
   - **Date Identified**: Recent days
   - **Date Fixed**: Current day

3. **Consultation Type Inconsistency**
   - **Description**: Inconsistent values used for consultation types in the database ('in_house', 'inhouse', 'In-House Consultation', etc.).
   - **Status**: Partially addressed with conditional checks in templates. Database standardization pending.
   - **Components Affected**: ManageConsultations display and filtering logic.
   - **Impact**: Office address button visibility conditions need to check multiple possible values.

## Functional Issues

1. **Add Phase Button Not Working**
   - **Description**: The "Add Phase" button in the CasePhaseTracker component was not opening the modal dialog correctly.
   - **Status**: Fixed by replacing event-based modal trigger with direct Alpine.js state control.
   - **Components Affected**: CasePhaseTracker
   - **Root Cause**: Issues with event propagation or handling in the modal component.

## Data Management Issues

1. **PHP Artisan Migrate Fresh Risk**
   - **Description**: Running `php artisan migrate:fresh` will delete the database.
   - **Status**: Active risk - must be avoided.
   - **Mitigation**: Core rule established to never run this command.

## Backend Issues

1. **CasePhaseTracker Livewire Error**
   - **Description**: "Unable to set component data. Public property [$] not found on component: [components.case-phase-tracker]"
   - **Status**: Fixed. Modified the component to accept caseId instead of the full case object.
   - **Components Affected**: CasePhaseTracker
   - **Date Fixed**: Current day

2. **Potential N+1 Query Issues**
   - **Description**: Some components may have inefficient database querying patterns loading related models.
   - **Status**: To be investigated.
   - **Impact**: May affect performance as the database grows.

3. **Search Bar Not Redirecting to Nearby Lawyers**
   - **Issue**: The search bar in the navbar wasn't redirecting users to the nearby-lawyers page when used, and required manual refresh for search results.
   - **Solution**: Replaced the Livewire-powered search form with a standard HTML form that submits directly to the client.nearby-lawyers route with the search parameter.
   - **Files Modified**:
     - resources/views/livewire/navbar-search.blade.php
     - app/Livewire/NavbarSearch.php
     - app/Livewire/Lawyers/NearbyLawyers.php 

## Chat/Messages Route Issue Fix

- **Issue**: Internal Server Error when accessing messages as a lawyer due to route returning a Livewire component object directly.
- **Error**: `TypeError: Symfony\Component\HttpFoundation\Response::setContent(): Argument #1 ($content) must be of type ?string, App\Livewire\Messages\Chat given`
- **Solution**: 
  - Updated the `/messages` route to return a proper view instead of a component object
  - Created a new `resources/views/messages/index.blade.php` file that renders the Chat component
  - The file structure is now:
    ```
    resources/views/
      messages/
        index.blade.php (for lawyer chat)
      client/messages/
        index.blade.php (for client chat)
    ```
- **Files Modified**:
  - `routes/web.php`
  - Created new file: `resources/views/messages/index.blade.php` 

## Duplicate Function Declaration in Nearby Lawyers Template

- **Issue**: Internal Server Error when viewing the nearby-lawyers page due to duplicate function declarations.
- **Error**: `Cannot redeclare function renderMaxBadge() (previously declared in /Users/admin/lexcav/storage/framework/views/6bca6618e50e694636d5829928225344.php:3)`
- **Solution**: Removed the duplicate PHP function declarations (renderMaxBadge, renderProBadge, and renderSubscriptionBadge) that were defined twice in the nearby-lawyers.blade.php file.
- **Root Cause**: The file had helper functions defined at the top of the file and then again later around line 849.
- **Files Modified**:
  - `resources/views/livewire/lawyers/nearby-lawyers.blade.php`
- **Date Fixed**: Current day

## Multiple Root Elements in Nearby Lawyers Template

- **Issue**: Internal Server Error when viewing the nearby-lawyers page due to multiple root elements.
- **Error**: `Livewire\Features\SupportMultipleRootElementDetection\MultipleRootElementsDetectedException: Livewire only supports one HTML element per component. Multiple root elements detected for component: [lawyers.nearby-lawyers]`
- **Solution**: Completely restructured the nearby-lawyers.blade.php file by:
  1. Splitting it into multiple smaller, manageable components
  2. Creating a single root element in the main file
  3. Using includes to maintain organization of the code
  4. Each component now handles a specific part of the UI
- **Root Cause**: The file had two root-level divs - one at the beginning and another after some PHP code declarations.
- **Files Modified/Created**:
  - `resources/views/livewire/lawyers/nearby-lawyers.blade.php` (main file, now includes components)
  - `resources/views/livewire/lawyers/components/subscription-badges.blade.php` (helper functions)
  - `resources/views/livewire/lawyers/components/lawyer-filters.blade.php` (sidebar filters)
  - `resources/views/livewire/lawyers/components/lawyer-listings.blade.php` (main content area)
  - `resources/views/livewire/lawyers/components/lawyer-card.blade.php` (individual lawyer cards)
  - `resources/views/livewire/lawyers/components/lawfirm-card.blade.php` (law firm cards)
  - `resources/views/livewire/lawyers/components/lawyer-modal.blade.php` (detail modal)
  - `resources/views/livewire/lawyers/components/lawyer-modal-content.blade.php` (modal content)
  - `resources/views/livewire/lawyers/components/reviews.blade.php` (reviews section)
- **Benefits**:
  - Fixed the multiple root elements issue
  - Made the code much more maintainable and easier to edit
  - Improved organization through component separation
  - Reduced file size for each component
- **Date Fixed**: Current day

## Law Firm Upload Revised Contract Button Issue

- **Issue**: The "Upload Revised Contract" button for law firms doesn't work after a client has requested changes.
- **Status**: Debugging paused. Debug tools and logs have been removed from the codebase.
- **Previous Debugging Steps (Removed)**:
  - Added temporary debug tools to force open the modal
  - Added extensive logging to the openUploadRevisedContractModal method
  - Temporarily removed the status check to allow testing with any case status
  - Added debug information display in the modal
  - Added ability to manually select cases for testing
- **Potential Issues (Still to investigate if debugging resumes)**:
  - The constant `STATUS_CHANGES_REQUESTED_BY_CLIENT` might not be correctly used or the status itself might not be `changes_requested_by_client` when the button is shown.
  - The case status might not be getting updated correctly when clients request changes.
  - There might be an issue with the modal rendering conditions or the `wire:click` action on the original button.
- **Date Identified**: Current day