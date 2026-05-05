# Meta Leads API - cURL Request Examples

This document provides cURL examples for the following API routes:

- `GET /api/v1/meta-leads`
- `GET /api/v1/meta-leads/{lead}`
- `POST /api/v1/meta-leads/sync`
- `DELETE /api/v1/meta-leads/{lead}`

## Base Setup

- Base URL: `http://localhost:8000/api/v1/meta-leads`
- Auth: Sanctum Bearer token required (`auth:sanctum`)
- Headers used in all requests:
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Accept: application/json`

## 1. List Meta Leads

Endpoint: `GET /api/v1/meta-leads`

Supported query params:
- `search` (searches `full_name`, `email`, `phone`)
- `date_from` (YYYY-MM-DD)
- `date_to` (YYYY-MM-DD)
- `form_id`
- `per_page` (default 20, max 100)
- `page`

```bash
curl -X GET "http://localhost:8000/api/v1/meta-leads?search=john&date_from=2026-05-01&date_to=2026-05-31&form_id=1234567890&per_page=20&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

Simple example:

```bash
curl -X GET "http://localhost:8000/api/v1/meta-leads" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \B00313
  
  -H "Accept: application/json"
```

## 2. Get Meta Lead by ID

Endpoint: `GET /api/v1/meta-leads/{lead}`

Note: `{lead}` is your local `meta_leads.id` (route-model binding), not `lead_id`.

```bash
curl -X GET "http://localhost:8000/api/v1/meta-leads/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 3. Sync Meta Leads

Endpoint: `POST /api/v1/meta-leads/sync`

Without `form_id` (uses `FACEBOOK_FORM_ID` from config):

```bash
curl -X POST "http://localhost:8000/api/v1/meta-leads/sync" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

With explicit `form_id`:

```bash
curl -X POST "http://localhost:8000/api/v1/meta-leads/sync" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"form_id\":\"123456789012345\"}"
```

## 4. Delete Meta Lead

Endpoint: `DELETE /api/v1/meta-leads/{lead}`

```bash
curl -X DELETE "http://localhost:8000/api/v1/meta-leads/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Notes

- Replace `YOUR_TOKEN_HERE` with a valid Sanctum token.
- Ensure the authenticated user has required permissions:
  - `view_leads` for list/show
  - `create_leads` for sync
  - `delete_leads` for delete
- If sync returns 0, verify `FACEBOOK_FORM_ID` and `FACEBOOK_PAGE_ACCESS_TOKEN`.
