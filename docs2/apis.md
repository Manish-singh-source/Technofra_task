# API Curl Reference

This file lists the API routes in the same order as `routes/api.php`.

## Base

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="your-sanctum-token"
AUTH_HEADER="Authorization: Bearer $TOKEN"
JSON_HEADER="Content-Type: application/json"
```

## Public Auth

```bash
# Login
curl -X POST "$BASE_URL/login" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -d '{"email":"user@example.com","password":"password"}'

# Forgot password
curl -X POST "$BASE_URL/forgot-password" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -d '{"email":"user@example.com"}'

# Reset password
curl -X POST "$BASE_URL/reset-password" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -d '{"email":"user@example.com","token":"reset-token","password":"NewPassword123!","password_confirmation":"NewPassword123!"}'
```

## Auth / Session

```bash
# Test FCM
curl -X POST "$BASE_URL/test-fcm" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Current user
curl -X GET "$BASE_URL/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Store FCM token
curl -X POST "$BASE_URL/fcm-token" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"fcm_token":"device-token"}'

# Logout
curl -X POST "$BASE_URL/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Logout all sessions
curl -X POST "$BASE_URL/logout-all" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Notifications

```bash
# List notifications
curl -X GET "$BASE_URL/notifications" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Mark notification as read
curl -X PATCH "$BASE_URL/notifications/1/read" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Mark all notifications as read
curl -X PATCH "$BASE_URL/notifications/read-all" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Dashboard

```bash
# Dashboard
curl -X GET "$BASE_URL/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Quick stats
curl -X GET "$BASE_URL/quick-stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Permissions

```bash
# Permission list
curl -X GET "$BASE_URL/permissions" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Grouped permissions
curl -X GET "$BASE_URL/permissions/grouped" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Roles

```bash
# List roles
curl -X GET "$BASE_URL/roles" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create role
curl -X POST "$BASE_URL/roles" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Manager","permissions":["view_dashboard","edit_users"]}'

# Update role
curl -X PUT "$BASE_URL/roles/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Manager","permissions":["view_dashboard"]}'

# Delete role
curl -X DELETE "$BASE_URL/roles/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Staff

```bash
# Staff form options
curl -X GET "$BASE_URL/staff/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff list
curl -X GET "$BASE_URL/staff" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff detail
curl -X GET "$BASE_URL/staff/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff analytics
curl -X GET "$BASE_URL/staff/1/analytics" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff lead chart
curl -X GET "$BASE_URL/staff/1/lead-chart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff followup chart
curl -X GET "$BASE_URL/staff/1/followup-chart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create staff
curl -X POST "$BASE_URL/staff" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"John","last_name":"Doe","email":"john@example.com"}'

# Update staff
curl -X PUT "$BASE_URL/staff/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"John","last_name":"Doe"}'

# Delete staff
curl -X DELETE "$BASE_URL/staff/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Restore staff
curl -X POST "$BASE_URL/staff/1/restore" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Force delete staff
curl -X DELETE "$BASE_URL/staff/1/force" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Staff V2

```bash
# Staff V2 form options
curl -X GET "$BASE_URL/staff-v2/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 analytics
curl -X GET "$BASE_URL/staff-v2/1/analytics" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 lead chart
curl -X GET "$BASE_URL/staff-v2/1/lead-chart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 followup chart
curl -X GET "$BASE_URL/staff-v2/1/followup-chart" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 list
curl -X GET "$BASE_URL/staff-v2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 detail
curl -X GET "$BASE_URL/staff-v2/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 create
curl -X POST "$BASE_URL/staff-v2" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"John","last_name":"Doe"}'

# Staff V2 update
curl -X PUT "$BASE_URL/staff-v2/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"John","last_name":"Doe"}'

# Staff V2 delete
curl -X DELETE "$BASE_URL/staff-v2/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 restore
curl -X POST "$BASE_URL/staff-v2/1/restore" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 force delete
curl -X DELETE "$BASE_URL/staff-v2/1/force" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 departments
curl -X GET "$BASE_URL/staff-v2/departments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff V2 teams
curl -X GET "$BASE_URL/staff-v2/teams" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff tasks
curl -X GET "$BASE_URL/staff/1/tasks" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Staff projects
curl -X GET "$BASE_URL/staff/1/projects" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Clients

