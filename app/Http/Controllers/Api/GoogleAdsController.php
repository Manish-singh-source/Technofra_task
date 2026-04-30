<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DigitalMarketingLead;
use App\Models\WebappLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleAdsController extends Controller
{
    //
    public function indexDigitalMarketing(Request $request): JsonResponse
    {
        $digitalMarketing = DigitalMarketingLead::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('website', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);

        if (!$digitalMarketing) {
            return ApiResponse::error('Digital Marketing not found.', null, 404);
        }

        return ApiResponse::success($digitalMarketing, 'Digital Marketing retrieved successfully.');
    }

    public function destroyDigitalMarketing($id)
    {
        $digitalMarketing = DigitalMarketingLead::find($id);

        if (!$digitalMarketing) {
            return ApiResponse::error('Digital Marketing not found.', null, 404);
        }

        try {
            $digitalMarketing->delete();
            return ApiResponse::success(null, 'Digital Marketing deleted successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to delete Digital Marketing.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }
    //

    public function indexWebAppsLeads(Request $request): JsonResponse
    {
        $webAppLeads = WebappLead::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('website', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);

        if (!$webAppLeads) {
            return ApiResponse::error('Web App Leads not found.', null, 404);
        }

        return ApiResponse::success($webAppLeads, 'Web App Leads retrieved successfully.');
    }

    public function destroyWebAppsLeads($id)
    {
        $webAppLead = WebappLead::find($id);

        if (!$webAppLead) {
            return ApiResponse::error('Web App Lead not found.', null, 404);
        }

        try {
            $webAppLead->delete();
            return ApiResponse::success(null, 'Web App Lead deleted successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to delete Web App Lead.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }
}
