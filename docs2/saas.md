# SaaS Readiness Audit And Conversion Plan

Date reviewed: 2026-07-11

## Short Answer

This project is **not currently a SaaS-based software architecture**.

It is a strong **single-company, multi-user CRM/operations platform**. It has authentication, staff/client roles, permissions, dashboards, projects, tasks, leads, renewals, settings, notifications, APIs, and mobile-token support. Those are useful SaaS features, but the codebase does not yet have the core SaaS requirement: **multiple independent customer organizations using the same application while their data, settings, users, files, billing, and permissions stay isolated from each other**.

## Why It Is Not SaaS Yet

### 1. No tenant or organization model

I did not find a `Tenant`, `Organization`, `Workspace`, `Company`, or similar owner model that represents each paying SaaS customer.

Current behavior:

- `users` are global.
- `settings` are global.
- roles and permissions are global.
- projects, tasks, clients, leads, services, vendors, todos, calendar events, and notifications are global records.

Required SaaS behavior:

- Every customer account should have its own tenant record.
- Every tenant-owned record should belong to that tenant.
- Users should access only records inside their tenant unless they are platform-level admins.

### 2. No tenant ID on business tables

Important tables do not have `tenant_id` or `organization_id`, including:

- `users`
- `settings`
- `roles`
- `permissions` pivots
- `clients` / client users
- `vendors`
- `services`
- `vendor_services`
- `projects`
- `tasks`
- `todos`
- `leads`
- `meta_leads`
- `google_leads`
- `digital_marketing_leads`
- `webapp_leads`
- `contactform`
- `bookcall`
- `calendar_events`
- `notifications`
- `fcm_tokens`
- `teams`
- `departments`
- project child tables
- task child tables
- lead child tables
- AMC tables

Because these tables are not tenant-scoped, one installation behaves like one company database.

### 3. No tenant resolver middleware

The HTTP kernel has auth, permission, role, API, and web middleware, but no middleware that resolves the current tenant from:

- subdomain, for example `acme.yourcrm.com`
- custom domain, for example `crm.acme.com`
- request header
- authenticated user's selected organization
- route prefix, for example `/app/{tenant}`

Without a tenant resolver, controllers cannot reliably know which tenant owns the current request.

### 4. Queries are mostly global

Controllers and services query models directly, for example:

- `User::where('role', 'staff')`
- `Role::query()`
- `Permission::all()`
- `Setting::get(...)`
- project/task/lead/client/vendor list queries without tenant filters

That is normal for an internal CRM, but it is unsafe for SaaS. In SaaS, every tenant-owned query must include tenant scoping automatically or consistently.

### 5. Settings are global

`settings` has a unique `key` column and static helpers:

- `Setting::get($key)`
- `Setting::set($key, $value)`
- `Setting::getAllSettings()`

This means company name, email, logos, legal text, renewal settings, departments, teams, mail settings, and branding are shared across the whole application.

For SaaS, settings must be tenant-specific, while platform settings remain separate.

### 6. Roles and permissions are global

The project uses `spatie/laravel-permission`, but `config/permission.php` has:

```php
'teams' => false,
```

Spatie's team feature is not enabled, and roles are not scoped per tenant. If two tenants both have an `Admin` role, they would currently share the same role definition unless you build custom isolation.

### 7. Authentication is not tenant-aware

Web login and API login find users by global email:

```php
User::where('email', $request->email)->first()
```

Current `users.email` is globally unique. That is acceptable for some SaaS products, but you still need the authenticated user to be tied to a tenant membership. Otherwise the app cannot know which organization context to load.

### 8. Registration creates only a user

The web registration flow creates a user only. It does not create:

- tenant organization
- tenant owner membership
- default tenant roles
- default tenant settings
- trial subscription
- onboarding workspace

A SaaS signup should create the full customer account workspace.

### 9. No subscription or billing system

I did not find Laravel Cashier, Stripe, Paddle, Razorpay, plan tables, subscriptions, invoices, usage limits, trials, or billing webhooks for the SaaS product itself.

There are "billing date", "plan type", vendor renewals, service renewals, and client billing fields, but these are CRM business data, not SaaS subscription billing for using this software.

### 10. Files are not tenant-isolated

Uploads use shared paths such as:

- `uploads/profile`
- `uploads/staff`
- `uploads/clients`
- `uploads/settings`

