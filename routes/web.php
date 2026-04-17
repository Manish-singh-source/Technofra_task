<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookCallController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIssueController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalMarketingLeadController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorServiceController;
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

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'showRegisterForm')->name('register');
    Route::post('/register', 'register');

    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');

    // Password Reset Routes
    Route::get('/forgot-password', 'showForgotPasswordForm')->name('password.request');
    Route::post('/forgot-password', 'forgotPassword')->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');

    // Keep old routes for backward compatibility
    // Route::get('/auth-basic-signin', 'showLoginForm')->name('auth-basic-signin');
    // Route::get('/auth-basic-signup', 'showRegisterForm')->name('auth-basic-signup');
});


// Protected routes (require authentication)
Route::middleware('auth')->group(function () {

    // Redirect root to dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard route (protected by auth middleware) - also serves as index
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============================= Permissions Controller ====================
    Route::controller(PermissionController::class)->group(function () {
        Route::get('/permissions', 'index')->name('permissions.index');
        Route::get('/add-permission', 'create')->name('permission.create');
        Route::post('/add-permission', 'store')->name('permission.store');
        Route::get('/edit-permission/{id}', 'edit')->name('permission.edit');
        Route::put('/edit-permission/{id}', 'update')->name('permission.update');
        Route::delete('/permission/delete/{id}', 'destroy')->name('permission.destroy');
    });
    // ============================= End Permissions Controller ====================


    // ============================= Role Controller ====================
    Route::controller(RoleController::class)->group(function () {
        Route::get('/roles', 'index')->name('roles.index')->middleware('permission:view_roles');
        Route::get('/create-role', 'create')->name('role.create')->middleware('permission:create_roles');
        Route::post('/store-role', 'store')->name('role.store')->middleware('permission:create_roles');
        Route::get('/edit-role/{id}', 'edit')->name('role.edit')->middleware('permission:edit_roles');
        Route::put('/edit-role/{id}', 'update')->name('role.update')->middleware('permission:edit_roles');
        Route::delete('/role/delete/{id}', 'destroy')->name('role.delete')->middleware('permission:delete_roles');
        Route::delete('/role/delete-selected', 'deleteSelected')->name('delete.selected.role')->middleware('permission:delete_roles');
        // permanent delete & restore 
    });
    // ============================= End Role Controller ====================

    // Permission routes
    // Route::middleware(['auth'])->group(function () {
    //     Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions')->middleware('permission:view_roles');
    //     Route::get('/add-permission', [PermissionController::class, 'create'])->name('add-permission')->middleware('permission:create_roles');
    //     Route::post('/add-permission', [PermissionController::class, 'store'])->name('store-permission')->middleware('permission:create_roles');
    //     Route::get('/edit-permission/{id}', [PermissionController::class, 'edit'])->name('permission.edit')->middleware('permission:edit_roles');
    //     Route::put('/edit-permission/{id}', [PermissionController::class, 'update'])->name('permission.update')->middleware('permission:edit_roles');
    //     Route::delete('/permission/delete/{id}', [PermissionController::class, 'destroy'])->name('permission.delete')->middleware('permission:delete_roles');
    //     Route::post('/permission/assign-to-role', [PermissionController::class, 'assignToRole'])->name('permission.assign-to-role')->middleware('permission:edit_roles');
    //     Route::post('/permission/remove-from-role', [PermissionController::class, 'removeFromRole'])->name('permission.remove-from-role')->middleware('permission:edit_roles');
    // });


    // ============================= Settings Controller ====================
    Route::controller(SettingController::class)->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('/', 'index')->name('settings')->middleware('permission:view_general_settings|view_company_information|view_email_settings');
            Route::put('/general', 'updateGeneral')->name('settings.update.general')->middleware('permission:view_general_settings');
            Route::put('/company', 'updateCompany')->name('settings.update.company')->middleware('permission:view_company_information');
            Route::put('/email', 'updateEmail')->name('settings.update.email')->middleware('permission:view_email_settings');
            Route::put('/renewal', 'updateRenewal')->name('settings.update.renewal')->middleware('permission:view_email_settings');
            Route::put('/teams', 'updateTeams')->name('settings.update.teams')->middleware('permission:view_general_settings');
            Route::put('/departments', 'updateDepartments')->name('settings.update.departments')->middleware('permission:view_general_settings');
            Route::post('/test-email', 'sendTestEmail')->name('settings.test.email')->middleware('permission:view_email_settings');
            Route::get('/search-tags', 'searchTags')->name('settings.search.tags');
        });
    });
    // ============================= End Settings Controller ====================



    // ============================= Staff Controller ====================
    Route::controller(StaffController::class)->group(function () {
        Route::get('/staff', 'index')->name('staff')->middleware('permission:view_staff');
        Route::delete('/staff/delete/{id}', 'destroy')->name('staff.destroy')->middleware('permission:delete_staff');
        Route::delete('/staff/delete-selected', 'deleteSelected')->name('delete.selected.staff')->middleware('permission:delete_staff');
        Route::post('/staff/restore/{id}', 'restore')->name('staff.restore')->middleware('permission:edit_staff');
        Route::delete('/staff/force-delete/{id}', 'forceDelete')->name('staff.force-delete')->middleware('permission:delete_staff');
        Route::get('/add-staff', 'create')->name('add-staff')->middleware('permission:create_staff');
        Route::get('/view-staff/{id}', 'show')->name('view-staff')->middleware('permission:view_staff');
        Route::post('/store-staff', 'store')->name('staff.store')->middleware('permission:create_staff');
        Route::put('/update-staff/{id}', 'update')->name('staff.update')->middleware('permission:edit_staff');
    });
    // ============================= End Staff Controller ====================


    // ============================= Client Controller ====================
    Route::controller(ClientController::class)->group(function () {
        Route::get('/client', 'client')->name('client')->middleware('permission:view_renewals');
        Route::post('/client/toggle-status',  'toggleStatus')->name('client.toggleStatus')->middleware('permission:edit_renewals');
        Route::get('/add-client', 'addclient')->name('add-client')->middleware('permission:create_renewals');
        Route::post('/client/store', 'storeclient')->name('client.store')->middleware('permission:create_renewals');
        Route::get('/edit-client/{id}', 'editclient')->name('client.edit')->middleware('permission:edit_renewals');
        Route::put('/update-client/{id}', 'updateclient')->name('client.update')->middleware('permission:edit_renewals');
        Route::delete('/client/delete/{id}', 'deleteclient')->name('client.delete')->middleware('permission:delete_renewals');
        Route::get('/client-details/{id}', 'viewclient')->name('client.view')->middleware('permission:view_renewals');
        Route::delete('/client/delete-selected',  'deleteSelected')->name('delete.selected.client')->middleware('permission:delete_renewals');
        // Client bulk upload routes
        Route::post('/client/bulk-upload', 'bulkUpload')->name('client.bulk-upload')->middleware('permission:create_renewals');
        Route::get('/client/download-template', 'downloadTemplate')->name('client.download-template')->middleware('permission:view_renewals');
        //end Client controller
    });

    // Route::get('/add-clients', [CustomerController::class, 'create'])->name('add-clients')->middleware('permission:create_renewals');

    // Clients routes
    Route::controller(CustomerController::class)->group(function () {
        Route::get('/clients', 'index')->name('clients')->middleware('permission:view_clients');
        Route::delete('/clients/{id}', 'delete')->name('clients.delete')->middleware('permission:delete_clients');
        Route::delete('/clients/delete-selected', 'deleteSelected')->name('delete.selected.clients')->middleware('permission:delete_clients');
        Route::get('/add-clients', 'create')->name('add-clients')->middleware('permission:create_clients');
        Route::post('/store-client', 'storeclient')->name('store-client')->middleware('permission:create_clients');
        Route::get('/clients-details/{id}', 'show')->name('clients-details')->middleware('permission:view_clients');
        Route::put('/clients-details/{id}', 'update')->name('clients.update')->middleware('permission:edit_clients');
    });

    // ============================= End Client Controller ====================






    // ============================= Vendor Controller ====================
    // Vendor CRUD routes
    Route::resource('vendors', VendorController::class)->middleware('permission:view_vendors');
    Route::get('/vendor', function () {
        return view('vendor');
    })->name('vendor')->middleware('permission:view_renewals');
    // Additional routes for backward compatibility
    Route::controller(VendorController::class)->group(function () {
        Route::get('/add-vendor', 'create')->name('add-vendor')->middleware('permission:create_vendors');
        Route::post('/vendor1/bulk-upload', 'bulkUpload')->name('vendors.bulk-upload')->middleware('permission:create_vendors');
        Route::get('/vendor1/download-template', 'downloadTemplate')->name('vendors.download-template')->middleware('permission:view_vendors');
        Route::get('/vendor1', 'index')->name('vendor1');
        Route::delete('/vendor1/delete-selected', 'deleteSelected')->name('delete.selected.vendor');
        Route::post('/vendor1/toggle-status',  'toggleStatus')->name('vendor1.toggleStatus');
    });

    // Vendor Service CRUD routes
    Route::resource('vendor-services', VendorServiceController::class)->middleware('permission:view_renewals');
    Route::post('/vendor-services/delete-selected', [VendorServiceController::class, 'deleteSelected'])->name('delete.selected.vendor-service')->middleware('permission:delete_renewals');
    // ============================= End Vendor Controller ====================


    // Project routes
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/project', 'index')->name('project')->middleware('permission:view_projects');
        Route::get('/my-projects', 'myProjects')->name('my-projects');
        Route::get('/edit-project/{id}', 'edit')->name('edit-project')->middleware('permission:edit_projects');
        Route::put('/edit-project/{id}', 'update')->name('update-project')->middleware('permission:edit_projects');
        Route::get('/add-project', 'create')->name('add-project')->middleware('permission:create_projects');
        Route::post('/add-project', 'store')->name('store-project')->middleware('permission:create_projects');
        Route::get('/project-details/{id}', 'show')->name('project-details')->middleware('permission:view_projects');
        Route::post('/project/{projectId}/milestones', 'storeMilestone')->name('project.milestones.store')->middleware('permission:edit_projects');
        Route::put('/project/{projectId}/milestones/{milestoneId}', 'updateMilestone')->name('project.milestones.update')->middleware('permission:edit_projects');
        Route::delete('/project/{projectId}/milestones/{milestoneId}', 'destroyMilestone')->name('project.milestones.destroy')->middleware('permission:edit_projects');
        Route::post('/project/{projectId}/issues', 'storeIssue')->name('project.issues.store')->middleware('permission:edit_projects');
        Route::put('/project/{projectId}/issues/{issueId}', 'updateIssue')->name('project.issues.update')->middleware('permission:edit_projects');
        Route::delete('/project/{projectId}/issues/{issueId}', 'destroyIssue')->name('project.issues.destroy')->middleware('permission:edit_projects');
        Route::delete('/project/delete/{id}', 'destroy')->name('project.destroy')->middleware('permission:delete_projects');
        Route::delete('/project/delete-selected', 'deleteSelected')->name('delete.selected.project')->middleware('permission:delete_projects');

        // Project File Routes
        Route::post('/project/{projectId}/upload-file', 'uploadFile')->name('project.upload-file')->middleware('permission:edit_projects');
        Route::get('/project/file/{fileId}/download', 'downloadFile')->name('project.file.download')->middleware('permission:view_projects');
        Route::delete('/project/file/{fileId}/delete', 'deleteFile')->name('project.file.delete')->middleware('permission:delete_projects');
        Route::post('/project-details/{id}/comment', 'storeComment')->name('project.comment.store')->middleware('permission:view_projects');
    });

    // Task routes
    Route::controller(TaskController::class)->group(function () {
        Route::get('/task', 'index')->name('task')->middleware('permission:view_tasks');
        Route::get('/add-task', 'create')->name('add-task')->middleware('permission:create_tasks');
        Route::post('/add-task', 'store')->name('add-task.store')->middleware('permission:create_tasks');
        Route::get('/task-details/{id}', 'show')->name('task-details')->middleware('permission:view_tasks');
        Route::post('/task-details/{id}/comment', 'storeComment')->name('task.comment.store')->middleware('permission:view_tasks');
        Route::get('/edit-task/{id}', 'edit')->name('edit-task')->middleware('permission:edit_tasks');
        Route::put('/edit-task/{id}', 'update')->name('edit-task.update')->middleware('permission:edit_tasks');
        Route::delete('/task/delete/{id}', 'destroy')->name('task.destroy')->middleware('permission:delete_tasks');
        Route::delete('/task/delete-selected', 'deleteSelected')->name('delete.selected.task')->middleware('permission:delete_tasks');
    });

    // Client issue routes
    Route::controller(ClientIssueController::class)->group(function () {
        Route::get('/client-issue', 'index')->name('client-issue');
        Route::get('/client-issue/create', 'create')->name('client-issue.create');
        Route::post('/client-issue', 'store')->name('client-issue.store');
        Route::get('/client-issue/{id}', 'show')->name('client-issue.show');
        Route::post('/client-issue/{clientIssue}/assign', 'assignTeam')->name('client-issue.assign');
        Route::patch('/client-issue/{id}/status', 'updateStatus')->name('client-issue.update-status');
        Route::delete('/client-issue/{id}', 'destroy')->name('client-issue.destroy');
        Route::delete('/client-issue/delete-selected', 'deleteSelected')->name('delete.selected.client-issue');

        // Client issue task routes
        Route::post('/client-issue/{clientIssue}/task', 'taskStore')->name('client-issue.task.store');
        Route::get('/client-issue/{clientIssue}/task/{task}', 'taskShow')->name('client-issue.task.show');
        Route::put('/client-issue/{clientIssue}/task/{task}', 'taskUpdate')->name('client-issue.task.update');
        Route::patch('/client-issue/{clientIssue}/task/{task}/status', 'taskUpdateStatus')->name('client-issue.task.update-status');
        Route::delete('/client-issue/{clientIssue}/task/{task}', 'taskDestroy')->name('client-issue.task.destroy');
    });


    // Lead CRUD routes
    Route::controller(LeadController::class)->group(function () {
        Route::get('/leads', 'index')->name('leads')->middleware('permission:view_leads');
        Route::get('/add-lead', 'create')->name('add-lead')->middleware('permission:create_leads');
        Route::post('/store-lead', 'store')->name('lead.store')->middleware('permission:create_leads');
        Route::get('/view-lead/{id}', 'show')->name('lead.show')->middleware('permission:view_leads');
        Route::get('/edit-lead/{id}', 'edit')->name('lead.edit')->middleware('permission:edit_leads');
        Route::put('/update-lead/{id}', 'update')->name('lead.update')->middleware('permission:edit_leads');
        Route::delete('/delete-lead/{id}', 'destroy')->name('lead.destroy')->middleware('permission:delete_leads');
        Route::post('/lead/toggle-status', 'toggleStatus')->name('lead.toggleStatus')->middleware('permission:edit_leads');
        Route::post('/lead/delete-selected', 'deleteSelected')->name('lead.delete-selected')->middleware('permission:delete_leads');
        Route::get('/lead/export', 'export')->name('lead.export')->middleware('permission:view_leads');
    });



    // Tag routes
    Route::controller(TagController::class)->group(function () {
        Route::get('/tags', 'index')->name('tags.index');
        Route::post('/tags', 'store')->name('tags.store');
        Route::put('/tags/{id}', 'update')->name('tags.update');
        Route::delete('/tags/{id}', 'destroy')->name('tags.destroy');
        Route::get('/tags/search', 'search')->name('tags.search');
        Route::get('/tags/all', 'getAllTags')->name('tags.all');
        Route::post('/tags/{id}/toggle-status', 'toggleStatus')->name('tags.toggle-status');
    });



    Route::controller(TodoController::class)->group(function () {
        Route::get('/to-do-list', 'index')->name('to-do-list');
        Route::get('/todos', 'list')->name('todos.list');
        Route::post('/todos', 'store')->name('todos.store');
        Route::put('/todos/{todo}', 'update')->name('todos.update');
        Route::delete('/todos/{todo}', 'destroy')->name('todos.destroy');
        Route::patch('/todos/{todo}/status', 'toggleStatus')->name('todos.status');
    });


    Route::controller(BookCallController::class)->group(function () {
        Route::get('/book-call', 'index')
            ->name('book-call.index')
            ->middleware('permission:view_book_calls');
        Route::delete('/book-call/{bookCall}', 'destroy')
            ->name('book-call.destroy')
            ->middleware('permission:view_book_calls');
    });

    // Digital Marketing Leads routes
    Route::controller(DigitalMarketingLeadController::class)->group(function () {
        Route::get('/digital-marketing-leads', 'index')
            ->name('digital-marketing-leads.index')
            ->middleware('permission:view_digital_marketing_leads');
        Route::delete('/digital-marketing-leads/{digitalMarketingLead}', 'destroy')
            ->name('digital-marketing-leads.destroy')
            ->middleware('permission:view_digital_marketing_leads');
    });




    // Service CRUD routes
    Route::resource('services', ServiceController::class)->middleware('permission:view_services');
    Route::controller(ServiceController::class)->group(function () {
        Route::post('/services/delete-selected', 'deleteSelected')->name('delete.selected.service')->middleware('permission:delete_services');
        Route::get('/servies', 'index')->name('servies');
    });


    // Backward-compatible route (menu currently uses app-to-do)
    Route::get('/app-to-do', function () {
        return redirect()->route('to-do-list');
    })->name('app-to-do');

    // Other protected routes
    Route::get('/add-servies', function () {
        return view('add-servies');
    })->name('add-servies')->middleware('permission:create_renewals');

    Route::get('/servies', function () {
        return view('servies');
    })->name('servies')->middleware('permission:view_renewals');

    Route::get('/user-profile', function () {
        return view('user-profile');
    })->name('user-profile');


    // Mail routes for sending renewal emails
    Route::controller(MailController::class)->group(function () {
        Route::get('/send-mail/{service_id}', 'sendMailForm')->name('send-mail');
        Route::post('/send-mail', 'sendMail')->name('send-mail.send');
        Route::post('/send-whatsapp-renewal/{service_id}', 'sendWhatsAppReminder')->name('send-whatsapp-renewal');
    });

    // Notification routes
    Route::controller(NotificationController::class)->group(function () {
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/renewal', 'getRenewalNotifications')->name('renewal');
            Route::get('/counts', 'getNotificationCounts')->name('counts');
            Route::get('/urgent', 'getUrgentNotifications')->name('urgent');
            Route::get('/summary', 'getNotificationSummary')->name('summary');
            Route::post('/mark-read', 'markAsRead')->name('mark-read');
            Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
        });
    });

    // Calendar Event routes
    Route::controller(CalendarEventController::class)->group(function () {
        Route::prefix('calendar')->name('calendar.')->middleware('permission:view_calendar|view_dashboard')->group(function () {
            Route::get('/events', 'getEvents')->name('events');
            Route::post('/events', 'store')->name('store');
            Route::get('/events/{id}', 'show')->name('show');
            Route::put('/events/{id}', 'update')->name('update');
            Route::delete('/events/{id}', 'destroy')->name('destroy');
            Route::post('/toggle-status', 'toggleStatus')->name('toggleStatus');
        });
    });
});
