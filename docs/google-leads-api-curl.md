# Google Leads API - cURL Requests

Base URL examples below use `http://localhost`. Update if your API host differs.

## 1) List Leads (Paginated)

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 2) List Leads with Query Filters

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads?search=john&type=real&campaign_id=789&lead_stage=NEW&per_page=25&page=2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 3) Lead Stats

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads/stats" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 4) Single Lead Detail

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 5) Only Test Leads

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads?type=test" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 6) Only Real Leads

```bash
curl -X GET "http://localhost/api/v1/google-ads-leads?type=real" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

