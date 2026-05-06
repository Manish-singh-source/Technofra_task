<?php

namespace App\Http\Controllers;

use App\Models\GoogleLead;
use Illuminate\Http\Request;

class GoogleLeadViewController extends Controller
{
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
}
