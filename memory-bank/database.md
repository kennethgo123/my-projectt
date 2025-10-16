# Database Structure

## Overview

LexCav uses a MySQL database with multiple interconnected tables designed to manage legal consultations, cases, users, and related data. The database follows a relational model with clearly defined relationships between entities.

## Core Tables

### Users
- **Table**: `users`
- **Primary Key**: `id`
- **Description**: Central user table that stores all users of the system (clients, lawyers, law firms, admins)
- **Key Fields**:
  - `name`: User's display name
  - `email`: Unique email address (used for authentication)
  - `password`: Encrypted password
  - `role_id`: References `roles` table to determine user type
  - `status`: Enum ('pending', 'approved', 'rejected', 'deactivated')
  - `firm_id`: Foreign key to law firm (for lawyers associated with firms)
  - `profile_completed`: Boolean indicating if profile setup is complete
  - `is_featured`: Boolean for featured users

### Roles
- **Table**: `roles`
- **Primary Key**: `id`
- **Description**: Defines user roles in the system
- **Values**: 'client', 'lawyer', 'law_firm', 'admin'

### Profiles

#### Lawyer Profiles
- **Table**: `lawyer_profiles`
- **Primary Key**: `id`
- **Foreign Key**: `user_id` references `users.id`
- **Description**: Extended profile information for lawyer users
- **Key Fields**:
  - `law_firm_id`: Optional reference to law firm if lawyer belongs to one
  - `first_name`, `last_name`: Personal details
  - `contact_number`: Phone contact
  - `address`: Personal address
  - `office_address`: Office location (for in-house consultations)
  - `google_maps_link`: Link to office on Google Maps
  - `show_office_address`: Boolean controlling visibility of office address
  - `city`: Location in Cavite (enum)
  - `bar_admission_type`, `bar_admission_file`: Legal credentials
  - `min_budget`, `max_budget`: Price range for services
  - `languages`: JSON array of languages spoken
  - `offers_online_consultation`: Boolean
  - `offers_inhouse_consultation`: Boolean

#### Client Profiles
- **Table**: `client_profiles`
- **Primary Key**: `id`
- **Foreign Key**: `user_id` references `users.id`
- **Description**: Extended profile information for client users
- **Key Fields**:
  - Personal and contact information

#### Law Firm Profiles
- **Table**: `law_firm_profiles`
- **Primary Key**: `id` 
- **Foreign Key**: `user_id` references `users.id`
- **Description**: Profile information for law firms
- **Key Fields**:
  - `name`: Firm name
  - `contact_number`: Phone
  - `address`: Office address
  - `google_maps_link`: Maps link
  - `website`: Firm website
  - `description`: About the firm
  - `consultation_types`: Available consultation types
  - `allow_lawyer_availability`: Whether individual lawyers can set availability

## Consultation System

### Consultations
- **Table**: `consultations`
- **Primary Key**: `id`
- **Foreign Keys**:
  - `client_id` references `users.id`
  - `lawyer_id` references `users.id`
- **Description**: Records of consultation requests and sessions
- **Key Fields**:
  - `status`: Enum ('pending', 'accepted', 'declined', 'completed')
  - `consultation_type`: Enum ('online', 'in_house', 'Online Consultation', 'In-House Consultation')
  - `description`: Client's description of legal issue
  - `preferred_dates`: JSON array of client's preferred consultation dates
  - `selected_date`: Date/time confirmed for consultation
  - `start_time`: DateTime when consultation begins (new field for precise timing)
  - `end_time`: DateTime when consultation ends (new field for precise timing)
  - `meeting_link`: URL for online consultations
  - `is_completed`: Boolean indicating completion
  - `can_start_case`: Boolean indicating if a case can be created from this consultation
  - `specific_lawyer_id`: References specific lawyer assigned by law firm
  - `assign_as_entity`: Boolean indicating if consultation is assigned to firm as entity
- **Relationships**:
  - One-to-one with `legal_cases` (a consultation can lead to one case)
  - Many-to-one with client users
  - Many-to-one with lawyer users

### Lawyer Availabilities
- **Table**: `lawyer_availabilities`
- **Primary Key**: `id`
- **Foreign Key**: `user_id` references `users.id`
- **Description**: Tracks when lawyers are available for consultations
- **Key Fields**:
  - `day_of_week`: Enum ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
  - `start_time`: Time when availability begins
  - `end_time`: Time when availability ends
  - `is_available`: Boolean indicating if this time slot is active
  - `has_lunch_break`: Boolean indicating if this availability includes a lunch break
  - `lunch_start_time`: Time when lunch break starts (nullable)
  - `lunch_end_time`: Time when lunch break ends (nullable)

