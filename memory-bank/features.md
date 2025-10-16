# LexCav Features Overview

This document provides a comprehensive overview of all features implemented in the LexCav legal consultation and case management system.

## Core System Features

### User Management
- **Multi-role System**: Clients, Lawyers, Law Firms, Admins, Super Admins
- **Profile Management**: Separate profiles for each user type with role-specific fields
- **User Approval Workflow**: Pending, approved, rejected, deactivated status system
- **Featured Users**: Highlighting system for prominent lawyers/firms

### Authentication & Authorization
- **Role-based Access Control**: Different permissions for each user role
- **Department-based Permissions**: Admin users can be assigned to departments with specific permissions
- **Profile Completion Requirements**: Users must complete profiles before full system access

### Consultation System
- **Consultation Booking**: Clients can book consultations with lawyers or law firms
- **Multiple Consultation Types**: Online and In-House consultations
- **Lawyer Availability System**: Lawyers can set weekly availability schedules with time slots
- **Lunch Break Management**: Lawyers can set lunch breaks that automatically block time slots
- **Time Slot Blocking**: Accepted consultations automatically block time slots to prevent double booking
- **Preferred Dates**: Clients can provide multiple preferred consultation times
- **Document Attachments**: Clients can attach documents to consultation requests
- **Status Tracking**: Pending, accepted, declined, completed consultation states
- **Meeting Links**: Automatic generation or custom meeting links for online consultations
- **Consultation Results**: Lawyers can record consultation outcomes and findings

### Case Management
- **Case Creation**: Convert consultations into legal cases
- **Case Phases**: Multi-phase case tracking system
- **Case Documents**: Document management for each case
- **Case Events**: Calendar events related to cases (hearings, meetings)
- **Case Tasks**: Task assignment and tracking for case progress
- **Case Updates**: Progress updates and notes
- **Case Invoicing**: Billing integration for case services

### Legal Services
- **Service Categories**: Organized legal service offerings
- **Lawyer-Service Associations**: Lawyers can specify which services they provide
- **Law Firm Services**: Firms can offer services through their lawyers
- **Service Pricing**: Flexible pricing models for different services

### Communication System
- **Real-time Notifications**: Live updates for system events
- **Email Notifications**: Email alerts for important events
- **Direct Messaging**: Communication between users
- **Notification Management**: Mark as read/unread, notification history

### Billing & Payments
- **Invoice Generation**: Automated invoice creation for cases
- **Payment Tracking**: Monitor payment status and history
- **Multiple Payment Methods**: Support for various payment options
- **Billing Status Management**: Pending, invoiced, paid, partial, disputed states

### Search & Discovery
- **Lawyer Search**: Find lawyers by location, specialization, availability
- **Law Firm Search**: Discover law firms and their services
- **Advanced Filtering**: Filter by budget, location, services, ratings
- **Featured Professionals**: Highlighted lawyers and firms

### Administrative Features
- **User Management**: Admin tools for managing all users
- **Department Management**: Organize admin staff into departments
- **Permission Management**: Fine-grained access control
- **Maintenance Mode**: System maintenance scheduling
- **Reporting**: Various reports for system monitoring
- **Subscription Management**: Handle user subscriptions and billing

### Investigation System (Latest Enhancement)
- **Unified Investigation Dashboard**: Comprehensive view of lawyer-client interactions for report investigations
- **Interaction Timeline Aggregator**: Chronological display of all communications, consultations, cases, and activities
- **Red Flag Detection**: Automated pattern analysis to identify suspicious behaviors
- **Investigation Case Management**: Track investigation status, priority, findings, and recommendations
- **Evidence Collection**: Centralized documentation and metadata preservation
- **Statistical Analysis**: Interaction metrics, response times, and communication patterns
- **Access Control**: Secure, permission-based access limited to authorized staff
- **Investigation Workflow**: Status tracking from assignment through completion

### Technical Features
- **Real-time Updates**: LiveWire components for dynamic interactions
- **File Management**: Secure document upload and storage
- **Data Validation**: Comprehensive form validation
- **Error Handling**: Graceful error management and user feedback
- **Performance Optimization**: Efficient database queries and caching
- **Security**: Data protection and secure access controls

## Recent Enhancements (Latest Update)

### Lunch Break Management
- **Availability Enhancement**: Lawyers can now set lunch breaks within their availability schedules
- **Automatic Time Blocking**: Time slots during lunch breaks are automatically unavailable to clients
- **Flexible Scheduling**: Different lunch times can be set for different days
- **Visual Indicators**: Lunch break times are clearly displayed in availability management

