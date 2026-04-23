# Client API - cURL Request Examples

This document provides cURL examples for the following API routes:

- `GET /api/v1/clients/`
- `GET /api/v1/clients/{id}`
- `POST /api/v1/clients/`
- `PUT|PATCH /api/v1/clients/{id}`
- `DELETE /api/v1/clients/{id}`

## Base Setup

- Base URL: `http://localhost:8000/api/v1/clients`
- Auth: Sanctum Bearer token required (`auth:sanctum`)
- Header used in all requests:
  - `Authorization: Bearer YOUR_TOKEN_HERE`
  - `Accept: application/json`

## 1. List Clients

Endpoint: `GET /api/v1/clients/`

Supports pagination via `?page=1` (10 items per page by default).

```bash
curl -X GET "http://localhost:8000/api/v1/clients?page=1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 2. Get Client by ID

Endpoint: `GET /api/v1/clients/{id}`

```bash
curl -X GET "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 3. Create Client

Endpoint: `POST /api/v1/clients/`

Required fields:
- `first_name`
- `last_name`
- `email` (unique)
- `phone`
- `password` (min 8 chars)

Optional fields:
- `profile_image` (file: jpeg/png/jpg/gif/webp)
- `status` (`active` or `inactive`)
- `address_line_1`, `address_line_2`, `city`, `state`, `country`, `pincode`
- `client_type`, `company_name`, `industry`, `website`

```bash
curl -X POST "http://localhost:8000/api/v1/clients" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "email=john.doe@example.com" \
  -F "phone=9876543210" \
  -F "password=secret123" \
  -F "status=active" \
  -F "address_line_1=123 Main Street" \
  -F "city=Ahmedabad" \
  -F "state=Gujarat" \
  -F "country=India" \
  -F "pincode=380001" \
  -F "client_type=Company" \
  -F "company_name=Acme Pvt Ltd" \
  -F "industry=Technology" \
  -F "website=https://acme.example" \
  -F "profile_image=@/absolute/path/to/profile.jpg"
```

If you do not want to upload an image, remove the `profile_image` line.

## 4. Update Client

Endpoint: `PUT /api/v1/clients/{id}` or `PATCH /api/v1/clients/{id}`

Required fields in current validation:
- `first_name`
- `last_name`
- `phone`

```bash
curl -X PUT "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "first_name=John" \
  -F "last_name=Smith" \
  -F "phone=9999999999" \
  -F "status=active" \
  -F "address_line_1=New Address Line 1" \
  -F "city=Surat" \
  -F "state=Gujarat" \
  -F "country=India" \
  -F "pincode=395007" \
  -F "client_type=Company" \
  -F "company_name=Acme Updated Pvt Ltd" \
  -F "industry=SaaS" \
  -F "website=https://acme-updated.example"
```

Patch example:

```bash
curl -X PATCH "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "first_name=John" \
  -F "last_name=Smith" \
  -F "phone=9999999999"
```

## 5. Delete Client

Endpoint: `DELETE /api/v1/clients/{id}`

```bash
curl -X DELETE "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Common Response Shapes

Success (helper-based endpoints):

```json
{
  "success": true,
  "message": "Clients found",
  "data": {}
}
```

Validation error:

```json
{
  "success": false,
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

## Notes

- Replace `YOUR_TOKEN_HERE` with a valid Sanctum token.
- `store` and `update` are best called with `multipart/form-data` (`-F`) because `profile_image` can be uploaded.
- In the current controller logic, `update` returns status code `201` and message text mentions creation; this is existing behavior.
