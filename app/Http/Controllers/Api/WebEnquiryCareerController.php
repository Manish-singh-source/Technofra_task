<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebEnquiryCareerController extends Controller
{
    private const RESUME_URL_PREFIX = 'https://technofra.com/';

    public function index(Request $request): JsonResponse
    {
        $allowedSortColumns = [
            'id',
            'fname',
            'email',
            'contact',
            'role',
            'experience',
            'ctc',
            'ectc',
            'location',
            'refrence',
            'created_at',
        ];

        $sortBy = (string) $request->input('sort_by', 'created_at');
        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtolower((string) $request->input('sort_order', 'desc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'desc';

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $query = DB::table('jobapplication')
            ->whereNull('deleted_at')
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = trim((string) $request->input('search'));

                $builder->where(function ($nested) use ($search) {
                    $nested->where('fname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('refrence', 'like', "%{$search}%");
                });
            });

        $careers = (clone $query)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        $data = collect($careers->items())
            ->map(fn ($item) => $this->mapCareer($item))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'current_page' => $careers->currentPage(),
                    'last_page' => $careers->lastPage(),
                    'per_page' => $careers->perPage(),
                    'total' => $careers->total(),
                    'from' => $careers->firstItem(),
                    'to' => $careers->lastItem(),
                    'has_more_pages' => $careers->hasMorePages(),
                ],
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
                'filters' => [
                    'search' => $request->input('search'),
                ],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $career = DB::table('jobapplication')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $career) {
            return response()->json([
                'success' => false,
                'message' => 'Career enquiry not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->mapCareer($career),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $updated = DB::table('jobapplication')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Career enquiry not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Career enquiry soft deleted successfully.',
        ]);
    }

    public function resumeUrl(int $id): JsonResponse
    {
        $career = DB::table('jobapplication')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (! $career) {
            return response()->json([
                'success' => false,
                'message' => 'Career enquiry not found.',
            ], 404);
        }

        $resumePath = ltrim((string) ($career->resume_file ?? ''), '/');
        $resumeUrl = $resumePath !== '' ? self::RESUME_URL_PREFIX.$resumePath : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $career->id,
                'resume_file' => $career->resume_file,
                'resume_url' => $resumeUrl,
            ],
        ]);
    }

    private function mapCareer(object $career): array
    {
        $resumePath = ltrim((string) ($career->resume_file ?? ''), '/');
        $resumeUrl = $resumePath !== '' ? self::RESUME_URL_PREFIX.$resumePath : null;

        return [
            'id' => (int) $career->id,
            'fname' => $career->fname,
            'email' => $career->email,
            'contact' => $career->contact,
            'role' => $career->role,
            'experience' => $career->experience,
            'ctc' => $career->ctc,
            'ectc' => $career->ectc,
            'location' => $career->location,
            'skills_text' => $career->skills_text,
            'skills_json' => $career->skills_json,
            'ai_tools_text' => $career->ai_tools_text,
            'ai_tools_json' => $career->ai_tools_json,
            'notice' => $career->notice,
            'rn' => $career->rn,
            'refrence' => $career->refrence,
            'resume_file' => $career->resume_file,
            'resume_url' => $resumeUrl,
            'portfolio_link' => $career->portfolio_link,
            'source_page' => $career->source_page,
            'created_at' => $career->created_at,
        ];
    }
}

