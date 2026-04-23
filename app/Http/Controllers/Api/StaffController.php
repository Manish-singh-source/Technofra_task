<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\StaffInviteMail;
use App\Models\Department;
use App\Models\Staff;
use App\Models\StaffDepartment;
use App\Models\StaffTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

    public function index()
    {
        $staffs = User::where('role', 'staff')->paginate(10);
        $staffsCount = User::where('role', 'staff')->count();
        if (! $staffs) {
            return ApiResponse::error('No staff found');
        }

        return ApiResponse::success(['staffs' => $staffs, 'totalStaffs' => $staffsCount], 'Staff found');
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
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profile_image'));
            } else {
                $fileName = Str::uuid() . '.png';
                $path = public_path('uploads/staff/' . $fileName);

                $avatar = app('avatar');
                $avatar->create($request->first_name . ' ' . $request->last_name)->save($path);
                $profileImagePath = 'uploads/staff/' . $fileName;
            }

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

            if ($user->profile_image && $user->profile_image !== 'default.png') {
                // delete existing image if it's not the default
                $imagePath = public_path($user->profile_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $profileImagePath = $user->profile_image; // keep existing if no new upload
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profile_image'));
            } elseif (!$user->profile_image || $user->profile_image === 'default.png') {
                // generate avatar if no existing image or it's default
                $fileName = Str::uuid() . '.png';
                $path = public_path('uploads/staff/' . $fileName);
                $avatar = app('avatar');
                $avatar->create($request->first_name . ' ' . $request->last_name)->save($path);
                $profileImagePath = 'uploads/staff/' . $fileName;
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
            if ($staff->profile_image) {
                $imagePath = public_path('uploads/staff/' . $staff->profile_image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
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


    // Helper function for uploading profile image
    private function uploadProfileImage($file)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/staff'), $fileName);
        return 'uploads/staff/' . $fileName;
    }
}
