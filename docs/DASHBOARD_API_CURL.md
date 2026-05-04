# Dashboard API cURL Request

## Endpoint
- `GET /api/v1/dashboard`

## cURL (Laravel dev server)
```bash
curl --location --request GET "http://127.0.0.1:8000/api/v1/dashboard" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

## Notes
- Replace `YOUR_SANCTUM_TOKEN` with a valid token from login API.
- This route is protected by `auth:sanctum`.

---

## Quick Stats Endpoint
- `GET /api/v1/quick-stats`

## cURL (Laravel dev server)
```bash
curl --location --request GET "http://127.0.0.1:8000/api/v1/quick-stats" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Store FCM Token Endpoint
- `POST /api/v1/fcm-token`

## cURL (Laravel dev server)
```bash
curl --location --request POST "http://127.0.0.1:8000/api/v1/fcm-token" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN" \
--header "Content-Type: application/json" \
--data '{
  "fcm_token": "YOUR_DEVICE_FCM_TOKEN",
  "device_id": "device-123",
  "platform": "android"
}'
```
