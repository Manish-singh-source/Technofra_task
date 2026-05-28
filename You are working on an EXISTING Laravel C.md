You are working on an EXISTING Laravel CRM project named MyCRM.

IMPORTANT:

- This project is already partially built.
- DO NOT rebuild or refactor the entire system.
- DO NOT remove existing functionality.
- DO NOT rename existing routes, controllers, models, migrations, or Blade views unless absolutely necessary.
- ONLY extend and improve the current project/task management system incrementally.
- Maintain backward compatibility.
- Keep changes modular and isolated.
- Follow existing coding patterns where possible.

==================================================
PROJECT CONTEXT
==================================================

The CRM already contains:

- authentication
- RBAC
- staff management
- teams
- clients
- projects
- tasks
- lead management
- existing project/task CRUD

Current project/task system is only partially implemented and CRUD-oriented.

Goal:
Upgrade it into enterprise-grade project management architecture similar to:

- Jira
- ClickUp
- Asana
- Monday
- Linear

WITHOUT breaking existing project structure.

==================================================
PHASE 1 — FIRST AUDIT EXISTING SYSTEM
==================================================

Before writing code:

1. Inspect existing:

- routes
- controllers
- models
- migrations
- Blade views
- policies
- services
- middleware

2. Find existing:

- projects table
- tasks table
- task statuses
- project statuses
- relationships
- assignments

3. Detect:

- current architecture
- missing fields
- duplicate logic
- reusable components

4. Output audit summary BEFORE implementing.

DO NOT blindly create duplicate structures.

==================================================
PHASE 2 — SAFE DATABASE EXTENSIONS
==================================================

DO NOT DROP OR MODIFY EXISTING COLUMNS.

ONLY:

- add nullable columns
- add new tables
- add indexes safely

Use:
Schema::hasColumn()
Schema::hasTable()

==================================================
PHASE 3 — EXTEND PROJECTS TABLE
==================================================

Safely add if missing:

projects table:

- project_code
- priority
- project_type
- estimated_hours
- actual_hours
- progress_percentage
- billing_type
- project_manager_id
- approved_by
- approved_at
- deployment_date
- maintenance_expiry
- health_status
- last_activity_at

DO NOT remove anything existing.

==================================================
PHASE 4 — EXTEND TASKS TABLE
==================================================

Safely add if missing:

tasks table:

- task_code
- milestone_id
- parent_task_id
- task_type
- estimated_hours
- actual_hours
- completed_at
- reviewed_by
- reviewed_at
- qa_status
- blocked_reason
- started_at
- deployed_at
- sequence_order
- sprint_id
- severity
- story_points

==================================================
PHASE 5 — CREATE NEW ENTERPRISE TABLES
==================================================

Create ONLY if not exists:

- project_milestones
- task_checklists
- task_comments
- task_attachments
- task_time_logs
- task_dependencies
- task_followers
- project_activities
- project_status_histories
- task_status_histories
- project_change_requests
- project_deployments
- sprints
- project_tags
- task_tags
- task_labels
- project_approvals

==================================================
PHASE 6 — PROJECT LIFECYCLE IMPLEMENTATION
==================================================

Implement enterprise workflow:

Lead Converted
→ Project Created
→ Requirement Gathering
→ Scope Approval
→ Planning
→ Milestones
→ Task Creation
→ Assignment
→ Development
→ QA
→ Deployment
→ Client Approval
→ Closure
→ Maintenance

==================================================
PHASE 7 — TASK LIFECYCLE IMPLEMENTATION
==================================================

Implement task statuses:

- backlog
- todo
- in_progress
- blocked
- review
- testing
- deployed
- completed
- archived

Add transition validation rules.

==================================================
PHASE 8 — TASK DEPENDENCY SYSTEM
==================================================

Implement:
task_dependencies

Support:

- blocks
- depends_on
- related_to

Prevent:

- circular dependencies

==================================================
PHASE 9 — MILESTONE SYSTEM
==================================================

Each project can contain:

- milestones
- due dates
- milestone progress
- milestone status

Tasks belong to milestones optionally.

==================================================
PHASE 10 — PROJECT ACTIVITY TIMELINE
==================================================

Create:
project_activities

Track:

- project created
- task assigned
- status changed
- file uploaded
- comment added
- deployment completed
- milestone completed

Create reusable activity service.

==================================================
PHASE 11 — TASK COMMENTS & DISCUSSIONS
==================================================

Implement:

