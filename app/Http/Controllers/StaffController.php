<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function create()
    {
        $roles = Role::all();
        return view('add-staff', compact('roles'));
    }

    public function index()
    {
        $staff = Staff::all();
        return view('staff', compact('staff'));
    }

    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        $roles = Role::all();
        return view('view-staff', compact('staff', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $staff = Staff::findOrFail($id);
        
        // Get old role to update user role later
        $oldRole = $staff->role;
        
        $staff->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        // Update user's role if email matches
        $user = User::where('email', $staff->email)->first();
        if ($user && $oldRole !== $request->role) {
            // Remove old role and assign new role
            $user->removeRole($oldRole);
            $newRole = Role::where('name', $request->role)->first();
            if ($newRole) {
                $user->assignRole($newRole);
            }
            // Clear permission cache
            Cache::forget('spatie.permission.cache');
        }

        return redirect()->route('staff')->with('success', 'Staff updated successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'departments' => 'nullable|array',
        ]);

        $profileImagePath = null;
        if ($request->hasFile('profileImage')) {
            $image = $request->file('profileImage');
            $ext = $image->getClientOriginalExtension();
            $imageName = time() . "." . $ext;
            $image->move(public_path('uploads/staff'), $imageName);
            $profileImagePath = $imageName;
        }


        

        $staff = Staff::create([
            'profile_image' => $profileImagePath,
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'active', // default
            'departments' => $request->departments,
        ]);

        // Create User for login
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

        // Clear permission cache
        Cache::forget('spatie.permission.cache');

        return redirect()->route('staff')->with('success', 'Staff added successfully.');
    }
    //destroy
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        
        // Delete associated user if exists
        $user = User::where('email', $staff->email)->first();
        if ($user) {
            $user->delete();
        }
        
        $staff->delete();
        return redirect()->route('staff')->with('success', 'Staff deleted successfully.');
    }
}
