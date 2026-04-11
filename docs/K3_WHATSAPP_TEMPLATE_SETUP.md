# K3 WhatsApp Template Setup for Calendar Appointments

## 1) `.env` Configuration

Add these keys in your `.env`:

```env
K3_WHATSAPP_BASE_URL=https://partnersv1.pinbot.ai
K3_WHATSAPP_BUSINESS_ID=1049973098188941
K3_WHATSAPP_API_KEY=your_k3_api_key
K3_WHATSAPP_DEFAULT_COUNTRY_CODE=91
K3_WHATSAPP_DEFAULT_LANGUAGE=en
K3_WHATSAPP_REMINDER_TEMPLATE=calendar_appointment_reminder
K3_WHATSAPP_EVENT_TIME_TEMPLATE=calendar_appointment_reminder
```

Then run:

```bash
php artisan config:clear
php artisan cache:clear
```

## 2) Template Creation in K3

Create a template with:

- `Template Name`: `calendar_appointment_reminder` (or your custom name)
- `Category`: Utility
- `Language`: `en`
- `Body Variables`: 3 placeholders

Suggested template body:

`Hello, your meeting "{{1}}" is scheduled on "{{2}}" at "{{3}}".`

This project sends:

- `{{1}}` = event title
- `{{2}}` = event date (`d M Y`)
- `{{3}}` = event time (`h:i A`, with status suffix)

## 3) API Payload Format Used by Project

The app sends this structure to K3:

```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "919876543210",
  "type": "template",
  "template": {
    "name": "calendar_appointment_reminder",
    "language": { "code": "en" },
    "components": [
      {
        "type": "body",
        "parameters": [
          { "type": "text", "text": "Meeting Title" },
          { "type": "text", "text": "17 Feb 2026" },
          { "type": "text", "text": "02:00 PM" }
        ]
      }
    ]
  }
}
```

## 4) How Scheduling Works

- Event-time alert: sent at selected time
- Scheduler frequency: every minute

Run scheduler and queue worker:

```bash
php artisan schedule:work
php artisan queue:work
```

## 5) Manual Test Command

```bash
php artisan whatsapp:test 919876543210 --template=calendar_appointment_reminder --param="Demo Meeting" --param="17 Feb 2026" --param="02:00 PM"
```

## 6) Dashboard Usage

Go to `/dashboard`:

1. Open `Add Appointments`
2. Set date/time
3. Add WhatsApp recipients (comma-separated)
4. Save appointment

Message will auto-send based on selected time.
