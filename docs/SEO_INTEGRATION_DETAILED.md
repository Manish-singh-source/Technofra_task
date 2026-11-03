# SEO Working Flow: Complete Button, API, and Integration Guide

This document explains the complete SEO workflow currently implemented in this Laravel application. It maps every visible SEO button to its purpose, web route, API endpoint, expected payload, permission, database effect, and the external API that should be used when the app is upgraded from manual data entry to automatic SEO data collection.

## 1. Important implementation boundary

The current application is an SEO operations and approval system. It already supports campaigns, audits, findings, baselines, keywords, ranking snapshots, competitors, monthly activities, technical issues, on-page logs, backlinks, performance records, and reports.

The current application does **not** automatically:

- crawl a website;
- query Google Search Console;
- query GA4;
- check Google index status;
- measure PageSpeed/Core Web Vitals automatically;
- fetch Google ranking positions;
- discover backlinks;
- change a client website's title, description, H1, schema, sitemap, or robots.txt;
- publish an SEO report on a schedule.

At present, those values are entered manually, imported from Excel, or stored as evidence URLs. The internal API is ready to receive many of the values, but the external connectors and scheduled synchronization still need to be built.

## 2. End-to-end SEO working flow

```text
Admin creates campaign
        |
        v
Assign client + SEO manager + executives
        |
        v
Executive prepares audit and findings
        |
        v
Submit audit ------------------------+
        |                             |
        v                             v
Manager approves                 Manager requests changes
        |                             |
        v                             +--> Executive edits and resubmits
Capture baseline
        |
        v
Manager approves and locks baseline
        |
        +--> Add keywords and ranking snapshots
        +--> Add competitor observations
        +--> Create monthly cycle
        +--> Assign SEO activities
        +--> Log technical fixes
        +--> Log on-page changes
        +--> Log backlink work
        +--> Import performance metrics
        |
        v
Executive submits completed work
        |
        v
Manager approves or requests changes
        |
        v
Create monthly report snapshot
        |
        v
Submit report --> Manager approves --> Manager publishes
        |
        v
Client sees published report only
```

## 3. Authentication and API conventions

### Internal application API

All SEO API endpoints are under `/api/v1/seo` and are protected by Laravel Sanctum:

```http
Authorization: Bearer {sanctum_token}
Accept: application/json
Content-Type: application/json
```

The login endpoint is:

```http
POST /api/v1/login
```

Successful responses generally follow this shape:

```json
{
  "success": true,
  "message": "Human-readable result",
  "data": {}
}
```

Validation and authorization failures are normally returned as HTTP `422` and `403`. Not-found resources return `404`. File imports use `multipart/form-data`, not JSON.

### Web UI versus API

The Blade UI uses authenticated web routes and CSRF protection. A mobile app, React frontend, Vue frontend, or external integration should use the `/api/v1/seo/...` endpoints instead. Do not call the Blade routes from a separate frontend.

## 4. Complete button inventory

The following sections cover every SEO button currently present in `resources/views/seo`.

## 5. Campaign list screen

Screen: `GET /seo/campaigns`

| Button | Where used | Why it is used | Web action | API equivalent | Permission |
|---|---|---|---|---|---|
| Archived campaigns | Active campaign list | Switches to reversible archived records | `GET /seo/campaigns/archived` | `GET /api/v1/seo/campaigns?archived=1` | Admin |
| Active campaigns | Archived list | Returns to non-archived campaigns | `GET /seo/campaigns` | `GET /api/v1/seo/campaigns` | Admin |
| Create SEO Campaign | Header | Opens campaign setup form | `GET /seo/campaigns/create` | No read endpoint; submit `POST /api/v1/seo/campaigns` | Admin |
| Open | Campaign row | Opens the campaign overview and audit workspace | `GET /seo/campaigns/{campaign}` | `GET /api/v1/seo/campaigns/{campaign}` | Campaign viewer |
| Archive | Campaign row | Soft-deletes a campaign so it is hidden but restorable | `DELETE /seo/campaigns/{campaign}` | `DELETE /api/v1/seo/campaigns/{campaign}` | Admin |
| Restore | Archived row | Restores a soft-deleted campaign | `PATCH /seo/campaigns/{campaign}/restore` | `POST /api/v1/seo/campaigns/{campaignId}/restore` | Admin |
| Pagination links | Bottom of table | Loads another page of campaigns | Laravel pagination URL | Use `page` and `per_page` query parameters | Based on list authorization |