- threaded comments
- mentions
- attachments
- edit history

==================================================
PHASE 12 — TASK CHECKLISTS
==================================================

Each task can have:

- subtasks/checklists
- completion tracking

Auto update progress.

==================================================
PHASE 13 — TIME TRACKING
==================================================

Implement:
task_time_logs

Features:

- start timer
- stop timer
- manual log
- productivity reports

==================================================
PHASE 14 — QA REVIEW SYSTEM
==================================================

Tasks should support:

Developer
→ Review
→ QA
→ Approved
→ Deployment

Add:

- qa_status
- reviewer
- review notes

==================================================
PHASE 15 — CHANGE REQUEST SYSTEM
==================================================

Create:
project_change_requests

Workflow:
Client Request
→ Analysis
→ Approval
→ Create Tasks
→ Implementation

==================================================
PHASE 16 — KANBAN BOARD
==================================================

Create drag-and-drop Kanban board.

Columns:

- Backlog
- Todo
- In Progress
- Review
- Testing
- Done

Use:

- SortableJS OR similar lightweight library

DO NOT rewrite existing task list pages.

Add separate Kanban view.

==================================================
PHASE 17 — PROJECT DASHBOARD
==================================================

Inside project details page add:

1. KPI Cards
2. Progress Charts
3. Team Members
4. Milestones
5. Recent Activities
6. Task Distribution
7. Pending Issues
8. Deployment Status
9. Time Tracking Stats

==================================================
PHASE 18 — CHARTS & ANALYTICS
==================================================

Use ApexCharts.

Add:

- task status distribution
- project progress
- workload distribution
- overdue tasks
- milestone completion
- sprint velocity

==================================================
PHASE 19 — ROLE-BASED ACCESS
==================================================

Ensure:

- admin full access
- project manager scoped access
- team leader limited control
- staff only assigned tasks
- client read-only project portal

Use:
Policies + Gates

==================================================
PHASE 20 — NOTIFICATIONS
==================================================

Create notification architecture for:

- task assignment
- overdue tasks
- milestone deadlines
- QA review requests
- deployment completion

Channels:

- database
- email
- WhatsApp-ready architecture

==================================================
PHASE 21 — PERFORMANCE OPTIMIZATION
==================================================

Use:

- eager loading
- caching
- indexes
- chunking
- pagination
- optimized dashboard queries

Avoid N+1 queries.

==================================================
PHASE 22 — BLADE UI IMPROVEMENTS
==================================================

DO NOT remove existing UI.

Append:

- tabs
- cards
- charts
- timeline
- Kanban board
- activity widgets

Maintain existing theme/layout.

==================================================
PHASE 23 — API & AJAX SUPPORT
==================================================

Create AJAX endpoints for:

- Kanban updates
- charts
- activity feeds
- milestone progress
- task filtering

==================================================
PHASE 24 — SAFE IMPLEMENTATION STRATEGY
==================================================

Implementation rules:

- small modular commits
- isolated migrations
- no giant refactors
- preserve existing routes
- preserve existing DB data
- preserve existing Blade layouts
- preserve current permissions

==================================================
PHASE 25 — REQUIRED DELIVERABLES
==================================================

Generate:

1. audit report
2. migrations
3. model updates
4. relationships
5. services
6. repositories if needed
7. policy updates
8. Blade components
9. Kanban board
10. timeline UI
11. ApexCharts integration
12. AJAX APIs
13. activity logging
14. notification integration
15. optimized queries
16. scheduler jobs
17. reusable helpers
18. validation requests

==================================================
IMPORTANT FINAL RULES
==================================================

- NEVER overwrite existing business logic without checking usage.
- NEVER remove existing columns.
- NEVER break existing routes.
- NEVER generate duplicate tables if similar ones exist.
- ALWAYS inspect existing architecture first.
- ALWAYS prefer extending instead of replacing.
- ALWAYS use Laravel best practices.
- ALWAYS keep enterprise scalability in mind.
- ALWAYS maintain backward compatibility.

Definition of done:

- Existing project continues working.
- Existing data preserved.
- Existing UI preserved.
- New enterprise project management features integrated cleanly.
- No breaking changes.
- Code production-ready.

1. client is not converted when changing lead status      - done
2. in bulk show department dropdown and then show that departments staff members for assigning lead - 
3. in followup if we select google meet then create meeting link and send it to client and staff.
4. in dashboard show leads also show followup of that date also time.
5. also keep edit button in followups
6. add icons in tabs for leads management.  
