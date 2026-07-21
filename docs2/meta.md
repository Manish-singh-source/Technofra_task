Yes, you can assign the Facebook Page and ad account now. After assigning them, **generate a new System User token**. The permissions and asset access of your previously generated token should not be treated as automatically updated.

Meta’s setup flow is to assign assets such as Pages and ad accounts to the System User, then generate a token for the selected app and permissions. ([Facebook Developers][1])

## Follow this order

### 1. Assign the Facebook Page

Open:

```text
Meta Business Settings
→ Users
→ System Users
→ Select your System User
→ Assign assets
→ Facebook Pages
```

Select the Page that owns your Instant Forms and enable the available permissions related to:

```text
View Page
Manage Page
Access leads
Manage leads
```

The Page assignment is essential because the Instant Form belongs to the Facebook Page.

### 2. Assign the ad account

From the same System User screen:

```text
Assign assets
→ Ad accounts
→ Select your ad account
```

For reading attribution such as campaign, ad set and ad details, give access to view the ad account.

You need ad-account assignment when your Laravel application retrieves information such as:

```text
ad_id
ad_name
adset_id
adset_name
campaign_id
campaign_name
```

For basic form answers such as name, email and phone number, Page and lead access are the most important. Meta recommends assigning only the necessary assets to each System User. ([Facebook][2])

### 3. Assign Leads Access

Also configure:

```text
Meta Business Settings
→ Integrations
→ Leads Access
```

Select your Page and assign the relevant:

```text
System User
Meta App or CRM integration
```

A valid token can still fail to retrieve leads when the System User or CRM does not have Leads Access. Meta provides a dedicated Lead Access check for confirming whether a user or app can download leads. ([Facebook Developers][3])

### 4. Generate a new token

Return to:

```text
Business Settings
→ Users
→ System Users
→ Select System User
→ Generate new token
```

Select:

* Your Meta App
* `Never expires`, when available
* The required permissions

Use these permissions for your integration:

```text
leads_retrieval
pages_manage_metadata
pages_read_engagement
pages_show_list
ads_read
```

Use `ads_management` instead of `ads_read` only when your Laravel software will create, edit, publish or manage campaigns.

#### Permission purpose

| Permission              | Purpose                                                     |
| ----------------------- | ----------------------------------------------------------- |
| `leads_retrieval`       | Retrieve submitted lead information                         |
| `pages_manage_metadata` | Subscribe your app to the Page’s `leadgen` webhook          |
| `pages_read_engagement` | Read required Page-related information                      |
| `pages_show_list`       | Find or list Pages accessible to the token                  |
| `ads_read`              | Read ad, ad set and campaign information                    |
| `ads_management`        | Create or modify ads; unnecessary for read-only integration |

Meta’s Lead Ads documentation identifies `leads_retrieval` as the central permission for downloading leads. ([Facebook Developers][4])

### 5. Replace the old token in Laravel

Update:

```env
FACEBOOK_SYSTEM_USER_ACCESS_TOKEN=YOUR_NEW_SYSTEM_USER_TOKEN
```

Then clear Laravel’s cached configuration:

```bash
php artisan optimize:clear
php artisan config:cache
```

You can keep the old token temporarily until the new one is tested, but your Laravel production application should use the newly generated token.

## Test the updated setup

### Check the Page’s forms

```bash
curl -G \
"https://graph.facebook.com/v25.0/YOUR_PAGE_ID/leadgen_forms" \
--data-urlencode "fields=id,name,status" \
--data-urlencode "access_token=YOUR_NEW_SYSTEM_USER_TOKEN"
```

### Check a form’s leads

```bash
curl -G \
"https://graph.facebook.com/v25.0/YOUR_FORM_ID/leads" \
--data-urlencode "fields=id,created_time,ad_id,form_id,field_data" \
--data-urlencode "access_token=YOUR_NEW_SYSTEM_USER_TOKEN"
```

### Subscribe the Page to lead webhooks

```bash
curl -X POST \
"https://graph.facebook.com/v25.0/YOUR_PAGE_ID/subscribed_apps" \
-F "subscribed_fields=leadgen" \
-F "access_token=YOUR_NEW_SYSTEM_USER_TOKEN"
```

Expected response:

```json
{
    "success": true
}
```

## Correct final structure

```text
System User
├── Assigned Meta App
├── Assigned Facebook Page
├── Assigned Ad Account
├── Assigned Leads Access
└── Newly generated token
    ├── leads_retrieval
    ├── pages_manage_metadata
    ├── pages_read_engagement
    ├── pages_show_list
    └── ads_read
```

Therefore: **assign the Page and ad account now, configure Leads Access, and then generate a fresh System User token.**

[1]: https://developers.facebook.com/documentation/facebook-login/guides/access-tokens?utm_source=chatgpt.com "Access Tokens for Meta Technologies - Meta for Developers"
[2]: https://www.facebook.com/business/help/327596604689624?utm_source=chatgpt.com "About System Users in Meta Business Suite"
[3]: https://developers.facebook.com/docs/graph-api/reference/has-lead-access/?utm_source=chatgpt.com "Has Lead Access - Graph API - Meta for Developers"
[4]: https://developers.facebook.com/documentation/ads-commerce/marketing-api/guides/lead-ads/retrieving?utm_source=chatgpt.com "Retrieving Leads - Meta for Developers - Facebook"
