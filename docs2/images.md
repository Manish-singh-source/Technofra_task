# Image Display Inventory

This file lists the places where the app renders images in the UI.

## Summary

- Visible image render points: `65`
- Count excludes:
  - commented-out image markup in `resources/views/clients-details.blade.php`
  - browser head assets like `favicon`, `apple-touch-icon`, and `manifest` links

## By File

| File | Count | What is shown |
| --- | ---: | --- |
| `resources/views/layout/master.blade.php` | 12 | CRM logo in the sidebar/header, authenticated user avatar, and product thumbnail images in the layout menu area |
| `resources/views/client-issue-details.blade.php` | 6 | Task attachment previews, full-size image modal preview, and team icon preview |
| `resources/views/project.blade.php` | 6 | Staff/member avatar images shown across project sections |
| `resources/views/customer-projects.blade.php` | 6 | Staff/member avatar images shown in the customer project lists |
| `resources/views/project-details.blade.php` | 6 | Staff avatars, client logo placeholder, file preview images, and comment avatars |
| `resources/views/settings/index.blade.php` | 5 | CRM logo, favicon, app logo, login logo, and team icon previews |
| `resources/views/task-details.blade.php` | 5 | Assignee/follower avatars, attachment image preview, modal image preview, and comment avatars |
| `resources/views/auth-basic-signin.blade.php` | 3 | Login page branding/logo images |
| `resources/views/client-issue-task-view.blade.php` | 2 | Attachment thumbnail and full preview modal image |
| `resources/views/staff/view.blade.php` | 2 | Staff profile image preview and uploaded image preview |
| `resources/views/auth-basic-signup.blade.php` | 1 | Signup page branding/logo image |
| `resources/views/auth-forgot-password.blade.php` | 1 | Password reset branding/logo image |
| `resources/views/auth-reset-password.blade.php` | 1 | Reset password branding/logo image |
| `resources/views/clients/index.blade.php` | 1 | Client profile image in the list table |
| `resources/views/clients/edit.blade.php` | 1 | Client profile image preview on edit |
| `resources/views/components/file-input.blade.php` | 1 | Generic file-input image preview |
| `resources/views/edit-project.blade.php` | 1 | Existing project file preview image |
| `resources/views/emails/notifications.blade.php` | 1 | Email logo image in the renewal notification template |
| `resources/views/emails/todo-reminder.blade.php` | 1 | Email logo image in the todo reminder template |
| `resources/views/staff/index.blade.php` | 1 | Staff avatar in the index list |
| `resources/views/task.blade.php` | 1 | Assignee avatar in the task list |
| `resources/views/user-profile.blade.php` | 1 | Profile image on the user profile page |

## Notes

- The dashboard, services, vendor services, and other list pages mostly use text and tables rather than inline image rendering.
- `resources/views/clients-details.blade.php` contains a commented-out `<img>` tag, so it was not counted as an active image display point.
- If you want, I can also make a second inventory for:
  - browser icon assets only
  - image uploads/previews only
  - avatar/profile-image usage only
