# Client Issue Module API (v1)

These APIs are protected with Sanctum authentication.

- Base URL: `http://127.0.0.1:8000/api/v1`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Common header: `Accept: application/json`
- File upload endpoints: use `multipart/form-data`

## 1. Get Client Issue Form Options

- Method: `GET`
- URL: `/client-issues/form-options`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/form-options' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

Returns:
- `projects`, `customers`
- `teams`, `team_options`
- `issue_priorities`: `low`, `medium`, `high`, `critical`
- `issue_statuses`: `open`, `in_progress`, `resolved`, `closed`
- `task_priorities`: `low`, `medium`, `high`, `critical`
- `task_statuses`: `todo`, `in_progress`, `review`, `done`

## 2. Get Client Issues List (All Filters + Pagination)

- Method: `GET`
- URL: `/client-issues`
- Filters/params:
  - `status` (optional): `open|in_progress|resolved|closed`
  - `search` (optional): matches `issue_description`, `project_name`, customer `first_name|last_name|email`
  - `per_page` (optional): default `10`, min `1`, max `100`
  - `page` (optional): pagination page number

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues?status=in_progress&search=mobile&per_page=25&page=1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

Response includes:
- `data.issues`
- `data.meta.pagination` (`current_page`, `last_page`, `per_page`, `total`, `from`, `to`, `has_more_pages`)

## 3. Create Client Issue

- Method: `POST`
- URL: `/client-issues`
- Content-Type: `application/json`

Required:
- `project_id` (must exist)
- `customer_id` (must exist and user role must be `client`)
- `issue_description` (string)

Optional:
- `priority` (`low|medium|high|critical`, default `medium`)
- `status` (`open|in_progress|resolved|closed`, default `open`)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "project_id": 1,
    "customer_id": 1,
    "issue_description": "Production website is not loading on mobile devices.",
    "priority": "high",
    "status": "open"
  }'
```

## 4. Get Client Issue Detail

- Method: `GET`
- URL: `/client-issues/{id}`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 5. Assign Team To Client Issue

- Method: `POST`
- URL: `/client-issues/{clientIssue}/assign`
- Content-Type: `application/json`

Required:
- `team_name` (must be one of `Team::getTeamOptions()`)

Optional:
- `note` (string)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/assign' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "team_name": "Development",
    "note": "Handle this with high priority."
  }'
```

## 6. Update Client Issue Status

- Method: `PATCH`
- URL: `/client-issues/{id}/status`
- Content-Type: `application/json`

Required:
- `status` (`open|in_progress|resolved|closed`)

```bash
curl --location --request PATCH 'http://127.0.0.1:8000/api/v1/client-issues/1/status' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "status": "resolved"
  }'
```

## 7. Delete Client Issue By ID

- Method: `DELETE`
- URL: `/client-issues/{id}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/client-issues/1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 8. Create Client Issue Task

- Method: `POST`
- URL: `/client-issues/{clientIssue}/tasks`
- Content-Type: `multipart/form-data`

Required:
- `title` (string, max 255)

Optional:
- `description` (string)
- `status` (`todo|in_progress|review|done`, default `todo`)
- `priority` (`low|medium|high|critical`, default `medium`)
- `assigned_to` (string, max 255)
- `start_date` (date)
- `due_date` (date)
- `due_time` (nullable; pass time string)
- `reminder_date` (date)
- `reminder_time` (nullable; pass time string)
- `checklist_data` (JSON array string or array)
- `labels_data` (JSON array string or array)
- `attachments[]` (file array, each max 10MB)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --form 'title="Investigate mobile rendering"' \
  --form 'description="Check CSS breakpoints and console errors"' \
  --form 'status="todo"' \
  --form 'priority="high"' \
  --form 'assigned_to="12"' \
  --form 'start_date="2026-05-20"' \
  --form 'due_date="2026-05-25"' \
  --form 'due_time="18:30:00"' \
  --form 'reminder_date="2026-05-24"' \
  --form 'reminder_time="10:00:00"' \
  --form 'checklist_data=["Audit CSS","Check logs","Test on devices"]' \
  --form 'labels_data=["mobile","urgent"]' \
  --form 'attachments[]=@"/path/to/screenshot.png"' \
  --form 'attachments[]=@"/path/to/console-log.txt"'
```

## 9. Get Client Issue Task Detail

- Method: `GET`
- URL: `/client-issues/{clientIssue}/tasks/{task}`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 10. Update Client Issue Task

- Method: `PUT` or `PATCH`
- URL: `/client-issues/{clientIssue}/tasks/{task}`
- Content-Type: `multipart/form-data` (recommended when using files)

Validation fields are same as Create Task.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --form 'title="Investigate mobile rendering and caching"' \
  --form 'description="Check CSS breakpoints, cache headers, and console errors"' \
  --form 'status="in_progress"' \
  --form 'priority="critical"' \
  --form 'assigned_to="12"' \
  --form 'start_date="2026-05-20"' \
  --form 'due_date="2026-05-28"' \
  --form 'due_time="20:00:00"' \
  --form 'reminder_date="2026-05-27"' \
  --form 'reminder_time="09:30:00"' \
  --form 'checklist_data=["Re-test UI","Validate cache","Share report"]' \
  --form 'labels_data=["mobile","critical"]' \
  --form 'attachments[]=@"/path/to/new-screenshot.png"'
```

## 11. Update Client Issue Task Status

- Method: `PATCH`
- URL: `/client-issues/{clientIssue}/tasks/{task}/status`
- Content-Type: `application/json`

Required:
- `status` (`todo|in_progress|review|done`)

```bash
curl --location --request PATCH 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1/status' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "status": "done"
  }'
```

## 12. Delete Client Issue Task

- Method: `DELETE`
- URL: `/client-issues/{clientIssue}/tasks/{task}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1' \
  --header 'Accept: application/json' \
  --header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## Important Route Note

- `deleteSelected(Request $request)` exists in the controller, but `/client-issues/delete-selected` is currently **not registered** in `routes/api.php`.
- So do not use bulk-delete cURL unless route is added.

## Response Format

Success:

```json
{
  "success": true,
  "message": "Client issues retrieved successfully.",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "project_id": [
      "The project id field is required."
    ]
  }
}
```

## HTTP Status Codes

- `200` for successful fetch, update, delete requests
- `201` for successful create requests
- `401` for unauthenticated requests
- `403` for permission or ownership failures
- `404` when the requested issue or task does not exist
- `422` for validation failures
- `500` for unexpected server errors
