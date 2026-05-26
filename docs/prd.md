# Product Requirements Document (PRD)

## 1. Document Control

1. Product: `MyCRM` (Technofra CRM Platform)
2. Repository: `Technofra_task`
3. PRD basis date: 2026-05-26
4. Source of truth used: current codebase (`routes`, `controllers`, `models`, `migrations`, `composer.json`, `package.json`)

## 2. Product Summary

MyCRM is a role-based CRM and operations platform built on Laravel that unifies customer management, lead handling, service renewals, projects, tasks, support issues, calendar events, and multi-source enquiries.  
It provides both web UI workflows and authenticated REST APIs under `/api/v1/*`, with external ingestion points for Meta and Google lead capture.

## 3. Business Objectives

1. Centralize fragmented CRM operations into one system.
2. Improve lead response and assignment speed.
3. Reduce service/renewal misses with reminder workflows.
4. Improve execution visibility across projects, tasks, and client issues.
5. Support secure access through configurable roles and permissions.

## 4. User Types

1. Admin/Super Admin: System-wide control, settings, RBAC, and monitoring.
2. Staff: Handles day-to-day leads, projects, tasks, calendar, and issue resolution.
3. Team/Department members: Scoped access through assignments and role permissions.
4. Client-side entities: Managed via client and issue workflows (client-facing operational support flows are present in data and modules).

## 5. Scope

### 5.1 In Scope (Implemented)

1. Authentication and user profile flows.
2. RBAC (roles, permissions, permission grouping).
3. Staff module (CRUD, soft delete/restore/force delete, API v1 + staff-v2).
4. Client module (CRUD, status toggle, bulk upload/template, business details).
5. Vendor and vendor services module.
6. Service and renewal tracking.
7. Project module (milestones, issues, comments, files, status logs).
8. Task module (CRUD, comments, attachments).
9. Client issue module (assignment, status, issue-linked tasks).
10. Leads module + lead management (assignment, source-aware operations, export).
11. External lead streams:
- Meta leads
- Google leads
- Digital marketing leads
- Web app leads
- Book-a-call entries
- Web enquiry contacts/careers
12. Todo module (attachments and reminder channel support).
13. Notifications module (counts, summary, read-state).
14. Calendar events module (web + API).
15. Settings module (general/company/email/renewal/teams/departments/logo settings).
16. API documentation tooling with L5 Swagger.

### 5.2 Out of Scope (Current)

1. Native mobile app.
2. Full billing/payment engine.
3. Accounting suite integrations (e.g., Tally/Zoho Books).
4. Microservice split architecture.

## 6. Functional Requirements

### 6.1 Authentication and Session Management

1. Web login/logout/register and password reset flows.
2. API login, forgot/reset password, `/me`, logout, logout-all.
3. Sanctum token-based protected APIs.
4. FCM token registration for push ecosystem.

### 6.2 Authorization (RBAC)

1. Create, update, delete roles.
2. List and group permissions.
3. Enforce role-permission checks across web and API modules.
4. Persist mappings via Spatie permission tables.

### 6.3 Staff and Organization

1. Staff CRUD with soft delete lifecycle.
2. Staff association with teams and departments.
3. API endpoints for staff tasks and staff projects.
4. Staff form options endpoints for clients/apps.

### 6.4 Client Management

1. Client CRUD in web and API.
2. Status toggle support.
3. Bulk upload and template download in web.
4. Client business details model support.

### 6.5 Vendor, Services, and Renewals

1. Vendor CRUD in web and API.
2. Vendor service CRUD with status and remarks.
3. Service entities tied to client/vendor contexts.
4. Renewal configuration and reminder trigger routes.

### 6.6 Projects

1. Project CRUD with soft delete.
2. Milestone CRUD nested under projects.
3. Issue CRUD nested under projects.
4. Project comments and file upload/download/delete.
5. Project usage endpoint and status-log history model.
6. My-projects view route for personal scope.

### 6.7 Tasks

1. Task CRUD with soft delete.
2. Task comments and attachment endpoints.
3. Task form options endpoint for dependent dropdowns/workflows.

### 6.8 Client Issues and Resolution Workflow

1. Client issue CRUD and status updates.
2. Team assignment for issue ownership.
3. Issue-linked task lifecycle: create, view, update, status update, delete.

### 6.9 Leads and Lead Management

1. Manual lead CRUD + status operations + export.
2. Unified lead-management endpoints for:
- source-specific detail view
- assignment and bulk assignment
- status updates
- deletion
3. Assigned-lead model support for ownership tracking.

### 6.10 External Lead Sources

1. Meta leads:
- webhook verify/handle endpoints
- UI index/show/status/sync/delete
- API index/show/sync/delete
2. Google leads:
- ingestion endpoint for ad lead posts
- UI list/show/status
- API list/stats/show
3. Digital marketing leads:
- web and API listing/status/deletion
4. Web app leads:
- persisted source with status support
5. Book-a-call:
- web and API listing/deletion
- meeting agenda field support
6. Web enquiry:
- contact listing/deletion
- career listing/view/resume/delete

