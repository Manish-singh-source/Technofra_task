# Tasks API Curl Requests

Base URL: `http://localhost:8000/api/v1`

> All task endpoints require authentication with a valid Sanctum bearer token.

## Common Variables

Use these placeholders in the examples below:

- `YOUR_TOKEN` - your Bearer token
- `TASK_ID` - task id
- `COMMENT_ID` - task comment id
- `ATTACHMENT_ID` - task attachment id
- `DEPENDS_ON_TASK_ID` - the task id this task depends on
- `CHECKLIST_ID` - checklist item id

## 1. Get Task Form Options

Optional query param: `project_id`

```bash
curl -X GET "http://localhost:8000/api/v1/tasks/form-options?project_id=12" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 2. List Tasks

Supported filters:

- `status`
- `priority`
- `project_id`
- `assignee_id`
- `search`
- `per_page`

`status` also accepts aliases like `all`, `running`, `hold`, and `delayed`.

```bash
curl -X GET "http://localhost:8000/api/v1/tasks?status=running&priority=high&project_id=3&assignee_id=8&search=payment&per_page=25" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 3. Get Task Detail

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 4. Create Task

Use `multipart/form-data` if you want to upload attachments at the same time.

Request fields:

- `task_title` required
- `project_related` optional project id
- `milestone_id` optional milestone id
- `priority` optional, `High|Medium|Low|high|medium|low`
- `status` optional, `not_started|in_progress|on_hold|completed|cancelled`
- `workflow_status` optional, one of `backlog|todo|in_progress|blocked|review|testing|deployed|completed|archived`
- `start_date` optional
- `due_date` optional
- `assignees[]` optional staff ids
- `followers[]` optional staff ids
- `tags[]` optional strings
- `task_description` optional
- `attach_files[]` optional files, max 10MB each

```bash
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "task_title=Prepare Q3 launch checklist" \
  -F "project_related=3" \
  -F "milestone_id=7" \
  -F "priority=High" \
  -F "status=in_progress" \
  -F "workflow_status=todo" \
  -F "start_date=2026-05-20" \
  -F "due_date=2026-05-30" \
  -F "assignees[]=8" \
  -F "assignees[]=9" \
  -F "followers[]=10" \
  -F "tags[]=launch" \
  -F "tags[]=urgent" \
  -F "task_description=Coordinate dev, QA, and ops before release." \
  -F "attach_files[]=@C:/path/to/spec.pdf" \
  -F "attach_files[]=@C:/path/to/checklist.xlsx"
```

## 5. Update Task

`PUT` and `PATCH` are both accepted.

```bash
curl -X PUT http://localhost:8000/api/v1/tasks/TASK_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "task_title=Prepare Q3 launch checklist (Updated)" \
  -F "project_related=3" \
  -F "priority=medium" \
  -F "status=on_hold" \
  -F "workflow_status=in_progress" \
  -F "start_date=2026-05-20" \
  -F "due_date=2026-06-02" \
  -F "assignees[]=8" \
  -F "followers[]=10" \
  -F "tags[]=launch" \
  -F "tags[]=blocked" \
  -F "task_description=Blocked pending final client assets." \
  -F "attach_files[]=@C:/path/to/new-attachment.png"
```

## 6. Delete Task

```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/TASK_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 7. List Task Comments

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 8. Create Task Comment

Request fields:

- `comment` required
- `parent_id` optional parent comment id
- `attachments[]` optional files, max 10MB each

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "comment=Please review the latest checklist update." \
  -F "parent_id=44" \
  -F "attachments[]=@C:/path/to/screenshot.png" \
  -F "attachments[]=@C:/path/to/log.txt"
```

## 9. Update Task Comment

`PUT` and `PATCH` are both accepted.

```bash
curl -X PATCH http://localhost:8000/api/v1/tasks/TASK_ID/comments/COMMENT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "comment": "Updated comment text after review."
  }'
```

## 10. List Task Attachments

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID/attachments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 11. Upload Task Attachments

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/attachments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "attach_files[]=@C:/path/to/design.png" \
  -F "attach_files[]=@C:/path/to/notes.docx"
```

## 12. Delete Task Attachment

```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/TASK_ID/attachments/ATTACHMENT_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 13. List Dependencies

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID/dependencies \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 14. Create Dependency

Allowed dependency types:

- `blocks`
- `depends_on`
- `related_to`

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/dependencies \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "depends_on_task_id": 42,
    "dependency_type": "blocks"
  }'
```

## 15. Delete Dependency

`dependency_type` is optional here, but you can include it to delete a specific relation.

```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/TASK_ID/dependencies/DEPENDS_ON_TASK_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "dependency_type=blocks"
```

## 16. List Checklists

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID/checklists \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 17. Create Checklist Item

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/checklists \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Confirm client assets",
    "parent_id": null,
    "sort_order": 1
  }'
```

## 18. Update Checklist Item

`PUT` and `PATCH` are both accepted.

```bash
curl -X PATCH http://localhost:8000/api/v1/tasks/TASK_ID/checklists/CHECKLIST_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Confirm client assets",
    "is_completed": true,
    "sort_order": 1
  }'
```

## 19. Delete Checklist Item

```bash
curl -X DELETE http://localhost:8000/api/v1/tasks/TASK_ID/checklists/CHECKLIST_ID \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 20. Start Time Log

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/time-logs/start \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": "Started implementation work."
  }'
```

## 21. Stop Time Log

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/time-logs/stop \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 22. Add Manual Time Log

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/time-logs/manual \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "duration_minutes": 90,
    "note": "Manual entry for design review",
    "started_at": "2026-06-01 10:00:00",
    "ended_at": "2026-06-01 11:30:00"
  }'
```

## 23. Time Log Report

```bash
curl -X GET http://localhost:8000/api/v1/tasks/TASK_ID/time-logs/report \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 24. Request Review

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/qa/request-review \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": "Feature is ready for QA review."
  }'
```

## 25. QA Review Decision

Allowed decisions:

- `approved`
- `changes_requested`

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/qa/review \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "decision": "approved",
    "note": "Looks good. Move to testing."
  }'
```

## 26. QA Approve

Allowed decisions:

- `passed`
- `failed`

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/qa/approve \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "decision": "passed",
    "note": "QA checks passed."
  }'
```

## 27. Mark Deployed

```bash
curl -X POST http://localhost:8000/api/v1/tasks/TASK_ID/deploy \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": "Deployed to production."
  }'
```

## Notes

- `workflow_status` values are: `backlog`, `todo`, `in_progress`, `blocked`, `review`, `testing`, `deployed`, `completed`, `archived`.
- `status` values are: `not_started`, `in_progress`, `on_hold`, `completed`, `cancelled`.
- `priority` is stored in lowercase even if you send `High`, `Medium`, or `Low`.
- Task create/update use `attach_files[]` for file uploads.
- Comment create uses `attachments[]` for file uploads.
- `dependency_type` values are: `blocks`, `depends_on`, `related_to`.
- `PUT` endpoints also accept `PATCH` where defined.
