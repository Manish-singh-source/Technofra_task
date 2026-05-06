<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GoogleLeadDetailResource;
use App\Http\Resources\GoogleLeadListResource;
use App\Models\GoogleLead;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleLeadApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $perPage = max(1, min($perPage, 50));

            $query = GoogleLead::query()->latest();

            if ($search = trim((string) $request->query('search', ''))) {
                $query->where(function ($q) use ($search): void {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($type = $request->query('type')) {
                if ($type === 'test') {
                    $query->where('is_test', true);
                } elseif ($type === 'real') {
                    $query->where('is_test', false);
                }
            }

            if ($campaignId = $request->query('campaign_id')) {
                $query->where('campaign_id', $campaignId);
            }

            if ($leadStage = $request->query('lead_stage')) {
                $query->where('lead_stage', $leadStage);
            }

            $leads = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => GoogleLeadListResource::collection($leads->items()),
                'meta' => [
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'per_page' => $leads->perPage(),
                    'total' => $leads->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    public function show(GoogleLead $googleLead): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => new GoogleLeadDetailResource($googleLead),
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();
            $monthStart = Carbon::now()->startOfMonth();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => GoogleLead::count(),
                    'real' => GoogleLead::where('is_test', false)->count(),
                    'test' => GoogleLead::where('is_test', true)->count(),
                    'today' => GoogleLead::whereDate('created_at', $today)->count(),
                    'this_week' => GoogleLead::where('created_at', '>=', $weekStart)->count(),
                    'this_month' => GoogleLead::where('created_at', '>=', $monthStart)->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->serverErrorResponse($e);
        }
    }

    protected function serverErrorResponse(\Throwable $e): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => 'Something went wrong',
        ];

        if (config('app.debug')) {
            $payload['error'] = $e->getMessage();
        }

        return response()->json($payload, 500);
    }
}

