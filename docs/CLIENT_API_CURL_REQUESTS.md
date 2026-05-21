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

Supported query params:
- `search` (optional): matches `first_name`, `last_name`, `email`
- `status` (optional): `active`, `inactive`, `1`, `0`, `true`, `false` (case-insensitive)
- `per_page` (optional): default `10`, min `1`, max `100`
- `page` (optional): page number for pagination

Pagination/filter example:

```bash
curl -X GET "http://localhost:8000/api/v1/clients?search=john&status=active&per_page=25&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

Status-only examples:

```bash
curl -X GET "http://localhost:8000/api/v1/clients?status=active" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

```bash
curl -X GET "http://localhost:8000/api/v1/clients?status=inactive" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

Pagination-only example:

```bash
curl -X GET "http://localhost:8000/api/v1/clients?per_page=10&page=1" \
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
- `password` (min 8 chars)

Optional fields:
- `profile_image` (file: jpeg/png/jpg/gif/webp)
- `last_name`
- `email` (unique)
- `phone`
- `status` (`active` or `inactive`)
- `send_invite_mail` (`true` or `false`)
- `address_line_1`, `address_line_2`, `city`, `state`, `country`, `pincode`
- `client_type`, `company_name`, `industry`, `website`
- `companies[index][client_type]`, `companies[index][company_name]`, `companies[index][industry]`, `companies[index][website]`

`companies` supports multiple business/company details. The old single-company fields (`client_type`, `company_name`, `industry`, `website`) are still accepted as a fallback when `companies` is not sent.

```bash
curl -X POST "http://localhost:8000/api/v1/clients" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "first_name=John" \
  -F "last_name=Doe" \
  -F "email=john.doe@example.com" \
  -F "phone=9876543210" \
  -F "password=secret123" \
  -F "send_invite_mail=true" \
  -F "status=active" \
  -F "address_line_1=123 Main Street" \
  -F "address_line_2=Suite 401" \
  -F "city=Ahmedabad" \
  -F "state=Gujarat" \
  -F "country=India" \
  -F "pincode=380001" \
  -F "companies[0][client_type]=Company" \
  -F "companies[0][company_name]=Acme Pvt Ltd" \
  -F "companies[0][industry]=Technology" \
  -F "companies[0][website]=https://acme.example" \
  -F "companies[1][client_type]=Organization" \
  -F "companies[1][company_name]=Acme Foundation" \
  -F "companies[1][industry]=Nonprofit" \
  -F "companies[1][website]=https://foundation.acme.example" \
  -F "profile_image=@/absolute/path/to/profile.jpg"
```

If you do not want to upload an image, remove the `profile_image` line.
If you do not want to send an invitation email, set `send_invite_mail=false` or remove that line.

## 4. Update Client

Endpoint: `PUT /api/v1/clients/{id}` or `PATCH /api/v1/clients/{id}`

Required fields:
- `first_name`

Optional fields:
- `profile_image` (file: jpeg/png/jpg/gif/webp)
- `last_name`
- `email` (unique, ignores the current client)
- `phone`
- `password` (min 8 chars)
- `send_invite_mail` (`true` or `false`; requires `email` and `password` when true)
- `status` (`active` or `inactive`)
- `address_line_1`, `address_line_2`, `city`, `state`, `country`, `pincode`
- `companies[index][client_type]`, `companies[index][company_name]`, `companies[index][industry]`, `companies[index][website]`

When using `multipart/form-data`, call the route with `POST` and `_method=PUT` or `_method=PATCH`. This works better with PHP file/form parsing than raw multipart `PUT`.

```bash
curl -X POST "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -F "_method=PUT" \
  -F "first_name=John" \
  -F "last_name=Smith" \
  -F "email=john.smith@example.com" \
  -F "phone=9999999999" \
  -F "password=newsecret123" \
  -F "send_invite_mail=true" \
  -F "status=active" \
  -F "address_line_1=New Address Line 1" \
  -F "address_line_2=Office 12" \
  -F "city=Surat" \
  -F "state=Gujarat" \
  -F "country=India" \
  -F "pincode=395007" \
  -F "companies[0][client_type]=Company" \
  -F "companies[0][company_name]=Acme Updated Pvt Ltd" \
  -F "companies[0][industry]=SaaS" \
  -F "companies[0][website]=https://acme-updated.example" \
  -F "companies[1][client_type]=Individual" \
  -F "companies[1][company_name]=John Smith Consulting" \
  -F "companies[1][industry]=Consulting" \
  -F "companies[1][website]=https://johnsmith.example"
```

JSON patch example without file upload:

```bash
curl -X PATCH "http://localhost:8000/api/v1/clients/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Smith",
    "email": "john.smith@example.com",
    "phone": "9999999999",
    "status": "active",
    "address_line_1": "New Address Line 1",
    "city": "Surat",
    "state": "Gujarat",
    "country": "India",
    "pincode": "395007",
    "companies": [
      {
        "client_type": "Company",
        "company_name": "Acme Updated Pvt Ltd",
        "industry": "SaaS",
        "website": "https://acme-updated.example"
      },
      {
        "client_type": "Organization",
        "company_name": "Acme Foundation",
        "industry": "Nonprofit",
        "website": "https://foundation.acme.example"
      }
    ]
  }'
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
- `store` returns `201`; `update` returns `200`.
- `mail_status` can be `not_requested`, `sent`, or `failed`.