### Campaign list API

```http
GET /api/v1/seo/campaigns?per_page=20&page=1
```

The API returns campaigns visible to the user. Administrators can request archived campaigns with `archived=1`; non-administrators cannot use the archived view.

## 6. Create campaign screen

Screen: `GET /seo/campaigns/create`

The form itself has no separate save button for each field. The final button sends all values in one transaction.

| Control/button | Purpose | Required/optional | Stored in |
|---|---|---|---|
| Campaign name | Human-readable campaign identifier | Required | `seo_campaigns.name` |
| Client | Links campaign to a client/customer | Required | `client_id` |
| Website URL | Primary website being optimized | Required | `website_url` |
| Industry | Helps classify the campaign | Optional | `industry` |
| Target country | Country targeting | Optional | `target_country` |
| Target locations | Local SEO locations, comma separated | Optional | `target_locations` |
| Language | Target search language | Required | `target_language` |
| Start date | Campaign start and reporting reference date | Required | `start_date` |
| Reporting frequency | Weekly, monthly, or quarterly reports | Required | `reporting_frequency` |
| Status | Draft, active, or on hold in the current Blade form | Required | `status` |
| SEO goals | Business and SEO objectives | Optional | `goals` |
| Manager checkboxes | Assign people who can review/approve | At least one manager | `seo_campaign_members` |
| Executive checkboxes | Assign people who execute work | Optional | `seo_campaign_members` |
| Create campaign | Creates campaign and team assignments | Final action | `POST /seo/campaigns` |
| Cancel | Leaves without saving | No API call | Campaign list |

### API request

```http
POST /api/v1/seo/campaigns
```

```json
{
  "client_id": 42,
  "name": "GreenLeaf Interiors SEO",
  "website_url": "https://example.com",
  "industry": "Interior design",
  "target_country": "India",
  "target_locations": "Mumbai, Pune",
  "target_language": "en",
  "search_engine": "google",
  "devices": ["desktop", "mobile"],
  "start_date": "2026-11-01",
  "goals": "Increase qualified organic leads and top-10 coverage.",
  "reporting_frequency": "monthly",
  "status": "active",
  "members": [
    {"user_id": 7, "campaign_role": "manager"},
    {"user_id": 12, "campaign_role": "executive"}
  ]
}
```

At least one manager is required. The API accepts `completed` and `cancelled` statuses even though the current Blade create form does not show them.

## 7. Campaign overview: audit buttons

Screen: `GET /seo/campaigns/{campaign}`

### Navigation buttons

| Button | Why it is used | API |
|---|---|---|
| Dashboard | Opens KPI summary and baseline/current view | `GET /api/v1/seo/campaigns/{campaign}/dashboard` |
| Workspace | Opens monthly work, technical, on-page, backlink, analytics, and report tabs | `GET /api/v1/seo/campaigns/{campaign}/workspace` |
| Keywords | Opens keyword and ranking management | `GET /api/v1/seo/campaigns/{campaign}/keywords` |
| Competitors | Opens competitor records | `GET /api/v1/seo/campaigns/{campaign}/competitors` |

### Audit buttons