### Advanced Time Slot Blocking
- **Consultation Time Slots**: Consultations now require specific start and end times
- **Conflict Prevention**: System prevents double-booking of time slots
- **Blocked Time Slot Tracking**: Database table tracks all blocked time periods
- **Automatic Cleanup**: Blocked slots are automatically removed when consultations are completed/declined
- **Multi-reason Blocking**: Support for different blocking reasons (consultations, lunch breaks, custom blocks)

### Enhanced Consultation Booking
- **Precise Time Selection**: Clients select specific time slots with start and end times
- **Real-time Availability**: Time slots exclude lunch breaks and already booked times
- **Improved User Experience**: Better visualization of available consultation times
- **Conflict Detection**: Prevents booking conflicts at the database level

### Calendar-Based Date Selection (Latest Enhancement)
- **Interactive Calendar Interface**: Visual calendar for date selection in consultation booking
- **Availability Indicators**: Green dots show dates with available time slots
- **Date Restrictions**: Past dates are automatically disabled and visually distinguished
- **Month Navigation**: Easy navigation between months with previous/next buttons
- **Real-time Updates**: Calendar dynamically updates when different lawyers are selected
- **Cross-month Availability**: Shows availability across different months for better planning
- **Visual Feedback**: Clear indication of selected dates, today's date, and availability status

## System Architecture

### Database Design
- **Relational Structure**: Well-normalized database with clear relationships
- **Audit Trails**: Tracking of important changes and events
- **Soft Deletes**: Preserve data integrity while allowing "deletion"
- **Indexing**: Optimized for common query patterns

### Frontend Technology
- **Laravel Blade**: Server-side templating
- **LiveWire**: Dynamic, reactive components
- **Tailwind CSS**: Utility-first styling framework
- **Alpine.js**: Lightweight JavaScript framework

### Backend Technology
- **Laravel Framework**: Modern PHP framework
- **MySQL Database**: Reliable relational database
- **Queue System**: Background job processing
- **Event System**: Decoupled event handling
- **Notification System**: Multi-channel notifications

## Integration Points

### External Services
- **Email Service**: SMTP integration for notifications
- **File Storage**: Secure document storage
- **Payment Processing**: Integration with payment gateways
- **Maps Integration**: Location services for offices

### API Capabilities
- **RESTful Design**: Clean API structure
- **Authentication**: Secure API access
- **Rate Limiting**: API usage controls
- **Documentation**: Comprehensive API docs

## Pro Bono Case Management (Latest Enhancement)

### Pro Bono Case Designation
- **Primary Lawyer Control**: Only primary lawyers can designate cases as pro bono
- **Irreversible Action**: Pro bono status cannot be undone once set
- **Confirmation Modal**: Multi-step confirmation process with required reasoning
- **Case Documentation**: Automatic case update creation when pro bono status is set
- **Visual Indicators**: Pro bono cases display special badges and status indicators
- **Client Transparency**: Pro bono status and reasoning visible to clients
- **Access Control**: Disabled for closed cases and non-primary lawyers
- **Audit Trail**: Complete tracking of when and why cases were marked as pro bono

## Enhanced Invoice System Integration (Latest Enhancement)

### Case-Specific Invoice Management
- **Improved UI**: Modern invoice interface with better user experience
- **Auto-Client Assignment**: Invoices automatically assigned to case client
- **Case Context**: Invoice creation within case context for better organization
- **Primary Lawyer Control**: Only primary lawyers can create/edit/send invoices
- **Advanced Features**: Sorting, filtering, search functionality
- **Detailed Invoice View**: Comprehensive invoice details modal
- **Item Management**: Dynamic invoice items with multiple types (service, expense, billable hours, other)
- **Payment Plans**: Support for full payment, 3 months, 6 months, and 1-year plans
- **Status Management**: Draft, pending, paid, partial, overdue, cancelled statuses
- **Notifications**: Automatic client notifications for invoice actions
- **Professional Invoice Numbers**: Smart sequential numbering system
- **Simplified Pricing**: Removed discount functionality for cleaner invoicing
- **Pro Bono Integration**: Automatic zero-amount invoicing for pro bono cases with instant payment marking

This system provides a complete solution for legal consultation and case management, with robust features for all stakeholders in the legal service ecosystem.