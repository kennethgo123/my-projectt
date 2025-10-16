# Component Definitions

## Livewire Components

### Client Components

1. **ManageConsultations**
   - Path: `app/Livewire/Client/ManageConsultations.php`
   - View: `resources/views/livewire/client/manage-consultations.blade.php`
   - Purpose: Allows clients to view and manage their consultations, including accessing meeting links for online consultations and office addresses for in-house consultations.
   - Features: Consultation listing, filtering, status viewing, action buttons based on consultation type and status.
   - Integration: Used in `resources/views/client/consultations/index.blade.php` with the new ClientNavbar component.
   - Updates: 
     - Improved lawyer name display to properly show full names from law_firm_lawyers table and firm names from law_firm_profiles table.
     - Fixed photo display to correctly use photo_path from the law_firm_profiles table for law firms.

2. **ManageCases**
   - Path: `app/Livewire/Client/ManageCases.php`
   - View: `resources/views/livewire/client/manage-cases.blade.php`
   - Purpose: Enables clients to view and manage their legal cases.
   - Features: Case listing, status viewing, document access, consultation history.
   - Integration: Used in `resources/views/client/cases/index.blade.php` with the new ClientNavbar component.

3. **ViewCase**
   - Path: `app/Livewire/Client/ViewCase.php`
   - View: `resources/views/livewire/client/view-case.blade.php`
   - Purpose: Provides detailed view of a specific case.
   - Features: Case details, timeline, documents, consultations, office address for in-house consultations.

4. **InvoiceManagement**
   - Path: `app/Livewire/Client/InvoiceManagement.php`
   - View: `resources/views/livewire/client/invoice-management.blade.php`
   - Purpose: Allows clients to view and manage their invoices.
   - Features: Invoice listing, filtering, payment processing.
   - Integration: Used in `resources/views/client/invoices/index.blade.php` with the new ClientNavbar component.

5. **BookConsultation**
   - Path: `app/Livewire/Client/BookConsultation.php`
   - View: `resources/views/livewire/client/book-consultation.blade.php`
   - Purpose: Enables clients to book consultations with lawyers or law firms.
   - Features: 
     - Interactive calendar interface for date selection
     - Lawyer availability-based time slot selection
     - Consultation type selection (Online/In-House)
     - Document upload functionality
     - Law firm lawyer selection for multi-lawyer firms
     - Real-time availability checking
   - Key Methods:
     - `generateCalendarDays()`: Creates calendar grid with availability indicators
     - `selectDate($date)`: Handles calendar date selection
     - `loadTimeSlotsForDate()`: Loads available time slots for selected date
     - `previousMonth()/nextMonth()`: Calendar navigation
     - `submitConsultation()`: Processes consultation booking
   - Properties:
     - `$selectedDate`: Currently selected calendar date
     - `$currentCalendarMonth`: Current month being displayed
     - `$calendarDays`: Array of calendar day data with availability
     - `$availableTimeSlots`: Time slots for selected date
     - `$useAvailability`: Toggle for availability-based booking

### Messages Components

1. **Chat**
   - Path: `App\Livewire\Messages\Chat`
   - View: `resources/views/livewire/messages/chat.blade.php`
   - Purpose: Provides messaging functionality between clients and lawyers.
   - Features: Real-time chat, message history, conversation list.
   - Integration: Used in `resources/views/client/messages/index.blade.php` with the new ClientNavbar component for clients.

### Lawyer Components

1. **CaseSetup**
   - Path: `app/Livewire/Lawyer/CaseSetup.php`
   - View: `resources/views/livewire/lawyer/case-setup.blade.php`
   - Purpose: Allows lawyers to set up and configure new cases.
   - Features: Case information entry, phase setup, document upload.

### Shared Components

1. **CasePhaseTracker**
   - Path: `app/Livewire/Components/CasePhaseTracker.php`
   - View: `resources/views/livewire/components/case-phase-tracker.blade.php`
   - Purpose: Tracks and displays the progress of a case through various phases.
   - Features: Phase visualization, phase management (add, edit, update), progress tracking.

## UI Components

1. **Modal Component**
   - Path: `resources/views/components/modal.blade.php`
   - Purpose: Reusable modal dialog component.
   - Note: Used with Alpine.js for state management; works best with direct state control rather than event dispatching.