### Blocked Time Slots
- **Table**: `blocked_time_slots`
- **Primary Key**: `id`
- **Foreign Keys**:
  - `user_id` references `users.id`
  - `consultation_id` (nullable) references `consultations.id`
- **Description**: Tracks blocked time periods to prevent double booking
- **Key Fields**:
  - `start_time`: DateTime when the block begins
  - `end_time`: DateTime when the block ends
  - `reason`: String indicating reason for block ('consultation', 'lunch_break', 'custom')
  - `title`: Optional title for custom blocks
  - `description`: Optional description for custom blocks
- **Purpose**: Prevents scheduling conflicts by tracking all unavailable time periods

## Case Management System

### Legal Cases
- **Table**: `legal_cases`
- **Primary Key**: `id`
- **Foreign Keys**:
  - `client_id` references `users.id`
  - `lawyer_id` references `users.id`
  - `consultation_id` references `consultations.id`
  - `service_id` references `legal_services.id`
- **Description**: Central table for legal cases
- **Key Fields**:
  - `case_number`: Unique identifier (auto-generated)
  - `title`: Case title
  - `description`: Case details
  - `status`: Case status ('pending', 'accepted', 'rejected', 'contract_sent', 'contract_signed', 'active', 'closed', 'completed', 'cancelled')
  - `billing_status`: Enum ('pending', 'invoiced', 'paid', 'partial', 'disputed')
  - `priority`: Enum ('low', 'medium', 'high', 'urgent')
  - `deadline`: Target completion date
  - `contract_path`: Path to contract document
  - `signature_path`: Path to signed contract
  - `contract_status`: Contract status tracking
  - `setup_completed`: Boolean indicating case setup completion
  - `current_phase`: Current phase of the case
  - `archived`: Boolean for archived cases
- **Relationships**:
  - Many-to-one with client users
  - Many-to-one with lawyer users
  - One-to-one with consultations
  - Many-to-one with legal services
  - One-to-many with case phases, events, tasks, documents, and updates

### Case Phases
- **Table**: `case_phases`
- **Primary Key**: `id`
- **Foreign Key**: `legal_case_id` references `legal_cases.id`
- **Description**: Tracks different phases of a legal case
- **Key Fields**:
  - `name`: Phase name
  - `description`: Phase details
  - `start_date`: Phase start date
  - `end_date`: Expected completion date
  - `is_current`: Boolean indicating active phase
  - `is_completed`: Boolean indicating completion
  - `order`: Numeric ordering of phases

### Case Events
- **Table**: `case_events`
- **Primary Key**: `id`
- **Foreign Key**: `legal_case_id` references `legal_cases.id`
- **Description**: Records events related to cases (hearings, meetings, etc.)
- **Key Fields**:
  - `title`: Event title
  - `description`: Event details
  - `start_datetime`: Event start time
  - `end_datetime`: Event end time
  - `location`: Event location
  - `is_completed`: Completion status
  - `created_by`: User who created the event

### Case Tasks
- **Table**: `case_tasks`
- **Primary Key**: `id`
- **Foreign Key**: `legal_case_id` references `legal_cases.id`
- **Description**: Tasks related to case progress
- **Key Fields**:
  - `title`: Task title
  - `description`: Task details
  - `due_date`: Task deadline
  - `assigned_to_id`: User assigned to task
  - `assigned_to_type`: Type of assignee (lawyer, client)
  - `assigned_by`: User who assigned the task
  - `is_completed`: Completion status

### Case Documents
- **Table**: `case_documents`
- **Primary Key**: `id`
- **Foreign Key**: `legal_case_id` references `legal_cases.id`
- **Description**: Documents related to cases
- **Key Fields**:
  - `title`: Document title
  - `description`: Document description
  - `file_path`: Path to stored document
  - `uploaded_by`: User who uploaded the document
  - `visibility`: Who can view the document

### Case Updates
- **Table**: `case_updates`
- **Primary Key**: `id`
- **Foreign Key**: `legal_case_id` references `legal_cases.id`
- **Description**: Updates and notes on case progress
- **Key Fields**:
  - `user_id`: User who created the update
  - `update_type`: Type of update
  - `content`: Update content
  - `is_client_visible`: Whether clients can see the update

## Billing System

### Invoices
- **Table**: `invoices`
- **Primary Key**: `id`
- **Foreign Keys**:
  - `legal_case_id` references `legal_cases.id`
  - `client_id` references `users.id`
  - `lawyer_id` references `users.id`
- **Description**: Billing invoices for legal services
- **Key Fields**:
  - `invoice_number`: Unique identifier
  - `amount`: Total invoice amount
  - `status`: Payment status
  - `due_date`: Payment deadline

