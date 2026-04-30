<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\BookCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookACallController extends Controller
{
    //
    public function index(Request $request): JsonResponse
    {
        $calls = BookCall::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('meeting_agenda', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);

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
