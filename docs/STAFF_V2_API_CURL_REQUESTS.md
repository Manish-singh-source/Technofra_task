# Staff V2 API - cURL Request Examples

This document provides cURL examples for interacting with the Staff V2 API endpoints. All requests require authentication via Sanctum token.

**Base URL:** http://localhost:8000/api/v1/staff-v2

**Authentication:** Include the Authorization: Bearer YOUR_TOKEN_HERE header in all requests.

## 1. Get Form Options

**Endpoint:** GET /form-options

**Permission Required:** None (within authenticated routes)

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/form-options" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 2. Get Departments

**Endpoint:** GET /departments

**Permission Required:** None (public within authenticated routes)

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/departments" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 3. Get Teams

**Endpoint:** GET /teams

**Permission Required:** None (public within authenticated routes)

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/teams" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 4. List Staff Members

**Endpoint:** GET /

**Permission Required:** view_staff

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 5. Get Staff Analytics

**Endpoint:** GET /{id}/analytics

**Permission Required:** view_staff

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/1/analytics?period=30d" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 6. Get Lead Chart Data

**Endpoint:** GET /{id}/lead-chart

**Permission Required:** view_staff

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/1/lead-chart?period=30d" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 7. Get Followup Chart Data

**Endpoint:** GET /{id}/followup-chart

**Permission Required:** view_staff

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/1/followup-chart?period=30d" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 8. Get Staff Member Details

**Endpoint:** GET /{id}

**Permission Required:** view_staff

`bash
curl -X GET "http://localhost:8000/api/v1/staff-v2/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 9. Create New Staff Member

**Endpoint:** POST /

**Permission Required:** create_staff

`bash
curl -X POST "http://localhost:8000/api/v1/staff-v2/" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "password": "password123",
    "departments": [1, 2],
    "team": 1,
    "status": "active",
    "sendWelcomeEmail": true
  }'
`

## 10. Update Staff Member

**Endpoint:** PUT /{id}

**Permission Required:** edit_staff

`bash
curl -X PUT "http://localhost:8000/api/v1/staff-v2/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Smith",
    "email": "john.smith@example.com",
    "phone": "+1234567890",
    "departments": [1],
    "team": 2,
    "status": "active"
  }'
`

## 11. Soft Delete Staff Member

**Endpoint:** DELETE /{id}

**Permission Required:** delete_staff

`bash
curl -X DELETE "http://localhost:8000/api/v1/staff-v2/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 12. Restore Staff Member

**Endpoint:** POST /{id}/restore

**Permission Required:** edit_staff

`bash
curl -X POST "http://localhost:8000/api/v1/staff-v2/1/restore" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## 13. Force Delete Staff Member

**Endpoint:** DELETE /{id}/force

**Permission Required:** delete_staff

`bash
curl -X DELETE "http://localhost:8000/api/v1/staff-v2/1/force" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
`

## Additional Notes

- Replace YOUR_TOKEN_HERE with your actual Sanctum authentication token
- All endpoints return JSON responses
- List and detail responses now mirror the web staff resource shape
- Analytics endpoints return the same chart datasets used by the web staff page
- File uploads (profile_image) would require using -F flags instead of JSON for multipart/form-data
- Error responses will include validation errors or server errors in JSON format
- Pagination is applied to the index endpoint (GET /)
