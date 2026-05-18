# Client Renewal Form Options API - cURL

Endpoint: `GET /api/v1/client-renewals/form-options`

Auth: Sanctum Bearer token required.

```bash
curl -X GET "http://localhost:8000/api/v1/client-renewals/form-options" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Response Shape

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "clients": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "company_names": [
          "Acme Pvt Ltd",
          "Acme Foundation"
        ],
        "companies": [
          {
            "id": 10,
            "client_type": "Company",
            "company_name": "Acme Pvt Ltd",
            "industry": "Technology",
            "website": "https://acme.example"
          },
          {
            "id": 11,
            "client_type": "Organization",
            "company_name": "Acme Foundation",
            "industry": "Nonprofit",
            "website": "https://foundation.acme.example"
          }
        ]
      }
    ],
    "vendors": [
      {
        "id": 1,
        "name": "Vendor Name"
      }
    ]
  }
}
```

Replace `YOUR_TOKEN_HERE` with a valid Sanctum token that has `view_renewals` permission.
