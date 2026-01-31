<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CustomerController extends Controller
{
    public function index(){
        $customers = Customer::all();
        return view('clients' ,compact('customers'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('add-clients', compact('roles'));
    }

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

        $customer = new Customer();
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

        // Create User for customer login
        if ($request->password) {
            $user = User::create([
                'name' => $request->client_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign 'customer' role to user
            $customerRole = Role::where('name', 'customer')->first();
            if ($customerRole) {
                $user->assignRole($customerRole);
            }
        }

        return redirect()->route('clients')->with('success', 'Customer added successfully.');
    }

    public function show($id)
    {
        $customer = Customer::with('projects.tasks')->findOrFail($id);
        return view('clients-details', compact('customer'));
    }

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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $customer->update($request->all());

        return redirect()->back()->with('success', 'Customer updated successfully.');
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        
        // Delete associated user if exists
        $user = User::where('email', $customer->email)->first();
        if ($user) {
            $user->delete();
        }
        
        $customer->delete();

        return redirect()->route('clients')->with('success', 'Customer deleted successfully.');
    }
    
}