<?php

namespace App\Http\Controllers;

use App\Models\MetaLead;
use App\Services\MetaLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MetaLeadUiController extends Controller
{
    private const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'loss'];

    public function index(Request $request): View
    {
        $query = $this->scopedMetaLeadQuery();

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

        $formIds = $this->scopedMetaLeadQuery()
            ->select('form_id')
            ->distinct()
            ->whereNotNull('form_id')
            ->pluck('form_id');

        $filters = $request->only(['search', 'date_from', 'date_to', 'form_id']);

        return view('leads.index', compact('leads', 'formIds', 'filters'));
    }

    public function show(MetaLead $lead): View
    {
        abort_unless($this->canAccessMetaLead($lead), 404);

        return view('leads.show', compact('lead'));
    }

    public function sync(Request $request): RedirectResponse
    {
        abort_unless($this->canViewLeadManagement(), 403);

        $redirectTo = $this->syncRedirectTarget($request);

        try {
            $service = app(MetaLeadService::class);
            $credentials = $service->credentialsForUser((int) auth()->id());

            if (! $service->hasRequiredCredentials($credentials)) {
                return redirect()->to($redirectTo)
                    ->with('error', 'Please save Meta Page Access Token and Form ID before syncing.');
            }

            $count = $service->syncLeadsFromForm(null, $credentials, (int) auth()->id());

            return redirect()->to($redirectTo)
                ->with('success', "Synced {$count} leads from Meta.");
        } catch (\Throwable $e) {
            return redirect()->to($redirectTo)
                ->with('error', 'Sync failed: '.$e->getMessage());
        }
    }

    private function syncRedirectTarget(Request $request): string
    {
        $returnTo = (string) $request->input('return_to', '');

        if ($returnTo !== '') {
            $appUrl = rtrim(url('/'), '/');

            if ($returnTo === $appUrl || str_starts_with($returnTo, $appUrl.'/')) {
                return $returnTo;
            }

            if (str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//')) {
                return $returnTo;
            }
        }

        return route('leads.index');
    }

    public function destroy(MetaLead $lead): RedirectResponse
    {
        abort_unless($this->canAccessMetaLead($lead), 404);

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    private function scopedMetaLeadQuery()
    {
        $query = MetaLead::query();
        $user = auth()->user();
        $userId = (int) ($user?->id ?? 0);

        abort_unless($this->canViewLeadManagement(), 403);

        if ($this->isStaffUser() && ! $this->hasAdminLevelLeadAccess()) {
            return $query->whereExists(function ($subQuery) use ($userId) {
                $subQuery->selectRaw('1')
                    ->from('assigned_leads')
                    ->where('assigned_leads.lead_model', 'meta')
                    ->whereColumn('assigned_leads.lead_id', 'meta_leads.id')
                    ->where(function ($jsonQuery) use ($userId) {
                        $jsonQuery
                            ->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                            ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                    });
            });
        }

        return $query->whereHas('users', fn ($userQuery) => $userQuery->where('users.id', $userId));
    }

    private function canAccessMetaLead(MetaLead $lead): bool
    {
        return $this->scopedMetaLeadQuery()
            ->where('meta_leads.id', $lead->id)
            ->exists();
    }

    private function isStaffUser(): bool
    {
        $role = strtolower((string) (auth()->user()?->getRawOriginal('role') ?? auth()->user()?->role ?? ''));

        return $role === 'staff';
    }

    private function canViewLeadManagement(): bool
    {
        return $this->hasAdminLevelLeadAccess()
            || (bool) auth()->user()?->can('view_leads_management');
    }

    private function hasAdminLevelLeadAccess(): bool
    {
        $user = auth()->user();
        $role = strtolower((string) ($user?->getRawOriginal('role') ?? $user?->role ?? ''));
        $adminRoles = ['admin', 'super-admin', 'super_admin', 'super_admin2'];

        if (in_array($role, $adminRoles, true)) {
            return true;
        }

        return method_exists($user, 'hasAnyRole') && $user->hasAnyRole($adminRoles);
    }

    public function updateStatus(Request $request, MetaLead $lead): RedirectResponse
    {
        abort_unless($this->canAccessMetaLead($lead), 404);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        if ($lead->status === 'converted' && $validated['status'] !== 'converted') {
            return redirect()->back()->with('error', 'Converted lead status cannot be changed.');
        }

        if ($lead->status === 'converted' && $validated['status'] === 'converted') {
            return redirect()->back()->with('success', 'Lead is already converted.');
        }

        if ($validated['status'] === 'converted') {
            $nameParts = preg_split('/\s+/', trim((string) ($lead->full_name ?? '')), 2);

            return redirect()
                ->route('client.create')
                ->withInput([
                    'convert_source' => 'meta',
                    'convert_id' => $lead->id,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $lead->email ?? '',
                    'phone' => $lead->phone ?? '',
                    'status' => 'active',
                    'city' => $lead->city ?? '',
                    'state' => $lead->state ?? '',
                    'company_name' => '',
                ])
                ->with('success', 'Conversion pending. Please complete client creation.');
        }

        $lead->status = $validated['status'];
        $lead->save();

        return redirect()->back()->with('success', 'Lead status updated successfully.');
    }
}
