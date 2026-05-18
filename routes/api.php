<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookACallController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientIssueController as ApiClientIssueController;
use App\Http\Controllers\Api\ClientRenewalController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FcmTestController;
use App\Http\Controllers\Api\GoogleAdsController;
use App\Http\Controllers\Api\GoogleLeadApiController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\MetaLeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProjectController as ApiProjectController;
use App\Http\Controllers\Api\RolesController as ApiRoleController;
use App\Http\Controllers\Api\ServiceController as ApiServiceController;
use App\Http\Controllers\Api\SettingController as ApiSettingController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\VendorController as ApiVendorController;
use App\Http\Controllers\Api\VendorRenewalController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\GoogleAdsLeadController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes (no authentication required)
Route::prefix('/v1')->group(function () {
    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset Routes
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('/v1')->group(function () {

        // Only for testing notification
        Route::post('/test-fcm', [FcmTestController::class, 'send'])->middleware('throttle:60,1');

        Route::controller(AuthController::class)->group(function () {
            Route::get('/me', 'me');
            Route::post('/fcm-token', 'storeFcmToken');
            Route::post('/logout', 'logout');
            Route::post('/logout-all', 'logoutAll');
        });

        Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index');
            Route::patch('/{id}/read', 'markAsRead');
            Route::patch('/read-all', 'markAllAsRead');
        });

        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index');
            Route::get('/quick-stats', 'quickStats');
        });

        // Permission API routes
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->middleware('permission:view_permissions');
            Route::get('/grouped', [PermissionController::class, 'apiGroupedPermissions'])->middleware('permission:view_permissions');
        });

        // Role API routes
        Route::prefix('roles')->group(function () {
            Route::controller(ApiRoleController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_roles');
                Route::post('/', 'store')->middleware('permission:create_roles');
                Route::match(['put', 'patch'], '/{id}', 'update')->middleware('permission:edit_roles');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_roles');
            });
        });

        // Staff API routes
        Route::controller(StaffController::class)->group(function () {
            Route::prefix('staff')->group(function () {
                Route::get('/form-options', 'apiFormOptions')->middleware('permission:create_staff');
                Route::get('/', 'apiIndex')->middleware('permission:view_staff');
                Route::get('/{id}', 'apiShow')->middleware('permission:view_staff');
                Route::post('/', 'apiStore')->middleware('permission:create_staff');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate')->middleware('permission:edit_staff');
                Route::delete('/{id}', 'apiDestroy')->middleware('permission:delete_staff');
                Route::post('/{id}/restore', 'apiRestore')->middleware('permission:edit_staff');
                Route::delete('/{id}/force', 'apiForceDelete')->middleware('permission:delete_staff');
            });
        });

        Route::controller(App\Http\Controllers\Api\StaffController::class)->group(function () {
            Route::prefix('staff-v2')->group(function () {
                Route::get('/departments', 'departments')->middleware('permission:view_staff');
                Route::get('/teams', 'teams')->middleware('permission:view_staff');

                Route::get('/', 'index')->middleware('permission:view_staff');
                Route::get('/{id}', 'show')->middleware('permission:view_staff');
                Route::post('/', 'store')->middleware('permission:create_staff');
                Route::put('/{id}', 'update')->middleware('permission:edit_staff');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_staff');
                Route::post('/{id}/restore', 'restore')->middleware('permission:edit_staff');
                Route::delete('/{id}/force', 'forceDelete')->middleware('permission:delete_staff');
            });

            Route::prefix('staff')->group(function () {
                Route::get('/{id}/tasks', 'staffTasks')->middleware('permission:view_tasks');
                Route::get('/{id}/projects', 'staffProjects')->middleware('permission:view_projects');
            });
        });

        // Customer/Client API routes
        Route::prefix('clients')->group(function () {
            Route::controller(ClientController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_clients');
                Route::get('/{id}', 'show')->middleware('permission:view_clients');
                Route::post('/', 'store')->middleware('permission:create_clients');
                Route::match(['put', 'patch'], '/{id}', 'update')->middleware('permission:edit_clients');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_clients');
            });
            // Route::get('/', [CustomerController::class, 'apiIndex']);
            // Route::get('/{id}', [CustomerController::class, 'apiShow']);
            // Route::post('/', [CustomerController::class, 'apiStore']);
            // Route::match(['put', 'patch'], '/{id}', [CustomerController::class, 'apiUpdate']);
            // Route::delete('/{id}', [CustomerController::class, 'apiDestroy']);
            // Route::post('/{id}/restore', [CustomerController::class, 'apiRestore']);
            // Route::delete('/{id}/force', [CustomerController::class, 'apiForceDelete']);
            // // Client Tasks
            // Route::get('/{id}/tasks', [CustomerController::class, 'apiClientTasks']);
            // Route::get('/{id}/tasks/{taskId}', [CustomerController::class, 'apiClientTaskDetail']);
            // // Client Projects
            // Route::get('/{id}/projects', [CustomerController::class, 'apiClientProjects']);
            // Route::get('/{id}/projects/{projectId}', [CustomerController::class, 'apiClientProjectDetail']);
            // // Client issues
            // Route::get('/{id}/issues', [CustomerController::class, 'apiClientIssues']);
            // Route::get('/{id}/issues/{issueId}', [CustomerController::class, 'apiClientIssueDetail']);
        });

        // Vendor API routes
        Route::prefix('vendors')->group(function () {
            Route::controller(ApiVendorController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_vendors');
                Route::get('/{id}', 'show')->middleware('permission:view_vendors');
                Route::post('/', 'store')->middleware('permission:create_vendors');
                Route::match(['put', 'patch'], '/{id}', 'update')->middleware('permission:edit_vendors');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_vendors');
            });
        });

        // Vendor Renewal API routes
        Route::prefix('vendor-renewals')->group(function () {
            Route::controller(VendorRenewalController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_renewals');
                Route::get('/{id}', 'show')->middleware('permission:view_renewals');
                Route::post('/', 'store')->middleware('permission:create_renewals');
                Route::put('/{id}', 'update')->middleware('permission:edit_renewals');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_renewals');
            });
        });


      

        Route::prefix('todos')->group(function () {
            Route::controller(TodoController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{todo}', 'show');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '/{todo}', 'update');
                Route::delete('/{todo}', 'delete');

                Route::get('/options', 'apiTodoOptions');
                Route::patch('/{todo}/status', 'apiToggleTodoStatus');
            });
        });

        // Lead API routes
        Route::prefix('leads')->group(function () {
            Route::controller(LeadController::class)->group(function () {
                Route::get('/form-options', 'apiFormOptions')->middleware('permission:view_leads');
                Route::get('/dashboard', 'dashboard')->middleware('permission:view_leads');

                Route::get('/', 'apiIndex')->middleware('permission:view_leads');
                Route::get('/{id}', 'apiShow')->middleware('permission:view_leads');
                Route::post('/', 'apiStore')->middleware('permission:create_leads');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate')->middleware('permission:edit_leads');
                Route::delete('/{id}', 'apiDestroy')->middleware('permission:delete_leads');
            });
        });

        // Meta Lead API routes
        Route::prefix('meta-leads')->group(function () {
            Route::controller(MetaLeadController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_leads');
                Route::get('/{lead}', 'show')->middleware('permission:view_leads');
                Route::post('/sync', 'sync')->middleware('permission:create_leads');
                Route::delete('/{lead}', 'destroy')->middleware('permission:delete_leads');
            });
        });


        // Project API routes
        Route::prefix('projects')->group(function () {
            // Projects Only
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/form-options', 'apiFormOptions')->middleware('permission:view_projects');
                Route::get('/', 'apiIndex')->middleware('permission:view_projects');
                Route::post('/', 'apiStore')->middleware('permission:create_projects');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate')->middleware('permission:edit_projects');
                Route::delete('/{id}', 'apiDestroy')->middleware('permission:delete_projects');
                Route::get('/{id}', 'apiShow')->middleware('permission:view_projects');
            });

            // Projects Milestone
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/{projectId}/milestones', 'apiMilestoneIndex');
                Route::post('/{projectId}/milestones', 'apiStoreMilestone');
                Route::match(['put', 'patch'], '/{projectId}/milestones/{milestoneId}', 'apiUpdateMilestone');
                Route::delete('/{projectId}/milestones/{milestoneId}', 'apiDestroyMilestone');
            });

            // Projects Issues
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/{projectId}/issues', 'apiIssueIndex');
                Route::post('/{projectId}/issues', 'apiStoreIssue');
                Route::match(['put', 'patch'], '/{projectId}/issues/{issueId}', 'apiUpdateIssue');
                Route::delete('/{projectId}/issues/{issueId}', 'apiDestroyIssue');
            });

            // Projects Comments
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/{projectId}/comments', 'apiCommentIndex');
                Route::post('/{projectId}/comments', 'apiStoreComment');
            });

            // Projects Files
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/{projectId}/files', 'apiFileIndex');
                Route::post('/{projectId}/files', 'apiUploadFile');
                Route::delete('/{projectId}/files/{fileId}', 'apiDeleteFile');
            });

            // Projects Usage
            Route::get('/{projectId}/usage', [ApiProjectController::class, 'apiUsage']);
        });

        // Task API routes
        Route::prefix('tasks')->group(function () {
            Route::get('/form-options', [ApiTaskController::class, 'apiFormOptions'])->middleware('permission:view_tasks');

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/', 'apiIndex')->middleware('permission:view_tasks');
                Route::get('/{id}', 'apiShow')->middleware('permission:view_tasks');
                Route::post('/', 'apiStore')->middleware('permission:create_tasks');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate')->middleware('permission:edit_tasks');
                Route::delete('/{id}', 'apiDestroy')->middleware('permission:delete_tasks');
            });


            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/comments', 'apiCommentIndex');
                Route::post('/{taskId}/comments', 'apiStoreComment');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/attachments', 'apiAttachmentIndex');
                Route::post('/{taskId}/attachments', 'apiUploadAttachment');
                Route::delete('/{taskId}/attachments/{attachmentId}', 'apiDeleteAttachment');
            });
        });


        // Client issue API routes
        Route::prefix('client-issues')->group(function () {
            Route::get('/form-options', [ApiClientIssueController::class, 'formOptions'])->middleware('permission:view_raise_issue');

            Route::controller(ApiClientIssueController::class)->group(function () {
                Route::get('/', 'index')->middleware('permission:view_raise_issue');
                Route::post('/', 'store')->middleware('permission:create_raise_issue');
                Route::get('/{id}', 'show')->middleware('permission:view_raise_issue');
                Route::patch('/{id}/status', 'updateStatus')->middleware('permission:edit_raise_issue');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_raise_issue');
            });

            Route::controller(ApiClientIssueController::class)->group(function () {
                Route::post('/{clientIssue}/assign', 'assignTeam')->middleware('permission:assign_tasks');
                Route::post('/{clientIssue}/tasks', 'taskStore')->middleware('permission:create_tasks');
                Route::get('/{clientIssue}/tasks/{task}', 'taskShow')->middleware('permission:view_tasks');
                Route::match(['put', 'patch'], '/{clientIssue}/tasks/{task}', 'taskUpdate')->middleware('permission:edit_tasks');
                Route::patch('/{clientIssue}/tasks/{task}/status', 'taskUpdateStatus')->middleware('permission:edit_tasks');
                Route::delete('/{clientIssue}/tasks/{task}', 'taskDestroy')->middleware('permission:delete_tasks');
            });
        });


        // Service API routes  -- not used anywhere
        Route::prefix('services')->group(function () {
            Route::get('/form-options', [ApiServiceController::class, 'formOptions'])->middleware('permission:view_services');
            Route::get('/', [ApiServiceController::class, 'index'])->middleware('permission:view_services');
            Route::get('/{id}', [ApiServiceController::class, 'show'])->middleware('permission:view_services');
            Route::post('/', [ApiServiceController::class, 'store'])->middleware('permission:create_services');
            Route::match(['put', 'patch'], '/{id}', [ApiServiceController::class, 'update'])->middleware('permission:edit_services');
            Route::delete('/{id}', [ApiServiceController::class, 'destroy'])->middleware('permission:delete_services');
            Route::post('/delete-selected', [ApiServiceController::class, 'deleteSelected'])->middleware('permission:delete_services');
        });

        // Calendar appointment API routes
        Route::prefix('calendar')->group(function () {
            Route::controller(CalendarEventController::class)->group(function () {
                Route::get('/events', 'apiIndex')->middleware('permission:view_calendar');
                Route::get('/events/{id}', 'apiShow')->middleware('permission:view_calendar');
                Route::post('/events', 'apiStore')->middleware('permission:manage_calendar');
                Route::match(['put', 'patch'], '/events/{id}', 'apiUpdate')->middleware('permission:manage_calendar');
                Route::delete('/events/{id}', 'apiDestroy')->middleware('permission:manage_calendar');
            });
        });



        // Settings API routes
        Route::prefix('settings')->group(function () {
            Route::get('/', [ApiSettingController::class, 'index'])->middleware('permission:manage_settings');

            Route::get('/general', [ApiSettingController::class, 'general'])->middleware('permission:view_general_settings');
            Route::match(['post', 'put', 'patch'], '/general', [ApiSettingController::class, 'updateGeneral'])->middleware('permission:manage_settings');
            Route::get('/company', [ApiSettingController::class, 'company'])->middleware('permission:view_company_information');
            Route::match(['post', 'put', 'patch'], '/company', [ApiSettingController::class, 'updateCompany'])->middleware('permission:manage_settings');
            Route::get('/email', [ApiSettingController::class, 'email'])->middleware('permission:view_email_settings');
            Route::match(['post', 'put', 'patch'], '/email', [ApiSettingController::class, 'updateEmail'])->middleware('permission:manage_settings');
            Route::get('/renewal', [ApiSettingController::class, 'renewal'])->middleware('permission:view_renewals');
            Route::match(['post', 'put', 'patch'], '/renewal', [ApiSettingController::class, 'updateRenewal'])->middleware('permission:manage_settings');
            Route::get('/teams', [ApiSettingController::class, 'teams'])->middleware('permission:view_staff');
            Route::match(['post', 'put', 'patch'], '/teams', [ApiSettingController::class, 'updateTeams'])->middleware('permission:manage_settings');
            Route::get('/departments', [ApiSettingController::class, 'departments'])->middleware('permission:view_staff');
            Route::match(['post', 'put', 'patch'], '/departments', [ApiSettingController::class, 'updateDepartments'])->middleware('permission:manage_settings');
            Route::post('/test-email', [ApiSettingController::class, 'sendTestEmail'])->middleware('permission:manage_settings');
            Route::get('/search-tags', [ApiSettingController::class, 'searchTags'])->middleware('permission:view_general_settings');
            Route::get('/app-logo', [ApiSettingController::class, 'getAppLogo'])->middleware('permission:view_general_settings');
            Route::match(['post', 'put', 'patch'], '/app-logo', [ApiSettingController::class, 'updateAppLogo'])->middleware('permission:manage_settings');
            Route::get('/login-logo', [ApiSettingController::class, 'getLoginLogo'])->middleware('permission:view_general_settings');
            Route::match(['post', 'put', 'patch'], '/login-logo', [ApiSettingController::class, 'updateLoginLogo'])->middleware('permission:manage_settings');
        });

        // Route::get('/clients', [ClientRenewalController::class, 'clientList']);
        // Route::get('/vendors', [ClientRenewalController::class, 'vendorList']);

        // Book a Call 
        Route::controller(BookACallController::class)->group(function () {
            Route::get('/book-a-call', 'index')->middleware('permission:view_book_calls');
            Route::delete('/book-a-call/{id}', 'destroy')->middleware('permission:delete_book_calls');
        });

        Route::controller(GoogleAdsController::class)->group(function () {
            Route::get('/digital-marketing', 'indexDigitalMarketing')->middleware('permission:view_digital_marketing_leads');
            Route::delete('/digital-marketing/{id}', 'destroyDigitalMarketing')->middleware('permission:delete_digital_marketing_leads');

            Route::get('/web-apps-leads', 'indexWebAppsLeads');
            Route::delete('/web-apps-leads/{id}', 'destroyWebAppsLeads');
        });


        Route::get('/google-ads-leads', [GoogleLeadApiController::class, 'index']);
        Route::get('/google-ads-leads/stats', [\App\Http\Controllers\Api\GoogleLeadApiController::class, 'stats']);
        Route::get('/leads/{googleLead}', [\App\Http\Controllers\Api\GoogleLeadApiController::class, 'show'])
            ->missing(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found',
                ], 404);
            });
    });
});

// Fallback route for authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Meta Lead Ads Webhook
Route::get('/facebook/webhook', [App\Http\Controllers\FacebookWebhookController::class, 'verify']);
Route::post('/facebook/webhook', [App\Http\Controllers\FacebookWebhookController::class, 'handle']);
Route::post('/google-ads/lead', [GoogleAdsLeadController::class, 'receive']);
