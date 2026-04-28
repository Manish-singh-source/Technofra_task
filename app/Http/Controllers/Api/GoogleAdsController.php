<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DigitalMarketingLead;
use App\Models\WebappLead;
use Illuminate\Http\JsonResponse;

class GoogleAdsController extends Controller
{
    //
    public function indexDigitalMarketing(): JsonResponse
    {
        $digitalMarketing = DigitalMarketingLead::paginate(10);

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

    public function indexWebAppsLeads(): JsonResponse
    {
        $webAppLeads = WebappLead::paginate(10);

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