For SaaS, file paths should include tenant context, or files should be stored with tenant ownership metadata.

### 11. Background jobs and notifications are not tenant-aware

Jobs and notification services send reminders, FCM notifications, calendar notifications, lead reminders, and renewal emails. These must carry tenant context so queued work does not read global settings or notify users from the wrong tenant.

### 12. Public integrations are not tenant-aware

The app has webhook/integration-style endpoints for Facebook and Google leads. In SaaS, each tenant needs its own integration configuration and incoming webhooks must map the external account/page/form/source to the correct tenant.

## Current Software Classification

This codebase is best described as:

**A Laravel-based CRM and operations management system for one company, with role-based access and API support.**

It is not yet:

- multi-tenant SaaS
- subscription SaaS
- white-label SaaS
- self-service SaaS platform

It can become SaaS, but it needs tenant architecture added across the full stack.

## Recommended SaaS Architecture

For this codebase, I recommend **single database, shared tables, tenant_id column**.

Why:

- It is the smallest practical migration from your current Laravel app.
- Your app has many related modules and many cross-table reports.
- You already use one database and standard Eloquent models.
- It avoids the operational complexity of one database per tenant.

Alternative architectures:

- **Database per tenant**: stronger isolation, harder migrations, harder reporting, more operational work.
- **Schema per tenant**: uncommon in MySQL-based Laravel apps and more complex.
- **Single database with `tenant_id`**: best fit for your current codebase.

## Core SaaS Data Model

Add these platform tables.

### `tenants`

Represents each SaaS customer/company.

Suggested columns:

```php
id
uuid
name
slug
domain nullable
custom_domain nullable
owner_user_id nullable
status enum: trialing, active, past_due, suspended, cancelled
timezone
locale
currency
trial_ends_at nullable
created_at
updated_at
deleted_at
```

### `tenant_user`

Allows one user to belong to one or more tenants.

Suggested columns:

```php
id
tenant_id
user_id
role
is_owner boolean
status enum: invited, active, suspended
invited_by nullable
joined_at nullable
created_at
updated_at
unique tenant_id + user_id
```

### `plans`

Defines SaaS plans.

Suggested columns:

```php
id
name
slug
description
price_monthly
price_yearly
currency
max_users nullable
max_clients nullable
max_projects nullable
max_storage_mb nullable
features json
is_active boolean
created_at
updated_at
```

### `subscriptions`

Tracks each tenant's SaaS subscription.

Suggested columns:

```php
id
tenant_id
plan_id
provider
provider_customer_id nullable
provider_subscription_id nullable
status
billing_cycle enum: monthly, yearly
trial_ends_at nullable
current_period_starts_at nullable
current_period_ends_at nullable
cancelled_at nullable
created_at
updated_at
```

### `tenant_invitations`

For inviting staff/client users into a tenant.

Suggested columns:

```php
id
tenant_id
email
role
token
invited_by
expires_at
accepted_at nullable
created_at
updated_at
```

### `tenant_domains`

Optional but useful for subdomain/custom-domain support.

Suggested columns:

```php
id
tenant_id
domain
type enum: subdomain, custom
is_primary boolean
verified_at nullable
created_at
updated_at
```

## Tables That Need Tenant Ownership

Add `tenant_id` to every tenant-owned table. At minimum:

### Identity and configuration

- `users`, if every user belongs to only one tenant; otherwise use only `tenant_user`
- `settings`
- `roles`
- `model_has_roles`
- `model_has_permissions`
- `departments`
- `teams`
- `staff_department`
- `staff_team`
- `tags`
- `fcm_tokens`
- `notification_reads`

### CRM and renewal records

- `clients`
- `client_business_details`
- `user_address`
- `vendors`
- `services`
- `vendor_services`
- `amc_services`
- `amc_service_details`

### Projects and tasks

- `projects`
- `tasks`
- `project_files`
- `project_comments`
- `project_milestones`
- `project_issues`
- `project_status_logs`
- `project_status_histories`
- `project_activities`
- `project_change_requests`
- `project_approvals`
- `project_deployments`
- `sprints`
- `task_comments`
- `task_comment_attachments`
- `task_attachments`
- `task_checklists`
- `task_time_logs`
- `task_dependencies`
- `task_followers`
- `task_status_histories`
- `task_tags`
- `task_labels`

