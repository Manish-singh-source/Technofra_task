# Staff API cURL Requests

Base URL: `http://127.0.0.1:8000/api/v1/staff`

Use a valid token in all requests:

```bash
TOKEN="YOUR_BEARER_TOKEN"
```

## List Staff (All)

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

## List Staff by Status

Active staff:

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff?status=active' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

Inactive staff:

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff?status=inactive' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

## Staff Analytics

### Staff dashboard analytics

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff/1/analytics?period=30d' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

### Staff lead chart

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff/1/lead-chart?period=30d' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

### Staff follow-up chart

```bash
curl --location 'http://127.0.0.1:8000/api/v1/staff/1/followup-chart?period=30d' \
--header "Accept: application/json" \
--header "Authorization: Bearer $TOKEN"
```

