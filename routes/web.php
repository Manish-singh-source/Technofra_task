<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
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
    Route::get('/client', [ClientController::class, 'client'])->name('client')->middleware('can:view_renewals');
    Route::post('/client/toggle-status',  [ClientController::class, 'toggleStatus'])->name('client.toggleStatus')->middleware('can:edit_renewals');
    Route::get('/add-client', [ClientController::class, 'addclient'])->name('add-client')->middleware('can:create_renewals');
    Route::get('/add-clients', [CustomerController::class, 'create'])->name('add-clients')->middleware('can:create_renewals');
    Route::post('/store-client', [CustomerController::class, 'storeclient'])->name('store-client')->middleware('can:create_renewals');
    Route::get('/edit-client/{id}', [ClientController::class, 'editclient'])->name('client.edit')->middleware('can:edit_renewals');
    Route::put('/update-client/{id}', [ClientController::class, 'updateclient'])->name('client.update')->middleware('can:edit_renewals');
    Route::delete('/client/delete/{id}', [ClientController::class, 'deleteclient'])->name('client.delete')->middleware('can:delete_renewals');
    Route::get('/client-details/{id}', [ClientController::class, 'viewclient'])->name('client.view')->middleware('can:view_renewals');
    Route::delete('/client/delete-selected',  [ClientController::class, 'deleteSelected'])->name('delete.selected.client')->middleware('can:delete_renewals');

    // Client bulk upload routes
    Route::post('/client/bulk-upload', [ClientController::class, 'bulkUpload'])->name('client.bulk-upload')->middleware('can:create_renewals');
    Route::get('/client/download-template', [ClientController::class, 'downloadTemplate'])->name('client.download-template')->middleware('can:view_renewals');
    //end Client controller

    // Other protected routes
    Route::get('/add-servies', function () {
        return view('add-servies');
    })->name('add-servies')->middleware('can:create_renewals');

    Route::get('/add-vendor', [VendorController::class, 'create'])->name('add-vendor')->middleware('can:create_renewals');

    Route::get('/app-to-do', function () {
        return view('app-to-do');
    })->name('app-to-do');

    Route::get('/servies', function () {
        return view('servies');
    })->name('servies')->middleware('can:view_renewals');

    Route::get('/user-profile', function () {
        return view('user-profile');
    })->name('user-profile');



    Route::get('/vendor', function () {
        return view('vendor');
    })->name('vendor')->middleware('can:view_renewals');



    // Vendor CRUD routes
    Route::resource('vendors', VendorController::class)->middleware('can:view_renewals');

    // Vendor bulk upload routes
    Route::post('/vendor1/bulk-upload', [VendorController::class, 'bulkUpload'])->name('vendors.bulk-upload')->middleware('can:create_renewals');
    Route::get('/vendor1/download-template', [VendorController::class, 'downloadTemplate'])->name('vendors.download-template')->middleware('can:view_renewals');

    // Service CRUD routes
    Route::resource('services', ServiceController::class)->middleware('can:view_renewals');
    Route::post('/services/delete-selected', [ServiceController::class, 'deleteSelected'])->name('delete.selected.service')->middleware('can:delete_renewals');

    // Vendor Service CRUD routes
    Route::resource('vendor-services', VendorServiceController::class)->middleware('can:view_renewals');
    Route::post('/vendor-services/delete-selected', [VendorServiceController::class, 'deleteSelected'])->name('delete.selected.vendor-service')->middleware('can:delete_renewals');

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
        Route::get('/staff', [StaffController::class, 'index'])->name('staff')->middleware('can:view_staff');
        Route::delete('/staff/delete/{id}', [StaffController::class, 'destroy'])->name('staff.destroy')->middleware('can:delete_staff');
        Route::get('/add-staff', [StaffController::class, 'create'])->name('add-staff')->middleware('can:create_staff');
        Route::get('/view-staff/{id}', [StaffController::class, 'show'])->name('view-staff')->middleware('can:view_staff');
        Route::post('/store-staff', [StaffController::class, 'store'])->name('staff.store')->middleware('can:create_staff');
        Route::put('/update-staff/{id}', [StaffController::class, 'update'])->name('staff.update')->middleware('can:edit_staff');
    });

    // Role routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles')->middleware('can:view_roles');
        Route::get('/add-role', [RoleController::class, 'create'])->name('add-role')->middleware('can:create_roles');
        Route::post('/add-role', [RoleController::class, 'store'])->name('store-role')->middleware('can:create_roles');
        Route::get('/edit-role/{id}', [RoleController::class, 'edit'])->name('role.edit')->middleware('can:edit_roles');
        Route::put('/edit-role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('can:edit_roles');
        Route::delete('/role/delete/{id}', [RoleController::class, 'destroy'])->name('role.delete')->middleware('can:delete_roles');
    });

    // Project routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/project', [ProjectController::class, 'index'])->name('project')->middleware('can:view_projects');
        Route::get('/edit-project/{id}', [ProjectController::class, 'edit'])->name('edit-project')->middleware('can:edit_projects');
        Route::put('/edit-project/{id}', [ProjectController::class, 'update'])->name('update-project')->middleware('can:edit_projects');
        Route::get('/add-project', [ProjectController::class, 'create'])->name('add-project')->middleware('can:create_projects');
        Route::post('/add-project', [ProjectController::class, 'store'])->name('store-project')->middleware('can:create_projects');
        Route::get('/project-details/{id}', [ProjectController::class, 'show'])->name('project-details')->middleware('can:view_projects');
        Route::delete('/project/delete/{id}', [ProjectController::class, 'destroy'])->name('project.destroy')->middleware('can:delete_projects');
    });

    // Task routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/task', [TaskController::class, 'index'])->name('task')->middleware('can:view_task');
        Route::get('/add-task', function () {
            return view('add-task');
        })->name('add-task')->middleware('can:create_task');
        Route::post('/add-task', [TaskController::class, 'store'])->name('add-task.store')->middleware('can:create_task');
        Route::get('/task-details/{id}', [TaskController::class, 'show'])->name('task-details')->middleware('can:view_task');
    });

    // Client issue routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/client-issue', function () {
            return view('client-issue');
        })->name('client-issue')->middleware('can:view_raise_issue');
        Route::get('/client-issue-details', function () {
            return view('client-issue-details');
        })->name('client-issue-details')->middleware('can:view_raise_issue');
    });

    // Clients routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/clients', [CustomerController::class, 'index'])->name('clients')->middleware('can:view_client');
        Route::delete('/clients/{id}', [CustomerController::class, 'delete'])->name('clients.delete')->middleware('can:delete_client');
        Route::get('/add-clients', [CustomerController::class, 'create'])->name('add-clients')->middleware('can:create_client');
        Route::get('/clients-details/{id}', [CustomerController::class, 'show'])->name('clients-details')->middleware('can:view_client');
        Route::put('/clients-details/{id}', [CustomerController::class, 'update'])->name('clients.update')->middleware('can:edit_client');
    });

    // Lead CRUD routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads')->middleware('can:view_leads');
        Route::get('/add-lead', [LeadController::class, 'create'])->name('add-lead')->middleware('can:create_leads');
        Route::post('/store-lead', [LeadController::class, 'store'])->name('lead.store')->middleware('can:create_leads');
        Route::get('/view-lead/{id}', [LeadController::class, 'show'])->name('lead.show')->middleware('can:view_leads');
        Route::get('/edit-lead/{id}', [LeadController::class, 'edit'])->name('lead.edit')->middleware('can:edit_leads');
        Route::put('/update-lead/{id}', [LeadController::class, 'update'])->name('lead.update')->middleware('can:edit_leads');
        Route::delete('/delete-lead/{id}', [LeadController::class, 'destroy'])->name('lead.destroy')->middleware('can:delete_leads');
        Route::post('/lead/toggle-status', [LeadController::class, 'toggleStatus'])->name('lead.toggleStatus')->middleware('can:edit_leads');
        Route::post('/lead/delete-selected', [LeadController::class, 'deleteSelected'])->name('lead.delete-selected')->middleware('can:delete_leads');
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