```bash
# List clients
curl -X GET "$BASE_URL/clients" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Client detail
curl -X GET "$BASE_URL/clients/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create client
curl -X POST "$BASE_URL/clients" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"Jane","last_name":"Client","email":"jane@example.com"}'

# Update client
curl -X PUT "$BASE_URL/clients/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"first_name":"Jane","last_name":"Client"}'

# Delete client
curl -X DELETE "$BASE_URL/clients/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Vendors

```bash
# List vendors
curl -X GET "$BASE_URL/vendors" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Vendor detail
curl -X GET "$BASE_URL/vendors/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create vendor
curl -X POST "$BASE_URL/vendors" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Vendor Name","email":"vendor@example.com"}'

# Update vendor
curl -X PUT "$BASE_URL/vendors/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Vendor Name"}'

# Delete vendor
curl -X DELETE "$BASE_URL/vendors/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Vendor Renewals

```bash
# Vendor renewal form options
curl -X GET "$BASE_URL/vendor-renewals/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List vendor renewals
curl -X GET "$BASE_URL/vendor-renewals" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Vendor renewal detail
curl -X GET "$BASE_URL/vendor-renewals/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create vendor renewal
curl -X POST "$BASE_URL/vendor-renewals" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"vendor_id":1,"service_name":"Hosting","service_details":"Renewal service","plan_type":"half_year","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Update vendor renewal
curl -X PUT "$BASE_URL/vendor-renewals/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"vendor_id":1,"service_name":"Hosting","service_details":"Renewal service","plan_type":"half_year","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Delete vendor renewal
curl -X DELETE "$BASE_URL/vendor-renewals/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Client Renewals

```bash
# Client renewal form options
curl -X GET "$BASE_URL/client-renewals/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List client renewals
curl -X GET "$BASE_URL/client-renewals" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Client renewal detail
curl -X GET "$BASE_URL/client-renewals/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create client renewal
curl -X POST "$BASE_URL/client-renewals" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"client_id":1,"client_business_detail_id":1,"vendor_id":1,"service_name":"Website","service_details":"Renewal service","plan_type":"half_year","is_amc":1,"amc_total_visits":4,"amc_start_date":"2026-06-01","amc_end_date":"2027-05-31","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Update client renewal
curl -X PUT "$BASE_URL/client-renewals/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"client_id":1,"client_business_detail_id":1,"vendor_id":1,"service_name":"Website","service_details":"Renewal service","plan_type":"half_year","is_amc":1,"amc_total_visits":4,"amc_start_date":"2026-06-01","amc_end_date":"2027-05-31","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Update client renewal AMC visit
curl -X POST "$BASE_URL/client-renewals/1/amc-visits/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"completed","details":"Site visit completed and checked all service points."}'

# Delete client renewal
curl -X DELETE "$BASE_URL/client-renewals/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Todos

```bash
# Todo options
curl -X GET "$BASE_URL/todos/options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List todos
curl -X GET "$BASE_URL/todos" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Todo detail
curl -X GET "$BASE_URL/todos/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create todo
curl -X POST "$BASE_URL/todos" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Follow up","description":"Call the client"}'

# Update todo
curl -X PUT "$BASE_URL/todos/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Follow up","description":"Call the client"}'

# Delete todo
curl -X DELETE "$BASE_URL/todos/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Update todo status
curl -X PATCH "$BASE_URL/todos/1/status" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"completed"}'
```

## Leads

```bash
# Lead form options
curl -X GET "$BASE_URL/leads/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Lead dashboard
curl -X GET "$BASE_URL/leads/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List leads
curl -X GET "$BASE_URL/leads" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Lead detail
curl -X GET "$BASE_URL/leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create lead
curl -X POST "$BASE_URL/leads" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Lead Name","email":"lead@example.com"}'

# Update lead
curl -X PUT "$BASE_URL/leads/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Lead Name"}'

# Delete lead
curl -X DELETE "$BASE_URL/leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Lead Management

```bash
# List lead management items
curl -X GET "$BASE_URL/lead-management" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# View lead management item
curl -X GET "$BASE_URL/lead-management/source/1/view" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Assignment list
curl -X GET "$BASE_URL/lead-management/source/1/assignment" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Assign lead
curl -X POST "$BASE_URL/lead-management/source/1/assign" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"assignee_id":1}'