| Button | Purpose | Web route | API endpoint | Allowed role |
|---|---|---|---|---|
| Save audit draft | Saves summary without submitting | `PUT /seo/campaigns/{campaign}/audit` | `PUT /api/v1/seo/campaigns/{campaign}/audit` | Manager/executive |
| Add finding | Adds a URL-level audit problem | `POST /seo/campaigns/{campaign}/audit/findings` | `POST /api/v1/seo/campaigns/{campaign}/audit/findings` | Manager/executive |
| Submit audit for review | Moves audit from `draft` to `submitted` | `POST /seo/campaigns/{campaign}/audit/submit` | `POST /api/v1/seo/campaigns/{campaign}/audit/submit` | Manager/executive |
| Approve | Approves submitted audit | `POST /seo/campaigns/{campaign}/audit/review` with `decision=approved` | Same API with `decision=approved` | Manager |
| Changes | Sends audit back for correction | Same route with `decision=changes_required` | Same API with `decision=changes_required` | Manager |

Audit findings use `category` (`technical`, `on_page`, `performance`, `other`), `severity` (`critical`, `high`, `medium`, `low`), title, affected URL, description, recommended solution, and optional assignee. An audit cannot be submitted until it has at least one finding. An approved audit is locked.

Example review payload:

```json
{"decision":"changes_required","feedback":"Add affected URLs and evidence for the indexing findings."}
```

## 8. Baseline buttons and API

The current overview controller/API supports baseline saving and approval, although the Blade view should expose these controls clearly if baseline management is required from the UI.

| Action | Purpose | API | Rule |
|---|---|---|---|
| Save baseline draft | Stores the fixed starting measurement set | `PUT /api/v1/seo/campaigns/{campaign}/baseline` | Initial audit must be approved |
| Approve baseline | Locks the baseline permanently | `POST /api/v1/seo/campaigns/{campaign}/baseline/approve` | Manager only |

Example:

```json
{
  "captured_on": "2026-11-01",
  "notes": "Initial values captured before optimization work.",
  "metrics": [
    {"key":"organic_clicks","value":420,"unit":"count","source":"search_console"},
    {"key":"organic_impressions","value":12400,"unit":"count","source":"search_console"},
    {"key":"organic_sessions","value":380,"unit":"count","source":"ga4"},
    {"key":"average_ctr","value":3.39,"unit":"percent","source":"search_console"},
    {"key":"average_position","value":29.4,"unit":"position","source":"search_console"},
    {"key":"indexed_pages","value":86,"unit":"count","source":"search_console"}
  ]
}
```

The baseline is the comparison anchor. Do not overwrite it when a new monthly report arrives.

## 9. Keyword screen

Screen: `GET /seo/campaigns/{campaign}/keywords`

| Button/control | Purpose | API |
|---|---|---|
| Add | Creates one keyword and optionally its initial rank | `POST /api/v1/seo/campaigns/{campaign}/keywords` |
| Download template | Downloads the required Excel structure | `GET /api/v1/seo/campaigns/{campaign}/keywords/template` |
| Import Excel | Bulk-creates keywords and optional initial rankings | `POST /api/v1/seo/campaigns/{campaign}/keywords/import` |
| Update | Opens ranking modal for a specific keyword | `POST /api/v1/seo/campaigns/{campaign}/keywords/{keyword}/rankings` |
| Save ranking | Stores a historical ranking snapshot | Same ranking endpoint |
| Delete | Permanently deletes a keyword and its ranking history | `DELETE /api/v1/seo/campaigns/{campaign}/keywords/{keyword}` |

Keyword fields include keyword, target page URL, location, device, search volume, difficulty, priority, language, and search engine. The Blade form exposes a smaller subset than the API.

Ranking payload:

```json
{
  "checked_on": "2026-11-01",
  "rank_position": 12,
  "source": "google_search_console_or_rank_tracker",
  "remarks": "Mobile result checked for Mumbai location."
}
```

Leave `rank_position` null when the keyword is not found within the provider's result limit. Ranking history must be appended as snapshots; never replace the previous snapshot.

Excel headers must be exactly:

