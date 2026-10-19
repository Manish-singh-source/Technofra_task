# Lead Alert Notifications

Lead alerts are sent to the lead's assigned staff and all admin/super-admin users.

Alert types:

- Overdue follow-up
- Follow-up due today
- High priority lead

The scheduled command is:

```bash
php artisan leads:send-alerts
```

It runs hourly through `app/Console/Kernel.php`. Configure the server cron to run Laravel's scheduler every minute:

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

Each recipient receives:

1. A database/in-app notification.
2. An email using `resources/views/emails/lead-alert.blade.php`.
3. A WhatsApp template message when K3 WhatsApp credentials and a phone number are available.
4. A Firebase push notification when Firebase project credentials and an active FCM token are available.

The notification is deduplicated per lead, recipient, alert type and day.

## Email configuration

Use the normal Laravel mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-user
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=crm@example.com
MAIL_FROM_NAME="MyCRM"
```

## WhatsApp configuration

The provider is the existing K3/Pinbot integration:

```env
K3_WHATSAPP_BASE_URL=https://partnersv1.pinbot.ai
K3_WHATSAPP_BUSINESS_ID=your-business-id
K3_WHATSAPP_API_KEY=your-api-key
K3_WHATSAPP_DEFAULT_COUNTRY_CODE=91
K3_WHATSAPP_DEFAULT_LANGUAGE=en
K3_WHATSAPP_LEAD_ALERT_TEMPLATE=lead_alert
```

Create and approve this WhatsApp Business template with the exact body variable order:

```text
Lead alert: {{1}} requires attention.
Alert: {{2}}
Priority: {{3}}
Next follow-up: {{4}}
Open CRM: {{5}}
```

The five variables sent by the application are:

1. Lead name
2. Alert label
3. Priority
4. Next follow-up date/time or `No next action`
5. Lead detail URL

The template must be approved by WhatsApp before production delivery. Phone numbers are normalized using the configured default country code.

## Firebase configuration

Firebase is optional. To enable push delivery:

```env
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_CREDENTIALS=C:/absolute/path/firebase-service-account.json
```

The mobile app must register an FCM token through the existing authenticated endpoint:

```http
POST /api/v1/fcm-token
```

If Firebase is not configured, email, WhatsApp and in-app delivery continue independently.

## Priority behavior

The Lead Management page supports `high`, `medium` and `low` priority. High priority leads are highlighted and sorted above medium and low priority leads. Alert delivery for high priority leads happens once per recipient per day.

## Manual test

Run the alert command manually after configuring the providers:

```bash
php artisan leads:send-alerts
```

The command output shows the number of recipient deliveries attempted. Provider failures are logged and do not stop alerts for other recipients.
