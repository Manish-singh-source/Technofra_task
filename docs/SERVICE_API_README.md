# Service Module API

These APIs are available under the protected Sanctum API prefix:

- Base URL: `/api/v1`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Content type: `Accept: application/json`

## 1. Get Service Form Options

- Method: `GET`
- URL: `/api/v1/services/form-options`
- Permission required: `create_services`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/services/form-options' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 2. Get Services List

- Method: `GET`
- URL: `/api/v1/services`
- Permission required: `view_services`

Optional query params:

- `tab=all|upcoming|active|inactive|pending|expired`
- `from_date=YYYY-MM-DD`
- `to_date=YYYY-MM-DD`
- `client_id=1`
- `vendor_id=1`
- `status=active`
- `search=hosting`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/services?tab=upcoming&from_date=2026-04-01&to_date=2026-04-30' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 3. Get Service Detail

- Method: `GET`
- URL: `/api/v1/services/{id}`
- Permission required: `view_services`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/services/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 4. Create Services For A Client

- Method: `POST`
- URL: `/api/v1/services`
- Permission required: `create_services`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/services' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "client_id": 1,
  "services": [
    {
      "vendor_id": 1,
      "service_name": "Website Hosting",
      "service_details": "Shared hosting plan with SSL",
      "remark_text": "Renew soon",
      "remark_color": "yellow",
      "start_date": "2026-04-01",
      "end_date": "2027-03-31",
      "billing_date": "2026-04-01",
      "status": "active"
    },
    {
      "vendor_id": 2,
      "service_name": "Domain Renewal",
      "service_details": "Primary company domain",
      "start_date": "2026-04-01",
      "end_date": "2027-03-31",
      "billing_date": "2026-04-01",
      "status": "pending"
    }
  ]
}'
```

## 5. Update Service

- Method: `PUT`
- URL: `/api/v1/services/{id}`
- Permission required: `edit_services`

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/services/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "client_id": 1,
  "vendor_id": 1,
  "service_name": "Website Hosting Premium",
  "service_details": "Upgraded hosting package with backup support",
  "remark_text": "Priority renewal",
  "remark_color": "blue",
  "start_date": "2026-04-01",
  "end_date": "2027-03-31",
  "billing_date": "2026-04-01",
  "status": "active"
}'
```

## 6. Delete Service By ID

- Method: `DELETE`
- URL: `/api/v1/services/{id}`
- Permission required: `delete_services`

```bash
curl --location --request DELETE 'http://127.0.0.1:8000/api/v1/services/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 7. Delete Selected Services

- Method: `POST`
- URL: `/api/v1/services/delete-selected`
- Permission required: `delete_services`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/services/delete-selected' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "ids": [1, 2, 3]
}'
```

## Response Format

Successful responses follow this structure:

```json
{
  "success": true,
  "message": "Services retrieved successfully.",
  "data": []
}
```

Validation and error responses follow this structure:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "client_id": [
      "Please select a client."
    ]
  }
}
```

Authentication and authorization errors follow this structure:

```json
{
  "success": false,
  "message": "You are not authorized to perform this action.",
  "errors": null
}
```

## HTTP Status Codes

- `200` for successful fetch, update, delete, and bulk delete requests
- `201` for successful create requests
- `401` for unauthenticated requests
- `403` for permission or authorization failures
- `404` when the requested service does not exist
- `422` for validation failures
- `500` for unexpected server errors
