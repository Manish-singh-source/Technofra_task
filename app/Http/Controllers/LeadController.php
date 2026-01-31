<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Display a listing of all leads.
     */
    public function index()
    {
        $leads = Lead::all();
        $staff = Staff::all()->keyBy('id');
        
        $allLeads = $leads->count();
        $newLeads = $leads->where('status', 'new')->count();
        $contactedLeads = $leads->where('status', 'contacted')->count();
        $qualifiedLeads = $leads->where('status', 'qualified')->count();
        $convertedLeads = $leads->where('status', 'converted')->count();
        $lostLeads = $leads->where('status', 'lost')->count();
        
        return view('leads', compact('leads', 'staff', 'allLeads', 'newLeads', 'contactedLeads', 'qualifiedLeads', 'convertedLeads', 'lostLeads'));
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create()
    {
        $staff = Staff::all();
        return view('add-lead', compact('staff'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'lead_value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:100',
            'assigned' => 'nullable|array',
            'assigned.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,
            'website' => $request->website,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'zipCode' => $request->zipCode,
            'lead_value' => $request->lead_value,
            'source' => $request->source,
            'assigned' => $request->assigned,
            'tags' => $request->tags,
            'description' => $request->description,
            'status' => $request->status ?? 'new',
        ]);

        return redirect()->route('leads')->with('success', 'Lead created successfully!');
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $staff = Staff::all();
        return view('edit-lead', compact('lead', 'staff'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'lead_value' => 'nullable|numeric|min:0',
            'source' => 'nullable|string|max:100',
            'assigned' => 'nullable|array',
            'assigned.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'description' => 'nullable|string',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $lead->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,
            'website' => $request->website,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'zipCode' => $request->zipCode,
            'lead_value' => $request->lead_value,
            'source' => $request->source,
            'assigned' => $request->assigned,
            'tags' => $request->tags,
            'description' => $request->description,
            'status' => $request->status ?? 'new',
        ]);

        return redirect()->route('leads')->with('success', 'Lead updated successfully!');
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id);
        $staff = Staff::all()->keyBy('id');
        return view('view-lead', compact('lead', 'staff'));
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads')->with('success', 'Lead deleted successfully!');
    }

    /**
     * Toggle lead status.
     */
    public function toggleStatus(Request $request)
    {
        $lead = Lead::findOrFail($request->id);
        $lead->status = $request->status;
        $lead->save();

        return response()->json(['success' => true]);
    }

    /**
     * Delete selected leads.
     */
    public function deleteSelected(Request $request)
    {
        $leadIds = $request->ids;
        Lead::whereIn('id', $leadIds)->delete();

        return response()->json(['success' => true, 'message' => 'Selected leads deleted successfully!']);
    }
}
