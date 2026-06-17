<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookCallController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIssueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalMarketingLeadController;
use App\Http\Controllers\GoogleLeadViewController;
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
use App\Http\Controllers\Web\ClientController as WebClientController;
use App\Http\Controllers\Web\LeadManagementController;
use App\Http\Controllers\Web\VendorController;
use App\Http\Controllers\Web\VendorServiceController;
use App\Http\Controllers\WebEnquiryController;
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
    Route::post('/forgot-password', 'forgotPassword')->name('password.email');
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
    Route::get('/user-profile', [AuthController::class, 'profile'])->name('user-profile');
    Route::put('/user-profile', [AuthController::class, 'updateProfile'])->name('user-profile.update');

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
        Route::get('/roles', 'index')->name('roles.index');
        Route::get('/create-role', 'create')->name('role.create');
        Route::post('/store-role', 'store')->name('role.store');
        Route::get('/edit-role/{id}', 'edit')->name('role.edit');
        Route::put('/edit-role/{id}', 'update')->name('role.update');
        Route::delete('/role/delete/{id}', 'destroy')->name('role.delete');
        Route::delete('/role/delete-selected', 'deleteSelected')->name('delete.selected.role');
        // permanent delete & restore 
    });
    // ============================= End Role Controller ====================

    // Permission routes
    // Route::group([], function () {
    //     Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
    //     Route::get('/add-permission', [PermissionController::class, 'create'])->name('add-permission');
    //     Route::post('/add-permission', [PermissionController::class, 'store'])->name('store-permission');
    //     Route::get('/edit-permission/{id}', [PermissionController::class, 'edit'])->name('permission.edit');
    //     Route::put('/edit-permission/{id}', [PermissionController::class, 'update'])->name('permission.update');
    //     Route::delete('/permission/delete/{id}', [PermissionController::class, 'destroy'])->name('permission.delete');
    //     Route::post('/permission/assign-to-role', [PermissionController::class, 'assignToRole'])->name('permission.assign-to-role');
    //     Route::post('/permission/remove-from-role', [PermissionController::class, 'removeFromRole'])->name('permission.remove-from-role');
    // });


    // ============================= Settings Controller ====================
    Route::controller(SettingController::class)->group(function () {
        Route::prefix('settings')->group(function () {
            Route::get('/', 'index')->name('settings');
            Route::put('/general', 'updateGeneral')->name('settings.update.general');
            Route::put('/company', 'updateCompany')->name('settings.update.company');
            Route::put('/email', 'updateEmail')->name('settings.update.email');
            Route::put('/renewal', 'updateRenewal')->name('settings.update.renewal');
            Route::put('/teams', 'updateTeams')->name('settings.update.teams');
            Route::put('/departments', 'updateDepartments')->name('settings.update.departments');
            Route::post('/test-email', 'sendTestEmail')->name('settings.test.email');
            Route::get('/search-tags', 'searchTags')->name('settings.search.tags');
        });
    });
    // ============================= End Settings Controller ====================



    // ============================= Staff Controller ====================
    Route::controller(StaffController::class)->group(function () {
        Route::get('/staff', 'index')->name('staff');
        Route::delete('/staff/delete/{id}', 'destroy')->name('staff.destroy');
        Route::delete('/staff/delete-selected', 'deleteSelected')->name('delete.selected.staff');
        Route::post('/staff/restore/{id}', 'restore')->name('staff.restore');
        Route::delete('/staff/force-delete/{id}', 'forceDelete')->name('staff.force-delete');
        Route::get('/add-staff', 'create')->name('add-staff');
        Route::get('/view-staff/{id}', 'show')->name('view-staff');
        Route::get('/staff/{id}/analytics', 'analytics')->name('staff.analytics');
        Route::get('/staff/{id}/lead-chart', 'leadChart')->name('staff.lead-chart');
        Route::get('/staff/{id}/followup-chart', 'followupChart')->name('staff.followup-chart');
        Route::post('/store-staff', 'store')->name('staff.store');
        Route::put('/update-staff/{id}', 'update')->name('staff.update');
    });
    // ============================= End Staff Controller ====================



    // ============================= Renewals Section Start ====================
    // Vendor CRUD routes
    Route::resource('vendors', VendorController::class);
    // Additional routes for backward compatibility
    Route::controller(VendorController::class)->group(function () {
        Route::get('/vendor1', 'index')->name('vendor1');

        Route::get('/add-vendor', 'create')->name('add-vendor');
        Route::post('/vendor1/bulk-upload', 'bulkUpload')->name('vendors.bulk-upload');
        Route::get('/vendor1/download-template', 'downloadTemplate')->name('vendors.download-template');
        Route::delete('/vendor1/delete-selected', 'deleteSelected')->name('delete.selected.vendor');
        Route::post('/vendor1/toggle-status',  'toggleStatus')->name('vendor1.toggleStatus');
    });

    // Vendor Service CRUD routes - Vendor Renewals
    Route::resource('vendor-services', VendorServiceController::class);
    Route::post('/vendor-services/delete-selected', [VendorServiceController::class, 'deleteSelected'])->name('delete.selected.vendor-service');


    // ============================= Client Controller ====================
    Route::controller(ClientController::class)->group(function () {
        Route::get('/clients', 'index')->name('client');
        Route::get('/clients/create', 'create')->name('client.create');
        Route::post('/clients', 'store')->name('client.store');
        Route::delete('/clients/delete-selected', 'deleteSelected')->name('delete.selected.client');
        Route::get('/clients/{client}', 'show')->name('client.view');
        Route::get('/clients/{client}/edit', 'edit')->name('client.edit');
        Route::put('/clients/{client}', 'update')->name('client.update');
        Route::delete('/clients/{client}', 'destroy')->name('client.delete');
        Route::patch('/clients/{client}/status', 'toggleStatus')->name('client.toggleStatus');
        Route::post('/clients/bulk-upload', 'bulkUpload')->name('client.bulk-upload');
        Route::get('/clients/download-template', 'downloadTemplate')->name('client.download-template');
    });

    // Service CRUD routes - Client Renewals
    Route::resource('services', ServiceController::class);
    Route::controller(ServiceController::class)->group(function () {
        Route::post('/services/delete-selected', 'deleteSelected')->name('delete.selected.service');
        Route::post('/services/{service}/amc-visits/{detail}', 'updateAmcVisit')->name('services.amc-visits.update');
        Route::get('/servies', 'index')->name('servies');
    });
    // ============================= Renewals Section End ====================



    // ============================= Projects Section Start ====================
    // Project routes
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/project', 'index')->name('project');
        Route::get('/my-projects', 'myProjects')->name('my-projects');
        Route::get('/edit-project/{id}', 'edit')->name('edit-project');
        Route::put('/edit-project/{id}', 'update')->name('update-project');
        Route::get('/add-project', 'create')->name('add-project');
        Route::post('/add-project', 'store')->name('store-project');
        Route::get('/project-details/{id}', 'show')->name('project-details');
        Route::post('/project/{projectId}/milestones', 'storeMilestone')->name('project.milestones.store');
        Route::put('/project/{projectId}/milestones/{milestoneId}', 'updateMilestone')->name('project.milestones.update');
        Route::delete('/project/{projectId}/milestones/{milestoneId}', 'destroyMilestone')->name('project.milestones.destroy');
        Route::post('/project/{projectId}/issues', 'storeIssue')->name('project.issues.store');
        Route::put('/project/{projectId}/issues/{issueId}', 'updateIssue')->name('project.issues.update');
        Route::delete('/project/{projectId}/issues/{issueId}', 'destroyIssue')->name('project.issues.destroy');
        Route::delete('/project/delete/{id}', 'destroy')->name('project.destroy');
        Route::delete('/project/delete-selected', 'deleteSelected')->name('delete.selected.project');

        // Project File Routes
        Route::post('/project/{projectId}/upload-file', 'uploadFile')->name('project.upload-file');
        Route::get('/project/file/{fileId}/download', 'downloadFile')->name('project.file.download');
        Route::delete('/project/file/{fileId}/delete', 'deleteFile')->name('project.file.delete');
        Route::post('/project-details/{id}/comment', 'storeComment')->name('project.comment.store');
        Route::get('/project/{projectId}/ajax/charts', 'ajaxCharts')->name('project.ajax.charts');
        Route::get('/project/{projectId}/ajax/activity-feed', 'ajaxActivityFeed')->name('project.ajax.activity-feed');
        Route::get('/project/{projectId}/ajax/milestone-progress', 'ajaxMilestoneProgress')->name('project.ajax.milestone-progress');
        Route::get('/project/{projectId}/ajax/task-filter', 'ajaxTaskFilter')->name('project.ajax.task-filter');
        Route::get('/project/{projectId}/ajax/kanban-snapshot', 'ajaxKanbanSnapshot')->name('project.ajax.kanban-snapshot');
    });

    // Task routes
    Route::controller(TaskController::class)->group(function () {
        Route::get('/task', 'index')->name('task');
        Route::get('/task-kanban', 'kanban')->name('task.kanban');
        Route::get('/task-kanban/data', 'kanbanData')->name('task.kanban.data');
        Route::post('/task-kanban/{id}/move', 'kanbanMove')->name('task.kanban.move');
        Route::get('/add-task', 'create')->name('add-task');
        Route::post('/add-task', 'store')->name('add-task.store');
        Route::get('/task-details/{id}', 'show')->name('task-details');
        Route::post('/task-details/{id}/comment', 'storeComment')->name('task.comment.store');
        Route::get('/edit-task/{id}', 'edit')->name('edit-task');
        Route::put('/edit-task/{id}', 'update')->name('edit-task.update');
        Route::delete('/task/delete/{id}', 'destroy')->name('task.destroy');
        Route::delete('/task/delete-selected', 'deleteSelected')->name('delete.selected.task');
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
    // ============================= Projects Section End ====================


    // ============================= Leads Section Start ====================
    Route::controller(LeadManagementController::class)->prefix('lead-management')->name('lead-management.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{source}/{id}/view', 'show')->name('show');
        Route::post('/{source}/{id}/assign', 'assign')->name('assign');
        Route::post('/bulk-assign', 'bulkAssign')->name('bulk-assign');
        Route::patch('/{source}/{id}/status', 'updateStatus')->name('status');
        Route::post('/{source}/{id}/followup', 'addFollowup')->name('followup');
        Route::get('/{source}/{id}/followups', 'followupHistory')->name('followups');
        Route::post('/{source}/{id}/note', 'addNote')->name('note');
        Route::post('/{source}/{id}/reminder', 'addReminder')->name('reminder');
        Route::get('/{source}/{id}/timeline', 'activityTimeline')->name('timeline');
        Route::post('/{source}/{id}/convert', 'convertLead')->name('convert');
        Route::post('/{source}/{id}/escalate', 'escalateLead')->name('escalate');
        Route::get('/performance/stats', 'performanceStats')->name('performance');
        Route::delete('/{source}/{id}', 'destroy')->name('destroy');
    });

    // Lead CRUD routes
    Route::controller(LeadController::class)->group(function () {
        Route::get('/leads', 'index')->name('leads');
        Route::get('/add-lead', 'create')->name('add-lead');
        Route::post('/store-lead', 'store')->name('lead.store');
        Route::get('/view-lead/{id}', 'show')->name('lead.show');
        Route::get('/edit-lead/{id}', 'edit')->name('lead.edit');
        Route::put('/update-lead/{id}', 'update')->name('lead.update');
        Route::delete('/delete-lead/{id}', 'destroy')->name('lead.destroy');
        Route::post('/lead/toggle-status', 'toggleStatus')->name('lead.toggleStatus');
        Route::post('/lead/delete-selected', 'deleteSelected')->name('lead.delete-selected');
        Route::get('/lead/export', 'export')->name('lead.export');
    });


    // Digital Marketing Leads routes
    Route::controller(DigitalMarketingLeadController::class)->group(function () {
        Route::get('/digital-marketing-leads', 'index')
            ->name('digital-marketing-leads.index')
        ;
        Route::patch('/digital-marketing-leads/{source}/{id}/status', 'updateStatus')
            ->name('digital-marketing-leads.status')
        ;
        Route::delete('/digital-marketing-leads/{digitalMarketingLead}', 'destroy')
            ->name('digital-marketing-leads.destroy')
        ;
    });

    Route::controller(WebEnquiryController::class)->group(function () {
        Route::get('/web-enquiry/contact', 'contact')
            ->name('web-enquiry.contact')
        ;
        Route::delete('/web-enquiry/contact/{id}', 'contactDestroy')
            ->name('web-enquiry.contact.destroy')
        ;
        Route::get('/web-enquiry/career', 'career')
            ->name('web-enquiry.career')
        ;
        Route::get('/web-enquiry/career/{id}', 'careerShow')
            ->name('web-enquiry.career.show')
        ;
        Route::delete('/web-enquiry/career/{id}', 'careerDestroy')
            ->name('web-enquiry.career.destroy')
        ;
    });


    Route::controller(BookCallController::class)->group(function () {
        Route::get('/book-call', 'index')
            ->name('book-call.index')
        ;
        Route::delete('/book-call/{bookCall}', 'destroy')
            ->name('book-call.destroy')
        ;
    });

    // Meta Leads:
    Route::prefix('leads/meta')->name('leads.')->group(function () {
        Route::get('/', [App\Http\Controllers\MetaLeadUiController::class, 'index'])
            ->name('index');
        Route::get('/{lead}', [App\Http\Controllers\MetaLeadUiController::class, 'show'])
            ->name('show');
        Route::patch('/{lead}/status', [App\Http\Controllers\MetaLeadUiController::class, 'updateStatus'])
            ->name('status');
        Route::post('/sync', [App\Http\Controllers\MetaLeadUiController::class, 'sync'])
            ->name('sync');
        Route::delete('/{lead}', [App\Http\Controllers\MetaLeadUiController::class, 'destroy'])
            ->name('destroy');
    });

    Route::resource('google-leads', GoogleLeadViewController::class)->only(['index', 'show']);
    Route::patch('/google-leads/{googleLead}/status', [GoogleLeadViewController::class, 'updateStatus'])
        ->name('google-leads.status');
    // ============================= Leads Section End ====================





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


    // ============================= To Do Section Start ====================
    Route::controller(TodoController::class)->group(function () {
        Route::get('/to-do-list', 'index')->name('to-do-list');
        Route::get('/todos', 'list')->name('todos.list');
        Route::post('/todos', 'store')->name('todos.store');
        Route::put('/todos/{todo}', 'update')->name('todos.update');
        Route::delete('/todos/{todo}', 'destroy')->name('todos.destroy');
        Route::patch('/todos/{todo}/status', 'toggleStatus')->name('todos.status');
    });
    // ============================= To Do Section End ====================




    // Backward-compatible route (menu currently uses app-to-do)
    Route::get('/app-to-do', function () {
        return redirect()->route('to-do-list');
    })->name('app-to-do');

    // Other protected routes
    Route::get('/add-servies', function () {
        return view('add-servies');
    })->name('add-servies');

    Route::get('/servies', function () {
        return view('servies');
    })->name('servies');

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
        Route::prefix('calendar')->name('calendar.')->group(function () {
            Route::get('/events', 'getEvents')->name('events');
            Route::post('/events', 'store')->name('store');
            Route::get('/events/{id}', 'show')->name('show');
            Route::put('/events/{id}', 'update')->name('update');
            Route::delete('/events/{id}', 'destroy')->name('destroy');
            Route::post('/toggle-status', 'toggleStatus')->name('toggleStatus');
        });
    });
});
