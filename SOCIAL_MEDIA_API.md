# Social Media API

Base URL: `/api/v1/social-media`
Authentication: Laravel Sanctum. Send `Authorization: Bearer {token}` and `Accept: application/json` to every endpoint. Responses use `{ "success": true, "data": ... }`; paginated responses also include `meta`.

This API mirrors the existing web flow: monthly planning, post creation and assignment, creative review, client approvals, scheduling, publishing, reporting, and festival master data.

The API is protected by the same authenticated route group as the application: `auth:sanctum` plus `active.user`.

## Roles and access

- Admin/manager: full access when the corresponding module permission is present or the user is a manager.
- Staff: assigned posts plus actions allowed by `view_social_media_posts`, `create_social_media_posts`, `edit_social_media_posts`, `delete_social_media_posts`, `review_social_media_posts`, `manage_social_content_plans`, `manage_social_publishing`, `manage_social_festival_events`, and `view_social_media_reports`.
- Admin/manager users: all posts, plans, queues, calendar events and reports within the relevant module permission. `/assigned` is an all-visible workspace for these users; it remains assignment-only for regular staff.
- Client/customer: only their non-draft plans and their own posts; they can comment and make client decisions.

## Dashboard and discovery

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/options` | Clients, staff, projects, statuses, post types, priorities and assignment roles |
| GET | `/dashboard?month=2026-11` | Status counts and calendar events |
| GET | `/calendar?month=2026-11` | Authorized post calendar events |
| GET | `/posts` | Paginated post list; filters: `status`, `priority`, `client_id`, `assigned_to`, `month`, `search`, `per_page` |
| GET | `/assigned` | Staff assignment queue; admin/manager users with full social-media visibility receive all visible posts |
| GET | `/queues/review` | Internal review queue |
| GET | `/queues/approvals` | Client approval queue |
| GET | `/publishing` | Publishing tracker |
| GET | `/reports?month=2026-11` | Monthly summary and authorized post rows |

`month` uses `YYYY-MM`; `per_page` is capped at 100. Dashboard, calendar, post list, reports, plans, and publishing endpoints apply supplied month/client filters to the authorized scope.

## Posts and workflow

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/posts` | Create a draft or assigned post |
| GET | `/posts/{post}` | Post detail with versions, comments, history and publishing records |
| PUT/PATCH | `/posts/{post}` | Update post fields, platforms and assignments |
| DELETE | `/posts/{post}` | Delete a post |
| POST | `/posts/{post}/start` | Assigned worker starts work |
| POST | `/posts/{post}/versions` | Upload creative version (`multipart/form-data`, `files[]`, optional `note`) |
| GET | `/posts/{post}/versions/{version}/files/{file}` | Authenticated version-file download |
| GET | `/posts/{post}/version-files/{file}/download` | Web-flow-compatible version-file download |
| GET | `/posts/{post}/version-files/{file}/preview` | Inline image/video preview for an authorized user |
| POST | `/posts/{post}/internal-decision` | `decision`: `approved` or `changes_requested` |
| POST | `/posts/{post}/client-decision` | `decision`: `approved`, `changes_requested`, or `rejected`; comment required for changes/rejection |
| POST | `/posts/{post}/comments` | Add comment; `is_internal` is staff-only and `parent_id` is optional |
| POST | `/posts/{post}/schedule` | Schedule approved content with `scheduled_at` |
| POST | `/posts/{post}/publish` | Confirm publication with `actual_published_at`, optional `published_url` and optional `proof` |

Create/update example:

```json
{
  "client_id": 12,
  "project_id": 8,
  "title": "Diwali greeting post",
  "content_category": "Festival",
  "post_type": "image",
  "priority": "high",
  "planned_at": "2026-11-08T10:00:00+05:30",
  "internal_deadline_at": "2026-11-05T18:00:00+05:30",
  "client_approval_deadline_at": "2026-11-07T12:00:00+05:30",
  "creative_brief": "Warm Diwali visual in brand colours.",
  "caption": "Wishing you a bright and prosperous Diwali.",
  "hashtags": "#Diwali #Festival",
  "platforms": ["Instagram", "Facebook", "LinkedIn"],
  "assignments": [
    {"user_id": 5, "role": "designer"},
    {"user_id": 7, "role": "reviewer"},
    {"user_id": 9, "role": "publisher"}
  ],
  "save_as_draft": false
}
```

Post status flow: `planned` → `assigned` → `in_progress` → `internal_review` → `pending_client_approval` → `client_approved` → `scheduled` → `published`. Revision branches use `internal_changes_requested` and `client_changes_requested`; terminal/paused statuses are `rejected`, `cancelled`, `on_hold`, and `completed`.

## Monthly content plans

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/plans` | Paginated plans |
| POST | `/plans` | Create/update one or multiple client plans using `client_id` or `client_ids[]` |
| GET | `/plans/{plan}` | Plan detail and linked posts |
| GET | `/plans/{plan}/posts/template` | Download the XLSX import template |
| PUT/PATCH | `/plans/{plan}` | Update plan metadata/status |
| DELETE | `/plans/{plan}` | Delete plan and linked posts |
| POST | `/plans/{plan}/client-decision` | Client decision: `approved`, `changes_requested`, or `not_required` |
| POST | `/plans/{plan}/posts/import` | Import XLS/XLSX/CSV as `multipart/form-data` with `file` |
| POST | `/plans/{plan}/posts/bulk-assign` | Assign `post_ids[]` to `user_ids[]` with one role |
| POST | `/plans/{plan}/posts/bulk-delete` | Delete selected `post_ids[]` belonging to the plan |

## Festival/event master

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/festivals` | Paginated festival list; optional `year` filter |
| POST | `/festivals` | Create event |
| GET | `/festivals/{festival}` | Event detail |
| PUT/PATCH | `/festivals/{festival}` | Update event |
| DELETE | `/festivals/{festival}` | Delete event |
| POST | `/festivals/import` | Seed/update the standard festival calendar, body `{ "year": 2026 }` |
| POST | `/festivals/bulk-delete` | Delete selected `festival_ids[]` |
| POST | `/festivals/delete-year` | Delete all events for `{ "year": 2026 }` |

The festival import/bulk-delete/delete-year actions are also available under `/plans/festivals/...` for clients that mirror the web Content Planning routes.

Festival fields: `name`, `event_date`, `category`, `country_region`, `recurrence_type` (`none` or `yearly`), optional `client_id`, `description`, and `reference_url`.

## Data and workflow notes

- Post create/update accepts `social_content_plan_id` and `social_festival_event_id`; the API verifies that the plan belongs to the selected client and that a linked festival belongs to that client and falls within the plan month.
- Assignments accept `{ "user_id": 5, "role": "designer" }`. Supported roles are `designer`, `writer`, `reviewer`, and `publisher`.
- Staff can open posts linked from the monthly planning workspace even when they are not individually assigned. Clients still only see their own posts.
- Version downloads/previews verify the post/version/file relationship, visibility scope, and stored file existence.
- `parent_id` comments must belong to the same post; clients cannot create internal comments.
- Bulk assignment accepts staff/manager users only, and bulk deletion rejects IDs outside the selected plan.

## Errors and uploads

- `401`: missing/invalid Sanctum token.
- `403`: outside the user's role, permission, or visibility scope.
- `404`: resource or protected version file not found.
- `422`: validation failure or invalid workflow transition.

Creative uploads are up to 50 MB each (`jpg`, `jpeg`, `png`, `webp`, `gif`, `mp4`, `mov`, `webm`, `pdf`). Publishing proof is up to 20 MB (`jpg`, `jpeg`, `png`, `webp`, `pdf`).
