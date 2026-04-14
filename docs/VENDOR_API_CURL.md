# Vendor API Curl Examples

Base URL: `http://localhost:8000/api/v1`

> All vendor endpoints require authentication with a valid Sanctum bearer token.

## 1. Login and get token

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

Response example:

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "..."
  }
}
```

## 2. Create Vendor

```bash
curl -X POST http://localhost:8000/api/v1/vendors \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vendor Name",
    "email": "vendor@example.com",
    "phone": "1234567890",
    "address": "123 Vendor Street",
    "status": "active"
  }'
```

## 3. Get list of vendors

```bash
curl -X GET http://localhost:8000/api/v1/vendors \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 4. Get vendor detail

```bash
curl -X GET http://localhost:8000/api/v1/vendors/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 5. Edit vendor

```bash
curl -X PUT http://localhost:8000/api/v1/vendors/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vendor Name Updated",
    "email": "vendor@example.com",
    "phone": "0987654321",
    "address": "456 Updated Street",
    "status": "inactive"
  }'
```

## 6. Delete vendor by ID

```bash
curl -X DELETE http://localhost:8000/api/v1/vendors/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 7. Delete all vendors

```bash
curl -X DELETE http://localhost:8000/api/v1/vendors/delete-all \
  -H "Authorization: Bearer YOUR_TOKEN"
```
