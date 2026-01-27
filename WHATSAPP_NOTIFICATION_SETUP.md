# WhatsApp Notification Setup Guide for Calendar Events

## 📋 Overview

This system sends **both Email and WhatsApp notifications** for calendar events at **two different times**:
1. **10 minutes before** the event time (Reminder)
2. **At the exact event time** (Event notification)

---

## 🚀 Features Implemented

✅ WhatsApp notifications via Twilio API  
✅ Dual notification timing (10-min reminder + event-time)  
✅ Email + WhatsApp combined notifications  
✅ Phone number validation  
✅ Separate tracking for each notification type  
✅ Updated UI with WhatsApp phone number input  
✅ Automatic scheduling via Laravel scheduler  

---

## 📝 Setup Instructions

### Step 1: Get Twilio WhatsApp API Credentials

1. **Create a Twilio Account**
   - Go to https://www.twilio.com/
   - Sign up for a free account
   - Verify your email and phone number

2. **Get Your Credentials**
   - Go to Twilio Console: https://console.twilio.com/
   - Find your **Account SID** and **Auth Token**
   - Copy these values

3. **Enable WhatsApp Sandbox** (For Testing)
   - Go to: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
   - Follow instructions to join the WhatsApp Sandbox
   - Send the code to the Twilio WhatsApp number from your phone
   - Note the sandbox number (usually starts with +1415...)

4. **For Production** (Optional - requires approval)
   - Request WhatsApp Business API access
   - Get your own WhatsApp Business number approved
   - This process can take several days

### Step 2: Configure .env File

Open your `.env` file and update the Twilio credentials:

```env
# Twilio WhatsApp Configuration
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=+14155238886
```

**Important Notes:**
- Replace `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx` with your actual Account SID
- Replace `your_auth_token_here` with your actual Auth Token
- For sandbox testing, use `+14155238886` (Twilio's sandbox number)
- For production, use your approved WhatsApp Business number

### Step 3: Run Database Migration

The migration has already been run, but if you need to run it again:

```bash
php artisan migrate
```

This adds the following fields to `calendar_events` table:
- `whatsapp_recipients` - Comma-separated phone numbers
- `reminder_10min_sent` - Boolean flag for 10-min reminder
- `reminder_10min_sent_at` - Timestamp when 10-min reminder was sent
- `event_time_notification_sent` - Boolean flag for event-time notification
- `event_time_notification_sent_at` - Timestamp when event-time notification was sent

### Step 4: Setup Laravel Scheduler

The system uses Laravel's task scheduler to check for notifications every minute.

**For Windows (XAMPP):**

1. Open Task Scheduler (search "Task Scheduler" in Windows)
2. Click "Create Basic Task"
3. Name: "Laravel Scheduler - Technofra"
4. Trigger: Daily at 12:00 AM
5. Action: Start a program
6. Program: `C:\xampp\php\php.exe`
7. Arguments: `C:\xampp\htdocs\Technofra-Renewal\artisan schedule:run`
8. Click "Finish"
9. Right-click the task → Properties
10. In "Triggers" tab, click "Edit"
11. Check "Repeat task every: 1 minute"
12. Duration: Indefinitely
13. Click OK

**For Linux/Mac:**

Add to crontab:
```bash
* * * * * cd /path/to/Technofra-Renewal && php artisan schedule:run >> /dev/null 2>&1
```

### Step 5: Start Queue Worker

The notifications are sent via queued jobs for better performance.

```bash
php artisan queue:work
```

**For production, use a process manager like Supervisor:**

Create file: `/etc/supervisor/conf.d/technofra-worker.conf`
```ini
[program:technofra-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/Technofra-Renewal/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/Technofra-Renewal/storage/logs/worker.log
```

Then run:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start technofra-worker:*
```

---

## 📱 How to Use

### Creating a Calendar Event with WhatsApp Notifications

1. Go to Dashboard (`/dashboard`)
2. Click "Add Event" button in the Calendar section
3. Fill in the event details:
   - **Title**: Event name
   - **Description**: Event details (optional)
   - **Date**: Event date
   - **Time**: Event time
   - **Email Recipients**: Comma-separated emails (required)
   - **WhatsApp Recipients**: Comma-separated phone numbers with country code (optional)

**Phone Number Format Examples:**
- Single: `+919876543210`
- Multiple: `+919876543210, +919876543211, +919876543212`
- With spaces: `+91 9876543210, +91 9876543211`

4. Click "Save Event"

### Notification Timeline

For an event scheduled at **2:00 PM**:
- **1:50 PM** → 10-minute reminder sent (Email + WhatsApp)
- **2:00 PM** → Event-time notification sent (Email + WhatsApp)

---

## 🧪 Testing

### Test WhatsApp Notification Manually

```bash
php artisan calendar:test-notification {event_id}
```

Replace `{event_id}` with the actual event ID.

### Check Scheduled Notifications

```bash
php artisan calendar:send-notifications
```

This command runs automatically every 5 minutes via the scheduler.

---

## 📊 Monitoring

### Check Logs

```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
- `10-min reminder WhatsApp sent: X success, Y failed`
- `Event-time notification WhatsApp sent: X success, Y failed`

### Database Check

```sql
SELECT id, title, event_date, event_time, 
       reminder_10min_sent, event_time_notification_sent 
FROM calendar_events 
WHERE status = 1 
ORDER BY event_date DESC;
```

---

## ⚠️ Important Notes

1. **Twilio Sandbox Limitations:**
   - Only works with numbers that have joined the sandbox
   - Each user must send the join code to Twilio's WhatsApp number
   - Messages have "Sent from your Twilio Sandbox" prefix

2. **Production WhatsApp:**
   - Requires Twilio WhatsApp Business API approval
   - Can take 1-2 weeks for approval
   - Costs apply based on usage

3. **Phone Number Format:**
   - Always include country code (e.g., +91 for India)
   - System auto-adds +91 if missing (configurable in WhatsAppService.php)

4. **Rate Limits:**
   - Twilio has rate limits on WhatsApp messages
   - Free tier: Limited messages per day
   - Paid tier: Higher limits

---

## 🔧 Troubleshooting

### WhatsApp messages not sending?

1. Check Twilio credentials in `.env`
2. Verify phone numbers have joined sandbox
3. Check logs: `storage/logs/laravel.log`
4. Ensure queue worker is running
5. Test Twilio API directly via Postman

### Notifications not triggering?

1. Verify scheduler is running (Task Scheduler/Cron)
2. Check event date/time is in the future
3. Ensure event status is active
4. Run manually: `php artisan calendar:send-notifications`

### Queue not processing?

1. Start queue worker: `php artisan queue:work`
2. Check failed jobs: `php artisan queue:failed`
3. Retry failed jobs: `php artisan queue:retry all`

---

## 📞 Support

For issues or questions, check:
- Twilio Documentation: https://www.twilio.com/docs/whatsapp
- Laravel Queue Documentation: https://laravel.com/docs/10.x/queues
- Laravel Scheduler Documentation: https://laravel.com/docs/10.x/scheduling