2. **ClientNavbar**
   - Path: `app/View/Components/ClientNavbar.php`
   - View: `resources/views/components/client-navbar.blade.php`
   - Purpose: Dedicated navigation component for client users.
   - Features: Stylized "Lexcav" text logo, Home, Find Legal Help, Manage My Cases, My Consultations, Messages, and My Invoices navigation links with active state styling.
   - Integration: Used in the main app layout for authenticated client users.

## Views

1. **Client Cases Index**
   - Path: `resources/views/client/cases/index.blade.php`
   - Purpose: Container view for the client cases management page.
   - Features: Uses the app layout with the ClientNavbar component and loads the ManageCases livewire component.

2. **Client Consultations Index**
   - Path: `resources/views/client/consultations/index.blade.php`
   - Purpose: Container view for the client consultations management page.
   - Features: Uses the app layout with the ClientNavbar component and loads the ManageConsultations livewire component.

3. **Client Messages Index**
   - Path: `resources/views/client/messages/index.blade.php`
   - Purpose: Container view for the client messages page.
   - Features: Uses the app layout with the ClientNavbar component and loads the Chat livewire component.

4. **Client Invoices Index**
   - Path: `resources/views/client/invoices/index.blade.php`
   - Purpose: Container view for the client invoices management page.
   - Features: Uses the app layout with the ClientNavbar component and loads the InvoiceManagement livewire component.

## Models

1. **LegalCase**
   - Path: `app/Models/LegalCase.php`
   - Purpose: Represents a legal case in the system.
   - Relations: Belongs to Client, Lawyer; Has many Documents, Consultations, CasePhases.

2. **Consultation**
   - Path: `app/Models/Consultation.php`
   - Purpose: Represents a consultation between client and lawyer.
   - Features: Tracks consultation type (online, in-house), status, meeting links, related case.
   - Key fields: `consultation_type`, `status`, `meeting_link`

3. **CasePhase**
   - Path: `app/Models/CasePhase.php`
   - Purpose: Represents a phase in a legal case's lifecycle.
   - Features: Tracks phase name, description, start/end dates, status (current, completed).

## JS Libraries

1. **Alpine.js**
   - Purpose: Lightweight JavaScript framework for component behavior.
   - Usage: Modal functionality, interactive UI elements, event handling.
   - Note: Used with two different approaches for modals - direct state management (preferred) and event dispatching. 

## Admin Departments

### IT Operations/Infrastructure Department
- **Purpose**: Manages system maintenance and infrastructure operations
- **Permissions**: 
  - `enable_maintenance_mode`: Can enable/disable maintenance mode
  - `schedule_maintenance`: Can schedule maintenance windows with start/end times
  - `view_maintenance_logs`: Can view maintenance activity logs
- **Functionality**:
  - Schedule maintenance mode with specific start and end date/time
  - During maintenance: Normal users see "LexCav is currently undergoing system maintenance so we can serve you better. Thank you for your patience"
  - Admin and staff users can still access the system during maintenance
  - Maintenance mode blocks authentication for regular users (client, lawyer, law_firm roles)

### Maintenance Mode System
- **Database Table**: `maintenance_schedules`
- **Fields**:
  - `title`: Maintenance title/description
  - `description`: Details about the maintenance
  - `start_datetime`: When maintenance begins
  - `end_datetime`: When maintenance ends
  - `is_active`: Whether this schedule is currently active
  - `created_by`: User who created the schedule
- **Middleware**: `MaintenanceModeMiddleware` checks for active maintenance and blocks regular users
- **Message**: Custom maintenance message shown to blocked users 

## Investigation System Components

### InvestigationDashboard
- **Path**: `app/Livewire/Admin/InvestigationDashboard.php`
- **View**: `resources/views/livewire/admin/investigation-dashboard.blade.php`
- **Purpose**: Provides comprehensive investigation tools for client reports against lawyers.
- **Features**:
  - Unified timeline of all lawyer-client interactions
  - Red flag detection and pattern analysis  
  - Investigation case management and workflow
  - Statistical analysis of communication patterns
  - Evidence collection and documentation
  - Date range and interaction type filtering
  - Investigation notes, findings, and recommendations
  - **Staff file attachment upload system with multiple file types**
  - **Irreversible completion confirmation modal**
  - **Investigation locking system for completed cases**
