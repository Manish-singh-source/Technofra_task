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
use App\Http\Controllers\Api\V1\LeadManagementController as ApiLeadManagementController;
use App\Http\Controllers\Api\MetaLeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProjectController as ApiProjectController;
use App\Http\Controllers\Api\RolesController as ApiRoleController;
use App\Http\Controllers\Api\ServiceController as ApiServiceController;
use App\Http\Controllers\Api\SettingController as ApiSettingController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\Api\TodoController;
use App\Http\Controllers\Api\WebEnquiryContactController;
use App\Http\Controllers\Api\V1\ClientController as ApiV1ClientController;
use App\Http\Controllers\Api\V1\VendorController as ApiVendorController;
use App\Http\Controllers\Api\V1\VendorRenewalController;
use App\Http\Controllers\Api\WebEnquiryCareerController;
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
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/grouped', [PermissionController::class, 'apiGroupedPermissions']);
        });

        // Role API routes
        Route::prefix('roles')->group(function () {
            Route::controller(ApiRoleController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });

        // Staff API routes
        Route::controller(StaffController::class)->group(function () {
            Route::prefix('staff')->group(function () {
                Route::get('/form-options', 'apiFormOptions');
                Route::get('/', 'apiIndex');
                Route::get('/{id}', 'apiShow');
                Route::get('/{id}/analytics', 'analytics')->whereNumber('id');
                Route::get('/{id}/lead-chart', 'leadChart')->whereNumber('id');
                Route::get('/{id}/followup-chart', 'followupChart')->whereNumber('id');
                Route::post('/', 'apiStore');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate');
                Route::delete('/{id}', 'apiDestroy');
                Route::post('/{id}/restore', 'apiRestore');
                Route::delete('/{id}/force', 'apiForceDelete');
            });
        });

        Route::prefix('staff-v2')->controller(StaffController::class)->group(function () {
            Route::get('/form-options', 'apiFormOptions');
            Route::get('/{id}/analytics', 'analytics')->whereNumber('id');
            Route::get('/{id}/lead-chart', 'leadChart')->whereNumber('id');
            Route::get('/{id}/followup-chart', 'followupChart')->whereNumber('id');
            Route::get('/', 'apiIndex');
            Route::get('/{id}', 'apiShow')->whereNumber('id');
            Route::post('/', 'apiStore');
            Route::match(['put', 'patch'], '/{id}', 'apiUpdate')->whereNumber('id');
            Route::delete('/{id}', 'apiDestroy')->whereNumber('id');
            Route::post('/{id}/restore', 'apiRestore')->whereNumber('id');
            Route::delete('/{id}/force', 'apiForceDelete')->whereNumber('id');
        });

        Route::controller(App\Http\Controllers\Api\StaffController::class)->group(function () {
            Route::prefix('staff-v2')->group(function () {
                Route::get('/departments', 'departments');
                Route::get('/teams', 'teams');
            });

            Route::prefix('staff')->group(function () {
                Route::get('/{id}/tasks', 'staffTasks')->whereNumber('id');
                Route::get('/{id}/projects', 'staffProjects')->whereNumber('id');
            });
        });

        // Customer/Client API routes
        Route::prefix('clients')->group(function () {
            Route::controller(ApiV1ClientController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{client}', 'show');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '/{client}', 'update');
                Route::delete('/{client}', 'destroy');
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
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });

        // Vendor Renewal API routes
        Route::prefix('vendor-renewals')->group(function () {
            Route::controller(VendorRenewalController::class)->group(function () {
                Route::get('/form-options', 'apiFormOptions');
                
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });


        // Client Renewal API routes
        Route::prefix('client-renewals')->group(function () {
            Route::controller(ClientRenewalController::class)->group(function () {
                Route::get('/form-options', 'apiFormOptions');

                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });

        // to do list api 
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
                Route::get('/form-options', 'apiFormOptions');
                Route::get('/dashboard', 'dashboard');

                Route::get('/', 'apiIndex');
                Route::get('/{id}', 'apiShow');
                Route::post('/', 'apiStore');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate');
                Route::delete('/{id}', 'apiDestroy');
            });
        });

        // Lead Management API routes
        Route::controller(ApiLeadManagementController::class)->prefix('lead-management')->group(function () {
            Route::get('/', 'index');
            Route::get('/{source}/{id}/view', 'show');
            Route::get('/{source}/{id}/assignment', 'listAssignments');
            Route::post('/{source}/{id}/assign', 'assign');
            Route::post('/bulk-assign', 'bulkAssign');
            Route::get('/{source}/{id}/status-history', 'statusHistory');
            Route::patch('/{source}/{id}/status', 'updateStatus');
            Route::post('/{source}/{id}/followup', 'addFollowup');
            Route::get('/{source}/{id}/followups', 'followupHistory');
            Route::get('/{source}/{id}/note', 'listNotes');
            Route::post('/{source}/{id}/note', 'addNote');
            Route::get('/{source}/{id}/reminder', 'listReminders');
            Route::post('/{source}/{id}/reminder', 'addReminder');
            Route::get('/{source}/{id}/timeline', 'activityTimeline');
            Route::post('/{source}/{id}/convert', 'convertLead');
            Route::post('/{source}/{id}/escalate', 'escalateLead');
            Route::get('/performance/stats', 'performanceStats');
            Route::delete('/{source}/{id}', 'destroy');
        });

        // Meta Lead API routes
        Route::prefix('meta-leads')->group(function () {
            Route::controller(MetaLeadController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{lead}', 'show');
                Route::post('/sync', 'sync');
                Route::delete('/{lead}', 'destroy');
            });
        });


        // Project API routes
        Route::prefix('projects')->group(function () {
            // Projects Only
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/form-options', 'apiFormOptions');
                Route::get('/', 'apiIndex');
                Route::post('/', 'apiStore');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate');
                Route::delete('/{id}', 'apiDestroy');
                Route::get('/{id}', 'apiShow');
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
            Route::get('/{projectId}/kanban', [ApiProjectController::class, 'apiKanbanBoard']);
            Route::post('/{projectId}/kanban/move', [ApiProjectController::class, 'apiKanbanMove']);
            Route::get('/{projectId}/charts', [ApiProjectController::class, 'apiCharts']);
            Route::get('/{projectId}/activity-feed', [ApiProjectController::class, 'apiActivityFeed']);
            Route::get('/{projectId}/milestone-progress', [ApiProjectController::class, 'apiMilestoneProgress']);
            Route::get('/{projectId}/tasks/filter', [ApiProjectController::class, 'apiFilterTasks']);

            // Projects Change Requests
            Route::controller(ApiProjectController::class)->group(function () {
                Route::get('/{projectId}/change-requests', 'apiChangeRequestIndex');
                Route::post('/{projectId}/change-requests', 'apiStoreChangeRequest');
                Route::match(['put', 'patch'], '/{projectId}/change-requests/{changeRequestId}/status', 'apiUpdateChangeRequestStatus');
            });
        });

        // Task API routes
        Route::prefix('tasks')->group(function () {
            Route::get('/form-options', [ApiTaskController::class, 'apiFormOptions']);

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/', 'apiIndex');
                Route::get('/{id}', 'apiShow');
                Route::post('/', 'apiStore');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate');
                Route::delete('/{id}', 'apiDestroy');
            });


            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/comments', 'apiCommentIndex');
                Route::post('/{taskId}/comments', 'apiStoreComment');
                Route::match(['put', 'patch'], '/{taskId}/comments/{commentId}', 'apiUpdateComment');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/attachments', 'apiAttachmentIndex');
                Route::post('/{taskId}/attachments', 'apiUploadAttachment');
                Route::delete('/{taskId}/attachments/{attachmentId}', 'apiDeleteAttachment');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/dependencies', 'apiDependencyIndex');
                Route::post('/{taskId}/dependencies', 'apiStoreDependency');
                Route::delete('/{taskId}/dependencies/{dependsOnTaskId}', 'apiDeleteDependency');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::get('/{taskId}/checklists', 'apiChecklistIndex');
                Route::post('/{taskId}/checklists', 'apiStoreChecklist');
                Route::match(['put', 'patch'], '/{taskId}/checklists/{checklistId}', 'apiUpdateChecklist');
                Route::delete('/{taskId}/checklists/{checklistId}', 'apiDeleteChecklist');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::post('/{taskId}/time-logs/start', 'apiTimeLogStart');
                Route::post('/{taskId}/time-logs/stop', 'apiTimeLogStop');
                Route::post('/{taskId}/time-logs/manual', 'apiTimeLogManual');
                Route::get('/{taskId}/time-logs/report', 'apiTimeLogReport');
            });

            Route::controller(ApiTaskController::class)->group(function () {
                Route::post('/{taskId}/qa/request-review', 'apiQaRequestReview');
                Route::post('/{taskId}/qa/review', 'apiQaReview');
                Route::post('/{taskId}/qa/approve', 'apiQaApprove');
                Route::post('/{taskId}/deploy', 'apiMarkDeployed');
            });
        });


        // Client issue API routes
        Route::prefix('client-issues')->group(function () {
            Route::get('/form-options', [ApiClientIssueController::class, 'formOptions']);

            Route::controller(ApiClientIssueController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::patch('/{id}/status', 'updateStatus');
                Route::delete('/{id}', 'destroy');
            });

            Route::controller(ApiClientIssueController::class)->group(function () {
                Route::post('/{clientIssue}/assign', 'assignTeam');
                Route::post('/{clientIssue}/tasks', 'taskStore');
                Route::get('/{clientIssue}/tasks/{task}', 'taskShow');
                Route::match(['put', 'patch'], '/{clientIssue}/tasks/{task}', 'taskUpdate');
                Route::patch('/{clientIssue}/tasks/{task}/status', 'taskUpdateStatus');
                Route::delete('/{clientIssue}/tasks/{task}', 'taskDestroy');
            });
        });


        // Service API routes  -- not used anywhere
        Route::prefix('services')->group(function () {
            Route::get('/form-options', [ApiServiceController::class, 'formOptions']);
            Route::get('/', [ApiServiceController::class, 'index']);
            Route::get('/{id}', [ApiServiceController::class, 'show']);
            Route::post('/', [ApiServiceController::class, 'store']);
            Route::match(['put', 'patch'], '/{id}', [ApiServiceController::class, 'update']);
            Route::delete('/{id}', [ApiServiceController::class, 'destroy']);
            Route::post('/delete-selected', [ApiServiceController::class, 'deleteSelected']);
        });

        // Calendar appointment API routes
        Route::prefix('calendar')->group(function () {
            Route::controller(CalendarEventController::class)->group(function () {
                Route::get('/events', 'apiIndex');
                Route::get('/events/{id}', 'apiShow');
                Route::post('/events', 'apiStore');
                Route::match(['put', 'patch'], '/events/{id}', 'apiUpdate');
                Route::delete('/events/{id}', 'apiDestroy');
            });
        });



        // Settings API routes
        Route::prefix('settings')->group(function () {
            Route::get('/', [ApiSettingController::class, 'index']);

            Route::get('/general', [ApiSettingController::class, 'general']);
            Route::match(['post', 'put', 'patch'], '/general', [ApiSettingController::class, 'updateGeneral']);
            Route::get('/company', [ApiSettingController::class, 'company']);
            Route::match(['post', 'put', 'patch'], '/company', [ApiSettingController::class, 'updateCompany']);
            Route::get('/email', [ApiSettingController::class, 'email']);
            Route::match(['post', 'put', 'patch'], '/email', [ApiSettingController::class, 'updateEmail']);
            Route::get('/renewal', [ApiSettingController::class, 'renewal']);
            Route::match(['post', 'put', 'patch'], '/renewal', [ApiSettingController::class, 'updateRenewal']);
            Route::get('/teams', [ApiSettingController::class, 'teams']);
            Route::match(['post', 'put', 'patch'], '/teams', [ApiSettingController::class, 'updateTeams']);
            Route::get('/departments', [ApiSettingController::class, 'departments']);
            Route::match(['post', 'put', 'patch'], '/departments', [ApiSettingController::class, 'updateDepartments']);
            Route::post('/test-email', [ApiSettingController::class, 'sendTestEmail']);
            Route::get('/search-tags', [ApiSettingController::class, 'searchTags']);
            Route::get('/app-logo', [ApiSettingController::class, 'getAppLogo']);
            Route::match(['post', 'put', 'patch'], '/app-logo', [ApiSettingController::class, 'updateAppLogo']);
            Route::get('/login-logo', [ApiSettingController::class, 'getLoginLogo']);
            Route::match(['post', 'put', 'patch'], '/login-logo', [ApiSettingController::class, 'updateLoginLogo']);
        });

        // Route::get('/clients', [ClientRenewalController::class, 'clientList']);
        // Route::get('/vendors', [ClientRenewalController::class, 'vendorList']);

        // Book a Call 
        Route::controller(BookACallController::class)->group(function () {
            Route::get('/book-a-call', 'index');
            Route::delete('/book-a-call/{id}', 'destroy');
        });

        Route::controller(GoogleAdsController::class)->group(function () {
            Route::get('/digital-marketing', 'indexDigitalMarketing');
            Route::delete('/digital-marketing/{id}', 'destroyDigitalMarketing');

            Route::get('/web-apps-leads', 'indexWebAppsLeads');
            Route::delete('/web-apps-leads/{id}', 'destroyWebAppsLeads');
        });

        Route::prefix('web-enquiry/careers')->controller(WebEnquiryCareerController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::delete('/{id}', 'destroy');
            Route::get('/{id}/resume-url', 'resumeUrl');
        });

        Route::prefix('web-enquiry/contacts')->controller(WebEnquiryContactController::class)->group(function () {
            Route::get('/', 'index');
            Route::delete('/{id}', 'destroy');
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

