<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\FileUpload;
use App\Http\Controllers\Controller;
use App\Mail\StaffInviteMail;
use App\Models\Department;
use App\Models\Staff;
use App\Models\StaffDepartment;
use App\Models\StaffTeam;
use App\Models\Team;
use App\Models\User;
use App\Services\UnifiedNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    //
    public function departments()
    {
        $departments = Department::get();
        if (! $departments) {
            return ApiResponse::error('No Department found');
        }

        return ApiResponse::success($departments, 'Departments found');
    }

    public function teams()
    {
        $teams = Team::get();
        if (! $teams) {
            return ApiResponse::error('No Team found');
        }

        return ApiResponse::success($teams, 'Teams found');
    }

    public function index(Request $request)
    {
        $statusFilter = $this->normalizeStaffStatusFilter($request->input('status'));

        $staffs = User::where('role', 'staff')
            ->when($statusFilter !== null, function ($query) use ($statusFilter) {
                $query->where(function ($statusQuery) use ($statusFilter) {
                    if ($statusFilter === 'active') {
                        $statusQuery->where('status', 'active')
                            ->orWhere('status', '1');
                    } else {
                        $statusQuery->where('status', 'inactive')
                            ->orWhere('status', '0');
                    }
                });
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);
        $staffsCount = $staffs->total();
        if (! $staffs) {
            return ApiResponse::error('No staff found');
        }

        return ApiResponse::success(['staffs' => $staffs, 'totalStaffs' => $staffsCount], 'Staff found');
    }

    private function normalizeStaffStatusFilter(mixed $status): ?string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'inactive';
        }

        if (is_numeric($status)) {
            return (int) $status === 1 ? 'active' : ((int) $status === 0 ? 'inactive' : null);
        }

        if (! is_string($status)) {
            return null;
        }

        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'active', '1', 'true' => 'active',
            'inactive', '0', 'false' => 'inactive',
            default => null,
        };
    }

    public function show($id)
    {
        $staff = User::with(['departments', 'teams'])->where('role', 'staff')->findOrFail($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found');
        }

        // Load tasks separately since it's a custom query, not a traditional relationship
        $staff->tasksCount = $staff->tasks()->count();
        $staff->projectsCount = $staff->projects()->count();

        return ApiResponse::success($staff, 'Staff found');
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'departments.*' => 'integer',
            'team' => 'nullable|integer',
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $profileImagePath = basename(FileUpload::uploadOrGenerateAvatar(
                $request->file('profile_image'),
                trim($request->first_name . ' ' . $request->last_name),
                'uploads/staff/'
            ));

            $user = User::create([
                'profile_image' => $profileImagePath,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => $request->status ?? 'active',
                'role' => 'staff',
            ]);

            if ($user && $request->team) {
                $teamMember = new StaffTeam();
                $teamMember->user_id = $user->id;
                $teamMember->team_id = $request->team;
                $teamMember->save();
            }

            if ($user && $request->departments) {
                foreach ($request->departments as $departmentId) {
                    $departmentMember = new StaffDepartment();
                    $departmentMember->user_id = $user->id;
                    $departmentMember->department_id = $departmentId;
                    $departmentMember->save();
                }
            }

            $sendWelcomeEmail = filter_var($request->sendWelcomeEmail ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sendWelcomeEmail = $sendWelcomeEmail ?? true;

            if ($sendWelcomeEmail) {
                $staffName = $request->first_name . ' ' . $request->last_name;
                try {
                    Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
                }
            }

            DB::commit();

            try {
                $creatorName = trim((string) auth()->user()?->name);
                $staffName = trim($request->first_name . ' ' . $request->last_name);

                app(UnifiedNotificationService::class)->sendToLoggedInUser(
                    'Staff Created',
                    $staffName . ' has been added successfully.',
                    'staff',
                    [
                        'type' => 'staff_created',
                        'staff_id' => (string) $user->id,
                        'staff_name' => $staffName,
                        'created_by' => $creatorName,
                        'source' => 'api_staff_v2',
                    ]
                );
            } catch (\Throwable $notificationException) {
                Log::warning('Staff created via API but notification failed: ' . $notificationException->getMessage(), [
                    'staff_id' => $user->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $sendWelcomeEmail ? 'Staff created successfully. Invitation email sent.' : 'Staff created successfully. Welcome email was not sent.',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'departments' => 'nullable|array',
            'departments.*' => 'integer',
            'team' => 'nullable|integer',
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::where('role', 'staff')->findOrFail($id);

            $profileImagePath = $user->profile_image;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = basename(FileUpload::updateFileUpload(
                    $request->file('profile_image'),
                    $user->profile_image ? 'uploads/staff/'.$user->profile_image : '',
                    'uploads/staff/'
                ));
            } elseif (! $user->profile_image || $user->profile_image === 'default.png') {
                $profileImagePath = basename(FileUpload::generateAvatar(
                    trim($request->first_name . ' ' . $request->last_name),
                    'uploads/staff/',
                    $user->profile_image ? 'uploads/staff/'.$user->profile_image : ''
                ));
            }

            // Update User
            $user->update([
                'profile_image' => $profileImagePath,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'status' => $request->status ?? $user->status,
            ]);

            // Update team: detach old and attach new
            StaffTeam::where('user_id', $user->id)->delete();
            if ($request->team) {
                StaffTeam::create([
                    'user_id' => $user->id,
                    'team_id' => $request->team,
                ]);
            }

            // Update departments: detach old and attach new
            StaffDepartment::where('user_id', $user->id)->delete();
            if ($request->departments) {
                foreach ($request->departments as $departmentId) {
                    StaffDepartment::create([
                        'user_id' => $user->id,
                        'department_id' => $departmentId,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully.',
                'data' => $user->load(['departments', 'teams']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update staff: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Soft delete a staff member.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->trashed()) {
            return ApiResponse::error('Staff member is already deleted');
        }

        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();

            return ApiResponse::success('Staff deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to delete staff' . $e->getMessage(), 500);
        }
    }

    public function restore($id)
    {
        $staff = User::withTrashed()->find($id);

        if (! $staff->trashed()) {
            return ApiResponse::error('Staff member is already active');
        }

        DB::beginTransaction();
        try {
            $staff->restore();
            DB::commit();

            return ApiResponse::success($staff, 'Staff restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to restore staff' . $e->getMessage(), 500);
        }
    }

    /**
     * Permanently delete a staff member.
     */
    public function forceDelete($id)
    {
        $staff = User::withTrashed()->find($id);
        if (! $staff) {
            return ApiResponse::error('Staff member not found');
        }

        DB::beginTransaction();
        try {
            FileUpload::deleteFile($staff->profile_image ? 'uploads/staff/'.$staff->profile_image : '');
            $staff->forceDelete();

            DB::commit();

            return ApiResponse::success('Staff permanently deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to permanently delete staff' . $e->getMessage(), 500);
        }
    }


    public function staffProjects($id)
    {
        $staff = User::find($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found', 404);
        }

        $projects = $staff->projects()->get();
        if (!$projects) {
            return ApiResponse::error('Staff projects not found', 404);
        }

        return ApiResponse::success($projects, 'Staff projects retrieved successfully');
    }

    public function staffTasks($id)
    {
        $staff = User::find($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found', 404);
        }

        $tasks = $staff->tasks()->get();

        if (!$tasks) {
            return ApiResponse::error('Staff tasks not found', 404);
        }

        return ApiResponse::success($tasks, 'Staff tasks retrieved successfully');
    }


}