### Leads and public enquiries

- `leads`
- `assigned_leads`
- `lead_activities`
- `lead_assignments`
- `lead_followups`
- `lead_notes`
- `lead_reminders`
- `lead_status_histories`
- `lead_escalations`
- `lead_conversions`
- `lead_pipeline_stages`
- `staff_lead_stats`
- `meta_leads`
- `google_leads`
- `digital_marketing_leads`
- `webapp_leads`
- `contactform`
- `jobapplication`
- `bookcall`

### Calendar, notifications, jobs

- `calendar_events`
- application notification tables, if any are database-backed
- reminder delivery logs
- queued job payloads should include tenant context where needed

## Tables That Can Stay Global

These can usually remain platform-level:

- `failed_jobs`
- `password_resets`, unless duplicate emails per tenant are allowed
- `personal_access_tokens`, but tokens must be tied to tenant context when used
- default permission definitions, if permissions are platform-defined
- `plans`
- `subscriptions`
- billing provider events
- platform admin audit logs

## Laravel Implementation Plan

### Phase 1: Add tenant foundation

Create:

- `Tenant` model
- `TenantUser` model
- `Plan` model
- `Subscription` model
- `TenantInvitation` model
- `TenantDomain` model if using domains

Create migrations for:

- `tenants`
- `tenant_user`
- `plans`
- `subscriptions`
- `tenant_invitations`
- `tenant_domains`

Add relationships:

```php
// User.php
public function tenants()
{
    return $this->belongsToMany(Tenant::class)
        ->withPivot(['role', 'is_owner', 'status'])
        ->withTimestamps();
}

// Tenant.php
public function users()
{
    return $this->belongsToMany(User::class)
        ->withPivot(['role', 'is_owner', 'status'])
        ->withTimestamps();
}
```

### Phase 2: Resolve current tenant per request

Create middleware:

```php
ResolveTenant
```

It should resolve tenant by one selected strategy:

- authenticated user's selected tenant stored in session/token
- subdomain
- custom domain
- route prefix

Store current tenant in a container service:

```php
app(CurrentTenant::class)->set($tenant);
```

Register middleware in `app/Http/Kernel.php` after auth for protected web/API routes.

### Phase 3: Add tenant scoping to models

Create reusable trait:

```php
BelongsToTenant
```

Responsibilities:

- auto-fill `tenant_id` when creating records
- add global scope `where tenant_id = current tenant`
- provide `withoutTenantScope()` only for platform admins and migrations

Example:

```php
protected static function bootBelongsToTenant()
{
    static::creating(function ($model) {
        if (! $model->tenant_id && app(CurrentTenant::class)->id()) {
            $model->tenant_id = app(CurrentTenant::class)->id();
        }
    });

    static::addGlobalScope('tenant', function ($builder) {
        if ($tenantId = app(CurrentTenant::class)->id()) {
            $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
        }
    });
}
```

Apply it to tenant-owned models such as:

- `User` or tenant-owned profile models
- `Setting`
- `Vendor`
- `VendorService`
- `Service`
- `Project`
- `Task`
- `Lead`
- `Todo`
- `CalendarEvent`
- `Team`
- `Department`
- `Tag`
- all child models for projects, tasks, and leads

### Phase 4: Migrate schema safely

Add nullable `tenant_id` first to avoid breaking existing data.

Example:

```php
Schema::table('projects', function (Blueprint $table) {
    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
    $table->index(['tenant_id', 'status']);
});
```

Then backfill existing data into a default tenant:

```php
$tenant = Tenant::firstOrCreate([
    'slug' => 'default',
], [
    'name' => Setting::get('company_name', 'Default Company'),
    'status' => 'active',
]);
```

After backfill and testing, make `tenant_id` non-null for tenant-owned tables.

### Phase 5: Fix unique constraints

Global unique fields must become tenant-aware where appropriate.

Examples:

- `settings.key` should become unique on `tenant_id + key`
- `tags.name` should become unique on `tenant_id + name`
- roles should become unique on `tenant_id + name + guard_name` if roles are tenant-customizable
- client/vendor/service names should not be globally unique if they become unique later

For users, choose one strategy:

Option A: Global email identity

- keep `users.email` globally unique
- use `tenant_user` for tenant memberships
- best for users who may switch organizations

