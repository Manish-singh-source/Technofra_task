# Dashboard API cURL Request

## Endpoint
- `GET /api/v1/dashboard`

## cURL (Laravel dev server)
```bash
curl --location --request GET "http://127.0.0.1:8000/api/v1/dashboard" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

## cURL (XAMPP/Apache)
```bash
curl --location --request GET "http://localhost/Technofra_task/public/api/v1/dashboard" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

## Notes
- Replace `YOUR_SANCTUM_TOKEN` with a valid token from login API.
- This route is protected by `auth:sanctum`.