# Bulk assign
curl -X POST "$BASE_URL/lead-management/bulk-assign" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"ids":[1,2],"assignee_id":1}'

# Status history
curl -X GET "$BASE_URL/lead-management/source/1/status-history" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Update status
curl -X PATCH "$BASE_URL/lead-management/source/1/status" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"qualified"}'

# Add followup
curl -X POST "$BASE_URL/lead-management/source/1/followup" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"followup_date":"2026-06-17","note":"Call back"}'

# Followup history
curl -X GET "$BASE_URL/lead-management/source/1/followups" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List notes
curl -X GET "$BASE_URL/lead-management/source/1/note" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Add note
curl -X POST "$BASE_URL/lead-management/source/1/note" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"note":"Customer requested callback"}'

# List reminders
curl -X GET "$BASE_URL/lead-management/source/1/reminder" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Add reminder
curl -X POST "$BASE_URL/lead-management/source/1/reminder" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"reminder_at":"2026-06-18 10:00:00"}'

# Activity timeline
curl -X GET "$BASE_URL/lead-management/source/1/timeline" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Convert lead
curl -X POST "$BASE_URL/lead-management/source/1/convert" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"target":"client"}'

# Escalate lead
curl -X POST "$BASE_URL/lead-management/source/1/escalate" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"reason":"High priority"}'

# Performance stats
curl -X GET "$BASE_URL/lead-management/performance/stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete lead management item
curl -X DELETE "$BASE_URL/lead-management/source/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Meta Leads

```bash
# List meta leads
curl -X GET "$BASE_URL/meta-leads" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Meta lead detail
curl -X GET "$BASE_URL/meta-leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Sync meta leads
curl -X POST "$BASE_URL/meta-leads/sync" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete meta lead
curl -X DELETE "$BASE_URL/meta-leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Projects

```bash
# Project form options
curl -X GET "$BASE_URL/projects/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Project list
curl -X GET "$BASE_URL/projects" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create project
curl -X POST "$BASE_URL/projects" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"New Project","client_id":1}'

# Update project
curl -X PUT "$BASE_URL/projects/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Updated Project"}'

# Delete project
curl -X DELETE "$BASE_URL/projects/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Project detail
curl -X GET "$BASE_URL/projects/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Milestones list
curl -X GET "$BASE_URL/projects/1/milestones" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create milestone
curl -X POST "$BASE_URL/projects/1/milestones" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Milestone 1"}'

# Update milestone
curl -X PUT "$BASE_URL/projects/1/milestones/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Milestone 1"}'

# Delete milestone
curl -X DELETE "$BASE_URL/projects/1/milestones/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Issues list
curl -X GET "$BASE_URL/projects/1/issues" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create issue
curl -X POST "$BASE_URL/projects/1/issues" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Issue 1"}'

# Update issue
curl -X PUT "$BASE_URL/projects/1/issues/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Issue 1"}'

# Delete issue
curl -X DELETE "$BASE_URL/projects/1/issues/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Comments list
curl -X GET "$BASE_URL/projects/1/comments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create comment
curl -X POST "$BASE_URL/projects/1/comments" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"comment":"Looks good"}'

# Files list
curl -X GET "$BASE_URL/projects/1/files" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Upload file
curl -X POST "$BASE_URL/projects/1/files" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/path/to/file.pdf"

# Delete file
curl -X DELETE "$BASE_URL/projects/1/files/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Project details
curl -X GET "$BASE_URL/projects/1/details" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Project usage
curl -X GET "$BASE_URL/projects/1/usage" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Kanban board
curl -X GET "$BASE_URL/projects/1/kanban" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Kanban move
curl -X POST "$BASE_URL/projects/1/kanban/move" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"task_id":1,"column":"done"}'

# Charts
curl -X GET "$BASE_URL/projects/1/charts" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Activity feed
curl -X GET "$BASE_URL/projects/1/activity-feed" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Milestone progress
curl -X GET "$BASE_URL/projects/1/milestone-progress" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Task filter
curl -X GET "$BASE_URL/projects/1/tasks/filter" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Change requests list
curl -X GET "$BASE_URL/projects/1/change-requests" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create change request
curl -X POST "$BASE_URL/projects/1/change-requests" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Change Request"}'

