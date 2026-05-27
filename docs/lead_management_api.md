# Lead Management API

Base URL: `http://localhost:8000/api/v1/lead-management`

Auth: All endpoints require Sanctum Bearer token.

```bash
TOKEN="your_sanctum_token"
BASE_URL="http://localhost:8000/api/v1/lead-management"
```

## 1. List Leads

```bash
curl -X GET "http://localhost:8000/api/v1/lead-management?search=john&status=new" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 2. Lead Details

```bash
curl -X GET "http://localhost:8000/api/v1/lead-management/lead/1/view" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 3. Assign Lead

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/assign" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "assigned_user_ids": [3, 5],
    "assignment_note": "Assigning for quick follow-up"
  }'
```

## 4. Bulk Assign Leads

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/bulk-assign" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "assigned_user_ids": [3, 5],
    "selected_leads": [
      {"source": "lead", "id": 1},
      {"source": "meta", "id": 12}
    ]
  }'
```

## 5. Update Lead Status

```bash
curl -X PATCH "http://localhost:8000/api/v1/lead-management/lead/1/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "contacted",
    "remarks": "Spoke with client",
    "lost_reason": null,
    "won_value": null
  }'
```

## 6. Add Followup

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/followup" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "followup_date": "2026-05-27 11:30:00",
    "followup_type": "call",
    "outcome": "interested",
    "discussion_notes": "Asked for proposal",
    "next_followup_date": "2026-05-30 10:00:00",
    "lead_status_after_followup": "qualified",
    "create_reminder": true,
    "reminder_type": ""
  }'
```

## 7. Followup History

```bash
curl -X GET "http://localhost:8000/api/v1/lead-management/lead/1/followups" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 8. Add Note

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/note" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note": "Client requested revised timeline",
    "is_private": true
  }'
```

## 9. Add Reminder

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/reminder" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "remind_at": "2026-05-30 09:30:00",
    "reminder_type": "dashboard"
  }'
```

## 10. Activity Timeline

```bash
curl -X GET "http://localhost:8000/api/v1/lead-management/lead/1/timeline" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 11. Convert Lead

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/convert" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 7,
    "conversion_value": 50000
  }'
```

## 12. Escalate Lead

```bash
curl -X POST "http://localhost:8000/api/v1/lead-management/lead/1/escalate" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "escalated_to": 4,
    "reason": "Need manager intervention"
  }'
```

## 13. Performance Stats

```bash
curl -X GET "http://localhost:8000/api/v1/lead-management/performance/stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 14. Delete Lead

```bash
curl -X DELETE "http://localhost:8000/api/v1/lead-management/lead/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Valid Source Values

- `lead`
- `digital_marketing`
- `webapp`
- `meta`
- `google`
