# MyCRM — Project Blackbook & SDLC Documentation

**Project Name:** MyCRM
**Version:** 1.0.0
**Prepared By:** Development Team
**Date:** April 14, 2026
**Status:** Planning Phase

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Project Overview](#2-project-overview)
3. [SDLC Phases](#3-sdlc-phases)
4. [System Architecture](#4-system-architecture)
5. [Database Schema Design](#5-database-schema-design)
6. [Module Specifications](#6-module-specifications)
7. [Role-Based Access Control (RBAC)](#7-role-based-access-control-rbac)
8. [API Endpoints Reference](#8-api-endpoints-reference)
9. [Notification & Reminder System](#9-notification--reminder-system)
10. [Google Meet Integration](#10-google-meet-integration)
11. [External Integrations](#11-external-integrations)
12. [Security Considerations](#12-security-considerations)
13. [Testing Plan](#13-testing-plan)
14. [Deployment Plan](#14-deployment-plan)
15. [Project Timeline & Milestones](#15-project-timeline--milestones)
16. [Risk Register](#16-risk-register)
17. [Appendix — Tech Stack](#17-appendix--tech-stack)

---

## 1. Executive Summary

MyCRM is a full-featured Customer Relationship Management system designed for agencies and service businesses. It centralizes client, staff, vendor, project, and task management under one unified platform with granular role-based access control. Key highlights include:

- Multi-level RBAC supporting unlimited custom roles
- Integrated client and vendor service renewal tracking
- Project and task management with milestone tracking
- Client-facing portal for issue reporting and tracking
- Lead management and appointment scheduling
- Automated email and WhatsApp reminders
- Google Meet integration for appointment scheduling
- Integration with external web forms (Book a Call, Digital Marketing Leads)

The system is intended to serve both the internal operations team and clients of **Technofra**, bridging communication, delivery, and support into a single dashboard.

---

## 2. Project Overview

### 2.1 Objectives

- Eliminate scattered communication across email, WhatsApp, and spreadsheets
- Provide clients with a self-service portal for issues and project visibility
- Automate renewal tracking for vendor services and client subscriptions
- Streamline staff and team operations with role-based access
- Centralize lead nurturing and appointment management

### 2.2 Target Users

| User Type       | Description                                          |
|----------------|------------------------------------------------------|
| Admin           | Full system access, manages all modules              |
| Staff           | Assigned projects and tasks based on role/team       |
| Team Leader     | Oversees team members, manages team-level issues     |
| Client          | Limited portal access — projects, issues, renewals   |
| Vendor (Passive)| No direct login; managed by admin                    |

### 2.3 Scope

**In Scope:**
- All 15 modules described in requirements
- Email and WhatsApp notifications
- Google Meet link generation
- External form lead capture (Technofra website)
- Client portal with issue tracking

**Out of Scope (v1.0):**
- Mobile native application
- Invoice/billing processing engine
- Third-party accounting integrations (Tally, Zoho Books)
- Live chat support within CRM

---

## 3. SDLC Phases

### Phase 1 — Requirements Analysis (Week 1–2)

**Activities:**
- Stakeholder interviews and requirements gathering
- Document all functional and non-functional requirements
- Review existing Technofra web forms and database schemas
- Define user personas and access matrix
- Identify integration touchpoints (Google, WhatsApp, Mail)

**Deliverables:**
- Business Requirements Document (BRD)
- Functional Requirements Specification (FRS)
- Use Case Diagrams
- RBAC Access Matrix

**Sign-off:** Project owner approval before Phase 2

---

### Phase 2 — System Design (Week 3–5)

**Activities:**
- Architecture design (Laravel MVC + REST API)
- Database Entity Relationship Diagram (ERD) design
- UI/UX wireframes for all major screens
- API contract definition
- Define notification triggers and flows
- Google Meet API integration design
- WhatsApp Business API evaluation (Meta Cloud API or Twilio)

**Deliverables:**
- System Architecture Document
- ER Diagram
- Wireframes (Figma)
- API Specification (OpenAPI 3.0)
- Integration Design Document

---

### Phase 3 — Development (Week 6–18)

Development follows a Sprint-based Agile approach (2-week sprints).

#### Sprint Plan

| Sprint | Duration      | Focus Area                                              |
|--------|---------------|--------------------------------------------------------|
| S1     | Week 6–7      | Project scaffold, auth, RBAC foundation, permissions   |
| S2     | Week 8–9      | Roles, Teams, Departments modules                      |
| S3     | Week 10–11    | Staff Management module                                |
| S4     | Week 12–13    | Client Management module                               |
| S5     | Week 14–15    | Vendor Management + Service Renewals                   |
| S6     | Week 16–17    | Project Management module                              |
| S7     | Week 18–19    | Task Management module                                 |
| S8     | Week 20–21    | Client Issues module + Client Portal                   |
| S9     | Week 22–23    | Leads, Book a Call, Digital Marketing Leads            |
| S10    | Week 24–25    | Appointments + Google Meet integration                 |
| S11    | Week 26–27    | Email/WhatsApp notifications, reminder scheduler       |
| S12    | Week 28–29    | Admin dashboard, reports, performance metrics          |

**Development Standards:**
- Laravel 11+ (PHP 8.2+)
- RESTful API architecture
- Sanctum/Passport for API authentication
- Eloquent ORM with database migrations
- Form Request validation classes
- Repository/Service pattern
- Queue jobs for emails and notifications (Redis/Horizon)
- Feature flags for staged rollout

---

### Phase 4 — Testing (Week 18–28, parallel with dev)

| Test Type          | Method                         | Tools                     |
|--------------------|--------------------------------|---------------------------|
| Unit Testing       | Feature-by-feature             | PHPUnit, Pest             |
| Integration Testing| API endpoint testing           | Postman, PHPUnit          |
| UAT                | Stakeholder sign-off per module| Manual + Test Scripts     |
| Security Testing   | OWASP vulnerability checks     | OWASP ZAP, manual         |
| Performance        | Load testing                   | k6, Laravel Telescope     |
| Cross-browser      | Chrome, Firefox, Safari, Edge  | BrowserStack              |

---

### Phase 5 — Deployment (Week 29–30)

- Staging environment deployment and final UAT
- DNS configuration and SSL certificate setup
- Data migration (existing leads, contacts if applicable)
- CI/CD pipeline setup (GitHub Actions or Envoyer)
- Server hardening and backup automation
- Go-live with monitored rollout

---

### Phase 6 — Maintenance & Support (Post-Launch)

- Bug fix SLA: Critical = 24 hours, Major = 72 hours, Minor = 2 weeks
- Monthly feature sprints for enhancements
- Quarterly security audits
- Performance monitoring via Laravel Pulse / New Relic

---

## 4. System Architecture

### 4.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                      Client Browser                       │
│              React / Blade + Inertia.js                  │
└────────────────────────┬────────────────────────────────┘
                         │ HTTPS
┌────────────────────────▼────────────────────────────────┐
│                   Laravel Application                      │
│   ┌────────────┐  ┌────────────┐  ┌──────────────────┐  │
│   │ Web Routes │  │ API Routes │  │  Queue Workers   │  │
│   └────────────┘  └────────────┘  └──────────────────┘  │
│   ┌────────────────────────────────────────────────────┐ │
│   │           Service / Repository Layer               │ │
│   └────────────────────────────────────────────────────┘ │
└──────────┬───────────────┬───────────────┬──────────────┘
           │               │               │
    ┌──────▼──────┐ ┌──────▼──────┐ ┌─────▼──────┐
    │  MySQL DB   │ │  Redis Cache│ │  File Store │
    │  (Primary)  │ │  + Queues   │ │  (S3/Local) │
    └─────────────┘ └─────────────┘ └────────────┘

External Services:
  ├── SMTP / Mail Service (Mailgun / SES)
  ├── WhatsApp Business API (Meta Cloud API)
  ├── Google Calendar + Meet API
  └── Technofra Website (MySQL shared DB or REST API)
```

### 4.2 Frontend Architecture

- **Admin Panel:** Laravel Blade + Livewire OR React (Inertia.js)
- **Client Portal:** Separate Blade views with restricted routes
- **Responsive Design:** TailwindCSS
- **Real-time updates:** Laravel Echo + Pusher (for notifications)

### 4.3 Backend Architecture Pattern

```
app/
├── Http/
│   ├── Controllers/
│   │   └── [Module]Controller.php
│   ├── Requests/
│   │   └── [Module]/Store|UpdateRequest.php
│   └── Middleware/
│       └── CheckPermission.php
├── Models/
├── Services/
│   └── [Module]Service.php
├── Repositories/
│   └── [Module]Repository.php
├── Jobs/
│   ├── SendWelcomeEmail.php
│   ├── SendReminderNotification.php
│   └── GenerateGoogleMeet.php
└── Notifications/
    ├── WelcomeNotification.php
    └── RenewalReminderNotification.php
```

---

## 5. Database Schema Design

### 5.1 Core RBAC Tables

```sql
-- Permissions
CREATE TABLE permissions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) UNIQUE NOT NULL,  -- e.g., 'clients.create'
    display_name VARCHAR(150),
    module      VARCHAR(50),                   -- e.g., 'clients'
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

-- Roles
CREATE TABLE roles (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(150),
    guard_name  VARCHAR(50) DEFAULT 'web',
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

-- Role-Permission Pivot
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED,
    role_id       BIGINT UNSIGNED,
    PRIMARY KEY (permission_id, role_id)
);
```

### 5.2 Users & Profiles

```sql
CREATE TABLE users (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(150) NOT NULL,
    email           VARCHAR(191) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    phone           VARCHAR(20),
    dob             DATE,
    status          ENUM('active','inactive','suspended') DEFAULT 'active',
    user_type       ENUM('admin','staff','client') NOT NULL,
    email_verified_at TIMESTAMP NULL,
    deleted_at      TIMESTAMP NULL,  -- Soft delete
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

-- Model has roles/permissions (Spatie-compatible)
CREATE TABLE model_has_roles (
    role_id     BIGINT UNSIGNED,
    model_type  VARCHAR(255),
    model_id    BIGINT UNSIGNED,
    PRIMARY KEY (role_id, model_id, model_type)
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED,
    model_type    VARCHAR(255),
    model_id      BIGINT UNSIGNED,
    PRIMARY KEY (permission_id, model_id, model_type)
);
```

### 5.3 Teams & Departments

```sql
CREATE TABLE teams (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    leader_id   BIGINT UNSIGNED REFERENCES users(id),
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE team_user (
    team_id     BIGINT UNSIGNED,
    user_id     BIGINT UNSIGNED,
    joined_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, user_id)
);

CREATE TABLE departments (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    head_id     BIGINT UNSIGNED REFERENCES users(id),
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE department_team (
    department_id BIGINT UNSIGNED,
    team_id       BIGINT UNSIGNED,
    PRIMARY KEY (department_id, team_id)
);

CREATE TABLE department_user (
    department_id BIGINT UNSIGNED,
    user_id       BIGINT UNSIGNED,
    PRIMARY KEY (department_id, user_id)
);
```

### 5.4 Client Profiles

```sql
CREATE TABLE client_profiles (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED UNIQUE REFERENCES users(id),
    contact_person  VARCHAR(150),
    website         VARCHAR(255),
    address_line_1  VARCHAR(255),
    address_line_2  VARCHAR(255),
    city            VARCHAR(100),
    state           VARCHAR(100),
    zip_code        VARCHAR(20),
    country         VARCHAR(100),
    client_type     ENUM('individual','company','organization','other') DEFAULT 'individual',
    client_type_custom VARCHAR(100),
    industry        VARCHAR(100),
    priority_level  ENUM('low','medium','high','critical') DEFAULT 'medium',
    billing_type    ENUM('hourly','fixed','retainer','subscription'),
    default_due_days TINYINT UNSIGNED DEFAULT 30,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### 5.5 Vendors & Services

```sql
CREATE TABLE vendors (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    email       VARCHAR(191),
    phone       VARCHAR(20),
    website     VARCHAR(255),
    address     TEXT,
    notes       TEXT,
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE vendor_services (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id       BIGINT UNSIGNED REFERENCES vendors(id),
    name            VARCHAR(150) NOT NULL,  -- e.g., 'Domain', 'Hosting'
    description     TEXT,
    cost            DECIMAL(10,2),
    start_date      DATE,
    end_date        DATE,
    renewal_reminder_days INT DEFAULT 30,  -- days before expiry to remind
    status          ENUM('active','expired','cancelled') DEFAULT 'active',
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

CREATE TABLE client_service_renewals (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id       BIGINT UNSIGNED REFERENCES users(id),
    vendor_service_id BIGINT UNSIGNED REFERENCES vendor_services(id),
    start_date      DATE,
    end_date        DATE,
    renewal_cost    DECIMAL(10,2),
    status          ENUM('active','expired','cancelled') DEFAULT 'active',
    notes           TEXT,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### 5.6 Projects

```sql
CREATE TABLE projects (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id       BIGINT UNSIGNED REFERENCES users(id),
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    tech_stacks     JSON,               -- array of tech tags
    tags            JSON,
    start_date      DATE,
    expected_end_date DATE,
    status          ENUM('not_started','in_progress','on_hold','completed','cancelled') DEFAULT 'not_started',
    billing_type    ENUM('hourly','fixed','retainer'),
    billing_amount  DECIMAL(12,2),
    blackbook_file  VARCHAR(255),       -- path to uploaded file
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

CREATE TABLE project_files (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  BIGINT UNSIGNED REFERENCES projects(id),
    name        VARCHAR(255),
    path        VARCHAR(255),
    size        BIGINT,
    mime_type   VARCHAR(100),
    uploaded_by BIGINT UNSIGNED REFERENCES users(id),
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE project_milestones (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  BIGINT UNSIGNED REFERENCES projects(id),
    title       VARCHAR(255) NOT NULL,
    description TEXT,
    due_date    DATE,
    status      ENUM('pending','in_progress','completed') DEFAULT 'pending',
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE project_issues (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  BIGINT UNSIGNED REFERENCES projects(id),
    reported_by BIGINT UNSIGNED REFERENCES users(id),
    title       VARCHAR(255),
    description TEXT,
    priority    ENUM('low','medium','high','critical') DEFAULT 'medium',
    status      ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    converted_to_task_id BIGINT UNSIGNED NULL REFERENCES tasks(id),
    assigned_to BIGINT UNSIGNED NULL REFERENCES users(id),
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE project_comments (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id  BIGINT UNSIGNED REFERENCES projects(id),
    user_id     BIGINT UNSIGNED REFERENCES users(id),
    comment     TEXT,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE project_staff (
    project_id  BIGINT UNSIGNED,
    user_id     BIGINT UNSIGNED,
    role_in_project VARCHAR(100),
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id, user_id)
);
```

### 5.7 Tasks

```sql
CREATE TABLE tasks (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id      BIGINT UNSIGNED NULL REFERENCES projects(id),
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    start_date      DATE,
    due_date        DATE,
    priority        ENUM('low','medium','high','critical') DEFAULT 'medium',
    status          ENUM('todo','in_progress','review','done','cancelled') DEFAULT 'todo',
    tags            JSON,
    created_by      BIGINT UNSIGNED REFERENCES users(id),
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

CREATE TABLE task_assignees (
    task_id     BIGINT UNSIGNED,
    user_id     BIGINT UNSIGNED,
    PRIMARY KEY (task_id, user_id)
);

CREATE TABLE task_followers (
    task_id     BIGINT UNSIGNED,
    user_id     BIGINT UNSIGNED,
    PRIMARY KEY (task_id, user_id)
);

CREATE TABLE task_files (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id     BIGINT UNSIGNED REFERENCES tasks(id),
    path        VARCHAR(255),
    name        VARCHAR(255),
    uploaded_by BIGINT UNSIGNED REFERENCES users(id),
    created_at  TIMESTAMP
);
```

### 5.8 Leads & Inquiries

```sql
CREATE TABLE leads (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150),
    email       VARCHAR(191),
    phone       VARCHAR(20),
    company     VARCHAR(150),
    source      VARCHAR(100),          -- 'manual','book_a_call','digital_marketing'
    status      ENUM('new','contacted','qualified','converted','lost') DEFAULT 'new',
    notes       TEXT,
    assigned_to BIGINT UNSIGNED NULL REFERENCES users(id),
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE TABLE book_a_call_enquiries (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150),
    email       VARCHAR(191),
    phone       VARCHAR(20),
    preferred_datetime DATETIME,
    message     TEXT,
    meet_link   VARCHAR(500),          -- generated Google Meet link
    status      ENUM('pending','scheduled','completed','cancelled') DEFAULT 'pending',
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP
);

CREATE TABLE digital_marketing_leads (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150),
    email       VARCHAR(191),
    phone       VARCHAR(20),
    business_type VARCHAR(150),
    message     TEXT,
    deleted_at  TIMESTAMP NULL,
    created_at  TIMESTAMP
);
```

### 5.9 Appointments

```sql
CREATE TABLE appointments (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255),
    client_id       BIGINT UNSIGNED NULL REFERENCES users(id),
    staff_id        BIGINT UNSIGNED NULL REFERENCES users(id),
    scheduled_at    DATETIME NOT NULL,
    duration_minutes SMALLINT DEFAULT 60,
    meet_link       VARCHAR(500),
    status          ENUM('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
    notes           TEXT,
    deleted_at      TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### 5.10 Audit & Login Logs

```sql
CREATE TABLE login_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED REFERENCES users(id),
    ip_address  VARCHAR(45),
    user_agent  TEXT,
    status      ENUM('success','failed') DEFAULT 'success',
    logged_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE activity_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED REFERENCES users(id),
    action      VARCHAR(255),          -- e.g., 'created_project'
    model_type  VARCHAR(100),
    model_id    BIGINT UNSIGNED,
    meta        JSON,
    created_at  TIMESTAMP
);
```

---

## 6. Module Specifications

### 6.1 Permissions Module

**Purpose:** Define granular permissions for the entire system.

**Permission Naming Convention:** `{module}.{action}`

Examples:
- `clients.view`, `clients.create`, `clients.edit`, `clients.delete`
- `projects.view`, `projects.create`, `projects.assign_staff`
- `tasks.view`, `tasks.create`, `tasks.assign`
- `leads.view`, `leads.create`, `leads.convert`

**Core CRUD Operations:**

| Action          | Endpoint                    | Auth Required | Roles     |
|----------------|-----------------------------|---------------|-----------|
| List           | GET /permissions             | Yes           | Admin     |
| Create         | POST /permissions            | Yes           | Admin     |
| View           | GET /permissions/{id}        | Yes           | Admin     |
| Edit           | PUT /permissions/{id}        | Yes           | Admin     |
| Delete         | DELETE /permissions/{id}     | Yes           | Admin     |

**Notes:**
- Default permissions are seeded during installation
- Permissions cannot be deleted if they are currently assigned to a role
- System permissions (core module access) are protected from deletion

---

### 6.2 Roles Module

**Purpose:** Group permissions into assignable roles.

**Default System Roles (Seeded):**
- `admin` — Full access to all modules
- `staff` — Standard staff permissions
- `client` — Client portal access only
- `team_leader` — Team management + issue assignment
- `team_member` — Task execution and status updates

**CRUD Operations:**

| Action          | Method | Endpoint               |
|----------------|--------|------------------------|
| List Roles      | GET    | /roles                 |
| Create Role     | POST   | /roles                 |
| View Role       | GET    | /roles/{id}            |
| Edit Role       | PUT    | /roles/{id}            |
| Delete Role     | DELETE | /roles/{id}            |
| Multi-Delete    | POST   | /roles/bulk-delete     |
| Assign Permissions | POST | /roles/{id}/permissions |

**Business Rules:**
- System roles (`admin`, `staff`, `client`) cannot be deleted
- A role must have at least one permission assigned
- Deleting a role will unassign it from all users (users revert to base permissions)
- Multi-delete will validate none of the selected are system roles

---

### 6.3 Teams Module

**Purpose:** Organize staff into functional working groups.

**Features:**
- Create teams with optional role/permission overrides
- Assign a single team leader per team
- Add/remove staff members
- Team leader sees team's issues first

**Business Rules:**
- A staff member can belong to multiple teams
- Only one team leader per team at a time
- Changing team leader automatically demotes previous leader to member
- Soft-delete preserves historical project/task assignment records

**Key Relationships:**
```
Team → has many → Staff (via team_user)
Team → belongs to one → Team Leader (user)
Team → can be in → Multiple Departments
Project → assigned to → Multiple Teams
Task → assigned to → Multiple Teams
Issue → assigned to → Team (visible to leader first)
```

---

### 6.4 Departments Module

**Purpose:** Logical grouping of teams and staff for organizational structure.

**Features:**
- Departments contain teams and individual staff
- Department head (single person) is designated per department
- Soft delete preserves all staff/team associations historically
- Optional role/permission scope per department

**Business Rules:**
- Staff can belong to one department directly (or via their team's department)
- Department head is not necessarily a team leader
- Removing a department head does not affect the user's account

---

### 6.5 Staff Management Module

**Purpose:** Complete lifecycle management of internal staff members.

#### Create Staff
Fields:
- Full Name, Email, Date of Birth, Phone Number
- Role assignment (filtered by selected team if team is chosen)
- Team (optional)
- Status (Active / Inactive)
- Auto-generate random password
- Send welcome email with credentials

#### Edit Staff
- Update all fields above
- Admin can reset password manually

#### Delete Staff (Soft Delete)
- Staff is deactivated, login blocked
- Historical data (projects, tasks) preserved

#### Permanent Delete
- Removes record and all associated data
- Only allowed if staff has no active projects/tasks

#### Restore Staff
- Reactivates soft-deleted staff
- Restores access with original role

#### View Staff Details
Includes:
- Profile information
- Assigned projects list
- Assigned tasks list
- Login logs (IP, browser, timestamp)
- Performance summary (tasks completed vs. assigned, project participation)

---

### 6.6 Client Management Module

**Purpose:** Full client lifecycle from onboarding to ongoing service management.

#### Create Client
**Basic Information:** Name, Contact Person, Email, Phone, Website
**Address:** Address Line 1 & 2, City, State, Zip, Country
**Business Info:** Client Type, Industry, Status, Priority Level, Billing Type, Default Due Days
**Role Assignment:** Select client role
**On Create:** Send welcome email with login credentials and portal access instructions

#### Edit Client
- Update all fields
- Admin can reset client password

#### Client Portal Access
Clients can:
- View their assigned projects
- Create and track issues
- View service renewal statuses

#### Client Service Renewals
- Admin assigns vendor services to clients
- Tracks start/end dates
- Sends renewal reminders (email + WhatsApp) based on configurable days before expiry
- Renewal history is viewable per client

---

### 6.7 Vendor Management Module

**Purpose:** Track external service providers and their services consumed.

#### Vendor Fields
- Company Name, Email, Phone, Website, Address, Notes

#### Vendor Services
Each vendor can have multiple services:
- Service Name (e.g., Domain, Hosting, SSL, Email)
- Description, Cost, Start Date, End Date
- Renewal Reminder Days (configurable per service)
- Status: Active / Expired / Cancelled

#### View Vendor
Displays:
- Vendor details
- All services with expiry dates
- Which clients are using each service (client_service_renewals)
- Upcoming renewals (within 30/60 days)

---

### 6.8 Project Management Module

**Purpose:** End-to-end project tracking from initiation to completion.

#### Create Project
- Client (dropdown)
- Title, Description, Tech Stacks (tags), Tags
- Start Date, Expected End Date
- Upload Blackbook File
- Assign Teams/Staff (optional)
- Billing Details
- Status auto-set to `not_started` on creation
- Auto-transition to `in_progress` on start date (scheduled job)

#### Project Views

**Overview Tab:**
- Project summary, client info, billing details
- Progress bar based on task completion percentage

**Team Members Tab:**
- List of assigned staff/teams
- Add / remove members

**Tasks Tab:**
- Tasks filtered for this project
- Quick task creation from project context

**Files Tab:**
- Upload, view, soft-delete project files
- File type icons, upload date, uploader name

**Milestones Tab:**
- Create milestones with due dates and status
- Visual timeline or list view

**Issues Tab:**
- Issues reported against this project
- Priority and status management
- Convert issue to task button

**Comments Tab:**
- Internal team comments for the project

---

### 6.9 Task Management Module

**Purpose:** Granular work item management, optionally linked to projects.

#### Create Task
- Title, Description
- Project (optional)
- Start Date, Due Date
- Priority (Low / Medium / High / Critical)
- Status (Todo / In Progress / Review / Done / Cancelled)
- Assign to Teams/Staff (optional)
- Followers (notified on updates)
- Attach media files
- Tags

#### Task Status Flow
```
Todo → In Progress → Review → Done
         ↓
      Cancelled (at any stage)
```

#### Notifications
- Assignees notified on task creation (email + optional WhatsApp)
- Followers notified on status change
- Reminder sent 24 hours before due date

---

### 6.10 Client Issues Module

**Purpose:** Client-facing issue reporting with role-based workflow.

#### Issue Lifecycle
```
Client Creates Issue
       ↓
Admin Reviews → Adds Priority → Assigns to Team/Staff
       ↓
Team Leader sees issue → Assigns to Team Member
       ↓
Team Member resolves → Updates Status
       ↓
Admin/Client confirms → Closes Issue
```

#### Role Views

**Client:**
- Create issue (select project, title, description)
- List own issues with status
- View issue detail and status history

**Admin:**
- List all client issues
- Edit priority, status
- Assign to team or individual staff
- View full issue history

**Team Leader:**
- See issues assigned to their team
- Assign to specific team member
- Update status

**Team Member:**
- See issues assigned personally
- Update status with comments

---

### 6.11 Leads Module

**Purpose:** Capture and nurture potential clients.

#### Lead Fields
- Name, Email, Phone, Company, Source
- Status: New → Contacted → Qualified → Converted → Lost
- Notes, Assigned Staff

#### Lead Conversion
When a lead is marked as `Converted`:
- Option to auto-create a Client account from lead data
- Lead remains in system with `converted` status linked to new client

---

### 6.12 Book A Call Module

**Purpose:** Capture call booking enquiries from the Technofra website.

- Data source: Technofra website form at `/contact` page
- Data flows into `book_a_call_enquiries` table
- Admin views and manages enquiries
- Google Meet link auto-generated for the selected date/time slot
- One meeting per unique date-time block (conflict prevention)
- Delete enquiry capability

---

### 6.13 Digital Marketing Leads Module

**Purpose:** Capture digital marketing campaign leads.

- Data source: `/digitalmarketingad` page on Technofra website
- Enquiries visible in admin dashboard
- Admin can delete enquiries
- No login or portal access for these leads unless manually converted

---

### 6.14 Appointments Module

**Purpose:** Schedule and manage meetings with clients or leads.

#### Fields
- Title, Client/Lead, Assigned Staff
- Scheduled Date & Time, Duration
- Notes
- Auto-generate Google Meet link
- Status: Scheduled / Completed / Cancelled / Rescheduled

#### Business Rules
- Only one appointment per date-time slot (validated at DB and application level)
- Confirmation email sent on creation
- Reminder sent 1 hour and 24 hours before appointment (email + WhatsApp)
- Google Meet link included in all notifications

---

## 7. Role-Based Access Control (RBAC)

### 7.1 Access Matrix

| Permission                   | Admin | Team Leader | Staff | Client |
|-----------------------------|-------|-------------|-------|--------|
| permissions.manage           | ✅    | ❌          | ❌    | ❌     |
| roles.manage                 | ✅    | ❌          | ❌    | ❌     |
| teams.manage                 | ✅    | ✅ (own)    | ❌    | ❌     |
| departments.manage           | ✅    | ❌          | ❌    | ❌     |
| staff.create                 | ✅    | ❌          | ❌    | ❌     |
| staff.view                   | ✅    | ✅ (team)   | ❌    | ❌     |
| staff.edit                   | ✅    | ❌          | ❌    | ❌     |
| staff.delete                 | ✅    | ❌          | ❌    | ❌     |
| clients.manage               | ✅    | ❌          | ❌    | ❌     |
| clients.view                 | ✅    | ✅          | ✅    | ✅ (own)|
| vendors.manage               | ✅    | ❌          | ❌    | ❌     |
| projects.create              | ✅    | ❌          | ❌    | ❌     |
| projects.view                | ✅    | ✅          | ✅    | ✅ (own)|
| projects.edit                | ✅    | ✅ (assigned)| ❌   | ❌     |
| tasks.create                 | ✅    | ✅          | ✅    | ❌     |
| tasks.view                   | ✅    | ✅          | ✅ (assigned)| ❌|
| issues.create                | ✅    | ✅          | ✅    | ✅     |
| issues.assign                | ✅    | ✅ (team)   | ❌    | ❌     |
| leads.manage                 | ✅    | ❌          | ✅    | ❌     |
| appointments.manage          | ✅    | ✅          | ❌    | ❌     |
| reports.view                 | ✅    | ✅ (team)   | ❌    | ❌     |

### 7.2 RBAC Implementation

**Library:** Spatie Laravel Permission (v6+)

```php
// Middleware usage
Route::middleware(['auth', 'permission:clients.create'])->group(function () {
    Route::post('/clients', [ClientController::class, 'store']);
});

// Controller usage
public function destroy(Client $client)
{
    $this->authorize('clients.delete');
    // ...
}
```

### 7.3 Dynamic Role Creation

- Admin can create unlimited custom roles
- New roles start with zero permissions
- Permissions are assigned via checkbox UI
- System roles (admin, staff, client) are protected from deletion but permissions can be modified

---

## 8. API Endpoints Reference

### Authentication
```
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
```

### Permissions
```
GET    /api/permissions
POST   /api/permissions
GET    /api/permissions/{id}
PUT    /api/permissions/{id}
DELETE /api/permissions/{id}
```

### Roles
```
GET    /api/roles
POST   /api/roles
GET    /api/roles/{id}
PUT    /api/roles/{id}
DELETE /api/roles/{id}
POST   /api/roles/bulk-delete
POST   /api/roles/{id}/permissions
```

### Teams
```
GET    /api/teams
POST   /api/teams
GET    /api/teams/{id}
PUT    /api/teams/{id}
DELETE /api/teams/{id}
POST   /api/teams/bulk-delete
POST   /api/teams/{id}/members
DELETE /api/teams/{id}/members/{userId}
PUT    /api/teams/{id}/leader
```

### Departments
```
GET    /api/departments
POST   /api/departments
GET    /api/departments/{id}
PUT    /api/departments/{id}
DELETE /api/departments/{id}
POST   /api/departments/bulk-delete
PUT    /api/departments/{id}/head
DELETE /api/departments/{id}/head
POST   /api/departments/{id}/staff
DELETE /api/departments/{id}/staff/{userId}
POST   /api/departments/{id}/teams
DELETE /api/departments/{id}/teams/{teamId}
```

### Staff
```
GET    /api/staff
POST   /api/staff
GET    /api/staff/{id}
PUT    /api/staff/{id}
DELETE /api/staff/{id}            (soft delete)
DELETE /api/staff/{id}/permanent  (hard delete)
POST   /api/staff/{id}/restore
GET    /api/staff/{id}/login-logs
GET    /api/staff/{id}/projects
GET    /api/staff/{id}/tasks
GET    /api/staff/{id}/performance
```

### Clients
```
GET    /api/clients
POST   /api/clients
GET    /api/clients/{id}
PUT    /api/clients/{id}
DELETE /api/clients/{id}          (soft delete)
DELETE /api/clients/{id}/permanent
POST   /api/clients/{id}/restore
POST   /api/clients/{id}/reset-password
GET    /api/clients/{id}/projects
GET    /api/clients/{id}/issues
GET    /api/clients/{id}/renewals
POST   /api/clients/{id}/renewals
```

### Vendors
```
GET    /api/vendors
POST   /api/vendors
GET    /api/vendors/{id}
PUT    /api/vendors/{id}
DELETE /api/vendors/{id}
POST   /api/vendors/{id}/restore
DELETE /api/vendors/{id}/permanent
POST   /api/vendors/{id}/services
PUT    /api/vendors/{vendorId}/services/{id}
DELETE /api/vendors/{vendorId}/services/{id}
GET    /api/vendors/{id}/services/{serviceId}/allocations
```

### Projects
```
GET    /api/projects
POST   /api/projects
GET    /api/projects/{id}
PUT    /api/projects/{id}
DELETE /api/projects/{id}
POST   /api/projects/{id}/restore
DELETE /api/projects/{id}/permanent
POST   /api/projects/{id}/staff
DELETE /api/projects/{id}/staff/{userId}
GET    /api/projects/{id}/tasks
GET    /api/projects/{id}/files
POST   /api/projects/{id}/files
DELETE /api/projects/{id}/files/{fileId}
GET    /api/projects/{id}/milestones
POST   /api/projects/{id}/milestones
PUT    /api/projects/{id}/milestones/{milestoneId}
DELETE /api/projects/{id}/milestones/{milestoneId}
GET    /api/projects/{id}/issues
POST   /api/projects/{id}/issues
PUT    /api/projects/{id}/issues/{issueId}
DELETE /api/projects/{id}/issues/{issueId}
POST   /api/projects/{id}/issues/{issueId}/convert-to-task
GET    /api/projects/{id}/comments
POST   /api/projects/{id}/comments
```

### Tasks
```
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{id}
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
POST   /api/tasks/{id}/assignees
DELETE /api/tasks/{id}/assignees/{userId}
POST   /api/tasks/{id}/followers
DELETE /api/tasks/{id}/followers/{userId}
POST   /api/tasks/{id}/files
```

### Issues (Client Portal)
```
GET    /api/issues                  (admin — all)
POST   /api/issues                  (client — create)
GET    /api/issues/{id}
PUT    /api/issues/{id}
PUT    /api/issues/{id}/assign
PUT    /api/issues/{id}/status
```

### Leads
```
GET    /api/leads
POST   /api/leads
GET    /api/leads/{id}
PUT    /api/leads/{id}
DELETE /api/leads/{id}
POST   /api/leads/{id}/convert
```

### Book a Call
```
GET    /api/book-a-call
DELETE /api/book-a-call/{id}
```

### Digital Marketing Leads
```
GET    /api/digital-marketing-leads
DELETE /api/digital-marketing-leads/{id}
```

### Appointments
```
GET    /api/appointments
POST   /api/appointments
GET    /api/appointments/{id}
PUT    /api/appointments/{id}
DELETE /api/appointments/{id}
GET    /api/appointments/slots/available?date=YYYY-MM-DD
```

---

## 9. Notification & Reminder System

### 9.1 Notification Channels

| Channel  | Use Case                                | Library / Service              |
|----------|-----------------------------------------|-------------------------------|
| Email    | All major events, welcome mails, reminders | Laravel Mail + Mailgun/SES |
| WhatsApp | Appointment reminders, renewal alerts   | Meta Cloud API / Twilio        |
| In-App   | Real-time bell notifications            | Laravel Echo + Pusher/Reverb   |

### 9.2 Notification Triggers

| Event                          | Email | WhatsApp | In-App |
|-------------------------------|-------|----------|--------|
| New staff/client created       | ✅    | ❌       | ❌     |
| Task assigned                  | ✅    | ❌       | ✅     |
| Task due in 24 hours           | ✅    | ✅       | ✅     |
| Issue assigned                 | ✅    | ❌       | ✅     |
| Issue status updated           | ✅    | ❌       | ✅     |
| Project status changed         | ✅    | ❌       | ✅     |
| Appointment scheduled          | ✅    | ✅       | ✅     |
| Appointment reminder (24h)     | ✅    | ✅       | ✅     |
| Appointment reminder (1h)      | ❌    | ✅       | ✅     |
| Vendor service expiring (30d)  | ✅    | ✅       | ✅     |
| Client renewal expiring (30d)  | ✅    | ✅       | ✅     |
| Password reset                 | ✅    | ❌       | ❌     |
| New lead from web form         | ✅    | ❌       | ✅     |

### 9.3 Reminder Scheduler

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new SendRenewalReminders)->daily();
    $schedule->job(new SendAppointmentReminders)->everyFifteenMinutes();
    $schedule->job(new SendTaskDueReminders)->dailyAt('08:00');
    $schedule->job(new UpdateProjectStatuses)->dailyAt('00:01');
}
```

### 9.4 WhatsApp Integration

**Recommended:** Meta Business Platform (WhatsApp Cloud API)
- Register a WhatsApp Business number
- Create message templates (pre-approved by Meta)
- Template variables: `{{name}}`, `{{date}}`, `{{meet_link}}`, `{{service_name}}`

**Template Examples:**
```
APPOINTMENT_REMINDER:
"Hello {{1}}, your meeting is scheduled for {{2}}. 
Join here: {{3}}"

RENEWAL_ALERT:
"Hi {{1}}, your {{2}} service expires on {{3}}. 
Please renew to avoid interruption."
```

---

## 10. Google Meet Integration

### 10.1 Setup Requirements

1. Google Cloud Project with Calendar API and Meet API enabled
2. OAuth2 Service Account credentials
3. Company Google Workspace account (recommended)

### 10.2 Meet Link Generation Flow

```
User submits appointment/book-a-call form
              ↓
Check: Is the date-time slot available?
  → YES → Create Google Calendar Event (via API)
         → Add Google Meet Conference Data
         → Store meet_link in database
         → Send confirmation email with meet_link
  → NO  → Return error: "Slot already booked, choose another time"
```

### 10.3 Conflict Prevention

```sql
-- Unique constraint on appointment slots
CREATE UNIQUE INDEX idx_appointment_slot 
ON appointments (scheduled_at, status) 
WHERE status != 'cancelled';
```

Application-level validation also checks for conflicts before API call to avoid unnecessary Google API requests.

### 10.4 Laravel Integration

```php
// config/google.php
return [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
    'service_account_json' => storage_path('app/google-service-account.json'),
];

// Job: GenerateGoogleMeet.php
public function handle(GoogleCalendarService $calendar)
{
    $event = $calendar->createEventWithMeet(
        title: $this->appointment->title,
        startTime: $this->appointment->scheduled_at,
        duration: $this->appointment->duration_minutes,
        attendees: [$this->appointment->client_email]
    );
    
    $this->appointment->update(['meet_link' => $event->hangoutLink]);
    
    // Dispatch notification
    Notification::send($this->appointment->client, 
        new AppointmentConfirmed($this->appointment));
}
```

---

## 11. External Integrations

### 11.1 Technofra Website — Book a Call Form

**Integration Method:**
- Option A: Shared MySQL database — CRM reads directly from the same DB
- Option B: REST API webhook — Technofra website POST to CRM API endpoint on form submission

**Recommended:** Option B (API webhook) for decoupling
```
POST /api/webhooks/book-a-call
Authorization: Bearer {WEBHOOK_SECRET}
{
  "name": "...",
  "email": "...",
  "phone": "...",
  "preferred_datetime": "2026-04-20T10:00:00"
}
```

### 11.2 Technofra Website — Digital Marketing Leads Form

Same pattern as Book a Call:
```
POST /api/webhooks/digital-marketing-lead
Authorization: Bearer {WEBHOOK_SECRET}
```

### 11.3 Email Service

**Recommended:** Mailgun (Transactional) + Mailchimp (Marketing)
- Laravel Mail driver: `mailgun`
- `.env`: `MAIL_MAILER=mailgun`
- Queue all emails via Redis for performance

---

## 12. Security Considerations

### 12.1 Authentication & Authorization
- Laravel Sanctum for SPA token-based auth
- Token expiry: 24 hours (configurable)
- Refresh token support
- Rate limiting: 60 requests/minute per user (100 for admin)
- Brute force protection: lockout after 5 failed login attempts (15 min)

### 12.2 Data Protection
- All passwords: `bcrypt` hashing (cost factor 12)
- Sensitive fields encrypted at rest: phone numbers, addresses
- HTTPS enforced (301 redirect from HTTP)
- CORS configured to allow only known origins

### 12.3 File Upload Security
- Allowed MIME types whitelist
- Max file size: 50MB per file
- Files stored outside web root or in S3 with signed URLs
- Virus scan on upload (ClamAV or third-party)

### 12.4 API Security
- All API routes behind `auth:sanctum` middleware
- Permission checks on every protected endpoint
- SQL injection protection via Eloquent/parameterized queries
- XSS protection via Blade's `{{ }}` auto-escaping
- CSRF protection on all non-API routes

### 12.5 Audit Trail
- All CRUD operations logged to `activity_logs`
- Login/logout events logged to `login_logs`
- Soft deletes preserve data integrity

---

## 13. Testing Plan

### 13.1 Unit Tests (PHPUnit / Pest)

| Module              | Test Cases                                          |
|---------------------|-----------------------------------------------------|
| RBAC                | Permission check, role assignment, unauthorized access |
| Staff               | Create, soft delete, restore, login log generation  |
| Client              | Onboarding flow, service renewal date calculations  |
| Vendor              | Service expiry calculations, reminder triggers      |
| Project             | Status auto-transition on start date                |
| Task                | Assignment, follower notifications                   |
| Appointments        | Slot conflict detection, Meet link generation       |
| Notifications       | Email dispatch, WhatsApp message queuing            |

### 13.2 Feature Tests

```php
/** @test */
public function admin_can_create_staff_and_send_welcome_email()
{
    Mail::fake();
    $admin = User::factory()->admin()->create();
    
    $response = $this->actingAs($admin)
        ->postJson('/api/staff', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role_id' => Role::where('name', 'staff')->first()->id,
        ]);
    
    $response->assertCreated();
    Mail::assertSent(WelcomeEmail::class);
}

/** @test */
public function appointment_slot_conflict_is_rejected()
{
    $existingAppointment = Appointment::factory()->create([
        'scheduled_at' => '2026-05-01 10:00:00',
        'status' => 'scheduled',
    ]);
    
    $response = $this->postJson('/api/appointments', [
        'scheduled_at' => '2026-05-01 10:00:00',
        // ...
    ]);
    
    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['scheduled_at']);
}
```

### 13.3 UAT Checklist (Per Module)

| # | Test Scenario                             | Pass Criteria                          |
|---|------------------------------------------|----------------------------------------|
| 1 | Create staff and verify welcome email    | Email received with correct credentials |
| 2 | Client creates issue from portal         | Issue visible in admin dashboard        |
| 3 | Book a call form submits to CRM          | Enquiry visible in Book a Call list     |
| 4 | Appointment created, meet link generated | Link in confirmation email + in record  |
| 5 | Vendor service nearing expiry            | Reminder sent on scheduled date         |
| 6 | Project reaches start date               | Status auto-changes to In Progress      |
| 7 | Issue assigned to team leader            | Only leader sees it initially           |
| 8 | Bulk delete roles (non-system)           | Roles removed, users unassigned         |

---

## 14. Deployment Plan

### 14.1 Server Requirements

| Component          | Minimum Spec            | Recommended              |
|-------------------|-------------------------|--------------------------|
| Web Server         | Nginx 1.18+             | Nginx 1.24+              |
| PHP                | 8.2                     | 8.3                      |
| MySQL              | 8.0                     | 8.0+ or MariaDB 10.6+    |
| Redis              | 6.0                     | 7.0+                     |
| Storage            | 20GB SSD                | 100GB SSD                |
| RAM                | 4GB                     | 8GB+                     |
| SSL                | Let's Encrypt           | Wildcard SSL (Cloudflare)|

### 14.2 Environment Setup

```bash
# Laravel installation
composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker (Supervisor)
php artisan queue:work redis --queue=high,default,low

# Scheduler (Cron)
* * * * * cd /var/www/mycrm && php artisan schedule:run >> /dev/null 2>&1
```

### 14.3 CI/CD Pipeline

```yaml
# .github/workflows/deploy.yml
name: Deploy MyCRM
on:
  push:
    branches: [main]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run tests
        run: ./vendor/bin/pest
  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via Envoyer/SSH
        run: php artisan deploy
```

---

## 15. Project Timeline & Milestones

| Milestone                     | Target Date    | Deliverable                         |
|------------------------------|----------------|-------------------------------------|
| M1 — Requirements Sign-off   | Week 2         | BRD, FRS approved                   |
| M2 — Design Complete         | Week 5         | ERD, Wireframes, API Spec           |
| M3 — RBAC + Auth Live        | Week 7         | Login, roles, permissions working   |
| M4 — Staff + Teams Live      | Week 9         | Staff onboarding flow working       |
| M5 — Client Module Live      | Week 13        | Client portal accessible            |
| M6 — Vendor + Renewals Live  | Week 15        | Renewal reminders working           |
| M7 — Projects + Tasks Live   | Week 19        | Core project workflow complete      |
| M8 — Issues + Portal Live    | Week 21        | Client issue reporting working      |
| M9 — Leads + Appointments    | Week 24        | Google Meet integration working     |
| M10 — Notifications Complete | Week 27        | Email + WhatsApp reminders working  |
| M11 — UAT Complete           | Week 28        | All test cases passed               |
| M12 — Go Live                | Week 30        | Production deployment               |

**Total Estimated Duration:** 30 weeks (~7.5 months)

---

## 16. Risk Register

| # | Risk                                     | Probability | Impact  | Mitigation                                              |
|---|------------------------------------------|-------------|---------|--------------------------------------------------------|
| 1 | WhatsApp Business API approval delay     | Medium      | High    | Start Meta verification early; use Twilio as fallback  |
| 2 | Google Meet API quota limits             | Low         | Medium  | Implement caching, batch requests, monitor quota       |
| 3 | Scope creep adding new modules           | High        | Medium  | Strict change control process; document all additions  |
| 4 | Technofra website DB schema changes      | Medium      | Medium  | Use webhook approach instead of shared DB              |
| 5 | Staff performance data inaccuracy        | Low         | Low     | Clearly define KPI calculation formulas upfront        |
| 6 | Data loss during migration               | Low         | High    | Full backup before migration; test on staging first    |
| 7 | RBAC misconfiguration allowing data leak | Low         | Critical| Security audit before go-live; automated RBAC tests    |
| 8 | Email delivery in spam                   | Medium      | Medium  | Configure SPF, DKIM, DMARC; use reputable SMTP service |

---

## 17. Appendix — Tech Stack

### Backend
| Layer            | Technology                              |
|-----------------|-----------------------------------------|
| Framework        | Laravel 11+ (PHP 8.2+)                  |
| Authentication   | Laravel Sanctum                         |
| RBAC             | Spatie Laravel Permission v6            |
| Database ORM     | Eloquent (MySQL 8.0)                    |
| Queue            | Redis + Laravel Horizon                 |
| Scheduler        | Laravel Scheduler (Cron)                |
| File Storage     | AWS S3 / Laravel Storage (local)        |
| Search           | Laravel Scout + Meilisearch (optional)  |
| Logging          | Laravel Telescope (dev), Sentry (prod)  |
| Activity Log     | Spatie Laravel Activity Log             |

### Frontend
| Layer            | Technology                              |
|-----------------|-----------------------------------------|
| Admin UI         | Laravel Blade + Livewire v3             |
| Styling          | TailwindCSS v3                          |
| Components       | Alpine.js                               |
| Real-time        | Laravel Echo + Pusher / Reverb          |
| Rich Text        | Trix Editor                             |
| Date Picker      | Flatpickr                               |
| Charts           | Chart.js                                |

### Integrations
| Service           | Purpose                                |
|------------------|----------------------------------------|
| Mailgun/AWS SES   | Transactional email                    |
| Meta Cloud API    | WhatsApp Business messaging            |
| Google Calendar   | Meet link generation + event creation  |
| Pusher / Reverb   | Real-time notifications                |
| Sentry            | Error tracking (production)            |

### DevOps
| Tool              | Purpose                                |
|------------------|----------------------------------------|
| GitHub            | Source control + CI/CD                 |
| GitHub Actions    | Automated tests + deployment           |
| Envoyer           | Zero-downtime deployment (optional)    |
| Supervisor        | Queue worker process management        |
| Certbot           | SSL certificate automation             |
| MySQL Backups     | Daily automated backups to S3          |

---

*End of MyCRM Blackbook v1.0.0*
*Document prepared: April 14, 2026*
*For internal use only — Technofra / MyCRM Project Team*
*Created By - Saurabh Damale*