# Update change request status
curl -X PATCH "$BASE_URL/projects/1/change-requests/1/status" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"approved"}'
```

## Tasks

```bash
# Task form options
curl -X GET "$BASE_URL/tasks/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Task list
curl -X GET "$BASE_URL/tasks" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Task detail
curl -X GET "$BASE_URL/tasks/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create task
curl -X POST "$BASE_URL/tasks" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Task 1"}'

# Update task
curl -X PUT "$BASE_URL/tasks/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Task 1"}'

# Delete task
curl -X DELETE "$BASE_URL/tasks/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Task comments
curl -X GET "$BASE_URL/tasks/1/comments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create task comment
curl -X POST "$BASE_URL/tasks/1/comments" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"comment":"Working on it"}'

# Update task comment
curl -X PUT "$BASE_URL/tasks/1/comments/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"comment":"Updated"}'

# Task attachments
curl -X GET "$BASE_URL/tasks/1/attachments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Upload task attachment
curl -X POST "$BASE_URL/tasks/1/attachments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/path/to/file.pdf"

# Delete task attachment
curl -X DELETE "$BASE_URL/tasks/1/attachments/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Task dependencies
curl -X GET "$BASE_URL/tasks/1/dependencies" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create task dependency
curl -X POST "$BASE_URL/tasks/1/dependencies" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"depends_on_task_id":2}'

# Delete task dependency
curl -X DELETE "$BASE_URL/tasks/1/dependencies/2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Checklists
curl -X GET "$BASE_URL/tasks/1/checklists" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create checklist item
curl -X POST "$BASE_URL/tasks/1/checklists" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Checklist item"}'

# Update checklist item
curl -X PUT "$BASE_URL/tasks/1/checklists/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Checklist item"}'

# Delete checklist item
curl -X DELETE "$BASE_URL/tasks/1/checklists/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Start time log
curl -X POST "$BASE_URL/tasks/1/time-logs/start" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Stop time log
curl -X POST "$BASE_URL/tasks/1/time-logs/stop" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Manual time log
curl -X POST "$BASE_URL/tasks/1/time-logs/manual" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"hours":1,"minutes":30}'

# Time log report
curl -X GET "$BASE_URL/tasks/1/time-logs/report" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# QA request review
curl -X POST "$BASE_URL/tasks/1/qa/request-review" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# QA review
curl -X POST "$BASE_URL/tasks/1/qa/review" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"notes":"Reviewed"}'

# QA approve
curl -X POST "$BASE_URL/tasks/1/qa/approve" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Mark deployed
curl -X POST "$BASE_URL/tasks/1/deploy" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Client Issues

```bash
# Client issue form options
curl -X GET "$BASE_URL/client-issues/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List client issues
curl -X GET "$BASE_URL/client-issues" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create client issue
curl -X POST "$BASE_URL/client-issues" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Issue title"}'

# Show client issue
curl -X GET "$BASE_URL/client-issues/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Update issue status
curl -X PATCH "$BASE_URL/client-issues/1/status" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"open"}'

# Delete client issue
curl -X DELETE "$BASE_URL/client-issues/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Assign team
curl -X POST "$BASE_URL/client-issues/1/assign" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"team_id":1}'

# Create task under issue
curl -X POST "$BASE_URL/client-issues/1/tasks" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Task under issue"}'

# Show task under issue
curl -X GET "$BASE_URL/client-issues/1/tasks/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Update task under issue
curl -X PUT "$BASE_URL/client-issues/1/tasks/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Updated task"}'

# Update task status under issue
curl -X PATCH "$BASE_URL/client-issues/1/tasks/1/status" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"done"}'

# Delete task under issue
curl -X DELETE "$BASE_URL/client-issues/1/tasks/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Services

```bash
# Service form options
curl -X GET "$BASE_URL/services/form-options" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# List services
curl -X GET "$BASE_URL/services" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Service detail
curl -X GET "$BASE_URL/services/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create service
curl -X POST "$BASE_URL/services" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"client_id":1,"client_business_detail_id":1,"vendor_id":1,"service_name":"Website","service_details":"Details","plan_type":"half_year","remark_text":"IMP","remark_color":"red","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Update service
curl -X PUT "$BASE_URL/services/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"client_id":1,"client_business_detail_id":1,"vendor_id":1,"service_name":"Website","service_details":"Details","plan_type":"half_year","remark_text":"IMP","remark_color":"red","start_date":"2026-06-01","end_date":"2026-11-30","billing_date":"2026-06-01","status":"active"}'

