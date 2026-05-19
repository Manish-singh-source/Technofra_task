# Web Enquiry Contact APIs

Base URL examples:
- Local: `http://127.0.0.1:8000/api/v1`
- Production: `https://technofra.com/api/v1`

All endpoints require:
- `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- `Accept: application/json`

## 1) List Contact Enquiries

`GET /web-enquiry/contacts`

Supports:
- Pagination: `per_page` (1-100, default 10)
- Search: `search`
- Sorting: `sort_by`, `sort_order` (`asc|desc`)

Allowed `sort_by` values:
- `id`, `fname`, `lname`, `contact`, `email`, `source_page`, `created_at`

Soft-deleted records are excluded (`deleted_at IS NULL`).

### Curl

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/web-enquiry/contacts?per_page=10&search=gmail&sort_by=created_at&sort_order=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 2) Soft Delete Contact Enquiry

`DELETE /web-enquiry/contacts/{id}`

Soft delete only:
- Sets `deleted_at = now()`
- Does not hard delete row

### Curl

```bash
curl -X DELETE "http://127.0.0.1:8000/api/v1/web-enquiry/contacts/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Notes

- If contact enquiry does not exist (or is already soft-deleted), delete returns `404`.
