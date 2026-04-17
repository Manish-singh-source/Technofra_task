# Settings Module API

These APIs are available under the protected Sanctum API prefix:

- Base URL: `/api/v1`
- Auth header: `Authorization: Bearer YOUR_SANCTUM_TOKEN`
- Content type for JSON requests: `Accept: application/json` and `Content-Type: application/json`
- Content type for file uploads: `Accept: application/json` and `multipart/form-data`

## 1. Get All Settings

- Method: `GET`
- URL: `/api/v1/settings`
- Permission required: `view_general_settings` or `view_company_information` or `view_email_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 2. Get General Settings

- Method: `GET`
- URL: `/api/v1/settings/general`
- Permission required: `view_general_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/general' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 3. Update General Settings

- Method: `PUT`
- URL: `/api/v1/settings/general`
- Permission required: `view_general_settings`

Use multipart form data when uploading `crm_logo` or `favicon`.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/general' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--form 'company_name="Technofra CRM"' \
--form 'crm_logo=@"/full/path/logo.png"' \
--form 'favicon=@"/full/path/favicon.ico"'
```

To remove existing images:

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/general' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "remove_crm_logo": true,
  "remove_favicon": true
}'
```

## 4. Get Company Information

- Method: `GET`
- URL: `/api/v1/settings/company`
- Permission required: `view_company_information`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/company' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 5. Update Company Information

- Method: `PUT`
- URL: `/api/v1/settings/company`
- Permission required: `view_company_information`

Office timing must follow: `office_start_time < lunch_start_time < lunch_end_time < office_end_time`.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/company' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "company_name": "Technofra CRM",
  "company_email": "info@example.com",
  "company_phone": "+91 9876543210",
  "address": "Office Address",
  "city": "Ahmedabad",
  "state": "Gujarat",
  "zip": "380001",
  "country": "India",
  "website": "https://example.com",
  "gst_number": "GST123456",
  "office_start_time": "09:00",
  "lunch_start_time": "13:00",
  "lunch_end_time": "14:00",
  "office_end_time": "18:00"
}'
```

## 6. Get Email Settings

- Method: `GET`
- URL: `/api/v1/settings/email`
- Permission required: `view_email_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/email' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 7. Update Email Settings

- Method: `PUT`
- URL: `/api/v1/settings/email`
- Permission required: `view_email_settings`

This updates the settings table, Laravel runtime mail config, and `.env` mail keys.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/email' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "mail_engine": "phpmailer",
  "email_protocol": "smtp",
  "email_encryption": "tls",
  "smtp_host": "smtp.example.com",
  "smtp_port": 587,
  "email": "noreply@example.com",
  "smtp_username": "noreply@example.com",
  "smtp_password": "secret-password",
  "mail_from_name": "Technofra CRM",
  "email_charset": "UTF-8",
  "bcc_all": "audit@example.com",
  "email_signature": "Regards, Technofra",
  "predefined_header": "<p>Hello</p>",
  "predefined_footer": "<p>Thank you</p>"
}'
```

## 8. Get Renewal Notification Settings

- Method: `GET`
- URL: `/api/v1/settings/renewal`
- Permission required: `view_email_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/renewal' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 9. Update Renewal Notification Settings

- Method: `PUT`
- URL: `/api/v1/settings/renewal`
- Permission required: `view_email_settings`

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/renewal' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "renewal_admin_email": "admin@example.com",
  "renewal_notification_time": "09:30",
  "renewal_notice_days": 7,
  "renewal_notifications_enabled": true
}'
```

## 10. Get Teams

- Method: `GET`
- URL: `/api/v1/settings/teams`
- Permission required: `view_general_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/teams' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 11. Update Teams

- Method: `PUT`
- URL: `/api/v1/settings/teams`
- Permission required: `view_general_settings`

The API replaces the current team list with the submitted list.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/teams' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--form 'teams[0][name]="Development"' \
--form 'teams[0][description]="Development team"' \
--form 'teams[0][icon]=@"/full/path/development.png"' \
--form 'teams[1][name]="Support"' \
--form 'teams[1][description]="Support team"' \
--form 'teams[1][existing_icon_path]="uploads/team-icons/support.png"'
```

## 12. Get Departments

- Method: `GET`
- URL: `/api/v1/settings/departments`
- Permission required: `view_general_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/departments' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## 13. Update Departments

- Method: `PUT`
- URL: `/api/v1/settings/departments`
- Permission required: `view_general_settings`

The API replaces the current department list with the submitted list.

```bash
curl --location --request PUT 'http://127.0.0.1:8000/api/v1/settings/departments' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "departments": [
    {
      "name": "Engineering"
    },
    {
      "name": "Sales"
    }
  ]
}'
```

## 14. Send Test Email

- Method: `POST`
- URL: `/api/v1/settings/test-email`
- Permission required: `view_email_settings`

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/test-email' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN' \
--header 'Content-Type: application/json' \
--data '{
  "test_email": "test@example.com"
}'
```

## 15. Search Tags

- Method: `GET`
- URL: `/api/v1/settings/search-tags?q=urgent`
- Permission required: authenticated user

```bash
curl --location 'http://127.0.0.1:8000/api/v1/settings/search-tags?q=urgent' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_SANCTUM_TOKEN'
```

## Response Format

Successful responses follow this structure:

```json
{
  "success": true,
  "message": "Settings fetched successfully.",
  "data": {}
}
```

Validation and error responses follow this structure:

```json
{
  "success": false,
  "message": "Validation error.",
  "errors": {
    "company_name": [
      "The company name field is required."
    ]
  }
}
```

Authentication and authorization errors follow this structure:

```json
{
  "success": false,
  "message": "Token not provided or invalid.",
  "errors": null
}
```

```json
{
  "success": false,
  "message": "You are not authorized to perform this action.",
  "errors": null
}
```

## HTTP Status Codes

- `200` for successful fetch, update, and test email requests
- `401` for unauthenticated requests
- `403` for permission or authorization failures
- `422` for validation failures
- `500` for unexpected server errors
