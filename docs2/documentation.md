# Project Documentation

## 1. Overview

This project is a Laravel-based CRM and operations platform for Technofra. It combines:

- internal staff management
- client and vendor management
- renewal tracking for services
- project and task tracking
- lead and enquiry management
- notification and reminder workflows
- API endpoints for web and mobile/integration use

The codebase uses a traditional Laravel MVC structure for web pages and a separate API layer for JSON-based consumers.

## 2. Tech Stack

- PHP 8.0.2+
- Laravel 9.x
- MySQL
- Blade templates
- Eloquent ORM
- Laravel Sanctum for API authentication
- Spatie Laravel Permission for RBAC
- L5 Swagger for API documentation
- CKEditor for rich text fields

## 3. Project Structure

### Main folders

- `app/Http/Controllers` - web controllers
- `app/Http/Controllers/Api` - API controllers
- `app/Http/Controllers/Web` - web-module controllers for some feature areas
- `app/Models` - Eloquent models
- `app/Services` - business logic layer
- `app/Actions` - reusable action classes
- `app/DTOs` - data transfer objects
- `app/Http/Requests` - request validation classes
- `app/Http/Resources` - API response transformers
- `resources/views` - Blade views
- `routes/web.php` - web routes
- `routes/api.php` - API routes
- `docs` - module-specific and feature-specific docs
- `docs2` - new consolidated documentation space

## 4. Authentication

### Web authentication

Web routes are protected with Laravel `auth` middleware.

### API authentication

API routes are protected by `auth:sanctum` except the public login and password reset endpoints.

### Public auth endpoints

- `POST /api/v1/login`
- `POST /api/v1/forgot-password`
- `POST /api/v1/reset-password`

## 5. Role-Based Access Control

The app uses role and permission-based access control via Spatie.

### Typical roles

- admin
- super_admin
- super_admin2
- staff
- client

### Permission checks

Many controllers check permissions with calls such as:

- `can('view_leads')`
- `can('edit_leads')`
- `can('view_services_detail')`
- `can('delete_services')`

This means UI access and API access may be restricted by role or permission.

## 6. Main Web Modules

### 6.1 Dashboard

The dashboard is the landing page after login and is exposed through:

- `GET /dashboard`

### 6.2 Permissions

Web routes support listing, creating, editing, and deleting permissions.

### 6.3 Roles

Role management includes:

- list roles
- create role
- update role
- delete role
- bulk delete support

### 6.4 Settings

Settings include:

- general settings
- company settings
- email settings
- renewal settings
- team settings
- department settings
- test email
- search tags

### 6.5 Staff

Staff module supports:

- list staff
- create staff
- edit staff
- delete staff
- restore and force delete
- analytics
- lead chart
- follow-up chart

### 6.6 Vendors

Vendor management is provided through resource routes and additional backward-compatible routes.

### 6.7 Vendor Services

Vendor services represent vendor renewal records.

Main features:

- create multiple vendor services at once
- edit and delete
- list and filter by renewal status
- send renewal email
- show detail page

### 6.8 Clients

Client management includes:

- list
- create
- edit
- delete
- status toggle
- bulk upload
- template download

### 6.9 Services

Client-facing services are similar to vendor services but represent client renewals.

Main features:

- create multiple services at once
- edit and delete
- show detail page
- renewal status badges
- filter and tab grouping

### 6.10 Projects

Project module includes:

- list
- create
- edit
- delete
- milestone management
- issue management
- file upload/download
- comments
- charts and usage views
- kanban helpers

### 6.11 Tasks

Task module includes:

- task list and kanban
- create/edit/delete
- comments
- attachments
- dependencies
- checklists
- time logs
- QA review flow

### 6.12 Client Issues

Client issue module includes:

- create and manage client issues
- assign team
- update status
- linked tasks

### 6.13 Leads

Lead management supports multiple source types and a combined view:

- lead
- digital marketing
- web app
- meta
- google
- contact form
- IndiaMart
- JustDial

### 6.14 Lead Management

This is the most feature-rich lead module.

It provides:

- merged lead list
- lead details
- assignments
- follow-ups
- notes
- reminders
- status history
- conversion
- escalation
- performance stats

### 6.15 Todos

Todo support includes:

- listing
- options
- create/update/delete
- status updates

### 6.16 Calendar

Calendar event APIs are available for event CRUD operations.

### 6.17 Book a Call

Book a call entries are listed and deletable.

### 6.18 Google Ads and Web Enquiries

The system also captures:

- Google Ads leads
- web enquiry contacts
- web enquiry careers

## 7. Renewal Modules

The renewal system is a major part of the app.

### 7.1 Client renewals

Client renewals are represented by the `services` module.

Common fields:

- client/company
- vendor
- service name
- service details
- plan type
- remark
- start date
- end date
- billing date
- status

### 7.2 Vendor renewals

Vendor renewals are represented by the `vendor-services` module.

Common fields:

- vendor
- service name
- service details
- plan type
- remark
- start date
- end date
- billing date
- status

### 7.3 Plan type values

Both renewal modules support:

- `monthly`
- `yearly`
- `quarterly`
- `half_year`

The UI displays these as:

- Monthly
- Yearly
- Quarterly
- Half Year

### 7.4 Effective status

Renewal status is often computed from the stored status and end date.

Typical display labels:

