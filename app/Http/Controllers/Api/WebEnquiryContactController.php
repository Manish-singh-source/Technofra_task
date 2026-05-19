<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebEnquiryContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $allowedSortColumns = [
            'id',
            'fname',
            'lname',
            'contact',
            'email',
            'source_page',
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

        $query = DB::table('contactform')
            ->whereNull('deleted_at')
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = trim((string) $request->input('search'));

                $builder->where(function ($nested) use ($search) {
                    $nested->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('massage', 'like', "%{$search}%")
                        ->orWhere('source_page', 'like', "%{$search}%");
                });
            });

        $contacts = (clone $query)
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends($request->query());

        $data = collect($contacts->items())
            ->map(fn ($item) => $this->mapContact($item))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'current_page' => $contacts->currentPage(),
                    'last_page' => $contacts->lastPage(),
                    'per_page' => $contacts->perPage(),
                    'total' => $contacts->total(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem(),
                    'has_more_pages' => $contacts->hasMorePages(),
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

    public function destroy(int $id): JsonResponse
    {
        $updated = DB::table('contactform')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        if (! $updated) {
            return response()->json([
                'success' => false,
                'message' => 'Contact enquiry not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact enquiry soft deleted successfully.',
        ]);
    }

    private function mapContact(object $contact): array
    {
        return [
            'id' => (int) $contact->id,
            'fname' => $contact->fname,
            'lname' => $contact->lname,
            'contact' => $contact->contact,
            'email' => $contact->email,
            'massage' => $contact->massage,
            'source_page' => $contact->source_page,
            'created_at' => $contact->created_at,
        ];
    }
}

