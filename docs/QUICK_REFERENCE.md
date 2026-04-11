# WhatsApp Notification - Quick Reference Card

## 🚀 Quick Start (5 Steps)

### 1️⃣ Get Twilio Credentials
```
1. Go to: https://www.twilio.com/
2. Sign up (Free)
3. Get: Account SID + Auth Token
4. Join WhatsApp Sandbox: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
5. Send join code to: +1 415 523 8886
```

### 2️⃣ Update .env File
```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=+14155238886
```

### 3️⃣ Setup Task Scheduler (Windows)
```
1. Open Task Scheduler
2. Create Basic Task: "Laravel Scheduler"
3. Program: C:\xampp\php\php.exe
4. Arguments: C:\xampp\htdocs\Technofra-Renewal\artisan schedule:run
5. Repeat: Every 1 minute
```

### 4️⃣ Start Queue Worker
```bash
cd C:\xampp\htdocs\Technofra-Renewal
php artisan queue:work
```

### 5️⃣ Test It!
```bash
php artisan whatsapp:test +919876543210
```

---

## 📱 Phone Number Format

✅ **Correct:**
- `+919876543210`
- `+91 9876543210`
- `+919876543210, +919876543211`

❌ **Wrong:**
- `9876543210` (missing country code)
- `919876543210` (missing +)

---

## 🔔 Notification Timeline

**Example: Event at 2:00 PM**

| Time | Action |
|------|--------|
| 1:50 PM | 📧 Email + 📱 WhatsApp (10-min reminder) |
| 2:00 PM | 📧 Email + 📱 WhatsApp (Event notification) |

---

## 🧪 Testing Commands

### Test WhatsApp
```bash
php artisan whatsapp:test +919876543210
```

### Test with Custom Message
```bash
php artisan whatsapp:test +919876543210 --message="Hello!"
```

### Manual Notification Check
```bash
php artisan calendar:send-notifications
```

### Check Queue
```bash
php artisan queue:work --once
```

### View Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

---

## 📊 Database Fields

| Field | Purpose |
|-------|---------|
| `whatsapp_recipients` | Phone numbers (comma-separated) |
| `reminder_10min_sent` | 10-min reminder sent? (true/false) |
| `reminder_10min_sent_at` | When was 10-min reminder sent? |
| `event_time_notification_sent` | Event notification sent? (true/false) |
| `event_time_notification_sent_at` | When was event notification sent? |

---

## 🔧 Troubleshooting

### WhatsApp not working?
```bash
# 1. Check credentials
cat .env | grep TWILIO

# 2. Check logs
type storage\logs\laravel.log

# 3. Test manually
php artisan whatsapp:test +919876543210

# 4. Verify queue is running
php artisan queue:work --once
```

### Notifications not sending?
```bash
# 1. Check scheduler
php artisan schedule:list

# 2. Run manually
php artisan calendar:send-notifications

# 3. Check event status
# Go to phpMyAdmin → calendar_events table
```

### Queue not processing?
```bash
# 1. Start queue worker
php artisan queue:work

# 2. Check failed jobs
php artisan queue:failed

# 3. Retry all
php artisan queue:retry all

# 4. Clear queue
php artisan queue:flush
```

---

## 📝 Important Notes

### Twilio Sandbox Limitations:
- ⚠️ Only works with numbers that joined sandbox
- ⚠️ Each user must send join code
- ⚠️ Messages have "Twilio Sandbox" prefix
- ⚠️ Limited free messages per day

### Production Setup:
- ✅ Apply for WhatsApp Business API
- ✅ Get your own WhatsApp number
- ✅ No sandbox prefix
- ✅ Higher message limits

---

## 📞 Support Files

| File | Purpose |
|------|---------|
| `WHATSAPP_NOTIFICATION_SETUP.md` | Detailed English guide |
| `WHATSAPP_SETUP_HINDI.md` | Detailed Hindi guide |
| `IMPLEMENTATION_SUMMARY.md` | Technical implementation details |
| `QUICK_REFERENCE.md` | This file |

---

## ✅ Checklist

Before going live:

- [ ] Twilio account created
- [ ] Credentials added to `.env`
- [ ] WhatsApp sandbox joined
- [ ] Task Scheduler configured
- [ ] Queue worker running
- [ ] Test message sent successfully
- [ ] Test event created
- [ ] 10-min reminder received
- [ ] Event notification received

---

## 🎯 Key Points

1. **Two Notifications**: 10 minutes before + Event time
2. **Both Channels**: Email + WhatsApp
3. **Separate Tracking**: Each notification tracked independently
4. **Queue System**: Asynchronous processing
5. **Auto Scheduling**: Runs every minute via scheduler

---

## 💡 Pro Tips

1. **Keep queue worker running** - Use Windows Service or NSSM
2. **Monitor logs** - Check `storage/logs/laravel.log` regularly
3. **Test first** - Always test with sandbox before production
4. **Phone format** - Always use international format (+91...)
5. **Sandbox join** - All recipients must join sandbox for testing

---

## 🔗 Useful Links

- Twilio Console: https://console.twilio.com/
- WhatsApp Sandbox: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
- Twilio Docs: https://www.twilio.com/docs/whatsapp
- Laravel Queue: https://laravel.com/docs/10.x/queues
- Laravel Scheduler: https://laravel.com/docs/10.x/scheduling

---

**Last Updated**: 2026-01-17  
**Version**: 1.0  
**Status**: ✅ Production Ready

