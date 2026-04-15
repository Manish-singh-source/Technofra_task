<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FcmTestController;
use App\Http\Controllers\Api\ProjectController as ApiProjectController;
use App\Http\Controllers\Api\RoleController as ApiRoleController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;
use App\Http\Controllers\Api\VendorController as ApiVendorController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

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

        // Role API routes
        Route::prefix('roles')->group(function () {
            Route::get('/', [ApiRoleController::class, 'index'])->middleware('permission:view_roles');
            Route::post('/', [ApiRoleController::class, 'store'])->middleware('permission:create_roles');
            Route::match(['put', 'patch'], '/{id}', [ApiRoleController::class, 'update'])->middleware('permission:edit_roles');
            Route::delete('/delete-all', [ApiRoleController::class, 'destroyAll'])->middleware('permission:delete_roles');
            Route::delete('/{id}', [ApiRoleController::class, 'destroy'])->middleware('permission:delete_roles');
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
                Route::get('/', 'index')->middleware('permission:view_staff');
                Route::get('/{id}', 'show')->middleware('permission:view_staff');
                Route::post('/', 'store')->middleware('permission:create_staff');
                Route::put('/{id}', 'update')->middleware('permission:edit_staff');
                Route::delete('/{id}', 'destroy')->middleware('permission:delete_staff');
                Route::post('/{id}/restore', 'restore')->middleware('permission:edit_staff');
                Route::delete('/{id}/force', 'forceDelete')->middleware('permission:delete_staff');
            });
            Route::prefix('staff')->group(function () {
                Route::get('/{id}/tasks', 'staffTasks')->middleware('permission:view_staff');
                Route::get('/{id}/projects', 'staffProjects')->middleware('permission:view_staff');
            });
        });

        // Customer/Client API routes
        Route::prefix('clients')->group(function () {
            Route::get('/', [CustomerController::class, 'apiIndex'])->middleware('permission:view_clients');
            Route::get('/{id}', [CustomerController::class, 'apiShow'])->middleware('permission:view_clients');
            Route::post('/', [CustomerController::class, 'apiStore'])->middleware('permission:create_clients');
            Route::match(['put', 'patch'], '/{id}', [CustomerController::class, 'apiUpdate'])->middleware('permission:edit_clients');
            Route::delete('/{id}', [CustomerController::class, 'apiDestroy'])->middleware('permission:delete_clients');
            Route::post('/{id}/restore', [CustomerController::class, 'apiRestore'])->middleware('permission:edit_clients');
            Route::delete('/{id}/force', [CustomerController::class, 'apiForceDelete'])->middleware('permission:delete_clients');
            // Client Tasks
            Route::get('/{id}/tasks', [CustomerController::class, 'apiClientTasks'])->middleware('permission:view_clients');
            Route::get('/{id}/tasks/{taskId}', [CustomerController::class, 'apiClientTaskDetail'])->middleware('permission:view_clients');
            // Client Projects
            Route::get('/{id}/projects', [CustomerController::class, 'apiClientProjects'])->middleware('permission:view_clients');
            Route::get('/{id}/projects/{projectId}', [CustomerController::class, 'apiClientProjectDetail'])->middleware('permission:view_clients');
            // Client issues
            Route::get('/{id}/issues', [CustomerController::class, 'apiClientIssues'])->middleware('permission:view_clients');
            Route::get('/{id}/issues/{issueId}', [CustomerController::class, 'apiClientIssueDetail'])->middleware('permission:view_clients');
        });

        // Vendor API routes
        Route::prefix('vendors')->group(function () {
            Route::get('/', [ApiVendorController::class, 'index'])->middleware('permission:view_vendors');
            Route::get('/{id}', [ApiVendorController::class, 'show'])->middleware('permission:view_vendors');
            Route::post('/', [ApiVendorController::class, 'store'])->middleware('permission:create_vendors');
            Route::match(['put', 'patch'], '/{id}', [ApiVendorController::class, 'update'])->middleware('permission:edit_vendors');
            Route::delete('/delete-all', [ApiVendorController::class, 'destroyAll'])->middleware('permission:delete_vendors');
            Route::delete('/{id}', [ApiVendorController::class, 'destroy'])->middleware('permission:delete_vendors');
        });

        // Calendar appointment API routes
        Route::prefix('calendar')->group(function () {
            Route::get('/events', [CalendarEventController::class, 'apiIndex'])->middleware('permission:view_calendar|view_dashboard');
            Route::get('/events/{id}', [CalendarEventController::class, 'apiShow'])->middleware('permission:view_calendar|view_dashboard');
            Route::post('/events', [CalendarEventController::class, 'apiStore'])->middleware('permission:view_calendar|view_dashboard');
            Route::match(['put', 'patch'], '/events/{id}', [CalendarEventController::class, 'apiUpdate'])->middleware('permission:view_calendar|view_dashboard');
            Route::delete('/events/{id}', [CalendarEventController::class, 'apiDestroy'])->middleware('permission:view_calendar|view_dashboard');
        });

        // Todo API routes
        Route::prefix('todos')->group(function () {
            Route::get('/options', [TodoController::class, 'apiTodoOptions']);
            Route::get('/', [TodoController::class, 'apiTodoCollection']);
            Route::get('/{todo}', [TodoController::class, 'apiTodoDetail']);
            Route::post('/create-todo', [TodoController::class, 'apiCreateTodo']);
            Route::match(['put', 'patch'], '/update-todo/{todo}', [TodoController::class, 'apiUpdateTodo']);
            Route::delete('/delete-todo/{todo}', [TodoController::class, 'apiDeleteTodo']);
            Route::patch('/toggle-todo-status/{todo}', [TodoController::class, 'apiToggleTodoStatus']);
        });

        // Project API routes
        Route::prefix('projects')->group(function () {
            Route::get('/form-options', [ApiProjectController::class, 'apiFormOptions'])->middleware('permission:create_projects');
            Route::get('/', [ApiProjectController::class, 'apiIndex']);
            Route::delete('/delete-all', [ApiProjectController::class, 'apiDeleteAll'])->middleware('permission:delete_projects');
            Route::delete('/force-delete-all', [ApiProjectController::class, 'apiForceDeleteAll'])->middleware('permission:delete_projects');
            Route::get('/{id}', [ApiProjectController::class, 'apiShow'])->middleware('permission:view_projects');
            Route::post('/', [ApiProjectController::class, 'apiStore'])->middleware('permission:create_projects');
            Route::match(['put', 'patch'], '/{id}', [ApiProjectController::class, 'apiUpdate'])->middleware('permission:edit_projects');
            Route::delete('/{id}', [ApiProjectController::class, 'apiDestroy'])->middleware('permission:delete_projects');
            Route::get('/{projectId}/milestones', [ApiProjectController::class, 'apiMilestoneIndex'])->middleware('permission:view_projects');
            Route::post('/{projectId}/milestones', [ApiProjectController::class, 'apiStoreMilestone'])->middleware('permission:edit_projects');
            Route::match(['put', 'patch'], '/{projectId}/milestones/{milestoneId}', [ApiProjectController::class, 'apiUpdateMilestone'])->middleware('permission:edit_projects');
            Route::delete('/{projectId}/milestones/{milestoneId}', [ApiProjectController::class, 'apiDestroyMilestone'])->middleware('permission:edit_projects');
            Route::get('/{projectId}/issues', [ApiProjectController::class, 'apiIssueIndex'])->middleware('permission:view_projects');
            Route::post('/{projectId}/issues', [ApiProjectController::class, 'apiStoreIssue'])->middleware('permission:edit_projects');
            Route::match(['put', 'patch'], '/{projectId}/issues/{issueId}', [ApiProjectController::class, 'apiUpdateIssue'])->middleware('permission:edit_projects');
            Route::delete('/{projectId}/issues/{issueId}', [ApiProjectController::class, 'apiDestroyIssue'])->middleware('permission:edit_projects');
            Route::get('/{projectId}/comments', [ApiProjectController::class, 'apiCommentIndex'])->middleware('permission:view_projects');
            Route::post('/{projectId}/comments', [ApiProjectController::class, 'apiStoreComment'])->middleware('permission:view_projects');
            Route::get('/{projectId}/files', [ApiProjectController::class, 'apiFileIndex'])->middleware('permission:view_projects');
            Route::post('/{projectId}/files', [ApiProjectController::class, 'apiUploadFile'])->middleware('permission:edit_projects');
            Route::delete('/{projectId}/files/{fileId}', [ApiProjectController::class, 'apiDeleteFile'])->middleware('permission:delete_projects');
            Route::get('/{projectId}/usage', [ApiProjectController::class, 'apiUsage'])->middleware('permission:view_projects');
        });

        // Task API routes
        Route::prefix('tasks')->group(function () {
            Route::get('/form-options', [ApiTaskController::class, 'apiFormOptions'])->middleware('permission:create_tasks');
            Route::get('/', [ApiTaskController::class, 'apiIndex'])->middleware('permission:view_tasks');
            Route::delete('/delete-all', [ApiTaskController::class, 'apiDeleteAll'])->middleware('permission:delete_tasks');
            Route::delete('/force-delete-all', [ApiTaskController::class, 'apiForceDeleteAll'])->middleware('permission:delete_tasks');
            Route::get('/{id}', [ApiTaskController::class, 'apiShow'])->middleware('permission:view_tasks');
            Route::post('/', [ApiTaskController::class, 'apiStore'])->middleware('permission:create_tasks');
            Route::match(['put', 'patch'], '/{id}', [ApiTaskController::class, 'apiUpdate'])->middleware('permission:edit_tasks');
            Route::delete('/{id}', [ApiTaskController::class, 'apiDestroy'])->middleware('permission:delete_tasks');
            Route::get('/{taskId}/comments', [ApiTaskController::class, 'apiCommentIndex'])->middleware('permission:view_tasks');
            Route::post('/{taskId}/comments', [ApiTaskController::class, 'apiStoreComment'])->middleware('permission:view_tasks');
            Route::get('/{taskId}/attachments', [ApiTaskController::class, 'apiAttachmentIndex'])->middleware('permission:view_tasks');
            Route::post('/{taskId}/attachments', [ApiTaskController::class, 'apiUploadAttachment'])->middleware('permission:edit_tasks');
            Route::delete('/{taskId}/attachments/{attachmentId}', [ApiTaskController::class, 'apiDeleteAttachment'])->middleware('permission:delete_tasks');
        });

        // Lead API routes
        Route::prefix('leads')->group(function () {
            Route::get('/form-options', [LeadController::class, 'apiFormOptions'])->middleware('permission:create_leads');
            Route::get('/', [LeadController::class, 'apiIndex'])->middleware('permission:view_leads');
            Route::get('/{id}', [LeadController::class, 'apiShow'])->middleware('permission:view_leads');
            Route::post('/', [LeadController::class, 'apiStore'])->middleware('permission:create_leads');
            Route::match(['put', 'patch'], '/{id}', [LeadController::class, 'apiUpdate'])->middleware('permission:edit_leads');
            Route::delete('/{id}', [LeadController::class, 'apiDestroy'])->middleware('permission:delete_leads');
        });

        // Permission API routes
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'apiIndex'])->middleware('permission:view_roles');
            Route::get('/grouped', [PermissionController::class, 'apiGroupedPermissions'])->middleware('permission:view_roles');
        });

        // Role API routes
        Route::prefix('roles')->group(function () {
            Route::get('/', function () {
                $roles = Role::with('permissions')->get();

                return response()->json([
                    'success' => true,
                    'data' => $roles,
                ]);
            })->middleware('permission:view_roles');
        });
    });
});

// Fallback route for authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
