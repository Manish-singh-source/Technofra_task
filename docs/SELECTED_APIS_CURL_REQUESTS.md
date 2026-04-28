# Selected APIs - cURL Request Examples

This document provides cURL examples for the selected API routes:

- `GET /api/v1/book-a-call`
- `DELETE /api/v1/book-a-call/{id}`

- `GET /api/v1/digital-marketing`
- `DELETE /api/v1/digital-marketing/{id}`

- `GET /api/v1/web-apps-leads`
- `DELETE /api/v1/web-apps-leads/{id}`

## Base Setup

- Base URL: `http://localhost:8000/api/v1`
- Auth: Sanctum Bearer token required (if route is under `auth:sanctum`)
- Header used in all requests:
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Accept: application/json`

## 1. List Book A Call Records

Endpoint: `GET /api/v1/book-a-call`

```bash
curl -X GET "http://localhost:8000/api/v1/book-a-call" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 2. Delete Book A Call Record

Endpoint: `DELETE /api/v1/book-a-call/{id}`

```bash
curl -X DELETE "http://localhost:8000/api/v1/book-a-call/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 3. List Digital Marketing Leads

Endpoint: `GET /api/v1/digital-marketing`

```bash
curl -X GET "http://localhost:8000/api/v1/digital-marketing" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 4. Delete Digital Marketing Lead

Endpoint: `DELETE /api/v1/digital-marketing/{id}`

```bash
curl -X DELETE "http://localhost:8000/api/v1/digital-marketing/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 5. List Web Apps Leads

Endpoint: `GET /api/v1/web-apps-leads`

```bash
curl -X GET "http://localhost:8000/api/v1/web-apps-leads" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 6. Delete Web Apps Lead

Endpoint: `DELETE /api/v1/web-apps-leads/{id}`

```bash
curl -X DELETE "http://localhost:8000/api/v1/web-apps-leads/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Notes

- Replace `YOUR_TOKEN_HERE` with a valid token.
- Replace the sample IDs (`1`) with actual record IDs from your system.
- If your local backend URL differs, update `http://localhost:8000`.
