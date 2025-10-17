# Lexcav Consultation and Case Management System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Consultation Flow](#consultation-flow)
3. [Case Management](#case-management)
4. [Communication System](#communication-system)
5. [Database Schema](#database-schema)

## System Overview

The Lexcav platform provides an end-to-end solution for managing legal consultations and cases, facilitating communication between lawyers and clients. The system is designed to handle the entire lifecycle from initial consultation requests to case closure.

## Consultation Flow

### 1. Consultation Request
- Client selects a lawyer from the nearby-lawyers page
- Client fills out consultation request form:
  - Preferred consultation type (Online/In-House)
  - Preferred date and time (3 options)
  - Brief description of legal issue
  - Any relevant documents (optional)
  
### 2. Lawyer Notification
- Real-time notification via:
  - In-app notification
  - Email notification
  - SMS notification (optional)
- Notification includes:
  - Client basic information
  - Consultation type
  - Proposed schedule
  - Issue description

### 3. Lawyer Response
- 48-hour window to respond
- Options:
  - Accept (select one of proposed schedules)
  - Propose alternative schedule
  - Decline (with reason)

### 4. Consultation Confirmation
- Client notified of lawyer's response
- If accepted:
  - Calendar invite sent to both parties
  - Meeting link generated (for online consultations)
  - Payment processing initiated
- If alternative schedule proposed:
  - Client can accept/decline
- If declined:
  - Client redirected to nearby-lawyers page

## Case Management

### 1. Case Creation
- Can be initiated from:
  - Successful consultation
  - Direct case creation by lawyer
- Required information:
  - Case title
  - Case type/category
  - Case description
  - Initial documents
  - Client information
  - Payment arrangement

### 2. Case Dashboard
#### Lawyer View
- Case Overview Section (20% width)
  - Status indicator
  - Important dates
  - Quick actions
  - Client information

- Main Content Area (80% width)
  - Case Updates Tab
    - Timeline of updates
    - Document attachments
    - Action items
  - Documents Tab
    - Organized by categories
    - Version control
    - Access permissions
  - Calendar Tab
    - Hearings
    - Deadlines
    - Meetings
  - Billing Tab
    - Payment history
    - Outstanding balances
    - Generate invoices

#### Client View
- Similar layout but with restricted actions
- Focus on:
  - Viewing updates
  - Accessing documents
  - Scheduling meetings
  - Payment management

### 3. Case Updates
- Structured update types:
  - Court proceedings
  - Document submissions
  - Client meetings
  - Administrative updates
- Each update includes:
  - Timestamp
  - Category
  - Description
  - Attachments
  - Next steps
  - Visibility settings

### 4. Case Closure
- Closure checklist:
  - All documents archived
  - Final billing completed
  - Closure summary prepared
  - Client sign-off received
- Post-closure:
  - Read-only access maintained
  - Export functionality
  - Feedback collection

## Communication System

### 1. Messaging Platform
- Real-time chat functionality
- Message types:
  - Text messages
  - File attachments
  - Quick responses
  - Action requests
- Features:
  - Read receipts
  - Typing indicators
  - Message search
  - Thread organization

### 2. Document Sharing
- Integrated with case management
- Features:
  - Drag-and-drop upload
  - Preview functionality
  - Version control
  - Access logs
- Security:
  - Encryption at rest
  - Access control
  - Watermarking
  - Audit trail

### 3. Notification System
- Multi-channel notifications:
  - In-app
  - Email
  - SMS (optional)
- Customizable preferences
- Priority levels
- Smart grouping

## Database Schema

### Tables

#### 1. consultations
```sql
CREATE TABLE consultations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    client_id BIGINT,
    lawyer_id BIGINT,
    status ENUM('pending', 'accepted', 'declined', 'completed'),
    consultation_type ENUM('online', 'in_house'),
    description TEXT,
    preferred_dates JSON, -- Stores array of datetime options
    selected_date DATETIME,
    meeting_link VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (lawyer_id) REFERENCES users(id)
);
```

#### 2. legal_cases
```sql
CREATE TABLE legal_cases (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    case_number VARCHAR(100),
    type VARCHAR(100),
    status ENUM('active', 'pending', 'closed'),
    description TEXT,
    lawyer_id BIGINT,
    client_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    closed_at TIMESTAMP,
    FOREIGN KEY (lawyer_id) REFERENCES users(id),
    FOREIGN KEY (client_id) REFERENCES users(id)
);
```

#### 3. case_updates
```sql
CREATE TABLE case_updates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    case_id BIGINT,
    user_id BIGINT,
    update_type VARCHAR(50),
    title VARCHAR(255),
    content TEXT,
    visibility ENUM('client', 'lawyer', 'both'),
    created_at TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES legal_cases(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 4. documents
```sql
CREATE TABLE documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    case_id BIGINT,
    uploaded_by BIGINT,
    name VARCHAR(255),
    file_path VARCHAR(255),
    file_type VARCHAR(50),
    size BIGINT,
    version INT,
    category VARCHAR(50),
    created_at TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES legal_cases(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
```

#### 5. messages
```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    case_id BIGINT,
    sender_id BIGINT,
    content TEXT,
    message_type ENUM('text', 'file', 'action_request'),
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES legal_cases(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
);
```

#### 6. notifications
```sql
CREATE TABLE notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    type VARCHAR(50),
    title VARCHAR(255),
    content TEXT,
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Indexes
```sql
-- Consultations
CREATE INDEX idx_consultation_status ON consultations(status);
CREATE INDEX idx_consultation_dates ON consultations(selected_date);

-- Legal Cases
CREATE INDEX idx_case_status ON legal_cases(status);
CREATE INDEX idx_case_type ON legal_cases(type);

-- Case Updates
CREATE INDEX idx_update_case ON case_updates(case_id);
CREATE INDEX idx_update_type ON case_updates(update_type);

-- Documents
CREATE INDEX idx_document_case ON documents(case_id);
CREATE INDEX idx_document_category ON documents(category);

-- Messages
CREATE INDEX idx_message_case ON messages(case_id);
CREATE INDEX idx_message_created ON messages(created_at);

-- Notifications
CREATE INDEX idx_notification_user ON notifications(user_id);
CREATE INDEX idx_notification_read ON notifications(read_at);
``` 