- **Key Methods**:
  - `loadTimelineData()`: Aggregates all interaction data using InvestigationTimelineService
  - `applyFilters()`: Filters timeline by type, category, and severity
  - `toggleTimelineDetails()`: Shows/hides detailed interaction information
  - `startInvestigation()`: Begins investigation workflow
  - `updateInvestigation()`: Updates investigation status and documentation (with completion confirmation)
  - `uploadAttachments()`: Handles file upload for investigation evidence/documents
  - `deleteAttachment()`: Removes attachments (disabled when investigation is locked)
  - `confirmComplete()`: Confirms irreversible investigation completion
  - `cancelComplete()`: Cancels completion process
- **Properties**:
  - `$timeline`: Array of aggregated interactions from InvestigationTimelineService
  - `$interactionStats`: Statistical analysis of lawyer-client interactions
  - `$redFlags`: Automatically detected suspicious patterns
  - `$investigation`: InvestigationCase model instance
  - `$attachments`: File upload array for staff attachments
  - `$attachmentDescription`: Optional description for uploaded files
  - `$attachmentType`: Type categorization (evidence, document, image, other)
  - `$showConfirmComplete`: Controls completion confirmation modal visibility
- **Services**: Uses `InvestigationTimelineService` for data aggregation and analysis
- **Models**: 
  - `InvestigationCase`: Tracks investigation workflow and documentation with locking functionality
  - `InvestigationAttachment`: File attachment management for investigations
  - `Report`: Source report being investigated
- **Access Control**: Requires `view_client_reports` permission
- **Route**: `/admin/investigation/{reportId}` accessible from client report management
- **Report Status Sync**: When investigation is completed, automatically updates parent report status to "resolved"
- **Clean Status Display**: Shows only "Investigation Completed" when investigation is completed (no duplicate report status)
- **Action Control**: Hides Edit/Resolve/Dismiss buttons when investigation is completed

## JavaScript Fixes Applied

### Notification System
- **Fixed**: Alpine.js syntax errors in notification dismissal
- **Issue**: UUID parameters not properly quoted in JavaScript expressions
- **Solution**: Added quotes around `{{ $notification->id }}` in `@click` handlers
- **Files Fixed**:
  - `resources/views/livewire/components/notification-dropdown.blade.php`
  - `resources/views/livewire/notifications/all-notifications.blade.php`

### Consultation Completion
- **Enhanced**: Added error handling and logging to consultation completion
- **Added**: Loading states and visual feedback for button interactions
- **Debugging**: Added console logging and detailed error tracking

## Client Dashboard Enhancements

### Navigation & FAQ System
- **Location**: `resources/views/livewire/client/dashboard.blade.php`
- **Added**: Tabbed navigation system with comprehensive FAQ
- **Features**:
  - **Dashboard Overview**: Quick start guide and action buttons
  - **Finding & Hiring Lawyers**: Search and messaging guidance
  - **Payments & Security**: Escrow system and payment methods explanation
  - **Disputes & Support**: Report creation and mediation process
  - **General FAQ**: Platform guidelines and recommendations
- **Technology**: Alpine.js for interactive tabs with smooth transitions
- **User Experience**: Professional layout with clear Q&A format

### InvestigationAttachment Model
- **Path**: `app/Models/InvestigationAttachment.php`
- **Database Table**: `investigation_attachments`
- **Purpose**: Manages file attachments for investigations (evidence, documents, images)
- **Features**:
  - Secure file storage in `investigation-attachments/{investigation_id}/` directories
  - UUID-based filename generation for security
  - File type categorization (evidence, document, image, other)
  - File size tracking and formatted display
  - Automatic file deletion when record is deleted
  - Upload tracking with user attribution
- **Fields**:
  - `investigation_case_id`: Foreign key to investigation case
  - `uploaded_by`: Foreign key to user who uploaded file
  - `original_filename`: Original name of uploaded file
  - `stored_filename`: UUID-based stored filename
  - `file_path`: Full storage path
  - `mime_type`: File MIME type
  - `file_size`: File size in bytes
  - `description`: Optional file description
  - `attachment_type`: Categorization (evidence, document, image, other)
- **Controller**: `InvestigationAttachmentController` handles secure downloads
- **Route**: `/investigation/attachment/download/{attachmentId}` for secure file access