```text
Keyword, Target URL, Location, Initial Rank, Checked On
```

The import endpoint accepts `.xlsx`/`.xls` up to 5 MB, skips active duplicates, and returns `created`, `skipped`, and `errors`.

## 10. Competitor screen

Screen: `GET /seo/campaigns/{campaign}/competitors`

| Button | Purpose | API |
|---|---|---|
| Add competitor | Stores one competitor observation | `POST /api/v1/seo/campaigns/{campaign}/competitors` |
| Competitor website link | Opens the competitor website in a new tab | External URL; no internal API |
| Campaign overview | Returns to campaign overview | `GET /api/v1/seo/campaigns/{campaign}` |

Payload fields: `name`, `website_url`, `services`, `strengths`, `weaknesses`, `opportunities`, `notes`, and `data_source`.

Competitor data should be labelled with its source. It is not a verified Google metric unless it came from a named provider and the provider/date are stored.

## 11. Workspace navigation

Screen: `GET /seo/campaigns/{campaign}/workspace`

The workspace tabs are navigation links, not data-mutating buttons:

| Tab | Purpose | Data represented |
|---|---|---|
| Monthly Work | Plan and review work items | Cycles and activities |
| Technical | Track SEO defects and fixes | Crawl, index, sitemap, canonical, speed, mobile, schema issues |
| On-page | Record before/after page changes | Metadata, H1, content, alt tags, internal links |
| Backlinks | Track off-page placements | Published URLs and verification status |
| Analytics | Store performance metrics | Search Console, GA4, manual, import |
| Reports | Create, submit, approve, and publish reports | Period snapshot and attachments |

The API workspace response is:

```http
GET /api/v1/seo/campaigns/{campaign}/workspace?tab=analytics
```

Clients are forced to the reports tab and only see `published` reports.

## 12. Monthly work buttons and activity workflow

The current Blade workspace contains an Add button for activities and a Save button for each activity row. The API also supports manager review even though review controls are not fully represented in the current Blade workspace.

### Monthly cycle

```http
POST /api/v1/seo/campaigns/{campaign}/cycles
```

```json
{
  "month":"2026-11-01",
  "objectives":"Improve local landing pages and resolve crawl issues.",
  "status":"active"
}
```

Only a manager can create/update a monthly cycle. One cycle is maintained per campaign/month.

### Activity buttons

| Button | Purpose | API | Role |
|---|---|---|---|
| Add | Creates and assigns a task/deliverable | `POST /api/v1/seo/campaigns/{campaign}/activities` | Manager/executive |
| Save | Saves progress, notes, evidence, or submission | `PATCH /api/v1/seo/campaigns/{campaign}/activities/{activity}` | Assigned executive/manager |
| Approve | Accepts submitted work | `POST /api/v1/seo/campaigns/{campaign}/activities/{activity}/review` | Manager |
| Request changes | Returns work to the executive | Same review endpoint with `changes_required` | Manager |

Activity create payload:

```json
{
  "seo_monthly_cycle_id": 15,
  "category":"on_page",
  "title":"Optimize service page metadata",
  "target_url":"https://example.com/services",
  "description":"Update title, description, H1 and internal links.",
  "assigned_to":12,
  "start_date":"2026-11-02",
  "deadline":"2026-11-10",
  "priority":"high"
}
```

Progress update:

```json
{
  "status":"submitted",
  "work_notes":"Updated title and description; waiting for client deployment.",
  "evidence_url":"https://example.com/services"
}
```

Review payload:

```json
{"decision":"approved","feedback":"Verified against the live page."}
```

State flow:

```text
todo -> in_progress -> submitted -> approved
                              \-> changes_required -> submitted
```

Approved/completed activities are locked.

## 13. Technical SEO issue buttons

