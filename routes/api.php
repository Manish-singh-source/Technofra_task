<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientIssueController as ApiClientIssueController;
use App\Http\Controllers\Api\ClientRenewalController;
use App\Http\Controllers\Api\FcmTestController;
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
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
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
            Route::post('/logout', 'logout');
            Route::post('/logout-all', 'logoutAll');
        });


        // Permission API routes
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::get('/grouped', [PermissionController::class, 'apiGroupedPermissions']);
        });

        // Role API routes
        Route::prefix('roles')->group(function () {
            Route::get('/', [ApiRoleController::class, 'index']);
            Route::post('/', [ApiRoleController::class, 'store']);
            Route::match(['put', 'patch'], '/{id}', [ApiRoleController::class, 'update']);
            Route::delete('/{id}', [ApiRoleController::class, 'destroy']);
        });

        // Staff API routes
        Route::controller(StaffController::class)->group(function () {
            Route::prefix('staff')->group(function () {
                Route::get('/form-options', 'apiFormOptions');
                Route::get('/', 'apiIndex');
                Route::get('/{id}', 'apiShow');
                Route::post('/', 'apiStore');
                Route::match(['put', 'patch'], '/{id}', 'apiUpdate');
                Route::delete('/{id}', 'apiDestroy');
                Route::post('/{id}/restore', 'apiRestore');
                Route::delete('/{id}/force', 'apiForceDelete');
            });
        });

        Route::controller(App\Http\Controllers\Api\StaffController::class)->group(function () {
            Route::prefix('staff-v2')->group(function () {
                Route::get('/departments', 'departments');
                Route::get('/teams', 'teams');

                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
                Route::post('/{id}/restore', 'restore');
                Route::delete('/{id}/force', 'forceDelete');
            });

            Route::prefix('staff')->group(function () {
                Route::get('/{id}/tasks', 'staffTasks');
                Route::get('/{id}/projects', 'staffProjects');
            });
        });

        // Customer/Client API routes
        Route::prefix('clients')->group(function () {
            // Route::get('/', [CustomerController::class, 'apiIndex']);
            // Route::get('/{id}', [CustomerController::class, 'apiShow']);
            // Route::post('/', [CustomerController::class, 'apiStore']);
            // Route::match(['put', 'patch'], '/{id}', [CustomerController::class, 'apiUpdate']);
            // Route::delete('/{id}', [CustomerController::class, 'apiDestroy']);
            Route::post('/{id}/restore', [CustomerController::class, 'apiRestore']);
            Route::delete('/{id}/force', [CustomerController::class, 'apiForceDelete']);
            // Client Tasks
            Route::get('/{id}/tasks', [CustomerController::class, 'apiClientTasks']);
            Route::get('/{id}/tasks/{taskId}', [CustomerController::class, 'apiClientTaskDetail']);
            // Client Projects
            Route::get('/{id}/projects', [CustomerController::class, 'apiClientProjects']);
            Route::get('/{id}/projects/{projectId}', [CustomerController::class, 'apiClientProjectDetail']);
            // Client issues
            Route::get('/{id}/issues', [CustomerController::class, 'apiClientIssues']);
            Route::get('/{id}/issues/{issueId}', [CustomerController::class, 'apiClientIssueDetail']);


            Route::controller(ClientController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/{id}', 'show');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });
        });

        // Vendor API routes
        Route::prefix('vendors')->group(function () {
            Route::get('/', [ApiVendorController::class, 'index']);
            Route::get('/{id}', [ApiVendorController::class, 'show']);
            Route::post('/', [ApiVendorController::class, 'store']);
            Route::match(['put', 'patch'], '/{id}', [ApiVendorController::class, 'update']);
            Route::delete('/{id}', [ApiVendorController::class, 'destroy']);
        });

        // Vendor Renewal API routes
        Route::prefix('vendor-renewals')->group(function () {
            Route::get('/', [VendorRenewalController::class, 'index']);
            Route::get('/{id}', [VendorRenewalController::class, 'show']);
            Route::post('/', [VendorRenewalController::class, 'store']);
            Route::put('/{id}', [VendorRenewalController::class, 'update']);
            Route::delete('/{id}', [VendorRenewalController::class, 'destroy']);
        });


        // Client Renewal API routes
        Route::prefix('client-renewals')->group(function () {
            Route::get('/', [ClientRenewalController::class, 'index']);
            Route::get('/{id}', [ClientRenewalController::class, 'show']);
            Route::post('/', [ClientRenewalController::class, 'store']);
            Route::put('/{id}', [ClientRenewalController::class, 'update']);
            Route::delete('/{id}', [ClientRenewalController::class, 'destroy']);
        });


        Route::prefix('todos')->group(function () {
            Route::get('/', [TodoController::class, 'index']);
            Route::get('/{todo}', [TodoController::class, 'show']);
            Route::post('/', [TodoController::class, 'store']);
            Route::match(['put', 'patch'], '/{todo}', [TodoController::class, 'update']);
            Route::delete('/{todo}', [TodoController::class, 'delete']);

            Route::get('/options', [TodoController::class, 'apiTodoOptions']);
            Route::patch('/{todo}/status', [TodoController::class, 'apiToggleTodoStatus']);
        });


        // Service API routes
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
            Route::get('/events', [CalendarEventController::class, 'apiIndex']);
            Route::get('/events/{id}', [CalendarEventController::class, 'apiShow']);
            Route::post('/events', [CalendarEventController::class, 'apiStore']);
            Route::match(['put', 'patch'], '/events/{id}', [CalendarEventController::class, 'apiUpdate']);
            Route::delete('/events/{id}', [CalendarEventController::class, 'apiDestroy']);
        });


        // Project API routes
        Route::prefix('projects')->group(function () {
            Route::get('/form-options', [ApiProjectController::class, 'apiFormOptions']);
            Route::get('/', [ApiProjectController::class, 'apiIndex']);
            Route::delete('/delete-all', [ApiProjectController::class, 'apiDeleteAll']);
            Route::delete('/force-delete-all', [ApiProjectController::class, 'apiForceDeleteAll']);
            Route::get('/{id}', [ApiProjectController::class, 'apiShow']);
            Route::post('/', [ApiProjectController::class, 'apiStore']);
            Route::match(['put', 'patch'], '/{id}', [ApiProjectController::class, 'apiUpdate']);
            Route::delete('/{id}', [ApiProjectController::class, 'apiDestroy']);
            Route::get('/{projectId}/milestones', [ApiProjectController::class, 'apiMilestoneIndex']);
            Route::post('/{projectId}/milestones', [ApiProjectController::class, 'apiStoreMilestone']);
            Route::match(['put', 'patch'], '/{projectId}/milestones/{milestoneId}', [ApiProjectController::class, 'apiUpdateMilestone']);
            Route::delete('/{projectId}/milestones/{milestoneId}', [ApiProjectController::class, 'apiDestroyMilestone']);
            Route::get('/{projectId}/issues', [ApiProjectController::class, 'apiIssueIndex']);
            Route::post('/{projectId}/issues', [ApiProjectController::class, 'apiStoreIssue']);
            Route::match(['put', 'patch'], '/{projectId}/issues/{issueId}', [ApiProjectController::class, 'apiUpdateIssue']);
            Route::delete('/{projectId}/issues/{issueId}', [ApiProjectController::class, 'apiDestroyIssue']);
            Route::get('/{projectId}/comments', [ApiProjectController::class, 'apiCommentIndex']);
            Route::post('/{projectId}/comments', [ApiProjectController::class, 'apiStoreComment']);
            Route::get('/{projectId}/files', [ApiProjectController::class, 'apiFileIndex']);
            Route::post('/{projectId}/files', [ApiProjectController::class, 'apiUploadFile']);
            Route::delete('/{projectId}/files/{fileId}', [ApiProjectController::class, 'apiDeleteFile']);
            Route::get('/{projectId}/usage', [ApiProjectController::class, 'apiUsage']);
        });

        // Task API routes
        Route::prefix('tasks')->group(function () {
            Route::get('/form-options', [ApiTaskController::class, 'apiFormOptions']);
            Route::get('/', [ApiTaskController::class, 'apiIndex']);
            Route::delete('/delete-all', [ApiTaskController::class, 'apiDeleteAll']);
            Route::delete('/force-delete-all', [ApiTaskController::class, 'apiForceDeleteAll']);
            Route::get('/{id}', [ApiTaskController::class, 'apiShow']);
            Route::post('/', [ApiTaskController::class, 'apiStore']);
            Route::match(['put', 'patch'], '/{id}', [ApiTaskController::class, 'apiUpdate']);
            Route::delete('/{id}', [ApiTaskController::class, 'apiDestroy']);
            Route::get('/{taskId}/comments', [ApiTaskController::class, 'apiCommentIndex']);
            Route::post('/{taskId}/comments', [ApiTaskController::class, 'apiStoreComment']);
            Route::get('/{taskId}/attachments', [ApiTaskController::class, 'apiAttachmentIndex']);
            Route::post('/{taskId}/attachments', [ApiTaskController::class, 'apiUploadAttachment']);
            Route::delete('/{taskId}/attachments/{attachmentId}', [ApiTaskController::class, 'apiDeleteAttachment']);
        });

        // Lead API routes
        Route::prefix('leads')->group(function () {
            Route::get('/form-options', [LeadController::class, 'apiFormOptions']);
            Route::get('/', [LeadController::class, 'apiIndex']);
            Route::get('/{id}', [LeadController::class, 'apiShow']);
            Route::post('/', [LeadController::class, 'apiStore']);
            Route::match(['put', 'patch'], '/{id}', [LeadController::class, 'apiUpdate']);
            Route::delete('/{id}', [LeadController::class, 'apiDestroy']);
        });

        // Client issue API routes
        Route::prefix('client-issues')->group(function () {
            Route::get('/form-options', [ApiClientIssueController::class, 'formOptions']);
            Route::get('/', [ApiClientIssueController::class, 'index']);
            Route::post('/', [ApiClientIssueController::class, 'store']);
            Route::get('/{id}', [ApiClientIssueController::class, 'show']);
            Route::post('/{clientIssue}/assign', [ApiClientIssueController::class, 'assignTeam']);
            Route::patch('/{id}/status', [ApiClientIssueController::class, 'updateStatus']);
            Route::delete('/{id}', [ApiClientIssueController::class, 'destroy']);
            Route::post('/delete-selected', [ApiClientIssueController::class, 'deleteSelected']);
            Route::post('/{clientIssue}/tasks', [ApiClientIssueController::class, 'taskStore']);
            Route::get('/{clientIssue}/tasks/{task}', [ApiClientIssueController::class, 'taskShow']);
            Route::match(['put', 'patch'], '/{clientIssue}/tasks/{task}', [ApiClientIssueController::class, 'taskUpdate']);
            Route::patch('/{clientIssue}/tasks/{task}/status', [ApiClientIssueController::class, 'taskUpdateStatus']);
            Route::delete('/{clientIssue}/tasks/{task}', [ApiClientIssueController::class, 'taskDestroy']);
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

    });
});

// Fallback route for authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
