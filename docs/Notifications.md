# Notifications API cURL Requests

## Base
- All endpoints are under: `GET/PATCH /api/v1/notifications...`
- Authentication: `auth:sanctum` required

## 1) Get Logged-in User Notifications
- `GET /api/v1/notifications`

```bash
curl --location --request GET "http://127.0.0.1:8000/api/v1/notifications" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

### Optional pagination
```bash
curl --location --request GET "http://127.0.0.1:8000/api/v1/notifications?per_page=20&page=1" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 2) Mark One Notification as Read
- `PATCH /api/v1/notifications/{id}/read`

```bash
curl --location --request PATCH "http://127.0.0.1:8000/api/v1/notifications/NOTIFICATION_ID/read" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 3) Mark All Notifications as Read
- `PATCH /api/v1/notifications/read-all`

```bash
curl --location --request PATCH "http://127.0.0.1:8000/api/v1/notifications/read-all" \
--header "Accept: application/json" \
--header "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## Notes
- Replace `YOUR_SANCTUM_TOKEN` with a valid login token.
- Replace `NOTIFICATION_ID` with the notification UUID from list API response.