| Button | Purpose | API |
|---|---|---|
| Log issue | Creates a technical issue | `POST /api/v1/seo/campaigns/{campaign}/technical-issues` |
| Save | Updates progress and evidence | `PATCH /api/v1/seo/campaigns/{campaign}/technical-issues/{technicalIssue}` |
| Verify closed | Uses the same update action with `verified_closed` | Same endpoint; manager authorization required |

Supported types: `crawl`, `indexing`, `sitemap`, `redirect`, `broken_link`, `canonical`, `speed`, `mobile`, `schema`, `core_web_vitals`, `other`.

Issue state flow:

```text
identified -> assigned -> fix_in_progress -> implemented
                                             -> verification_pending
                                             -> verified_closed
                                             -> reopened
```

Payload example:

```json
{
  "title":"Missing XML sitemap URLs",
  "issue_type":"sitemap",
  "affected_url":"https://example.com/sitemap.xml",
  "description":"Important service URLs are not listed.",
  "severity":"high",
  "assigned_to":12
}
```

An executive can update an assigned issue. Only a manager can set `verified_closed`; this is where an external crawl or Search Console result should be attached as `evidence_url`.

## 14. On-page log buttons

| Button | Purpose | API |
|---|---|---|
| Save on-page log | Stores the before/after SEO work record | `POST /api/v1/seo/campaigns/{campaign}/on-page` |
| Evidence/live URL link | Opens the page used to verify the change | External URL |

Payload fields include `page_url`, `target_keyword`, old/new meta title, old/new meta description, `h1_changes`, `content_changes`, `alt_tag_changes`, `internal_link_changes`, `evidence_url`, `optimized_on`, and optional `seo_activity_id`.

This endpoint records work; it does not edit the target website. To automate deployment, add a separate client-website integration or CMS connector with explicit approval and audit logging.

## 15. Backlink buttons

| Button | Purpose | API |
|---|---|---|
| Add | Records an outreach/citation/guest-post activity | `POST /api/v1/seo/campaigns/{campaign}/backlinks` |
| View link | Opens the published URL | External URL |

Payload:

```json
{
  "activity_type":"Local business citation",
  "target_website":"https://directory.example",
  "target_url":"https://example.com/services",
  "published_url":"https://directory.example/listing/example",
  "anchor_text":"interior designers in Mumbai",
  "link_type":"nofollow",
  "verification_status":"submitted",
  "submitted_on":"2026-11-05",
  "live_on":null,
  "remarks":"Awaiting publication verification."
}
```

The current Blade form defaults link type to `dofollow`, but the API supports `dofollow`, `nofollow`, `sponsored`, `ugc`, and `unknown`. A backlink counts in a report only when it is `verified` and has a live date in the report period.

## 16. Analytics/performance buttons

| Button | Purpose | API |
|---|---|---|
| Save | Saves one period/metric value | `POST /api/v1/seo/campaigns/{campaign}/performance` |

Payload:

```json
{
  "period_start":"2026-11-01",
  "period_end":"2026-11-30",
  "source":"search_console",
  "metric_key":"organic_clicks",
  "metric_value":1250,
  "dimensions":{"country":"IND","device":"MOBILE"}
}
```

Allowed sources: `manual`, `ga4`, `search_console`, `import`. The record is upserted by campaign, period, source, and metric key. Recommended standard keys:

| Metric key | Source | Meaning |
|---|---|---|
| `organic_clicks` | Search Console | Google Search clicks |
| `organic_impressions` | Search Console | Search impressions |
| `average_ctr` | Search Console | Click-through rate |
| `average_position` | Search Console | Average search position |
| `organic_sessions` | GA4 | Organic sessions |
| `organic_users` | GA4 | Organic users |
| `conversions` | GA4 | Chosen conversion count |
| `indexed_pages` | Search Console/URL Inspection | Indexed URL count, if measured |
| `top_10_keywords` | Internal/rank provider | Number of tracked keywords in positions 1-10 |
| `core_web_vitals_lcp` | PageSpeed/CrUX | LCP measurement or summary |