Option B: Tenant-specific email identity

- change unique email to `tenant_id + email`
- harder for login and password resets
- useful only if you need the same email to exist as different accounts in different tenants

Recommended: **Option A, global email plus tenant memberships**.

### Phase 6: Make settings tenant-aware

Change `settings` from global:

```php
key unique
```

to tenant-scoped:

```php
tenant_id nullable
key
unique tenant_id + key
```

Update helpers:

```php
Setting::get($key, $default = null, $tenantId = null)
Setting::set($key, $value, $type = 'text', $tenantId = null)
Setting::getAllSettings($tenantId = null)
```

Split settings into:

- platform settings: `tenant_id = null`
- tenant settings: `tenant_id = current tenant`

Affected areas:

- company information
- logos
- mail from name/email
- renewal settings
- legal pages
- teams/departments settings
- notification settings

### Phase 7: Tenant-aware roles and permissions

You have two good choices.

Option A: Use Spatie teams

- set `config/permission.php` `teams` to `true`
- add `team_id` columns to Spatie permission tables
- use tenant ID as Spatie team ID
- set current permission team after tenant resolution

Option B: Keep permissions global, make role assignment tenant-aware

- global permissions define capabilities
- tenant-specific roles decide which permissions a tenant role has
- use custom role tables or add `tenant_id` to roles

Recommended: **Use Spatie teams if you want tenant-specific roles.** If all tenants share the same role templates, keep permission definitions global and scope role assignments through `tenant_user`.

### Phase 8: Update controllers and services

Every list, show, create, update, delete, export, dashboard, and report endpoint must become tenant-aware.

High-risk files and areas:

- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/StaffController.php`
- `app/Http/Controllers/ClientController.php`
- `app/Http/Controllers/ProjectController.php`
- `app/Http/Controllers/TaskController.php`
- `app/Http/Controllers/LeadController.php`
- `app/Http/Controllers/Web/LeadManagementController.php`
- `app/Http/Controllers/Api/ProjectController.php`
- `app/Http/Controllers/Api/TaskController.php`
- `app/Http/Controllers/Api/LeadController.php`
- `app/Http/Controllers/Api/V1/LeadManagementController.php`
- `app/Http/Controllers/Api/SettingController.php`
- all vendor/client renewal controllers
- dashboard controllers
- notification controllers

Rules:

- Never trust a raw ID without checking tenant ownership.
- When assigning staff to projects/tasks/leads, verify the assigned user belongs to the same tenant.
- When selecting clients/vendors/services, restrict options to the tenant.
- Exports must include only current tenant records.
- Bulk upload must create records under current tenant.
- Bulk delete must delete only current tenant records.

### Phase 9: Fix JSON user references

Some records store users in JSON fields:

- `projects.members`
- `tasks.assignees`
- `tasks.followers`
- `leads.assigned`
- `assigned_leads.staff_ids`

This works for a single-company app but is risky for SaaS because IDs can be mixed across tenants.

Recommended change:

- Keep JSON only for display metadata if needed.
- Add pivot tables for relationships.

Suggested pivots:

- `project_members(project_id, user_id, tenant_id, role)`
- `task_assignees(task_id, user_id, tenant_id)`
- `task_followers(task_id, user_id, tenant_id)`
- `lead_assignees(lead_id, user_id, tenant_id, assigned_by)`

At minimum, validate every JSON ID belongs to the current tenant.

### Phase 10: SaaS signup and onboarding

Change registration flow to create a tenant.

Signup should collect:

- company name
- owner name
- owner email
- password
- plan
- optional phone

On successful signup:

- create `tenants`
- create owner `users` record
- create `tenant_user` owner membership
- create tenant default settings
- create default departments/teams if needed
- assign owner/admin role in that tenant
- create trial subscription
- redirect to onboarding/dashboard

### Phase 11: Tenant-aware login

After login:

- load user's active tenants
- if one tenant, select it automatically
- if multiple tenants, show organization switcher
- store selected tenant in session for web
- include tenant context in Sanctum API tokens or require `X-Tenant-ID`

API options:

- `/api/v1/tenants` returns memberships
- `/api/v1/tenants/{tenant}/switch` selects tenant
- protected endpoints require selected tenant

### Phase 12: Subscription billing

Add SaaS billing for the software itself.

Recommended Laravel options:

- Stripe with Laravel Cashier
- Paddle with Laravel Cashier Paddle
- Razorpay with custom subscription tables and webhooks, if Indian billing is required

Features to implement:

- plan selection
- trial
- checkout
- subscription status webhooks
- invoice history
- billing portal
- cancellation
- plan upgrade/downgrade
- grace period
- payment failure lockout

Middleware:

```php
EnsureTenantSubscriptionIsActive
```

Apply it to paid app routes, but allow:

- billing pages
- profile
- logout
- support/contact

### Phase 13: Plan limits and feature gates

Add feature checks for plan limits.

Examples:

- max users
- max clients
- max projects
- max leads
- max storage
- advanced project management
- API access
- white-label branding
- integrations
- WhatsApp reminders
- FCM push notifications
- custom domain

Create:

```php
PlanLimitService
FeatureGateService
```

Use them in store/update actions and UI rendering.

### Phase 14: Tenant-aware files

Change upload paths from shared folders to tenant folders.

Examples:

```text
uploads/tenants/{tenant_id}/profile
uploads/tenants/{tenant_id}/staff
uploads/tenants/{tenant_id}/clients
uploads/tenants/{tenant_id}/settings
uploads/tenants/{tenant_id}/projects/{project_id}
uploads/tenants/{tenant_id}/tasks/{task_id}
```

Also:

- store `tenant_id` on file records
- verify tenant ownership before download/delete
- prevent path traversal
- add storage usage tracking for plan limits

### Phase 15: Tenant-aware jobs and notifications

All queued jobs should include `tenant_id` when they need tenant settings or tenant-owned data.

Affected jobs/services:

- calendar reminder jobs
- todo reminder jobs
- lead reminder jobs
- renewal mail jobs
- FCM notifications
- WhatsApp notifications
- project notifications
- lead analytics updates

Pattern:

```php
public function __construct(public int $tenantId, ...)
```

Then inside `handle()`:

```php
app(CurrentTenant::class)->set(Tenant::findOrFail($this->tenantId));
```

### Phase 16: Tenant-aware integrations

For Meta/Facebook, Google Ads, web enquiries, and public forms:

Create tables:

- `tenant_integrations`
- `tenant_webhooks`
- `tenant_api_keys`

Map incoming payloads to tenant by:

- webhook token
- page ID
- form ID
- Google customer/account ID
- custom public form endpoint

Do not store all incoming leads globally without a tenant.

### Phase 17: Platform admin area

SaaS needs a separate platform admin area for your company.

Platform admin features:

- view all tenants
- create/suspend/cancel tenants
- impersonate tenant owner safely
- view subscription status
- view usage
- manage plans
- view failed webhooks
- platform audit logs
- global announcements

Keep this separate from tenant admins.

Suggested roles:

- `platform_super_admin`
- `platform_support`
- `tenant_owner`
- `tenant_admin`
- `tenant_staff`
- `tenant_client`

### Phase 18: Security requirements

Before calling the product SaaS-ready, add tests and protections for:

- cross-tenant record access by ID
- cross-tenant update/delete
- cross-tenant file download
- cross-tenant exports
- cross-tenant dashboard counts
- cross-tenant notifications
- cross-tenant role changes
- password reset behavior
- API token tenant switching
- bulk upload tenant assignment
- webhook tenant mapping

Add audit logging:

- login/logout
- tenant switch
- billing changes
- user invites
- role/permission changes
- destructive deletes
- exports/downloads
- integration changes

### Phase 19: Deployment and operations

For SaaS production, add:

- queues with a real worker process
- scheduler/cron
- object storage such as S3-compatible storage
- backups with restore testing
- tenant data export
- tenant data deletion process
- monitoring and alerts
- error tracking
- rate limiting
- API throttling per tenant
- database indexes on `tenant_id`
- cache keys prefixed by tenant
- log context containing tenant ID

## Concrete Code Changes Checklist

### New files/classes

- `app/Models/Tenant.php`
- `app/Models/TenantUser.php`
- `app/Models/Plan.php`
- `app/Models/Subscription.php`
- `app/Models/TenantInvitation.php`
- `app/Models/TenantDomain.php`
- `app/Support/CurrentTenant.php`
- `app/Models/Concerns/BelongsToTenant.php`
- `app/Http/Middleware/ResolveTenant.php`
- `app/Http/Middleware/EnsureTenantSubscriptionIsActive.php`
- `app/Services/TenantOnboardingService.php`
- `app/Services/PlanLimitService.php`
- `app/Services/FeatureGateService.php`
- `app/Services/TenantStorageService.php`
- `app/Services/BillingService.php`

### Existing files to update

- `app/Http/Kernel.php`
- `routes/web.php`
- `routes/api.php`
- `app/Models/User.php`
- `app/Models/Setting.php`
- all tenant-owned models
- all controllers that query tenant-owned data
- all services that query tenant-owned data
- all jobs that read tenant-owned data/settings
- all notification/mail classes that use company settings
- upload helpers
- seeders
- factories
- tests
- Swagger/OpenAPI docs
- API documentation under `docs/`

### Migrations to add

- create tenant foundation tables
- add `tenant_id` to tenant-owned tables
- add tenant-aware indexes
- update unique indexes
- migrate/backfill existing data to default tenant
- optionally normalize JSON relationships into pivot tables
- optionally enable Spatie teams

## Suggested Migration Order

1. Create `tenants` and `tenant_user`.
2. Create one default tenant for existing data.
3. Attach all existing users to the default tenant.
4. Add nullable `tenant_id` to tenant-owned tables.
5. Backfill `tenant_id` on all existing rows.
6. Add indexes on `tenant_id`.
7. Add `CurrentTenant`, `ResolveTenant`, and tenant model trait.
8. Update settings to tenant-aware.
9. Update auth and registration.
10. Update controllers/services in modules one by one.
11. Add cross-tenant security tests.
12. Make `tenant_id` non-null where appropriate.
13. Add billing/subscriptions.
14. Add platform admin.
15. Add tenant-aware integrations and file storage.

## Module-by-Module SaaS Conversion Notes

### Auth

Needs tenant onboarding, tenant selection, tenant invitations, tenant-aware password reset decisions, and API tenant context.

### Staff

Staff should be tenant members, not global users selected by `role = staff`. Department/team assignment must be tenant-scoped.

### Clients

Clients should belong to a tenant. If clients can log in, they should be tenant members with restricted role/policies.

### Vendors and services

Vendor and client renewal records must be tenant-scoped. Renewal notifications must use tenant settings and tenant mail branding.

### Projects and tasks

Projects, tasks, members, assignees, followers, files, comments, milestones, issues, approvals, deployments, and time logs must all remain inside one tenant.

### Leads

Leads from manual entry, Meta, Google Ads, web apps, digital marketing, book calls, contact forms, and career forms need tenant mapping.

### Settings

Split into platform settings and tenant settings. Company info, logos, mail sender, legal content, teams, departments, and renewal settings should be tenant-specific.

### Notifications

Notification reads, FCM tokens, push sends, email sends, WhatsApp sends, and dashboard notification counts need tenant scoping.

### API

Every `/api/v1/...` endpoint should require a tenant context except public endpoints and platform-admin endpoints.

### Documentation

Update API docs to mention tenant context, headers, tenant switching, subscription errors, and permission behavior.

## SaaS Definition Of Done

You can call this product SaaS-ready when all of these are true:

- A new company can sign up without developer/manual database work.
- Signup creates a tenant workspace, owner, roles, settings, and trial/subscription.
- Multiple companies can use the same deployed app and database.
- Users can only see data from their selected tenant.
- All tenant-owned tables have tenant ownership.
- All tenant-owned queries are scoped.
- Roles/permissions are tenant-safe.
- Files are tenant-isolated.
- Notifications and jobs run with tenant context.
- Integrations map incoming data to the correct tenant.
- Billing plans, subscription status, and feature limits are enforced.
- Platform admins can manage tenants separately from tenant admins.
- Automated tests prove cross-tenant isolation.

## Final Verdict

Your software is **not SaaS-based yet**. It is currently a **single-tenant CRM/operations system**.

To make it SaaS-based, the biggest required change is not UI. The biggest required change is adding **tenant ownership and tenant isolation everywhere**: database schema, Eloquent models, controllers, settings, permissions, uploads, jobs, notifications, APIs, integrations, and billing.

The safest path is to convert it into a **single-database multi-tenant Laravel SaaS** using `tenant_id`, a tenant resolver middleware, tenant-scoped models, tenant-aware settings, and subscription billing.
