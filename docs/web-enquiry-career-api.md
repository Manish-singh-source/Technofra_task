# Web Enquiry Career APIs

Base URL examples:
- Local: `http://127.0.0.1:8000/api/v1`
- Production: `https://technofra.com/api/v1`

All endpoints require:
- `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- `Accept: application/json`

## 1) List Career Enquiries

`GET /web-enquiry/careers`

Supports:
- Pagination: `per_page` (1-100, default 10)
- Search: `search`
- Applicant type filter: `applicant_type` (`all|fresher|experience`, default `all`)
- Sorting: `sort_by`, `sort_order` (`asc|desc`)

Response data includes `applicant_type`.

Allowed `sort_by` values:
- `id`, `fname`, `email`, `contact`, `role`, `experience`, `ctc`, `ectc`, `location`, `refrence`, `created_at`

### Curl

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/web-enquiry/careers?per_page=10&search=developer&applicant_type=fresher&sort_by=created_at&sort_order=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 2) Career Enquiry Detail

`GET /web-enquiry/careers/{id}`

Returns all career fields including `applicant_type` and `resume_url`.

### Curl

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/web-enquiry/careers/2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 3) Soft Delete Career Enquiry

`DELETE /web-enquiry/careers/{id}`

Soft delete only:
- Sets `deleted_at = now()`
- Record is excluded from list/detail after delete

### Curl

```bash
curl -X DELETE "http://127.0.0.1:8000/api/v1/web-enquiry/careers/2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 4) Resume URL API

`GET /web-enquiry/careers/{id}/resume-url`

Returns:
- `resume_file` (raw db path)
- `resume_url` (prefixed with `https://technofra.com/`)

### Curl

```bash
curl -X GET "http://127.0.0.1:8000/api/v1/web-enquiry/careers/2/resume-url" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Notes

- If a record is missing or already soft-deleted, detail/delete/resume endpoints return `404`.
- `resume_url` is also included directly in list and detail responses.