### 6.11 Notifications

1. API notification listing and mark-read/mark-all-read.
2. Web notification feeds:
- renewal notifications
- counts
- urgent notifications
- summary
3. Read-state persistence table (`notification_reads`).

### 6.12 Calendar and Scheduling

1. Calendar event CRUD in web and API.
2. Event status toggle support.
3. Event schema includes WhatsApp-related fields.

### 6.13 Todos

1. Todo CRUD in web and API.
2. Todo status toggle.
3. Attachments and reminder channel support.

### 6.14 Settings and Configuration

1. Settings categories: general, company, email, renewal, teams, departments.
2. Test email trigger.
3. Tag search helper.
4. App logo and login logo retrieval/update endpoints.

## 7. API Requirements

### 7.1 Public API

1. `/api/v1/login`
2. `/api/v1/forgot-password`
3. `/api/v1/reset-password`
4. `/api/facebook/webhook` (GET/POST)
5. `/api/google-ads/lead` (POST)

### 7.2 Protected API (auth:sanctum)

1. Auth/account + FCM token
2. Dashboard + quick stats
3. Notifications
4. Permissions + roles
5. Staff + staff-v2 + staff workload endpoints
6. Clients
7. Vendors + vendor renewals
8. Client renewals
9. Todos
10. Leads + meta leads + google ads lead view/stats
11. Projects + milestones/issues/comments/files/usage
12. Tasks + comments + attachments
13. Client issues + issue tasks + assignment
14. Services
15. Calendar events
16. Settings
17. Book-a-call
18. Digital marketing + web apps leads
19. Web enquiry contact/career

## 8. Data Requirements (High-Level)

### 8.1 Core Identity and Security

1. `users`
2. `password_resets`
3. `personal_access_tokens`
4. Spatie permission tables
5. `fcm_tokens`

### 8.2 CRM and Operations

1. `clients`, `client_business_details`, `user_address`
2. `vendors`, `vendor_services`, `services`
3. `projects`, `project_milestones`, `project_issues`, `project_files`, `project_comments`, `project_status_logs`
4. `tasks`, `task_attachments`, `task_comments`
5. `client_issues`, `client_issue_tasks`, `client_issue_team_assignments`

### 8.3 Lead and Enquiry Ecosystem

1. `leads`, `assigned_leads`
2. `meta_leads`, `google_leads`, `digital_marketing_leads`, `webapp_leads`
3. `bookcall`
4. web enquiry tables (`jobapplication`, `contactform`)

### 8.4 Productivity and Messaging

1. `todos`
2. `calendar_events`
3. `notifications`, `notification_reads`
4. `settings`, `tags`

## 9. Integrations

1. Meta/Facebook:
- `facebook/php-business-sdk`
- webhook verification/processing
2. Google:
- `google/auth`
- Google ads lead ingestion routes
3. Email:
- Laravel mail workflows and test-email endpoint
4. WhatsApp:
- renewal reminder trigger route
5. Push:
- FCM token capture + test notification endpoint

## 10. Non-Functional Requirements

1. Security:
- Sanctum auth, RBAC enforcement, throttled password reset endpoint
2. Reliability:
- migration-based schema management
- soft-delete recoverability in major modules
3. Maintainability:
- clear web/API controller separation
- modular models and dedicated tables
4. Performance:
- list endpoints designed for scalable records and dashboard usage
5. Documentation:
- extensive module docs already present in `/docs` with API curl references

## 11. Reporting and KPI Requirements

1. Dashboard should expose operational summaries and quick stats.
2. Lead funnel monitoring should cover source and status conversion tracking.
3. Renewal tracking should surface critical upcoming expiries.
4. Delivery tracking should monitor project/task progress and issue closure speed.

## 12. Risks and Constraints

1. Multi-source lead schemas can drift and create normalization overhead.
2. Permission misconfiguration can create data access risk.
3. Legacy data migration and nullable relationship handling must be validated.
4. Email/WhatsApp reminder reliability depends on provider setup and credentials.

## 13. Implementation Status Notes

1. Project is implemented as Laravel monolith (`laravel/framework ^9.19`).
2. API and web are both first-class and actively wired in routes.
3. Documentation set in `/docs` is extensive and module-focused.
4. The PRD reflects implemented behavior from live code, not only roadmap intent.

## 14. Technical Stack

1. Backend:
- PHP `^8.0.2`
- Laravel `^9.19`
- Sanctum
- Spatie Laravel Permission
- L5 Swagger
- Maatwebsite Excel
- Facebook Business SDK
- Google Auth
2. Frontend/build:
- Blade-based views
- Vite tooling (`vite`, `laravel-vite-plugin`, `axios`)
3. Database:
- MySQL/MariaDB with migration-led schema evolution

## 15. Source Mapping Used

1. `routes/web.php`
2. `routes/api.php`
3. `app/Http/Controllers/*`
4. `app/Http/Controllers/Api/*`
5. `app/Models/*`
6. `database/migrations/*`
7. `composer.json`
8. `package.json`

