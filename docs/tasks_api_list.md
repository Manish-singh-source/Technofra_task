# Tasks API List (v1)

Base URL: `http://127.0.0.1:8000/api/v1`  
Auth: `Authorization: Bearer <TOKEN>` (Sanctum)  
All task routes are inside `/tasks`.

## 1) Task Form Options

Endpoint: `GET /tasks/form-options`  
Optional query params:
- `project_id` (optional, integer; returned back as `selected_project_id`)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/form-options?project_id=12' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

Returns:
- `projects`
- `staff`
- `statuses`: `not_started`, `in_progress`, `on_hold`, `completed`, `cancelled`
- `priorities`: `low`, `medium`, `high`

## 2) List Tasks (All Filters)

Endpoint: `GET /tasks`  
Supported filters/query params:
- `status` (optional): `not_started`, `in_progress`, `on_hold`, `completed`, `cancelled`
- `status` aliases also accepted: `all` (no status filter), `running` -> `in_progress`, `hold` -> `on_hold`, `delayed` -> `on_hold`
- `priority` (optional): generally `low`, `medium`, `high` (compared in lowercase)
- `project_id` (optional, integer)
- `assignee_id` (optional, integer/string id searched in JSON `assignees`)
- `search` (optional, searches in `title` + `description`)
- `per_page` (optional, default `10`, min `1`, max `100`)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks?status=running&priority=high&project_id=3&assignee_id=8&search=payment&per_page=25' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## 3) Get Task Detail

Endpoint: `GET /tasks/{id}`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/15' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## 4) Create Task (Multipart/Form-Data with All Params)

Endpoint: `POST /tasks`  
Content type: `multipart/form-data`

Required:
- `task_title` (string, max 255)

Optional:
- `project_related` (project id, must exist)
- `priority` (`High|Medium|Low|high|medium|low`; stored as lowercase)
- `status` (`not_started|in_progress|on_hold|completed|cancelled`)
- `start_date` (date)
- `due_date` (date, must be `>= start_date`)
- `assignees[]` (array of staff ids)
- `followers[]` (array of staff ids)
- `tags[]` (array of strings)
- `task_description` (string)
- `attach_files[]` (array of files, each max 10MB)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>' \
  --form 'task_title="Prepare Q3 launch checklist"' \
  --form 'project_related="3"' \
  --form 'priority="High"' \
  --form 'status="in_progress"' \
  --form 'start_date="2026-05-20"' \
  --form 'due_date="2026-05-30"' \
  --form 'assignees[]="8"' \
  --form 'assignees[]="9"' \
  --form 'followers[]="10"' \
  --form 'followers[]="11"' \
  --form 'tags[]="launch"' \
  --form 'tags[]="urgent"' \
  --form 'task_description="Coordinate dev, QA, and ops before release."' \
  --form 'attach_files[]=@"/path/to/spec.pdf"' \
  --form 'attach_files[]=@"/path/to/checklist.xlsx"'
```

## 5) Update Task (PUT/PATCH; Same Params as Create)

Endpoint: `PUT /tasks/{id}` or `PATCH /tasks/{id}`  
Validation is the same as create in current implementation.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/tasks/15' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>' \
  --form 'task_title="Prepare Q3 launch checklist (Updated)"' \
  --form 'project_related="3"' \
  --form 'priority="medium"' \
  --form 'status="on_hold"' \
  --form 'start_date="2026-05-20"' \
  --form 'due_date="2026-06-02"' \
  --form 'assignees[]="8"' \
  --form 'followers[]="10"' \
  --form 'tags[]="launch"' \
  --form 'tags[]="blocked"' \
  --form 'task_description="Blocked pending final client assets."' \
  --form 'attach_files[]=@"/path/to/new-attachment.png"'
```

## 6) Delete Task

Endpoint: `DELETE /tasks/{id}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/tasks/15' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## 7) List Task Comments

Endpoint: `GET /tasks/{taskId}/comments`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/15/comments' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## 8) Add Task Comment

Endpoint: `POST /tasks/{taskId}/comments`  
Body field:
- `comment` (required, string, max 1000)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/15/comments' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>' \
  --header 'Content-Type: application/json' \
  --data '{
    "comment": "Please review the latest checklist update."
  }'
```

## 9) List Task Attachments

Endpoint: `GET /tasks/{taskId}/attachments`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/15/attachments' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## 10) Upload Task Attachments

Endpoint: `POST /tasks/{taskId}/attachments`  
Required:
- `attach_files[]` (array, min 1 file, each max 10MB)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/15/attachments' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>' \
  --form 'attach_files[]=@"/path/to/design.png"' \
  --form 'attach_files[]=@"/path/to/notes.docx"'
```

## 11) Delete One Attachment

Endpoint: `DELETE /tasks/{taskId}/attachments/{attachmentId}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/tasks/15/attachments/22' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer <TOKEN>'
```

## Notes / Behavior Details

- Task list response includes:
  - `meta.counts`: `all`, `not_started`, `in_progress`, `on_hold`, `completed`, `cancelled`, `late`
  - `meta.pagination`
  - `meta.restricted_to_assigned_tasks`
- `late` count means deadline is before today and status is not `completed`, `cancelled`, or `on_hold`.
- `priority` is normalized to lowercase when saved.
- `due_date` is saved as `deadline`.
- Access control can restrict non-admin users to tasks where they are assignee/follower.
