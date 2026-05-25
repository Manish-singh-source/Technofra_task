<?php

namespace App\Http\Controllers;

use App\Models\DigitalMarketingLead;
use App\Models\WebappLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DigitalMarketingLeadController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'loss'];

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

    public function updateStatus(Request $request, string $source, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        if ($source === 'digital_marketing') {
            $lead = DigitalMarketingLead::findOrFail($id);
        } elseif ($source === 'webapp') {
            $lead = WebappLead::findOrFail($id);
        } else {
            abort(404);
        }

        if ($lead->status === 'converted' && $validated['status'] !== 'converted') {
            return redirect()->back()->with('error', 'Converted lead status cannot be changed.');
        }

        if ($lead->status === 'converted' && $validated['status'] === 'converted') {
            return redirect()->back()->with('success', 'Lead is already converted.');
        }

        if ($validated['status'] === 'converted') {
            $nameParts = preg_split('/\s+/', trim((string) ($lead->name ?? '')), 2);

            return redirect()
                ->route('client.create')
                ->withInput([
                    'convert_source' => $source,
                    'convert_id' => $lead->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $lead->email ?? '',
                    'phone' => $lead->phone ?? '',
                    'status' => 'active',
                    'company_name' => $lead->company ?? '',
                    'website' => $lead->website ?? '',
                ])
                ->with('success', 'Conversion pending. Please complete client creation.');
        }

        $lead->status = $validated['status'];
        $lead->save();

        return redirect()
            ->route('digital-marketing-leads.index')
            ->with('success', 'Lead status updated successfully.');
    }
}
