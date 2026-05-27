# Notification Settings Usage Map

This document explains the **Settings > Notifications** section, where each setting is stored, and where it is used in runtime.

## Settings UI Location

- View file: `resources/views/settings/index.blade.php`
- Tab block: around lines `734-856`
- Form action for Notification tab: `route('settings.update.renewal')`
- Controller method handling save: `App\Http\Controllers\SettingController@updateRenewal`

Important behavior:
- Notification tab and Renewal tab both submit to the same update endpoint.
- Notification tab sends hidden fields for:
  - `renewal_admin_email`
  - `renewal_notification_time`
  - `renewal_notice_days`
- So saving Notification tab also re-saves these renewal fields.

## Route + Save Flow

### Web route
- `routes/web.php:114-123`
  - `PUT /settings/renewal` -> `SettingController@updateRenewal`

### Save method
- `app/Http/Controllers/SettingController.php:295-364`
- Validates and stores these keys using `Setting::set(..., 'text')`:
  - `renewal_admin_email`
  - `renewal_notification_time`
  - `renewal_notice_days`
  - `renewal_notifications_enabled`
  - `auto_calendar_event_email_enabled`
  - `auto_calendar_event_whatsapp_enabled`
  - `auto_todo_reminder_email_enabled`
  - `auto_todo_reminder_whatsapp_enabled`

## Key-by-Key Usage

### 1) `renewal_notifications_enabled`
- Saved from Settings Notification/Renewal tab.
- Runtime usage:
  - `app/Console/Commands/SendNotificationEmails.php:39-43`
- Effect:
  - If off (`0/false/off/no`), daily renewal summary email command exits without sending.

### 2) `renewal_admin_email`
- Saved from Settings.
- Runtime usage:
  - `app/Console/Commands/SendNotificationEmails.php:45-56`
- Effect:
  - Primary recipient for daily renewal summary email.
  - Fallback order:
    1. `renewal_admin_email`
    2. `company_email` setting
    3. `ADMIN_EMAIL` env

### 3) `renewal_notice_days`
- Saved from Settings.
- Runtime usage:
  - `app/Console/Commands/SendNotificationEmails.php:58-77`
- Effect:
  - Sets upcoming renewal window (`today` to `today + notice_days`) for both `services` and `vendor_services`.

### 4) `renewal_notification_time`
- Saved from Settings.
- Runtime usage:
  - Scheduler reads it in `app/Console/Kernel.php:23-33`
- Effect:
  - Controls what time `notifications:send-daily` runs.
  - Defaults to `16:00` if invalid/unavailable.

### 5) `auto_calendar_event_email_enabled`
- Saved from Settings Notification tab.
- Runtime usage:
  - `app/Jobs/SendCalendarEventNotification.php:55-64`
  - `app/Jobs/SendEventTimeNotification.php:52-56`
  - `app/Jobs/Send10MinReminderNotification.php:52-56`
- Effect:
  - Master email toggle for calendar event notifications (regular/event-time/10-min reminder flows).

### 6) `auto_calendar_event_whatsapp_enabled`
- Saved from Settings Notification tab.
- Runtime usage:
  - `app/Jobs/SendEventTimeNotification.php:57-61`
  - `app/Jobs/Send10MinReminderNotification.php:57-61`
- Effect:
  - Master WhatsApp toggle for event-time and 10-min reminder WhatsApp sends.
- Note:
  - `SendCalendarEventNotification` currently only handles email, not WhatsApp.

### 7) `auto_todo_reminder_email_enabled`
- Saved from Settings Notification tab.
- Runtime usage:
  - `app/Console/Commands/SendTodoReminderEmails.php:24-33,49-53`
- Effect:
  - Global gate for todo reminder emails.
  - Per-todo flag `todo->reminder_email` must also be true.

### 8) `auto_todo_reminder_whatsapp_enabled`
- Saved from Settings Notification tab.
- Runtime usage:
  - `app/Console/Commands/SendTodoReminderEmails.php:29-33,49-53`
- Effect:
  - Global gate for todo reminder WhatsApp.
  - Per-todo flag `todo->reminder_whatsapp` must also be true.

## Scheduler Wiring

From `app/Console/Kernel.php`:
- `calendar:send-notifications` -> every minute
- `todos:send-reminders` -> every minute
- `notifications:send-daily` -> daily at `renewal_notification_time`

So these settings affect behavior only when scheduler/queue workers are running.

## Related Notification Endpoints (Dashboard bell/feed)

- Web notification routes: `routes/web.php:391-401`
- Controller: `app/Http/Controllers/NotificationController.php`
- Data source: `app/Services/NotificationService.php`

Important:
- `NotificationService` builds renewal alerts based on `services.end_date` and read tracking.
- It does **not** read the above settings toggles directly.
- Those toggles mainly control **automatic sending jobs/commands**, not feed generation.

## API Side (same keys)

- `app/Http/Controllers/Api/SettingController.php` also reads/writes the same renewal/notification keys via `/api/v1/settings/renewal`.
- So Web and API settings remain aligned in the same `settings` table keys.

## Quick Conclusion

The Notification settings are actively used in production paths:
- Daily renewal emails: fully controlled by renewal keys.
- Calendar auto notifications: controlled by calendar email/WhatsApp toggles.
- Todo auto reminders: controlled by todo email/WhatsApp toggles.

No dead key was found in this section; all 8 saved keys are consumed by at least one runtime command/job.
