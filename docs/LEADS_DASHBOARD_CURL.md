# Leads Dashboard API cURL

Endpoint: `GET /api/v1/leads/dashboard`  
Auth: `Bearer` token (Sanctum)

## 1) Basic Request

```bash
curl --request GET "http://localhost/api/v1/leads/dashboard" \
  --header "Accept: application/json" \
  --header "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 2) With Query Params (if added later)

```bash
curl --request GET "http://localhost/api/v1/leads/dashboard?date=2026-04-30" \
  --header "Accept: application/json" \
  --header "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 3) Expected Response Shape

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "9999999999",
      "created_at": "2026-04-30T08:21:00.000000Z",
      "lead_type": "leads"
    }
  ],
  "leadsCount": {
    "todaysLeadsCount": 0,
    "allLeadsCount": 0
  },
  "bookCallsCount": {
    "todaysBookCallsCount": 0,
    "allBookCallsCount": 0
  },
  "digitalMarketingLeadsCount": {
    "todaysDigitalMarketingLeadsCount": 0,
    "allDigitalMarketingLeadsCount": 0
  },
  "webAppLeadsCount": {
    "todaysWebAppLeadsCount": 0,
    "allWebAppLeadsCount": 0
  }
}
```

