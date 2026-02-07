<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIssueController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\VendorServiceController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route (protected by auth middleware) - also serves as index
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');


// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Client Controller
    Route::get('/client', [ClientController::class, 'client'])->name('client')->middleware('permission:view_renewals');
    Route::post('/client/toggle-status',  [ClientController::class, 'toggleStatus'])->name('client.toggleStatus')->middleware('permission:edit_renewals');
    Route::get('/add-client', [ClientController::class, 'addclient'])->name('add-client')->middleware('permission:create_renewals');
    Route::get('/add-clients', [CustomerController::class, 'create'])->name('add-clients')->middleware('permission:create_renewals');
    Route::post('/store-client', [CustomerController::class, 'storeclient'])->name('store-client')->middleware('permission:create_clients');
    Route::get('/edit-client/{id}', [ClientController::class, 'editclient'])->name('client.edit')->middleware('permission:edit_renewals');
    Route::put('/update-client/{id}', [ClientController::class, 'updateclient'])->name('client.update')->middleware('permission:edit_renewals');
    Route::delete('/client/delete/{id}', [ClientController::class, 'deleteclient'])->name('client.delete')->middleware('permission:delete_renewals');
    Route::get('/client-details/{id}', [ClientController::class, 'viewclient'])->name('client.view')->middleware('permission:view_renewals');
    Route::delete('/client/delete-selected',  [ClientController::class, 'deleteSelected'])->name('delete.selected.client')->middleware('permission:delete_renewals');

    // Client bulk upload routes
    Route::post('/client/bulk-upload', [ClientController::class, 'bulkUpload'])->name('client.bulk-upload')->middleware('permission:create_renewals');
    Route::get('/client/download-template', [ClientController::class, 'downloadTemplate'])->name('client.download-template')->middleware('permission:view_renewals');
    //end Client controller

    // Other protected routes
    Route::get('/add-servies', function () {
        return view('add-servies');
    })->name('add-servies')->middleware('permission:create_renewals');

    Route::get('/add-vendor', [VendorController::class, 'create'])->name('add-vendor')->middleware('permission:create_vendors');

    Route::get('/app-to-do', function () {
        return view('app-to-do');
    })->name('app-to-do');

    Route::get('/servies', function () {
        return view('servies');
    })->name('servies')->middleware('permission:view_renewals');

    Route::get('/user-profile', function () {
        return view('user-profile');
    })->name('user-profile');



    Route::get('/vendor', function () {
        return view('vendor');
    })->name('vendor')->middleware('permission:view_renewals');



    // Vendor CRUD routes
    Route::resource('vendors', VendorController::class)->middleware('permission:view_vendors');

    // Vendor bulk upload routes
    Route::post('/vendor1/bulk-upload', [VendorController::class, 'bulkUpload'])->name('vendors.bulk-upload')->middleware('permission:create_vendors');
    Route::get('/vendor1/download-template', [VendorController::class, 'downloadTemplate'])->name('vendors.download-template')->middleware('permission:view_vendors');

    // Service CRUD routes
    Route::resource('services', ServiceController::class)->middleware('permission:view_services');
    Route::post('/services/delete-selected', [ServiceController::class, 'deleteSelected'])->name('delete.selected.service')->middleware('permission:delete_services');

    // Vendor Service CRUD routes
    Route::resource('vendor-services', VendorServiceController::class)->middleware('permission:view_renewals');
    Route::post('/vendor-services/delete-selected', [VendorServiceController::class, 'deleteSelected'])->name('delete.selected.vendor-service')->middleware('permission:delete_renewals');

    // Mail routes for sending renewal emails
    Route::get('/send-mail/{service_id}', [MailController::class, 'sendMailForm'])->name('send-mail');
    Route::post('/send-mail', [MailController::class, 'sendMail'])->name('send-mail.send');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/renewal', [NotificationController::class, 'getRenewalNotifications'])->name('renewal');
        Route::get('/counts', [NotificationController::class, 'getNotificationCounts'])->name('counts');
        Route::get('/urgent', [NotificationController::class, 'getUrgentNotifications'])->name('urgent');
        Route::get('/summary', [NotificationController::class, 'getNotificationSummary'])->name('summary');
        Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Calendar Event routes
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/events', [CalendarEventController::class, 'getEvents'])->name('events');
        Route::post('/events', [CalendarEventController::class, 'store'])->name('store');
        Route::get('/events/{id}', [CalendarEventController::class, 'show'])->name('show');
        Route::put('/events/{id}', [CalendarEventController::class, 'update'])->name('update');
        Route::delete('/events/{id}', [CalendarEventController::class, 'destroy'])->name('destroy');
        Route::post('/toggle-status', [CalendarEventController::class, 'toggleStatus'])->name('toggleStatus');
    });

    // Additional routes for backward compatibility
    Route::get('/vendor1', [VendorController::class, 'index'])->name('vendor1');
     Route::delete('/vendor1/delete-selected', [VendorController::class, 'deleteSelected'])->name('delete.selected.vendor');
    Route::post('/vendor1/toggle-status',  [VendorController::class, 'toggleStatus'])->name('vendor1.toggleStatus');
    Route::get('/servies', [ServiceController::class, 'index'])->name('servies');

    // Staff routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff')->middleware('permission:view_staff');
        Route::delete('/staff/delete/{id}', [StaffController::class, 'destroy'])->name('staff.destroy')->middleware('permission:delete_staff');
        Route::get('/add-staff', [StaffController::class, 'create'])->name('add-staff')->middleware('permission:create_staff');
        Route::get('/view-staff/{id}', [StaffController::class, 'show'])->name('view-staff')->middleware('permission:view_staff');
        Route::post('/store-staff', [StaffController::class, 'store'])->name('staff.store')->middleware('permission:create_staff');
        Route::put('/update-staff/{id}', [StaffController::class, 'update'])->name('staff.update')->middleware('permission:edit_staff');
    });

    // Role routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles')->middleware('permission:view_roles');
        Route::get('/add-role', [RoleController::class, 'create'])->name('add-role')->middleware('permission:create_roles');
        Route::post('/add-role', [RoleController::class, 'store'])->name('store-role')->middleware('permission:create_roles');
        Route::get('/edit-role/{id}', [RoleController::class, 'edit'])->name('role.edit')->middleware('permission:edit_roles');
        Route::put('/edit-role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('permission:edit_roles');
        Route::delete('/role/delete/{id}', [RoleController::class, 'destroy'])->name('role.delete')->middleware('permission:delete_roles');
    });

    // Permission routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions')->middleware('permission:view_roles');
        Route::get('/add-permission', [PermissionController::class, 'create'])->name('add-permission')->middleware('permission:create_roles');
        Route::post('/add-permission', [PermissionController::class, 'store'])->name('store-permission')->middleware('permission:create_roles');
        Route::get('/edit-permission/{id}', [PermissionController::class, 'edit'])->name('permission.edit')->middleware('permission:edit_roles');
        Route::put('/edit-permission/{id}', [PermissionController::class, 'update'])->name('permission.update')->middleware('permission:edit_roles');
        Route::delete('/permission/delete/{id}', [PermissionController::class, 'destroy'])->name('permission.delete')->middleware('permission:delete_roles');
        Route::post('/permission/assign-to-role', [PermissionController::class, 'assignToRole'])->name('permission.assign-to-role')->middleware('permission:edit_roles');
        Route::post('/permission/remove-from-role', [PermissionController::class, 'removeFromRole'])->name('permission.remove-from-role')->middleware('permission:edit_roles');
    });

    // Project routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/project', [ProjectController::class, 'index'])->name('project')->middleware('permission:view_projects');
        Route::get('/my-projects', [ProjectController::class, 'myProjects'])->name('my-projects');
        Route::get('/edit-project/{id}', [ProjectController::class, 'edit'])->name('edit-project')->middleware('permission:edit_projects');
        Route::put('/edit-project/{id}', [ProjectController::class, 'update'])->name('update-project')->middleware('permission:edit_projects');
        Route::get('/add-project', [ProjectController::class, 'create'])->name('add-project')->middleware('permission:create_projects');
        Route::post('/add-project', [ProjectController::class, 'store'])->name('store-project')->middleware('permission:create_projects');
        Route::get('/project-details/{id}', [ProjectController::class, 'show'])->name('project-details')->middleware('permission:view_projects');
        Route::delete('/project/delete/{id}', [ProjectController::class, 'destroy'])->name('project.destroy')->middleware('permission:delete_projects');
    });

    // Task routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/task', [TaskController::class, 'index'])->name('task')->middleware('permission:view_tasks');
        Route::get('/add-task', function () {
            return view('add-task');
        })->name('add-task')->middleware('permission:create_tasks');
        Route::post('/add-task', [TaskController::class, 'store'])->name('add-task.store')->middleware('permission:create_tasks');
        Route::get('/task-details/{id}', [TaskController::class, 'show'])->name('task-details')->middleware('permission:view_tasks');
    });

    // Client issue routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/client-issue', [ClientIssueController::class, 'index'])->name('client-issue');
        Route::get('/client-issue/create', [ClientIssueController::class, 'create'])->name('client-issue.create');
        Route::post('/client-issue', [ClientIssueController::class, 'store'])->name('client-issue.store');
        Route::get('/client-issue/{id}', [ClientIssueController::class, 'show'])->name('client-issue.show');
        Route::delete('/client-issue/{id}', [ClientIssueController::class, 'destroy'])->name('client-issue.destroy');
        
        // Client issue task routes
        Route::post('/client-issue/{clientIssue}/task', [ClientIssueController::class, 'taskStore'])->name('client-issue.task.store');
        Route::get('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskShow'])->name('client-issue.task.show');
        Route::put('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskUpdate'])->name('client-issue.task.update');
        Route::patch('/client-issue/{clientIssue}/task/{task}/status', [ClientIssueController::class, 'taskUpdateStatus'])->name('client-issue.task.update-status');
        Route::delete('/client-issue/{clientIssue}/task/{task}', [ClientIssueController::class, 'taskDestroy'])->name('client-issue.task.destroy');
    });

    // Clients routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/clients', [CustomerController::class, 'index'])->name('clients')->middleware('permission:view_clients');
        Route::delete('/clients/{id}', [CustomerController::class, 'delete'])->name('clients.delete')->middleware('permission:delete_clients');
        Route::get('/add-clients', [CustomerController::class, 'create'])->name('add-clients')->middleware('permission:create_clients');
        Route::get('/clients-details/{id}', [CustomerController::class, 'show'])->name('clients-details')->middleware('permission:view_clients');
        Route::put('/clients-details/{id}', [CustomerController::class, 'update'])->name('clients.update')->middleware('permission:edit_clients');
    });

    // Lead CRUD routes - removed lead permissions from routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads');
        Route::get('/add-lead', [LeadController::class, 'create'])->name('add-lead');
        Route::post('/store-lead', [LeadController::class, 'store'])->name('lead.store');
        Route::get('/view-lead/{id}', [LeadController::class, 'show'])->name('lead.show');
        Route::get('/edit-lead/{id}', [LeadController::class, 'edit'])->name('lead.edit');
        Route::put('/update-lead/{id}', [LeadController::class, 'update'])->name('lead.update');
        Route::delete('/delete-lead/{id}', [LeadController::class, 'destroy'])->name('lead.destroy');
        Route::post('/lead/toggle-status', [LeadController::class, 'toggleStatus'])->name('lead.toggleStatus');
        Route::post('/lead/delete-selected', [LeadController::class, 'deleteSelected'])->name('lead.delete-selected');
        Route::get('/lead/export', [LeadController::class, 'export'])->name('lead.export');
    });
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email')->middleware('throttle:5,1');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Keep old routes for backward compatibility
Route::get('/auth-basic-signin', [AuthController::class, 'showLoginForm'])->name('auth-basic-signin');
Route::get('/auth-basic-signup', [AuthController::class, 'showRegisterForm'])->name('auth-basic-signup');

// End Lead CRUD routes
