# Role-Based Authentication and Authorization System

## Overview

This document describes the complete role-based authentication and authorization system implemented in the Technofra application.

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `email_verified_at` - Email verification timestamp
- `remember_token` - Remember me token
- `created_at`, `updated_at` - Timestamps

### Roles Table (Spatie Permission)
- `id` - Primary key
- `name` - Role name (e.g., 'super-admin', 'admin', 'manager', 'staff', 'customer')
- `guard_name` - Guard name (default: 'web')
- `created_at`, `updated_at` - Timestamps

### Permissions Table (Spatie Permission)
- `id` - Primary key
- `name` - Permission name (e.g., 'view_staff', 'create_projects')
- `guard_name` - Guard name (default: 'web')
- `created_at`, `updated_at` - Timestamps

### Role-Permissions Junction Table
- `permission_id` - Foreign key to permissions
- `role_id` - Foreign key to roles

### Staff Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `profile_image` - Profile image path
- `first_name`, `last_name` - Name fields
- `email` - Email address
- `phone` - Phone number
- `role` - Role name
- `password` - Hashed password
- `status` - 'active' or 'inactive'
- `departments` - JSON array of departments
- `created_at`, `updated_at` - Timestamps

### Customers Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `client_name` - Company/client name
- `contact_person` - Contact person name
- `email` - Email address
- `phone`, `website` - Contact info
- `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `country` - Address fields
- `client_type` - 'Individual', 'Company', or 'Organization'
- `industry` - Industry type
- `status` - 'Active', 'Inactive', or 'Suspended'
- `priority_level` - 'Low', 'Medium', or 'High'
- `assigned_manager_id` - Assigned manager
- `default_due_days` - Default due days
- `billing_type` - 'Hourly', 'Fixed', or 'Retainer'
- `role` - Role name
- `password` - Hashed password
- `created_at`, `updated_at` - Timestamps

## API Endpoints

### Authentication Endpoints

#### POST /api/auth/login
Login and receive authentication token.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "type": "staff"
        },
        "profile": { ... },
        "roles": ["manager"],
        "permissions": ["view_staff", "create_projects", ...],
        "menu_items": [ ... ],
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### POST /api/auth/register
Register a new user.

**Request:**
```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### POST /api/auth/logout
Logout and revoke token (requires authentication).

#### GET /api/auth/user
Get current user data with permissions (requires authentication).

#### POST /api/auth/refresh-token
Refresh authentication token (requires authentication).

### Staff Endpoints

#### GET /api/staff
Get all staff members (requires `view_staff` permission).

#### POST /api/staff
Create a new staff member (requires `create_staff` permission).

**Request:**
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "role": "staff",
    "password": "password123",
    "departments": ["IT", "Support"]
}
```

### Client Endpoints

#### GET /api/clients
Get all customers (requires `view_clients` permission).

#### POST /api/clients
Create a new customer (requires `create_clients` permission).

### Permission Endpoints

#### GET /api/permissions
Get all permissions (requires `view_roles` permission).

#### GET /api/permissions/grouped
Get permissions grouped by module (requires `view_roles` permission).

### Role Endpoints

#### GET /api/roles
Get all roles with permissions (requires `view_roles` permission).

## Web Routes

### Role Management
- `GET /roles` - List all roles
- `GET /add-role` - Show create role form
- `POST /add-role` - Create new role
- `GET /edit-role/{id}` - Show edit role form
- `PUT /edit-role/{id}` - Update role
- `DELETE /role/delete/{id}` - Delete role

### Permission Management
- `GET /permissions` - List all permissions
- `GET /add-permission` - Show create permission form
- `POST /add-permission` - Create new permission
- `GET /edit-permission/{id}` - Show edit permission form
- `PUT /edit-permission/{id}` - Update permission
- `DELETE /permission/delete/{id}` - Delete permission

### Staff Management
- `GET /staff` - List all staff
- `GET /add-staff` - Show create staff form
- `POST /store-staff` - Create new staff
- `GET /view-staff/{id}` - View staff details
- `PUT /update-staff/{id}` - Update staff
- `DELETE /staff/delete/{id}` - Delete staff

### Customer Management
- `GET /clients` - List all customers
- `GET /add-clients` - Show create customer form
- `POST /store-client` - Create new customer
- `GET /clients-details/{id}` - View customer details
- `PUT /clients-details/{id}` - Update customer
- `DELETE /clients/{id}` - Delete customer

## Default Roles and Permissions

### Roles
1. **super-admin** - Full access to all features
2. **admin** - Most permissions except sensitive operations
3. **manager** - Can manage projects, tasks, clients, and view reports
4. **staff** - Basic access to view and create content
5. **customer** - Limited access to own projects and issues

### Permission Modules
- `renewals` - view, create, edit, delete
- `leads` - view, create, edit, delete
- `projects` - view, create, edit, delete
- `tasks` - view, create, edit, delete
- `raise_issue` - view, create, edit, delete
- `clients` - view, create, edit, delete
- `staff` - view, create, edit, delete
- `roles` - view, create, edit, delete
- `permissions` - view, create, edit, delete
- `services` - view, create, edit, delete
- `vendors` - view, create, edit, delete
- `dashboard` - view, create, edit, delete

### Additional Permissions
- `manage_users` - Manage user accounts
- `manage_settings` - Manage system settings
- `view_reports` - View reports
- `export_data` - Export data
- `import_data` - Import data
- `send_notifications` - Send notifications
- `manage_calendar` - Manage calendar events
- `view_all_projects` - View all projects
- `view_own_projects` - View own projects only
- `assign_tasks` - Assign tasks to users
- `view_all_tasks` - View all tasks
- `view_own_tasks` - View own tasks only

## Usage Examples

### Using API Authentication

```javascript
// Login
const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    })
});

const data = await response.json();
const token = data.data.token;

// Use token for authenticated requests
const staffResponse = await fetch('/api/staff', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
    }
});
```

### Checking Permissions in Blade Templates

```blade
@can('view_staff')
    <a href="{{ route('staff') }}">Staff</a>
@endcan

@can('create_projects')
    <button>Create Project</button>
@endcan
```

### Using MenuHelper in Views

```php
use App\Helpers\MenuHelper;

$menuItems = MenuHelper::getMenuItems();
$canCreateProject = MenuHelper::canAccess('create_projects');
$userRoles = MenuHelper::getUserRoles();
```

## Running Migrations

```bash
# Run the migration to add user_id to staff and customers tables
php artisan migrate

# Seed permissions and roles
php artisan db:seed --class=PermissionSeeder
```

## Security Best Practices

1. **Password Hashing**: All passwords are hashed using Laravel's Hash facade (bcrypt)
2. **Token-based Authentication**: API uses Laravel Sanctum for token-based authentication
3. **Permission Middleware**: Routes are protected with permission middleware
4. **CSRF Protection**: Web routes are protected with CSRF tokens
5. **Input Validation**: All inputs are validated before processing
6. **Rate Limiting**: API routes are rate-limited to prevent abuse

## Troubleshooting

### Clear Permission Cache
```bash
php artisan cache:forget spatie.permission.cache
```

### Regenerate Permissions
```bash
php artisan db:seed --class=PermissionSeeder
```

### Check User Permissions
```php
$user = User::find(1);
$permissions = $user->getAllPermissions()->pluck('name');
$roles = $user->getRoleNames();
```
