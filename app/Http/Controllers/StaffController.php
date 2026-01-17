<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::all();
        return view('staff', compact('staff'));
    }

    public function show($id)
    {
        $staff = Staff::findOrFail($id);
        return view('view-staff', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $id,
            'phone' => 'required|string|max:20',
            'role' => 'required|string|max:255',
        ]);

        $staff = Staff::findOrFail($id);
        $staff->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
        ]);

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


        

        Staff::create([
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

        return redirect()->route('staff')->with('success', 'Staff added successfully.');
    }
    //destroy
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        return redirect()->route('staff')->with('success', 'Staff deleted successfully.');
    }
}
