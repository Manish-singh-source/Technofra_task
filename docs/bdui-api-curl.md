# BDUI API - cURL Requests

Base URL examples use `http://localhost`. Update host/port as needed.

## 1) Get Dashboard Schema

```bash
curl -X GET "http://localhost/api/ui/v1/dashboard" \
  -H "Accept: application/json"
```

## 2) Future Auth Variant (if auth middleware is enabled later)

```bash
curl -X GET "http://localhost/api/ui/v1/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

