# Project API Curl Requests

Base URL: `http://localhost:8000/api/v1`

> All project endpoints require authentication with a valid Sanctum bearer token.

## Common Variables

Use these placeholders in the examples below:

- `YOUR_TOKEN` - your Bearer token
- `PROJECT_ID` - project id
- `MILESTONE_ID` - milestone id
- `ISSUE_ID` - issue id
- `FILE_ID` - project file id
- `CHANGE_REQUEST_ID` - change request id

## 1. Get Project Form Options

```bash
curl -X GET http://localhost:8000/api/v1/projects/form-options \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 2. List Projects

Filter with optional query params: `status`, `customer_id`, `priority`, `search`.

```bash
curl -X GET "http://localhost:8000/api/v1/projects?status=in_progress&search=crm" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 3. Create Project

```bash
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_name": "CRM Mobile App",
    "customer": 3,
    "status": "in_progress",
    "lifecycle_stage": "project_created",
    "start_date": "2026-04-01",
    "deadline": "2026-05-20",
    "billing_type": "fixed_rate",
    "total_rate": 85000,
    "estimated_hours": 180,
    "tags": ["mobile", "crm"],
    "members": [2, 5],
    "description": "Client CRM app with dashboard and task tracking",
    "priority": "high",
    "technologies": ["Laravel", "Flutter", "MySQL"]
  }'
```

If you need to upload files while creating the project, use `multipart/form-data` and add repeated `project_files[]` fields:

```bash
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "project_name=CRM Mobile App" \
  -F "customer=3" \
  -F "status=in_progress" \
  -F "priority=high" \
  -F "members[]=2" \
  -F "members[]=5" \
  -F "technologies[]=Laravel" \
  -F "technologies[]=Flutter" \
  -F "project_files[]=@C:/path/to/spec.pdf"
```

## 4. Get Project Detail

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 5. Get Project Detail Dashboard

This endpoint returns the richer payload used by the project details page, including:

- project header data
- member metrics
- task, milestone, and issue summaries
- usage charts and weekly activity
- progress cards
- recent activity
- time tracking and deployment summaries
- workload and velocity charts

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/details \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 6. Update Project

`PUT` and `PATCH` are both accepted.

```bash
curl -X PUT http://localhost:8000/api/v1/projects/PROJECT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "project_name": "CRM Mobile App v2",
    "customer": 3,
    "status": "in_progress",
    "lifecycle_stage": "development",
    "start_date": "2026-04-01",
    "deadline": "2026-06-10",
    "billing_type": "hourly_rate",
    "total_rate": 90000,
    "estimated_hours": 220,
    "tags": ["mobile", "crm", "v2"],
    "members": [2, 5],
    "description": "Updated scope with reporting and notifications",
    "priority": "high",
    "technologies": ["Laravel", "Flutter", "MySQL", "Redis"]
  }'
```

To update and upload files in the same request, switch to multipart form data:

```bash
curl -X PATCH http://localhost:8000/api/v1/projects/PROJECT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "project_name=CRM Mobile App v2" \
  -F "customer=3" \
  -F "status=in_progress" \
  -F "members[]=2" \
  -F "members[]=5" \
  -F "project_files[]=@C:/path/to/updated-spec.pdf"
```

## 7. Delete Project

```bash
curl -X DELETE http://localhost:8000/api/v1/projects/PROJECT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 8. List Milestones

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/milestones \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 9. Create Milestone

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/milestones \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Design Phase",
    "description": "Finalize UI and UX screens",
    "status": "pending",
    "due_date": "2026-04-20"
  }'
```

## 10. Update Milestone

`PUT` and `PATCH` are both accepted.

```bash
curl -X PATCH http://localhost:8000/api/v1/projects/PROJECT_ID/milestones/MILESTONE_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Design Phase",
    "description": "Finalize UI, UX, and design handoff",
    "status": "in_progress",
    "due_date": "2026-04-24"
  }'
```

## 11. Delete Milestone

```bash
curl -X DELETE http://localhost:8000/api/v1/projects/PROJECT_ID/milestones/MILESTONE_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 12. List Issues

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/issues \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 13. Create Issue

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/issues \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "issue_description": "Login button overlaps on small screens",
    "priority": "high",
    "status": "open"
  }'
```

## 14. Update Issue

`PUT` and `PATCH` are both accepted.

```bash
curl -X PUT http://localhost:8000/api/v1/projects/PROJECT_ID/issues/ISSUE_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "issue_description": "Login button overlaps on small screens",
    "priority": "medium",
    "status": "in_progress"
  }'
```

## 15. Delete Issue

```bash
curl -X DELETE http://localhost:8000/api/v1/projects/PROJECT_ID/issues/ISSUE_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 16. List Comments

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 17. Create Comment

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Client asked to move the release date by one week."
  }'
```

## 18. List Files

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/files \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 19. Upload File

Use `multipart/form-data` and send the file under the `file` field.

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/files \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@C:/path/to/project-brief.pdf"
```

## 20. Delete File

```bash
curl -X DELETE http://localhost:8000/api/v1/projects/PROJECT_ID/files/FILE_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 21. Project Usage

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/usage \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 22. Kanban Board

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/kanban \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 23. Move Task on Kanban

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/kanban/move \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "task_id": 25,
    "to_column": "in_progress"
  }'
```

Allowed `to_column` values:

- `backlog`
- `todo`
- `in_progress`
- `review`
- `done`
- `not_started`
- `pending`
- `completed`

## 24. Project Charts

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/charts \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 25. Activity Feed

Use `per_page` to change page size. The controller clamps it between 5 and 50.

```bash
curl -X GET "http://localhost:8000/api/v1/projects/PROJECT_ID/activity-feed?per_page=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 26. Milestone Progress

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/milestone-progress \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 27. Filter Tasks

Query params supported: `status`, `priority`, `q`, `limit`.

```bash
curl -X GET "http://localhost:8000/api/v1/projects/PROJECT_ID/tasks/filter?status=in_progress&priority=high&q=login&limit=25" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 28. List Change Requests

```bash
curl -X GET http://localhost:8000/api/v1/projects/PROJECT_ID/change-requests \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 29. Create Change Request

```bash
curl -X POST http://localhost:8000/api/v1/projects/PROJECT_ID/change-requests \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Add reporting module",
    "description": "Client wants monthly and yearly reporting screens",
    "impact_level": "high"
  }'
```

## 30. Update Change Request Status

`PUT` and `PATCH` are both accepted.

```bash
curl -X PATCH http://localhost:8000/api/v1/projects/PROJECT_ID/change-requests/CHANGE_REQUEST_ID/status \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "approved",
    "description": "Reviewed by engineering and approved for implementation",
    "impact_level": "medium"
  }'
```

Allowed `status` values:

- `requested`
- `analysis`
- `approved`
- `rejected`
- `implemented`

Allowed `impact_level` values:

- `low`
- `medium`
- `high`
- `critical`

## Notes

- `customer` in project create/update requests is the customer user id.
- `members[]` should contain staff ids.
- `project_files[]` is only used on project create/update requests.
- File upload endpoints use `multipart/form-data`.
- All `PUT` routes also accept `PATCH`.
