<?php

namespace App\Http\Controllers;

use App\Models\MetaLead;
use App\Services\MetaLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetaLeadUiController extends Controller
{
    public function index(Request $request): View
    {
        $query = MetaLead::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_time', '<=', $request->date_to);
        }

        if ($request->filled('form_id')) {
            $query->where('form_id', $request->form_id);
        }

        $leads = $query->orderByDesc('created_time')
            ->paginate(20)
            ->withQueryString();

        $formIds = MetaLead::select('form_id')
            ->distinct()
            ->whereNotNull('form_id')
            ->pluck('form_id');

        $filters = $request->only(['search', 'date_from', 'date_to', 'form_id']);

        return view('leads.index', compact('leads', 'formIds', 'filters'));
    }

    public function show(MetaLead $lead): View
    {
        return view('leads.show', compact('lead'));
    }

    public function sync(Request $request): RedirectResponse
    {
        try {
            $count = app(MetaLeadService::class)->syncLeadsFromForm();

            return redirect()->route('leads.index')
                ->with('success', "Synced {$count} leads from Meta.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Sync failed: '.$e->getMessage());
        }
    }

    public function destroy(MetaLead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }
}
