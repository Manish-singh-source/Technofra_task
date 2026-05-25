<?php

namespace App\Http\Controllers;

use App\Models\GoogleLead;
use Illuminate\Http\Request;

class GoogleLeadViewController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'loss'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $type = $request->query('type');

        $leads = GoogleLead::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($type === 'test', function ($query) {
                $query->where('is_test', true);
            })
            ->when($type === 'real', function ($query) {
                $query->where('is_test', false);
            })
            ->latest()
            ->paginate(15);

        return view('google-leads.index', compact('leads', 'search', 'type'));
    }

    public function show(GoogleLead $googleLead)
    {
        $lead = $googleLead;

        return view('google-leads.show', compact('lead'));
    }

    public function updateStatus(Request $request, GoogleLead $googleLead)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        if ($googleLead->status === 'converted' && $validated['status'] !== 'converted') {
            return redirect()->back()->with('error', 'Converted lead status cannot be changed.');
        }

        if ($googleLead->status === 'converted' && $validated['status'] === 'converted') {
            return redirect()->back()->with('success', 'Lead is already converted.');
        }

        if ($validated['status'] === 'converted') {
            $nameParts = preg_split('/\s+/', trim((string) ($googleLead->full_name ?? '')), 2);

            return redirect()
                ->route('client.create')
                ->withInput([
                    'convert_source' => 'google',
                    'convert_id' => $googleLead->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $googleLead->email ?? '',
                    'phone' => $googleLead->phone ?? '',
                    'status' => 'active',
                    'company_name' => $googleLead->company ?? '',
                ])
                ->with('success', 'Conversion pending. Please complete client creation.');
        }

        $googleLead->status = $validated['status'];
        $googleLead->save();

        return redirect()->back()->with('success', 'Lead status updated successfully.');
    }
}
