<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Get the logged-in customer
     */
    private function getLoggedInCustomer()
    {
        $user = Auth::user();
        if ($user && $user->hasRole('customer')) {
            return Customer::where('email', $user->email)->first();
        }
        return null;
    }

    /**
     * Check if current user is a customer
     */
    private function isCustomer()
    {
        return Auth::user() && Auth::user()->hasRole('customer');
    }

    public function index()
    {
        $customer = $this->getLoggedInCustomer();
        
        if ($customer) {
            // Customer can only see their own projects
            $projects = Project::with('customer')->where('customer_id', $customer->id)->get();
        } else {
            // Admin/Staff can see all projects
            $projects = Project::with('customer')->get();
        }
        
        $staff = Staff::all()->keyBy('id');
        $allProjects = $projects->count();
        $planningProjects = $projects->where('status', 'not_started')->count();
        $inProgressProjects = $projects->where('status', 'in_progress')->count();
        $onHoldProjects = $projects->where('status', 'on_hold')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        $cancelledProjects = $projects->where('status', 'cancelled')->count();
        return view('project', compact('projects', 'staff', 'allProjects', 'planningProjects', 'inProgressProjects', 'onHoldProjects', 'completedProjects', 'cancelledProjects'));
    }

    public function create()
    {
        $customers = Customer::all();
        $staff = Staff::all();
        return view('add-project', compact('customers', 'staff'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => 'required|exists:customers,id',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => 'exists:staff,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Project::create([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $request->status ?? 'not_started',
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'billing_type' => $request->billing_type,
            'total_rate' => $request->total_rate,
            'estimated_hours' => $request->estimated_hours,
            'tags' => $request->tags,
            'members' => $request->members,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'technologies' => $request->technologies,
        ]);

        return redirect()->route('project')->with('success', 'Project created successfully!');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $customers = Customer::all();
        $staff = Staff::all();
        return view('edit-project', compact('project', 'customers', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'customer' => 'required|exists:customers,id',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_type' => 'nullable|in:fixed_rate,hourly_rate',
            'total_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'members' => 'nullable|array',
            'members.*' => 'exists:staff,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $project->update([
            'project_name' => $request->project_name,
            'customer_id' => $request->customer,
            'status' => $request->status ?? 'not_started',
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
            'billing_type' => $request->billing_type,
            'total_rate' => $request->total_rate,
            'estimated_hours' => $request->estimated_hours,
            'tags' => $request->tags,
            'members' => $request->members,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'technologies' => $request->technologies,
        ]);

        return redirect()->route('project')->with('success', 'Project updated successfully!');
    }

    public function show($id)
    {
        $customer = $this->getLoggedInCustomer();
        $project = Project::with('customer')->findOrFail($id);
        
        // If user is a customer, verify they own this project
        if ($customer && $project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this project.');
        }
        
        $staff = Staff::all()->keyBy('id');
        
        if ($customer) {
            // Customer can only see their own projects
            $allProjects = Project::where('customer_id', $customer->id)->get();
        } else {
            $allProjects = Project::all();
        }
        
        return view('project-details', compact('project', 'staff', 'allProjects'));
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('project')->with('success', 'Project deleted successfully!');
    }

    /**
     * Customer's own projects - simplified view for customers
     */
    public function myProjects()
    {
        $customer = $this->getLoggedInCustomer();
        
        if (!$customer) {
            // Redirect non-customers to the main project page
            return redirect()->route('project');
        }
        
        // Customer can only see their own projects
        $projects = Project::with('customer')->where('customer_id', $customer->id)->get();
        $staff = Staff::all()->keyBy('id');
        $allProjects = $projects->count();
        $planningProjects = $projects->where('status', 'not_started')->count();
        $inProgressProjects = $projects->where('status', 'in_progress')->count();
        $onHoldProjects = $projects->where('status', 'on_hold')->count();
        $completedProjects = $projects->where('status', 'completed')->count();
        $cancelledProjects = $projects->where('status', 'cancelled')->count();
        
        return view('customer-projects', compact('projects', 'staff', 'allProjects', 'planningProjects', 'inProgressProjects', 'onHoldProjects', 'completedProjects', 'cancelledProjects', 'customer'));
    }

}