## 17. Report buttons and workflow

| Button/action | Purpose | API |
|---|---|---|
| Create report | Creates a report draft and snapshots current data | `POST /api/v1/seo/campaigns/{campaign}/reports` |
| Submit report | Sends draft/changes-required report to manager | `POST /api/v1/seo/campaigns/{campaign}/reports/{report}/submit` |
| Approve report | Approves a submitted report | `POST /api/v1/seo/campaigns/{campaign}/reports/{report}/review` with `approved` |
| Request changes | Sends report back for correction | Same review endpoint with `changes_required` |
| Publish | Makes report visible to client | Same review endpoint with `published` |
| Attachment link | Downloads/opens XLSX, XLS, or PSD evidence | Public storage URL in current implementation |

Create report fields: title, period start/end, observations, optional monthly cycle, and up to 10 attachments. Each attachment is limited to 50 MB and currently accepts Excel or PSD.

```json
{
  "seo_monthly_cycle_id":15,
  "title":"November 2026 SEO Performance Report",
  "period_start":"2026-11-01",
  "period_end":"2026-11-30",
  "observations":"Organic visibility improved after metadata and technical fixes.",
  "attachments": []
}
```

For multipart upload, send text fields plus repeated `attachments[]` files. The report snapshot is generated at creation time. If performance data changes later, create a new report or add an explicit snapshot-refresh feature; the current code does not automatically refresh an existing snapshot.

## 18. Which external APIs should be used

Use a layered approach. Google APIs should be the source of truth for Google-owned data; use a specialist provider only where Google does not expose the required SEO dataset.

### Recommended minimum stack

| SEO requirement | Recommended API | Use it for | Priority |
|---|---|---|---|
| Search traffic and queries | Google Search Console API | Clicks, impressions, CTR, position, query/page/device/country breakdowns | Must have |
| GA4 traffic and conversions | Google Analytics Data API v1 | Organic sessions/users, landing pages, conversions, engagement metrics | Must have |
| Index inspection and sitemap status | Search Console URL Inspection + Sitemaps APIs | Inspect URL indexing, list/submit sitemap | Must have |
| Page performance | PageSpeed Insights API v5, plus CrUX API/History where needed | Lighthouse audits, performance, SEO audits, field data | Must have |
| Keyword volume/ideas | Google Ads API `KeywordPlanIdeaService` | Keyword ideas, average monthly searches, competition | Recommended |
| Reliable daily rank tracking | DataForSEO, Semrush, Ahrefs, or similar paid provider | Location/device-specific SERP positions | Recommended if rank automation is required |
| Backlink discovery | Ahrefs, Semrush, Majestic, or DataForSEO backlinks product | Referring domains, new/lost links, anchors | Optional; do not scrape Google |
| Website crawling | Screaming Frog scheduled export, Sitebulb, or a controlled crawler service | Broken links, canonicals, metadata, robots, sitemap, schema | Recommended for technical automation |
| CMS deployment | WordPress REST API, Shopify Admin API, or client-specific API | Apply approved metadata/content changes | Only when required |

### Why these APIs are the correct fit

The Search Console API exposes Search Analytics, Sitemaps, Sites, and URL Inspection services. GA4 reporting should use the Google Analytics Data API `runReport` method. PageSpeed Insights provides Lighthouse analysis and recommendations; Google recommends the CrUX APIs for current real-user data as CrUX data in PageSpeed is being discontinued. Google Ads' `KeywordPlanIdeaService` provides keyword ideas and historical metrics, but it is not a ranking API.

Official references:

