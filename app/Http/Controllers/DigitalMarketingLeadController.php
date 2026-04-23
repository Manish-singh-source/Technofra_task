<?php

namespace App\Http\Controllers;

use App\Models\DigitalMarketingLead;
use App\Models\WebappLead;

class DigitalMarketingLeadController extends Controller
{
    public function index()
    {
        $leads = DigitalMarketingLead::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $webappLeads = WebappLead::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('digital-marketing-leads.index', compact('leads', 'webappLeads'));
    }

    public function destroy(DigitalMarketingLead $digitalMarketingLead)
    {
        $digitalMarketingLead->delete();

        return redirect()
            ->route('digital-marketing-leads.index')
            ->with('success', 'Lead deleted successfully.');
    }
}