# Delete service
curl -X DELETE "$BASE_URL/services/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete selected services
curl -X POST "$BASE_URL/services/delete-selected" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"ids":[1,2,3]}'
```

## Calendar

```bash
# List events
curl -X GET "$BASE_URL/calendar/events" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Event detail
curl -X GET "$BASE_URL/calendar/events/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Create event
curl -X POST "$BASE_URL/calendar/events" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Event title"}'

# Update event
curl -X PUT "$BASE_URL/calendar/events/1" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Event title"}'

# Delete event
curl -X DELETE "$BASE_URL/calendar/events/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Settings

```bash
# Settings index
curl -X GET "$BASE_URL/settings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# General settings
curl -X GET "$BASE_URL/settings/general" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/general" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"app_name":"Technofra"}'

# Company settings
curl -X GET "$BASE_URL/settings/company" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/company" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"company_name":"Technofra"}'

# Email settings
curl -X GET "$BASE_URL/settings/email" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/email" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"mail_host":"smtp.example.com"}'

# Renewal settings
curl -X GET "$BASE_URL/settings/renewal" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/renewal" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"reminder_days":7}'

# Teams settings
curl -X GET "$BASE_URL/settings/teams" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/teams" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"teams":[]}'

# Departments settings
curl -X GET "$BASE_URL/settings/departments" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/departments" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"departments":[]}'

# Test email
curl -X POST "$BASE_URL/settings/test-email" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"email":"user@example.com"}'

# Search tags
curl -X GET "$BASE_URL/settings/search-tags" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# App logo
curl -X GET "$BASE_URL/settings/app-logo" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/app-logo" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "logo=@/path/to/logo.png"

# Login logo
curl -X GET "$BASE_URL/settings/login-logo" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
curl -X POST "$BASE_URL/settings/login-logo" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "logo=@/path/to/logo.png"
```

## Book A Call

```bash
# List booked calls
curl -X GET "$BASE_URL/book-a-call" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete booked call
curl -X DELETE "$BASE_URL/book-a-call/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Google Ads

```bash
# Digital marketing leads
curl -X GET "$BASE_URL/digital-marketing" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete digital marketing lead
curl -X DELETE "$BASE_URL/digital-marketing/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Web apps leads
curl -X GET "$BASE_URL/web-apps-leads" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete web apps lead
curl -X DELETE "$BASE_URL/web-apps-leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Web Enquiry

```bash
# Careers list
curl -X GET "$BASE_URL/web-enquiry/careers" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Career detail
curl -X GET "$BASE_URL/web-enquiry/careers/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Career resume URL
curl -X GET "$BASE_URL/web-enquiry/careers/1/resume-url" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete career enquiry
curl -X DELETE "$BASE_URL/web-enquiry/careers/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Contacts list
curl -X GET "$BASE_URL/web-enquiry/contacts" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Delete contact enquiry
curl -X DELETE "$BASE_URL/web-enquiry/contacts/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Google Ads Leads

```bash
# List Google Ads leads
curl -X GET "$BASE_URL/google-ads-leads" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Google Ads lead stats
curl -X GET "$BASE_URL/google-ads-leads/stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Show Google Ads lead detail
# Note: this uses the later /leads/{googleLead} route declared near the end of api.php.
curl -X GET "$BASE_URL/leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Fallback / Webhooks

```bash
# Authenticated user fallback
curl -X GET "http://127.0.0.1:8000/api/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Facebook webhook verification
curl -X GET "http://127.0.0.1:8000/api/facebook/webhook" \
  -H "Accept: application/json"

# Facebook webhook handler
curl -X POST "http://127.0.0.1:8000/api/facebook/webhook" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -d '{}'

# Google Ads lead webhook
curl -X POST "http://127.0.0.1:8000/api/google-ads/lead" \
  -H "Accept: application/json" \
  -H "$JSON_HEADER" \
  -d '{}'
```
