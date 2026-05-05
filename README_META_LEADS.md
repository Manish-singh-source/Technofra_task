# Meta Lead Ads API Integration

## 1. Overview
This integration adds Meta (Facebook) Lead Ads support to the Laravel application with:
- Real-time webhook ingestion for new leads
- Manual and scheduled lead sync from a Meta Lead Form
- Persistent lead storage in a dedicated `meta_leads` table
- Config-driven setup using environment variables

## 2. Prerequisites
- A Meta Business Manager account
- A Facebook Page connected to your Lead Ads form
- A Meta App created in [developers.facebook.com](https://developers.facebook.com/)
- The app configured with Webhooks product and required permissions for Lead Ads

Basic Meta setup flow:
1. Create an app in Meta for Developers.
2. Add the Webhooks product.
3. Subscribe the Page to the app.
4. Subscribe to the `leadgen` field for the Page object.
5. Generate a Page Access Token with required permissions.

## 3. Installation
Install the Facebook Business SDK:

```bash
composer require facebook/php-business-sdk
```

## 4. Environment Variables
Add the following keys to your environment:

| Key | Description |
| --- | --- |
| `FACEBOOK_APP_ID` | Meta App ID |
| `FACEBOOK_APP_SECRET` | Meta App Secret |
| `FACEBOOK_PAGE_ID` | Facebook Page ID receiving leads |
| `FACEBOOK_PAGE_ACCESS_TOKEN` | Long-lived Page Access Token used for Graph API calls |
| `FACEBOOK_WEBHOOK_VERIFY_TOKEN` | Verify token used during webhook verification handshake |
| `FACEBOOK_FORM_ID` | Default Lead Form ID used by scheduled/manual sync |
| `FACEBOOK_GRAPH_API_VERSION` | Graph API version (default: `v20.0`) |

## 5. How to Get Page Access Token
1. Open Graph API Explorer and generate a User Access Token with lead-related permissions (for example, `leads_retrieval` and relevant page permissions).
2. Exchange the short-lived user token for a long-lived user token.
3. Use the long-lived user token to fetch a Page Access Token for the target page.
4. Save that Page Access Token in `FACEBOOK_PAGE_ACCESS_TOKEN`.

## 6. Run Migration
Run:

```bash
php artisan migrate
```

## 7. Webhook Setup
1. In Meta Developer Dashboard, go to your app > Webhooks.
2. Add a callback URL:
   - `https://your-domain.com/api/facebook/webhook`
3. Set verify token to match `FACEBOOK_WEBHOOK_VERIFY_TOKEN`.
4. Subscribe the Page object to the `leadgen` field.

## 8. Testing the Webhook
Use Meta Lead Ads Testing Tool:
1. Open your Lead Form in Meta Lead Ads Testing Tool.
2. Submit a test lead.
3. Confirm your app receives `EVENT_RECEIVED` response.
4. Check `meta_leads` table for stored/updated records.

## 9. Manual Sync
Sync with default form ID:

```bash
php artisan meta:sync-leads
```

Sync with an explicit form ID:

```bash
php artisan meta:sync-leads --form_id=123
```

## 10. Scheduler
Hourly scheduler registration has been added for command-based sync.

Ensure your server cron is running Laravel scheduler:

```bash
php artisan schedule:run
```

Recommended cron entry (every minute) should invoke Laravel scheduler so hourly tasks run on schedule.

## 11. Files Created
### New files
- `database/migrations/2026_05_05_000000_create_meta_leads_table.php` - Creates `meta_leads` table schema.
- `app/Models/MetaLead.php` - Eloquent model for lead records.
- `config/meta.php` - Meta integration config from environment.
- `app/Services/MetaLeadService.php` - Lead fetch/sync logic and persistence.
- `app/Http/Controllers/FacebookWebhookController.php` - Webhook verify + webhook event handler.
- `app/Console/Commands/SyncMetaLeads.php` - Manual sync Artisan command.
- `README_META_LEADS.md` - Integration documentation.

### Existing files modified
- `routes/api.php` - Added webhook routes under `/api/facebook/webhook`.
- `.env.example` - Added Meta Lead Ads environment keys.
- `app/Http/Middleware/VerifyCsrfToken.php` - Excluded webhook endpoint from CSRF verification.
- `app/Console/Kernel.php` - Added hourly scheduler for `meta:sync-leads`.

## 12. Troubleshooting
- Token expired:
  - Regenerate and update `FACEBOOK_PAGE_ACCESS_TOKEN`.
- 403 on webhook verification:
  - Verify `FACEBOOK_WEBHOOK_VERIFY_TOKEN` matches Meta dashboard token exactly.
  - Ensure callback URL points to `/api/facebook/webhook`.
- Leads not appearing:
  - Confirm Page subscription includes `leadgen`.
  - Confirm correct form ID in `FACEBOOK_FORM_ID`.
  - Check logs for Graph API errors and permission issues.
  - Run manual sync command to validate API/token/form configuration.

## 13. How to Find `FACEBOOK_PAGE_ID`
Use any one of these methods:

1. From your Facebook Page URL:
   - Open your page in browser.
   - If URL is like `https://www.facebook.com/{page-username}`, copy the username and resolve numeric ID using Graph API:
   - `GET https://graph.facebook.com/v20.0/{page-username}?access_token={YOUR_PAGE_ACCESS_TOKEN}`
   - The `id` in response is your `FACEBOOK_PAGE_ID`.

2. From Graph API Explorer:
   - Open Graph API Explorer.
   - Run `GET /me/accounts` with a user token that can access your page.
   - Find your page in response; copy its `id`.

3. From Meta Business settings (if visible):
   - Open the Page in Business settings.
   - Page details often show the Page ID directly.

## 14. About `FACEBOOK_WEBHOOK_VERIFY_TOKEN`
`FACEBOOK_WEBHOOK_VERIFY_TOKEN` is not shown anywhere in Meta because Meta does not generate it for you.  
You create this value yourself and use the exact same value in both Laravel and Meta.

Steps:
1. Generate a secure random string (example: `meta_verify_9f3kL2xP7...`).
2. Add it in your environment:
   - `FACEBOOK_WEBHOOK_VERIFY_TOKEN=your_random_string`
3. In Meta Developers dashboard:
   - Open your app > Webhooks > Page > Edit Callback URL.
   - Callback URL: `https://your-domain.com/api/facebook/webhook`
   - Verify token: paste the same string.
4. Save/Verify.

If verification fails:
- Ensure your callback URL is public and HTTPS.
- Ensure token matches exactly (no extra spaces).
- Ensure your `GET /api/facebook/webhook` route is active and returns the challenge on successful token match.
