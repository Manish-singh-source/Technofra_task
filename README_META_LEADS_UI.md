# Meta Leads UI

## 1. Overview
This UI adds a Meta leads management flow on top of `meta_leads`:
- Leads list page with search, date/form filters, pagination, and manual sync trigger
- Lead detail page with contact/meta cards, raw `field_data`, copy lead ID, and delete action

## 2. Routes Added
| Method | URI | Name | Description |
| --- | --- | --- | --- |
| GET | `/leads/meta` | `leads.index` | Meta leads list with filters and pagination |
| GET | `/leads/meta/{lead}` | `leads.show` | Meta lead detail page |
| POST | `/leads/meta/sync` | `leads.sync` | Manual sync trigger from Meta |
| DELETE | `/leads/meta/{lead}` | `leads.destroy` | Delete a stored Meta lead |

## 3. Layout Detection
Detected and used layout: `resources/views/layout/master.blade.php`  
Views extend with: `@extends('layout.master')`

## 4. CSS Framework Detected
Detected framework: **Bootstrap 5-style admin UI** (cards, table classes, badges, buttons, breadcrumb patterns used in existing views).

## 5. How to Access
- List page: `/leads/meta`
- Detail page: `/leads/meta/{lead_id_bound_record}`

## 6. Pagination Styling
The list uses Laravel paginator: `{{ $leads->links() }}`.  
If pagination style does not match Bootstrap in your environment, run:

```bash
php artisan vendor:publish --tag=laravel-pagination
```

## 7. Auth Protection
New routes were appended inside the existing `Route::middleware('auth')->group(...)`, matching the current project pattern.  
Per-route permissions are also applied:
- `view_leads` for list/detail
- `create_leads` for sync
- `delete_leads` for delete

## 8. Files Created
- `app/Http/Controllers/MetaLeadUiController.php` - Controller for Meta leads list/detail/sync/delete.
- `resources/views/leads/index.blade.php` - Meta leads list page.
- `resources/views/leads/show.blade.php` - Meta lead detail page.
- `README_META_LEADS_UI.md` - This implementation guide.

## 9. Files Modified
- `routes/web.php` - Appended Meta leads UI route group under the `auth` block.

## 10. Screenshots (Placeholder)
- Add list page screenshot here
- Add detail page screenshot here
- Add filter/sync interaction screenshot here