### Invoice Items
- **Table**: `invoice_items`
- **Primary Key**: `id`
- **Foreign Key**: `invoice_id` references `invoices.id`
- **Description**: Line items for invoices
- **Key Fields**:
  - `description`: Service description
  - `quantity`: Number of units
  - `unit_price`: Price per unit
  - `amount`: Total item amount

### Payments
- **Table**: `payments`
- **Primary Key**: `id`
- **Foreign Key**: `invoice_id` references `invoices.id`
- **Description**: Tracks payments made against invoices
- **Key Fields**:
  - `amount`: Payment amount
  - `payment_method`: Method used
  - `transaction_id`: External payment reference
  - `status`: Payment status

## Legal Services

### Legal Services
- **Table**: `legal_services`
- **Primary Key**: `id`
- **Description**: Types of legal services offered
- **Key Fields**:
  - `name`: Service name
  - `description`: Service description
  - `category`: Service category

### Lawyer-Service Relationships
- **Table**: `lawyer_legal_service`
- **Primary Keys**: (`lawyer_id`, `legal_service_id`)
- **Foreign Keys**:
  - `lawyer_id` references `users.id`
  - `legal_service_id` references `legal_services.id`
- **Description**: Many-to-many relationship between lawyers and services they offer

### Law Firm-Service Relationships
- **Table**: `law_firm_legal_service`
- **Primary Keys**: (`law_firm_id`, `legal_service_id`)
- **Foreign Keys**:
  - `law_firm_id` references `users.id`
  - `legal_service_id` references `legal_services.id`
- **Description**: Many-to-many relationship between law firms and services they offer

## Communication System

### Messages
- **Table**: `messages`
- **Primary Key**: `id`
- **Foreign Keys**:
  - `sender_id` references `users.id`
  - `recipient_id` references `users.id`
  - `legal_case_id` (optional) references `legal_cases.id`
- **Description**: Direct messages between users
- **Key Fields**:
  - `message`: Message content
  - `read_at`: When message was read
  - `attachment_path`: Optional file attachment

### Notifications
- **Table**: `app_notifications`
- **Primary Key**: `id`
- **Foreign Key**: `user_id` references `users.id`
- **Description**: System notifications for users
- **Key Fields**:
  - `type`: Notification type
  - `data`: JSON data for notification
  - `read_at`: When notification was read

## Administrative System

### Departments
- **Table**: `departments`
- **Primary Key**: `id`
- **Description**: Administrative departments for staff users
- **Key Fields**:
  - `name`: Department name
  - `description`: Department description
- **Current Departments**:
  - User Management Department
  - Financial Department
  - Law Services Department
  - Client Support Services
  - IT Operations/Infrastructure

### Permissions
- **Table**: `permissions`
- **Primary Key**: `id`
- **Description**: System permissions that can be assigned to departments or users
- **Key Fields**:
  - `name`: Permission display name
  - `slug`: Unique permission identifier
  - `description`: Permission description
  - `module`: Permission module/category

### Maintenance Schedules
- **Table**: `maintenance_schedules`
- **Primary Key**: `id`
- **Foreign Key**: `created_by` references `users.id`
- **Description**: Scheduled system maintenance windows
- **Key Fields**:
  - `title`: Maintenance title/description
  - `description`: Detailed maintenance description
  - `start_datetime`: When maintenance begins
  - `end_datetime`: When maintenance ends
  - `is_active`: Whether this schedule is currently active
  - `is_completed`: Whether maintenance has been completed
  - `created_by`: User who created the schedule
- **Purpose**: Enables IT Operations department to schedule maintenance that blocks regular users while allowing staff access

## Key Relationships

1. **User Role Relationship**:
   - Users have one role (client, lawyer, law firm, admin)
   - Each role has specific permissions and access levels

2. **Profile Relationships**:
   - Each lawyer has one lawyer profile
   - Each client has one client profile
   - Each law firm has one law firm profile

3. **Consultation Flow**:
   - Client requests consultation with lawyer
   - Lawyer accepts/rejects consultation
   - If accepted, consultation occurs and can lead to a legal case

4. **Case Management Flow**:
   - Cases are created from consultations or directly
   - Cases have phases, tasks, events, documents, and updates
   - Cases can generate invoices for billing

5. **Service Relationships**:
   - Lawyers offer multiple legal services
   - Law firms offer multiple legal services
   - Legal cases are associated with specific legal services

## Database Consistency Issues

1. **Consultation Type Inconsistency**:
   - The `consultation_type` field in the `consultations` table has inconsistent values: 'online', 'in_house', 'Online Consultation', 'In-House Consultation'
   - This requires conditional checks in templates to handle all possible values

2. **Field Naming Consistency**:
   - Some tables use different field names for similar concepts
   - For example, some Boolean fields use 'is_' prefix while others don't 