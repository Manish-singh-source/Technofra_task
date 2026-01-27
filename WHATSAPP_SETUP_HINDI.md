# WhatsApp Notification Setup - हिंदी गाइड

## 📋 क्या बनाया गया है?

अब Calendar Events के लिए **Email और WhatsApp दोनों** पर notification आएगी, **दो बार**:
1. **Event से 10 मिनट पहले** (Reminder)
2. **Event के समय पर** (Event Notification)

---

## 🚀 Setup कैसे करें?

### Step 1: Twilio Account बनाएं

1. **Twilio पर जाएं**: https://www.twilio.com/
2. **Sign Up** करें (Free account)
3. Email और Phone verify करें
4. Console में जाएं: https://console.twilio.com/
5. **Account SID** और **Auth Token** copy करें

### Step 2: WhatsApp Sandbox Join करें (Testing के लिए)

1. यहाँ जाएं: https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
2. Screen पर दिखाया गया code copy करें (जैसे: "join abc-xyz")
3. अपने WhatsApp से इस number पर भेजें: **+1 415 523 8886**
4. Code भेजें (जैसे: "join abc-xyz")
5. Confirmation message आएगा

**Important**: जिन लोगों को WhatsApp notification भेजनी है, उन सभी को यह process करना होगा।

### Step 3: .env File Update करें

File खोलें: `c:\xampp\htdocs\Technofra-Renewal\.env`

नीचे दिए गए lines में अपनी details डालें:

```env
TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=+14155238886
```

**Replace करें:**
- `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx` → आपका Account SID
- `your_auth_token_here` → आपका Auth Token
- `+14155238886` → Testing के लिए यही रखें (Twilio sandbox number)

### Step 4: Task Scheduler Setup करें (Windows)

यह जरूरी है ताकि notifications automatically check हों।

1. Windows में **Task Scheduler** खोलें
2. **Create Basic Task** पर click करें
3. Name: `Laravel Scheduler - Technofra`
4. Trigger: **Daily** at 12:00 AM
5. Action: **Start a program**
6. Program/script: `C:\xampp\php\php.exe`
7. Add arguments: `C:\xampp\htdocs\Technofra-Renewal\artisan schedule:run`
8. **Finish** पर click करें
9. Task पर right-click → **Properties**
10. **Triggers** tab में जाएं → **Edit** करें
11. **Repeat task every: 1 minute** select करें
12. Duration: **Indefinitely**
13. **OK** करें

### Step 5: Queue Worker Start करें

**Command Prompt** खोलें और run करें:

```bash
cd C:\xampp\htdocs\Technofra-Renewal
php artisan queue:work
```

**Important**: यह window खुली रखें। बंद करेंगे तो notifications नहीं जाएंगे।

**Production के लिए**: Windows Service बना सकते हैं या NSSM tool use कर सकते हैं।

---

## 📱 कैसे Use करें?

### Calendar Event बनाना

1. Dashboard पर जाएं: `http://localhost/Technofra-Renewal/dashboard`
2. Calendar section में **"Add Event"** button पर click करें
3. Details भरें:
   - **Title**: Event का नाम
   - **Description**: Event की details (optional)
   - **Date**: Event की तारीख
   - **Time**: Event का समय
   - **Email Recipients**: Email addresses (comma से separate)
   - **WhatsApp Recipients**: Phone numbers with country code

**Phone Number Format:**
- Single: `+919876543210`
- Multiple: `+919876543210, +919876543211, +919876543212`
- Spaces के साथ: `+91 9876543210, +91 9876543211`

4. **Save Event** पर click करें

### Notifications कब आएंगी?

अगर Event **2:00 PM** पर है:
- **1:50 PM** → 10 minute reminder (Email + WhatsApp)
- **2:00 PM** → Event notification (Email + WhatsApp)

---

## 🧪 Testing कैसे करें?

### Manual Test

Command Prompt में:

```bash
cd C:\xampp\htdocs\Technofra-Renewal
php artisan calendar:test-notification 1
```

(यहाँ `1` को अपने event की ID से replace करें)

### Check करें Notifications

```bash
php artisan calendar:send-notifications
```

---

## ⚠️ Important बातें

### Twilio Sandbox की Limitations:

1. **सिर्फ वही लोग** WhatsApp message receive कर सकते हैं जिन्होंने sandbox join किया है
2. हर user को Twilio के WhatsApp number पर join code भेजना होगा
3. Messages में "Sent from your Twilio Sandbox" लिखा आएगा
4. Free tier में limited messages ही भेज सकते हैं

### Production के लिए:

1. Twilio से **WhatsApp Business API** approval लेनी होगी
2. 1-2 weeks लग सकते हैं
3. Charges apply होंगे (per message)
4. Sandbox prefix नहीं आएगा

### Phone Number Format:

- हमेशा **country code** के साथ (India के लिए +91)
- अगर +91 नहीं है तो system automatically add कर देगा
- Spaces और dashes ignore हो जाएंगे

---

## 🔧 Problems और Solutions

### WhatsApp message नहीं आ रहा?

1. ✅ Check करें: Twilio credentials `.env` में सही हैं?
2. ✅ Phone number ने sandbox join किया है?
3. ✅ Queue worker चल रहा है? (`php artisan queue:work`)
4. ✅ Logs check करें: `storage/logs/laravel.log`

### Notifications trigger नहीं हो रहे?

1. ✅ Task Scheduler properly setup है?
2. ✅ Event का date/time future में है?
3. ✅ Event active है (status = 1)?
4. ✅ Manually run करके देखें: `php artisan calendar:send-notifications`

### Queue process नहीं हो रहा?

1. ✅ Queue worker start करें: `php artisan queue:work`
2. ✅ Failed jobs check करें: `php artisan queue:failed`
3. ✅ Retry करें: `php artisan queue:retry all`

---

## 📞 Help चाहिए?

### Logs देखें:

```bash
cd C:\xampp\htdocs\Technofra-Renewal
type storage\logs\laravel.log
```

### Database में Check करें:

phpMyAdmin में जाकर यह query run करें:

```sql
SELECT id, title, event_date, event_time, 
       whatsapp_recipients,
       reminder_10min_sent, 
       event_time_notification_sent 
FROM calendar_events 
WHERE status = 1 
ORDER BY event_date DESC;
```

---

## 📝 Quick Checklist

Setup complete करने के लिए:

- [ ] Twilio account बनाया
- [ ] Account SID और Auth Token copy किया
- [ ] WhatsApp sandbox join किया
- [ ] `.env` file में credentials डाले
- [ ] Task Scheduler setup किया
- [ ] Queue worker start किया
- [ ] Test event बनाया
- [ ] Test notification भेजा

---

## 🎯 Summary

अब आपका system:
- ✅ Email + WhatsApp दोनों पर notifications भेजेगा
- ✅ 10 minute पहले reminder भेजेगा
- ✅ Event time पर notification भेजेगा
- ✅ Automatically schedule होगा
- ✅ Multiple phone numbers support करेगा

**All the best! 🚀**