- [Google Search Console API](https://developers.google.com/webmaster-tools)
- [Search Console API reference](https://developers.google.com/webmaster-tools/v1/api_reference_index)
- [Google Analytics Data API reports](https://developers.google.com/analytics/devguides/reporting/data/v1/basics)
- [PageSpeed Insights API](https://developers.google.com/speed/docs/insights/v5/get-started)
- [Google Ads keyword planning](https://developers.google.com/google-ads/api/docs/keyword-planning/overview)

## 19. External API to internal data mapping

### Search Console to `seo_performance_records`

Query Search Analytics by date range and dimensions such as `query`, `page`, `country`, and `device`. Aggregate or store dimensions in the `dimensions` JSON column. Save totals as metrics:

```text
clicks       -> organic_clicks
impressions  -> organic_impressions
ctr          -> average_ctr
position     -> average_position
```

Do not calculate average position as a simple average across arbitrary rows unless the aggregation method is understood. Prefer Search Console's returned aggregate row for campaign-level totals.

### GA4 to `seo_performance_records`

Call `runReport` with a date range and an organic-search filter. Typical mappings:

```text
sessions              -> organic_sessions
totalUsers            -> organic_users
conversions           -> conversions
engagementRate        -> engagement_rate
screenPageViews       -> organic_pageviews
```

Store the property ID, date range, filter, and query metadata in `dimensions` or a separate sync-log table.

### Search Console URL Inspection to `seo_technical_issues`

For each tracked target URL, call URL Inspection. If the result indicates not indexed, create/update a technical issue with `issue_type=indexing`, affected URL, severity, status, and the raw response reference. Never mark a fix verified merely because the API request succeeded; inspect the returned index verdict.

### PageSpeed/CrUX to `seo_technical_issues` and performance

For mobile and desktop, call PageSpeed with `strategy=mobile` and `strategy=desktop`. Save important audit failures as technical issues and save stable numeric metrics as performance records. Keep the raw response in a separate cache/object store rather than placing the whole response in a metric row.

### Google Ads Keyword Planner to `seo_keywords`

Use Keyword Planner only for discovery and volume/difficulty-like planning fields. It does not tell the current organic ranking. Store returned average monthly searches and competition in `search_volume` and `keyword_difficulty` only with a source/date record so users know those values are estimates.

### Rank provider to `seo_keyword_rankings`

For each campaign keyword, send keyword, language, country/city, device, and search engine to the provider. Save the returned position as a dated ranking snapshot with `source=provider_name`. Store provider job ID, SERP location, and result URL in `remarks` or a new `provider_metadata` JSON column.

## 20. Recommended integration architecture

Do not call external Google/provider APIs directly from a browser button. Use this server-side flow:

```text
User clicks Sync / Run audit
        |
        v
Laravel creates sync job and returns job ID
        |
        v
Queue worker calls external API
        |
        v
Validate, normalize, deduplicate, store raw response reference
        |
        v
Upsert internal SEO records
        |
        v
Create audit activity/log and notify user
        |
        v
Report uses stored, timestamped data
```

Recommended additions:

1. Add `seo_integrations` for campaign/provider/property IDs and encrypted credentials.
2. Add `seo_sync_runs` for provider, date range, status, request count, error, and timestamps.
3. Add queued jobs such as `SyncSearchConsoleJob`, `SyncGa4Job`, `SyncPageSpeedJob`, and `SyncRankingsJob`.
4. Add retry/backoff and provider rate-limit handling.
5. Add a manual `Sync now` button that only queues work; show `Queued`, `Running`, `Succeeded`, or `Failed` status.
6. Store UTC timestamps and display them in the configured local timezone.
7. Keep raw provider responses out of ordinary report rows; store them in object storage or a dedicated JSON table with retention rules.
8. Add idempotency keys so the same campaign/date/provider query does not create duplicates.

## 21. OAuth and credentials

For multi-client software, prefer Google OAuth per client/property rather than one shared service account. Store refresh tokens encrypted and never send them to the frontend. Required Google Cloud setup normally includes:

1. Create/select a Google Cloud project.
2. Enable Search Console API, Google Analytics Data API, PageSpeed Insights API, and, if needed, Google Ads API.
3. Configure OAuth consent screen and redirect URI.
4. Request only required scopes.
5. Store encrypted refresh token and selected Search Console property/GA4 property ID.
6. Verify the user has access to the exact property before saving the connection.
7. Refresh access tokens server-side.

Suggested environment variables:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
GOOGLE_PAGESPEED_API_KEY=
GOOGLE_ADS_DEVELOPER_TOKEN=
GOOGLE_ADS_LOGIN_CUSTOMER_ID=
SEO_SYNC_QUEUE=redis
```

Do not put client secrets, refresh tokens, Google Ads developer tokens, or third-party provider credentials in JavaScript or Blade templates.

## 22. Suggested new application buttons

The current UI is mostly manual. For automatic integration, add these buttons with role-aware visibility:

| New button | Location | Action |
|---|---|---|
| Connect Google | Campaign settings | Starts OAuth and selects Search Console/GA4 properties |
| Test connection | Integration settings | Calls a low-cost property read and confirms access |
| Sync Search Console | Analytics tab | Queues Search Console import for selected period |
| Sync GA4 | Analytics tab | Queues GA4 report import |
| Inspect URL | Technical tab | Queues URL Inspection for one affected URL |
| Run PageSpeed | Technical tab/on-page tab | Queues mobile/desktop PageSpeed checks |
| Sync rankings | Keywords tab | Queues provider rank checks |
| Generate keyword ideas | Keywords tab | Calls Google Ads API and presents suggestions for approval |
| Refresh report snapshot | Reports tab | Rebuilds a draft snapshot only; never silently changes published reports |
| Retry sync | Sync history | Retries failed provider job |

Every sync button should show the date range, source, last successful run, current job status, and error detail.

## 23. Implementation order

Use this order to reduce risk:

1. Add integration settings and encrypted credentials.
2. Add Search Console OAuth and a connection test.
3. Import Search Console aggregate metrics into `seo_performance_records`.
4. Add GA4 `runReport` synchronization.
5. Add URL Inspection and sitemap checks as technical issues.
6. Add PageSpeed/CrUX metrics and issue creation.
7. Add Google Ads keyword idea generation with an approval step before creating keywords.
8. Select one rank provider and add asynchronous ranking snapshots.
9. Add sync history, retries, rate-limit handling, and notifications.
10. Add report snapshot refresh for drafts and lock published snapshots.
11. Add CMS integrations only after the approval workflow is trusted.

## 24. Testing checklist

- Admin can create a campaign with at least one manager.
- Non-admin cannot create, archive, or restore campaigns.
- A client sees only their campaigns and published reports.
- An audit cannot be submitted without findings.
- An approved audit cannot be edited.
- A baseline cannot be captured before audit approval.
- An approved baseline cannot be changed.
- Duplicate keywords are rejected/skipped correctly.
- Ranking history is append-only and calculates improvement correctly.
- Only assigned executives/managers can update work.
- Only managers can verify technical issues closed.
- Approved activities are locked.
- Reports follow draft -> submitted -> approved -> published.
- A report cannot be published before approval.
- External API retries do not duplicate performance rows.
- A failed sync records a useful error and can be retried.
- Tokens and provider secrets never appear in API responses or logs.
- All external values show source and collection date.

## 25. Final recommendation

For this app, implement Google Search Console API and Google Analytics Data API first. They directly fill the current Analytics tab and provide the most reliable organic traffic/reporting data. Add Search Console URL Inspection and PageSpeed Insights next for Technical SEO. Add Google Ads Keyword Planner for keyword research. Use a paid rank-tracking and backlink provider only when automated rankings/backlink discovery are a real product requirement; neither Search Console nor GA4 is a general rank-tracking/backlink API.

Keep the existing internal SEO API as the application contract. External connectors should normalize their results into the existing campaign, ranking, performance, technical issue, and report models rather than exposing provider-specific response structures to the frontend.
