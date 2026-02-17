<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Team;
use App\Models\Project;
use App\Models\User;
use App\Mail\StaffInviteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display the staff creation form.
     */
    public function create()
    {
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        return view('add-staff', compact('roles', 'teams'));
    }

    /**
     * Display a listing of staff members.
     */
    public function index()
    {
        $staff = Staff::with('user')->get();
        return view('staff', compact('staff'));
    }

    /**
     * Display the specified staff member.
     */
    public function show($id)
    {
        $staff = Staff::with('user')->findOrFail($id);
        $roles = Role::all();
        $teams = Team::getTeamOptions();
        $projects = $staff->projects()->latest()->get();
        $tasks = $staff->tasks()->with('project')->latest()->get();
        return view('view-staff', compact('staff', 'roles', 'teams', 'projects', 'tasks'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
            'departments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $staff = Staff::findOrFail($id);
        
        // Get old role and email to update user later
        $oldRole = $staff->role;
        $oldEmail = $staff->email;
        
        DB::beginTransaction();
        try {
            $staff->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
                'team' => $request->team ?: null,
                'departments' => $request->departments,
            ]);

            // Update associated user
            $user = $staff->user ?? User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                ]);

                // Update role if changed
                if ($oldRole !== $request->role) {
                    $user->removeRole($oldRole);
                    $newRole = Role::where('name', $request->role)->first();
                    if ($newRole) {
                        $user->assignRole($newRole);
                    }
                }
            }

            // Clear permission cache
            Cache::forget('spatie.permission.cache');
            
            DB::commit();
            return redirect()->route('staff')->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profileImage')) {
                $image = $request->file('profileImage');
                $ext = $image->getClientOriginalExtension();
                $imageName = time() . "." . $ext;
                $image->move(public_path('uploads/staff'), $imageName);
                $profileImagePath = $imageName;
            }

            // Create User first
            $user = User::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role to user
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create Staff record linked to user
            $staff = Staff::create([
                'user_id' => $user->id,
                'profile_image' => $profileImagePath,
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'departments' => $request->departments,
                'team' => $request->team ?: null,
            ]);

            // Clear permission cache
            Cache::forget('spatie.permission.cache');

            // Send invitation email with login credentials
            $staffName = $request->firstName . ' ' . $request->lastName;
            try {
                Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
            } catch (\Exception $mailException) {
                // Log the mail error but don't fail the staff creation
                Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
            }

            DB::commit();
            return redirect()->route('staff')->with('success', 'Staff added successfully. Invitation email sent to ' . $request->email);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create staff: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated user if exists
            if ($staff->user) {
                $staff->user->delete();
            } else {
                // Fallback: find user by email
                $user = User::where('email', $staff->email)->first();
                if ($user) {
                    $user->delete();
                }
            }
            
            $staff->delete();
            
            DB::commit();
            return redirect()->route('staff')->with('success', 'Staff deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete staff: ' . $e->getMessage());
        }
    }

    /**
     * Delete selected staff members.
     */
    public function deleteSelected(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        if (empty($ids)) {
            return redirect()->route('staff')->with('error', 'No staff selected for deletion.');
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $staff = Staff::find($id);
                if ($staff) {
                    // Delete associated user if exists
                    if ($staff->user) {
                        $staff->user->delete();
                    } else {
                        // Fallback: find user by email
                        $user = User::where('email', $staff->email)->first();
                        if ($user) {
                            $user->delete();
                        }
                    }
                    $staff->delete();
                }
            }
            
            DB::commit();
            return redirect()->route('staff')->with('success', 'Selected staff members deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff')->with('error', 'Failed to delete selected staff: ' . $e->getMessage());
        }
    }

    /**
     * API: Get all staff members.
     */
    public function apiIndex()
    {
        $staff = Staff::with('user.roles')->get();
        return response()->json([
            'success' => true,
            'data' => $staff,
        ]);
    }

    /**
     * API: Store a new staff member.
     */
    public function apiStore(Request $request)
    {
        $teams = Team::getTeamOptions();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
            'team' => ['nullable', 'string', 'max:255', Rule::in($teams)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create User first
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role to user
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->assignRole($role);
            }

            // Create Staff record linked to user
            $staff = Staff::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'status' => 'active',
                'departments' => $request->departments,
                'team' => $request->team ?: null,
            ]);

            // Clear permission cache
            Cache::forget('spatie.permission.cache');

            // Send invitation email with login credentials
            $staffName = $request->first_name . ' ' . $request->last_name;
            try {
                Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
            } catch (\Exception $mailException) {
                // Log the mail error but don't fail the staff creation
                Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully. Invitation email sent.',
                'data' => $staff->load('user.roles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create staff: ' . $e->getMessage(),
            ], 500);
        }
    }
}
