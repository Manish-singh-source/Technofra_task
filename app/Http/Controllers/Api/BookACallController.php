<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\BookCall;
use Illuminate\Http\JsonResponse;

class BookACallController extends Controller
{
    //
    public function index(): JsonResponse
    {
        $calls = BookCall::paginate(10);

        if (!$calls) {
            return ApiResponse::error('Book A Calls not found.', null, 404);
        }

        return ApiResponse::success($calls, 'Book A Calls retrieved successfully.');
    }

    public function destroy($id)
    {
        $call = BookCall::find($id);

        if (!$call) {
            return ApiResponse::error('Book A Call not found.', null, 404);
        }

        try {
            $call->delete();
            return ApiResponse::success(null, 'Book A Call deleted successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to delete Book A Call.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }
}