- upcoming
- active
- inactive
- pending
- expired

### 7.5 Sorting behavior

The renewal listing pages prioritize records in this order:

- expired
- upcoming
- active
- inactive
- pending

This makes overdue items visible first.

## 8. API Overview

The API lives under `routes/api.php` and is protected with `auth:sanctum` for most endpoints.

The API includes:

- auth
- dashboard
- permissions
- roles
- staff
- clients
- vendors
- vendor renewals
- client renewals
- todos
- leads
- lead management
- meta leads
- projects
- tasks
- client issues
- services
- calendar
- settings
- book a call
- google ads leads
- web enquiries

## 9. API Documentation Sources

There are two helpful docs outputs in this repo:

- `docs2/apis.md` - curl examples for the full API route set
- L5 Swagger config and annotations under `app/Swagger` and `config/l5-swagger.php`

Swagger docs route:

- `GET /api/documentation`

## 10. Web Route Summary

Important web groups from `routes/web.php`:

- authentication
- dashboard
- permissions
- roles
- settings
- staff
- vendors
- vendor-services
- clients
- services
- projects
- tasks
- client issues
- leads
- lead management
- notifications
- todos
- calendar
- book a call
- google ads leads
- web enquiries

## 11. API Route Summary

Important API groups from `routes/api.php`:

- `POST /api/v1/login`
- `POST /api/v1/forgot-password`
- `POST /api/v1/reset-password`
- `GET /api/v1/dashboard`
- `GET /api/v1/staff`
- `GET /api/v1/clients`
- `GET /api/v1/vendors`
- `GET /api/v1/vendor-renewals`
- `GET /api/v1/client-renewals`
- `GET /api/v1/leads`
- `GET /api/v1/lead-management`
- `GET /api/v1/projects`
- `GET /api/v1/tasks`
- `GET /api/v1/services`
- `GET /api/v1/settings`

For complete example requests, see [`docs2/apis.md`](./apis.md).

## 12. Key Models

### Service

Represents client renewals.

Important fields:

- `client_id`
- `client_business_detail_id`
- `vendor_id`
- `service_name`
- `service_details`
- `plan_type`
- `remark_text`
- `remark_color`
- `start_date`
- `end_date`
- `billing_date`
- `status`

### VendorService

Represents vendor renewals.

Important fields:

- `vendor_id`
- `service_name`
- `service_details`
- `plan_type`
- `remark_text`
- `remark_color`
- `start_date`
- `end_date`
- `billing_date`
- `status`

### Lead

The merged lead system stores lead metadata and supports value tracking.

Important fields:

- `name`
- `email`
- `phone`
- `company`
- `source`
- `lead_value`
- `status`

## 13. Lead Management Notes

Lead management merges multiple sources into a common API view.

The API list response includes:

- source type
- source id
- name
- email
- phone/number
- company
- source label
- lead value
- assigned to
- created at
- status

The detail response includes:

- lead details
- timeline
- follow-ups
- notes
- reminders
- assignments
- status history
- staff
- status options

## 14. Validation Pattern

The app uses request validation heavily.

Common patterns:

- `FormRequest` classes for API and web submissions
- `Validator::make()` for controller-based validation in some modules
- model-level `fillable` protection

## 15. UI Pattern

The Blade views generally follow this pattern:

- top alerts for success/error
- breadcrumb header
- filters or tabs
- table or form card
- action buttons for view/edit/delete

Some pages use DataTables with default ordering disabled to preserve backend ordering.

## 16. File Uploads and Rich Text

### File uploads

Used in modules such as:

- projects
- tasks

### Rich text

CKEditor is used for fields such as:

- service details
- task descriptions
- project-related descriptions

## 17. Notifications and Reminders

The system includes notification and reminder flows for:

- service renewals
- vendor renewals
- leads
- tasks
- bookings

## 18. Swagger / OpenAPI

L5 Swagger is installed and configured.

Relevant files:

- `config/l5-swagger.php`
- `app/Swagger/SwaggerInfo.php`
- `app/Http/Controllers/SwaggerController.php`

Generated docs are stored under:

- `storage/api-docs`

## 19. Environment Setup

### Typical local setup

1. Clone the repository.
2. Install dependencies with Composer.
3. Copy `.env.example` to `.env`.
4. Configure database credentials.
5. Run migrations and seeders as needed.
6. Generate the app key.
7. Start the local server.

### Example commands

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## 20. Useful Docs in This Repo

- `docs2/apis.md` - full curl-based API reference
- `docs/SERVICES_CRUD_DOCUMENTATION.md`
- `docs/VENDOR_CRUD_DOCUMENTATION.md`
- `docs/lead_management_api.md`
- `docs/project-api.md`
- `docs/SERVICE_API_README.md`
- `docs/VENDOR_API_CURL.md`
- `docs/CLIENT_API_CURL_REQUESTS.md`
- `docs/SETTINGS_API_README.md`

## 21. Current Notes and Conventions

- `half_year` is supported in both renewal modules.
- Renewal tables show the most urgent records first.
- API list and detail payloads are resource-driven where possible.
- The lead management API merges multiple source tables into one unified view.
- Some older compatibility routes still exist for backward support.

## 22. Suggested Next Steps

If you extend the system, update these first:

- route docs
- request validation
- model `$fillable`
- API resources
- curl examples
- Swagger annotations

