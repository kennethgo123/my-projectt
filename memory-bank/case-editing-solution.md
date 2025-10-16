# Case Number and Title Editing Solution

## Problem
Lawyers couldn't edit case number and title from the case setup page at `/lawyer/case/setup/{id}`.

## Solution
Added inline editing functionality to the case header section of the lawyer case setup page.

## Implementation Details

### Backend Changes (app/Livewire/Lawyer/CaseSetup.php)
1. **Added Properties**:
   - `public $editingCase = false;` - Controls edit mode state
   - `public $editCaseNumber = '';` - Holds case number during editing
   - `public $editCaseTitle = '';` - Holds case title during editing

2. **Added Validation Rules**:
   - `'editCaseNumber' => 'nullable|string|max:50'`
   - `'editCaseTitle' => 'required|string|max:255'`

3. **Added Methods**:
   - `startEditingCase()` - Enters edit mode and populates form fields
   - `cancelEditingCase()` - Exits edit mode and resets form
   - `updateCaseDetails()` - Validates and saves changes to database

### Frontend Changes (resources/views/livewire/lawyer/case-setup.blade.php)
1. **Modified Case Header**: Added conditional display for edit/view modes
2. **Edit Mode Features**:
   - Input fields for case number and title
   - Save and Cancel buttons
   - Proper validation error display
3. **View Mode Features**:
   - Display current case number and title
   - Edit button (only shown when case is not read-only)

## Key Features
- **Inline Editing**: Edit directly in the header without navigating to separate page
- **Validation**: Proper form validation with error messages
- **Read-Only Respect**: Edit functionality disabled for closed cases
- **Database Safety**: Uses transactions for safe updates
- **User Feedback**: Success/error messages for user actions

## Files Modified
- `/app/Livewire/Lawyer/CaseSetup.php`
- `/resources/views/livewire/lawyer/case-setup.blade.php`

## Date Implemented
Current day
