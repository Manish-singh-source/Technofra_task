<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index()
    {
        $customers = Customer::with('user')->get();
        return view('clients', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        $roles = Role::all();
        return view('add-clients', compact('roles'));
    }

    /**
     * Store a newly created customer.
     */
    public function storeclient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|min:3',
            'contact_person' => 'required|string|min:3',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|min:10',
            'website' => 'nullable|url',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'client_type' => 'required|in:Individual,Company,Organization',
            'industry' => 'required|string',
            'status' => 'required|in:Active,Inactive,Suspended',
            'priority_level' => 'nullable|in:Low,Medium,High',
            'assigned_manager_id' => 'nullable|integer',
            'default_due_days' => 'nullable|integer',
            'billing_type' => 'nullable|in:Hourly,Fixed,Retainer',
            'role' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $userId = null;

            // Create User for customer login if password is provided
            if ($request->password) {
                // Check if email already exists in users table
                $existingUser = User::where('email', $request->email)->first();
                if ($existingUser) {
                    return back()->withErrors(['email' => 'This email is already registered as a user.'])->withInput();
                }

                $user = User::create([
                    'name' => $request->client_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $userId = $user->id;

                // Assign role to user
                $roleName = $request->role ?? 'customer';
                $customerRole = Role::where('name', $roleName)->first();
                if ($customerRole) {
                    $user->assignRole($customerRole);
                } else {
                    // Create customer role if it doesn't exist
                    $customerRole = Role::create(['name' => 'customer']);
                    $user->assignRole($customerRole);
                }

                // Clear permission cache
                Cache::forget('spatie.permission.cache');
            }

            // Create Customer record
            $customer = new Customer();
            $customer->user_id = $userId;
            $customer->client_name = $request->client_name;
            $customer->contact_person = $request->contact_person;
            $customer->email = $request->email;
            $customer->phone = $request->phone;
            $customer->website = $request->website;
            $customer->address_line1 = $request->address_line1;
            $customer->address_line2 = $request->address_line2;
            $customer->city = $request->city;
            $customer->state = $request->state;
            $customer->postal_code = $request->postal_code;
            $customer->country = $request->country;
            $customer->client_type = $request->client_type;
            $customer->industry = $request->industry;
            $customer->status = $request->status;
            $customer->priority_level = $request->priority_level;
            $customer->assigned_manager_id = $request->assigned_manager_id;
            $customer->default_due_days = $request->default_due_days;
            $customer->billing_type = $request->billing_type;
            $customer->role = $request->role;
            $customer->password = $request->password ? Hash::make($request->password) : null;
            $customer->save();

            DB::commit();
            return redirect()->route('clients')->with('success', 'Customer added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id)
    {
        $customer = Customer::with([
            'projects.tasks',
            'user',
            'clientIssues' => function ($query) {
                $query->latest();
            },
            'clientIssues.project',
            'clientIssues.teamAssignments.assignedStaff',
        ])->findOrFail($id);
        $roles = Role::all();
        return view('clients-details', compact('customer', 'roles'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|min:3',
            'contact_person' => 'required|string|min:3',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|min:10',
            'website' => 'nullable|url',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'client_type' => 'required|in:Individual,Company,Organization',
            'industry' => 'required|string',
            'status' => 'required|in:Active,Inactive,Suspended',
            'priority_level' => 'nullable|in:Low,Medium,High',
            'assigned_manager_id' => 'nullable|integer',
            'default_due_days' => 'nullable|integer',
            'billing_type' => 'nullable|in:Hourly,Fixed,Retainer',
            'role' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $oldEmail = $customer->email;
            $oldRole = $customer->role;

            $customer->update($request->except(['password']));

            // Update associated user if exists
            $user = $customer->user ?? User::where('email', $oldEmail)->first();
            if ($user) {
                $user->update([
                    'name' => $request->client_name,
                    'email' => $request->email,
                ]);

                // Update role if changed
                if ($oldRole !== $request->role && $request->role) {
                    if ($oldRole) {
                        $user->removeRole($oldRole);
                    }
                    $newRole = Role::where('name', $request->role)->first();
                    if ($newRole) {
                        $user->assignRole($newRole);
                    }
                }

                // Clear permission cache
                Cache::forget('spatie.permission.cache');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer.
     */
    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete associated user if exists
            if ($customer->user) {
                $customer->user->delete();
            } else {
                $user = User::where('email', $customer->email)->first();
                if ($user) {
                    $user->delete();
                }
            }
            
            $customer->delete();

            DB::commit();
            return redirect()->route('clients')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete customer: ' . $e->getMessage());
        }
    }

    /**
     * API: Get all customers.
     */
    public function apiIndex()
    {
        $customers = Customer::with('user.roles')->get();
        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * API: Store a new customer.
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|min:3',
            'contact_person' => 'required|string|min:3',
            'email' => 'required|email|unique:customers,email|unique:users,email',
            'phone' => 'nullable|string|min:10',
            'address_line1' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country' => 'required|string',
            'client_type' => 'required|in:Individual,Company,Organization',
            'industry' => 'required|string',
            'status' => 'required|in:Active,Inactive,Suspended',
            'role' => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create User
            $user = User::create([
                'name' => $request->client_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role
            $roleName = $request->role ?? 'customer';
            $role = Role::firstOrCreate(['name' => $roleName]);
            $user->assignRole($role);

            // Create Customer
            $customer = Customer::create([
                'user_id' => $user->id,
                'client_name' => $request->client_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'website' => $request->website,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'client_type' => $request->client_type,
                'industry' => $request->industry,
                'status' => $request->status,
                'priority_level' => $request->priority_level,
                'assigned_manager_id' => $request->assigned_manager_id,
                'default_due_days' => $request->default_due_days,
                'billing_type' => $request->billing_type,
                'role' => $roleName,
                'password' => Hash::make($request->password),
            ]);

            Cache::forget('spatie.permission.cache');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
                'data' => $customer->load('user.roles'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage(),
            ], 500);
        }
    }
}
