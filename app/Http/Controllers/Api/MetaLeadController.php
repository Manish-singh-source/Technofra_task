<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetaLead;
use App\Services\MetaLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaLeadController extends Controller
{
    public function index(Request $request): JsonResponse
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

        $perPage = (int) $request->integer('per_page', 20);
        $perPage = $perPage > 0 ? min($perPage, 100) : 20;

        $leads = $query->orderByDesc('created_time')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Meta leads fetched successfully.',
            'data' => $leads->items(),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
                'from' => $leads->firstItem(),
                'to' => $leads->lastItem(),
            ],
            'filters' => $request->only(['search', 'date_from', 'date_to', 'form_id']),
        ]);
    }

    public function show(MetaLead $lead): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Meta lead fetched successfully.',
            'data' => $lead,
        ]);
    }

    public function sync(Request $request, MetaLeadService $metaLeadService): JsonResponse
    {
        try {
            $formId = $request->input('form_id');
            $count = $metaLeadService->syncLeadsFromForm($formId);

            return response()->json([
                'success' => true,
                'message' => "Synced {$count} leads from Meta.",
                'synced_count' => $count,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(MetaLead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted.',
        ]);
    }
}
