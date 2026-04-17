# Client Issue Module API

These APIs are available under the protected Sanctum API prefix:

- Base URL: `/api/v1`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Content type: `Accept: application/json`
- Content type for file upload endpoints: `multipart/form-data`

## 1. Get Client Issue Form Options

- Method: `GET`
- URL: `/api/v1/client-issues/form-options`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/form-options' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 2. Get Client Issues List

- Method: `GET`
- URL: `/api/v1/client-issues`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 3. Create Client Issue

- Method: `POST`
- URL: `/api/v1/client-issues`

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
- URL: `/api/v1/client-issues/{id}`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 5. Assign Team To Client Issue

- Method: `POST`
- URL: `/api/v1/client-issues/{clientIssue}/assign`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/assign' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "team_name": "Development"
}'
```

## 6. Update Client Issue Status

- Method: `PATCH`
- URL: `/api/v1/client-issues/{id}/status`

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
- URL: `/api/v1/client-issues/{id}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/client-issues/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 8. Delete Selected Client Issues

- Method: `POST`
- URL: `/api/v1/client-issues/delete-selected`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/delete-selected' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "ids": [1, 2, 3]
}'
```

## 9. Create Client Issue Task

- Method: `POST`
- URL: `/api/v1/client-issues/{clientIssue}/tasks`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--form 'title="Investigate mobile rendering"' \
--form 'description="Check CSS breakpoints and console errors"' \
--form 'status="todo"' \
--form 'priority="high"' \
--form 'assigned_to="12"' \
--form 'start_date="2026-04-17"' \
--form 'due_date="2026-04-19"' \
--form 'attachments[]=@C:/path/to/screenshot.png'
```

## 10. Get Client Issue Task Detail

- Method: `GET`
- URL: `/api/v1/client-issues/{clientIssue}/tasks/{task}`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 11. Update Client Issue Task

- Method: `PUT`
- URL: `/api/v1/client-issues/{clientIssue}/tasks/{task}`

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/client-issues/4/tasks/2' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data-raw '{
  "title":"Investigate mobile rendering and caching",
  "description":"Check CSS breakpoints, cache headers, and console errors",
  "status":"in_progress",
  "priority":"critical",
  "assigned_to":"12"
}'
```

## 12. Update Client Issue Task Status

- Method: `PATCH`
- URL: `/api/v1/client-issues/{clientIssue}/tasks/{task}/status`

```bash
curl --location --request PATCH 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1/status' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "status": "done"
}'
```

## 13. Delete Client Issue Task

- Method: `DELETE`
- URL: `/api/v1/client-issues/{clientIssue}/tasks/{task}`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/client-issues/1/tasks/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## Response Format

Successful responses follow this structure:

```json
{
  "success": true,
  "message": "Client issues retrieved successfully.",
  "data": {}
}
```

Validation and error responses follow this structure:

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

Authorization and authentication responses follow this structure:

```json
{
  "success": false,
  "message": "You are not authorized to perform this action.",
  "errors": null
}
```

## HTTP Status Codes

- `200` for successful fetch, update, delete, assign, and bulk delete requests
- `201` for successful create requests
- `401` for unauthenticated requests
- `403` for permission or ownership failures
- `404` when the requested issue or task does not exist
- `422` for validation failures
- `500` for unexpected server errors
