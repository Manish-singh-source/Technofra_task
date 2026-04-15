# Todo API Documentation

These APIs are available under the protected Sanctum API prefix:

- Base URL: `/api/v1/todos`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Content type: `Accept: application/json`
- File uploads: use `multipart/form-data` for create and update when sending attachments

## 1. Get Todo Options

```bash
curl --location 'http://127.0.0.1:8000/api/v1/todos/options' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 2. Get Todo List

```bash
curl --location 'http://127.0.0.1:8000/api/v1/todos' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 3. Get Single Todo

```bash
curl --location 'http://127.0.0.1:8000/api/v1/todos/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 4. Create Todo With Multiple Attachments

```bash
curl --location 'http://127.0.0.1:8000/api/v1/todos/create-todo' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--form 'title="Prepare weekly report"' \
--form 'description="Collect status from all teams"' \
--form 'task_date="2026-04-14"' \
--form 'task_time="10:00"' \
--form 'repeat_interval="1"' \
--form 'repeat_unit="week"' \
--form 'repeat_days[]="monday"' \
--form 'repeat_days[]="friday"' \
--form 'reminder_time="09:30"' \
--form 'starts_on="2026-04-14"' \
--form 'ends_type="never"' \
--form 'attachments[]=@"C:/path/to/file-one.pdf"' \
--form 'attachments[]=@"C:/path/to/file-two.png"'
```

## 5. Update Todo And Append New Attachments

Note: update keeps existing attachments and appends any newly uploaded files.

```bash
curl --location --request POST 'http://127.0.0.1:8000/api/v1/todos/update-todo/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--form '_method="PUT"' \
--form 'title="Prepare weekly report - updated"' \
--form 'description="Include blockers and approvals"' \
--form 'task_date="2026-04-14"' \
--form 'task_time="11:00"' \
--form 'repeat_interval="1"' \
--form 'repeat_unit="week"' \
--form 'repeat_days[]="monday"' \
--form 'reminder_time="10:30"' \
--form 'starts_on="2026-04-14"' \
--form 'ends_type="after"' \
--form 'ends_after_occurrences="5"' \
--form 'attachments[]=@"C:/path/to/file-three.docx"'
```

## 6. Toggle Todo Status

```bash
curl --location --request PATCH 'http://127.0.0.1:8000/api/v1/todos/toggle-todo-status/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "is_completed": true
}'
```

## 7. Delete Todo

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/todos/delete-todo/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## Attachment Response Shape

```json
{
  "attachments": [
    {
      "name": "file-one.pdf",
      "path": "uploads/todo_attachments/1710000000_xxxxx.pdf",
      "url": "http://127.0.0.1:8000/uploads/todo_attachments/1710000000_xxxxx.pdf",
      "size": 234567,
      "mime_type": "application/pdf"
    }
  ],
  "attachments_count": 1
}
```
