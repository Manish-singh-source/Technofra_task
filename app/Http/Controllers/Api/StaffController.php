<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\StaffInviteMail;
use App\Models\Staff;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\FlareClient\Api;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    //
    public function index()
    {
        $staffs = Staff::with('user')->get();
        if (! $staffs) {
            return ApiResponse::error('No staff found');
        }

        return ApiResponse::success($staffs, 'Staff found');
    }

    public function show($id)
    {
        $staff = Staff::with('user')->findOrFail($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found');
        }

        return ApiResponse::success($staff, 'Staff found');
    }

    public function store($request)
    {
        $teams = Team::getTeamOptions();

        $payload = array_merge($request->all(), [
            'first_name' => $request->input('first_name', $request->input('firstName')),
            'last_name' => $request->input('last_name', $request->input('lastName')),
            'sendWelcomeEmail' => $request->input('sendWelcomeEmail', $request->input('send_welcome_email')),
        ]);

        $validated = Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'departments.*' => 'string|max:255',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'sendWelcomeEmail' => 'nullable|boolean',
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
            if ($request->hasFile('profileImage')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profileImage'));
            }

            $user = User::create([
                'name' => $payload['first_name'].' '.$payload['last_name'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password']),
            ]);

            $role = Role::where('name', $payload['role'])->first();
            if ($role) {
                $user->assignRole($role);
            }

            $staff = Staff::create([
                'user_id' => $user->id,
                'profile_image' => $profileImagePath,
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'role' => $payload['role'],
                'password' => Hash::make($payload['password']),
                'status' => $payload['status'] ?? 'active',
                'departments' => $payload['departments'] ?? [],
                'team' => ! empty($payload['team']) ? $payload['team'] : null,
            ]);

            $this->refreshPermissionCache();

            $sendWelcomeEmail = filter_var($payload['sendWelcomeEmail'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sendWelcomeEmail = $sendWelcomeEmail ?? true;

            if ($sendWelcomeEmail) {
                $staffName = $payload['first_name'].' '.$payload['last_name'];
                try {
                    Mail::to($payload['email'])->send(new StaffInviteMail($staffName, $payload['email'], $payload['password']));
                } catch (\Exception $mailException) {
                    Log::error('Failed to send staff invitation email: '.$mailException->getMessage());
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $sendWelcomeEmail ? 'Staff created successfully. Invitation email sent.' : 'Staff created successfully. Welcome email was not sent.',
                'data' => $this->formatStaffResource($staff->load('user.roles')),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to create staff: '.$e->getMessage());
        }
    }

    /**
     * API: Soft delete a staff member.
     */
    public function destroy($id)
    {
        $staff = Staff::withTrashed()->find($id);

        if ($staff->trashed()) {
            return ApiResponse::error('Staff member is already deleted');
        }

        DB::beginTransaction();
        try {
            $staff->delete();
            DB::commit();

            return ApiResponse::success('Staff deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to delete staff'.$e->getMessage(), 500);
        }
    }

    public function restore($id)
    {
        $staff = Staff::withTrashed()->find($id);

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

            return ApiResponse::error('Failed to restore staff'.$e->getMessage(), 500);
        }
    }

    /**
     * Permanently delete a staff member.
     */
    public function forceDelete($id)
    {
        $staff = Staff::withTrashed()->find($id);
        if (! $staff) {
            return ApiResponse::error('Staff member not found');
        }

        DB::beginTransaction();
        try {
            if ($staff->profile_image) {
                $imagePath = public_path('uploads/staff/'.$staff->profile_image);
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
            $staff->forceDelete();

            DB::commit();

            return ApiResponse::success('Staff permanently deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to permanently delete staff'.$e->getMessage(), 500);
        }
    }

    public function staffTasks($id)
    {
        $staff = Staff::find($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found', 404);
        }

        $tasks = $staff->tasks()->with('project')->get()->map(function ($task) {
            $task->assignees = Staff::whereIn('id', $task->assignees ?? [])->get();
            $task->followers = Staff::whereIn('id', $task->followers ?? [])->get();
            // $task->members = Staff::whereIn('id', $task->members ?? [])->get();

            if ($task->project) {
                // $task->project->members = Staff::whereIn('id', $task->project->membersList() ?? [])->get();
                // $task->project->members = $task->project->membersList();
            }

            return $task;
        });

        return ApiResponse::success($tasks, 'Staff tasks retrieved successfully');
    }

    public function staffProjects($id)
    {
        $staff = Staff::find($id);
        if (! $staff) {
            return ApiResponse::error('Staff not found', 404);
        }

        $projects = $staff->projects()->with('customer')->get()->map(function ($project) {
            $project->members = Staff::whereIn('id', $project->members ?? [])->get();

            return $project;
        });

        return ApiResponse::success($projects, 'Staff projects retrieved successfully');
    }
}
