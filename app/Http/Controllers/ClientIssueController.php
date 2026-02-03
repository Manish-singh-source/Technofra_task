<?php

namespace App\Http\Controllers;

use App\Models\ClientIssue;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientIssueController extends Controller
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

    /**
     * Display a listing of client issues.
     */
    public function index()
    {
        $customer = $this->getLoggedInCustomer();
        
        if ($customer) {
            // Customer can only see issues for their own projects
            $clientIssues = ClientIssue::with(['project', 'customer'])
                ->whereHas('project', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->get();
            // Customer can only see their own projects
            $projects = Project::where('customer_id', $customer->id)->get();
            $customers = collect([$customer]);
        } else {
            // Admin/Staff can see all issues
            $clientIssues = ClientIssue::with(['project', 'customer'])->get();
            // Admin/Staff can see all projects and customers
            $projects = Project::all();
            $customers = Customer::all();
        }
        
        return view('client-issue', compact('clientIssues', 'projects', 'customers'));
    }

    /**
     * Store a newly created client issue in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'customer_id' => 'required|exists:customers,id',
            'issue_description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // If user is a customer, verify they own this project
        $customer = $this->getLoggedInCustomer();
        if ($customer) {
            $project = Project::findOrFail($request->project_id);
            if ($project->customer_id !== $customer->id) {
                return redirect()->back()->with('error', 'You are not authorized to create issues for this project.');
            }
        }

        ClientIssue::create([
            'project_id' => $request->project_id,
            'customer_id' => $request->customer_id,
            'issue_description' => $request->issue_description,
            'priority' => $request->priority ?? 'medium',
            'status' => $request->status ?? 'open',
        ]);

        return redirect()->route('client-issue')->with('success', 'Client issue created successfully!');
    }

    /**
     * Show the form for creating a new client issue.
     */
    public function create()
    {
        $customer = $this->getLoggedInCustomer();
        
        if ($customer) {
            // Customer can only see their own projects
            $projects = Project::where('customer_id', $customer->id)->get();
            $customers = collect([$customer]);
        } else {
            // Admin/Staff can see all projects and customers
            $projects = Project::all();
            $customers = Customer::all();
        }
        
        return view('client-issue', compact('projects', 'customers'));
    }

    /**
     * Display the specified client issue.
     */
    public function show($id)
    {
        $clientIssue = ClientIssue::with(['project', 'customer'])->findOrFail($id);
        
        // If user is a customer, verify they own this issue's project
        $customer = $this->getLoggedInCustomer();
        if ($customer && $clientIssue->project->customer_id !== $customer->id) {
            abort(403, 'You are not authorized to view this issue.');
        }
        
        return view('client-issue-details', compact('clientIssue'));
    }

    /**
     * Remove the specified client issue from storage.
     */
    public function destroy($id)
    {
        $clientIssue = ClientIssue::findOrFail($id);
        $clientIssue->delete();
        
        return redirect()->route('client-issue')->with('success', 'Client issue deleted successfully!');
    }
}
