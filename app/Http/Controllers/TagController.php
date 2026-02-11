<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display the tags management page.
     */
    public function index()
    {
        $tags = Tag::orderBy('name')->paginate(20);
        return view('settings.tags', compact('tags'));
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tag = Tag::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'color' => $request->color ?? '#3498db',
                'description' => $request->description,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully.',
                'data' => $tag,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tag: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name,' . $id,
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tag->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'color' => $request->color ?? $tag->color,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully.',
                'data' => $tag,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tag: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tag.
     */
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);

        try {
            DB::beginTransaction();
            $tag->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tag: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search tags (AJAX).
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $tags = Tag::search($query)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Get all tags (for global search).
     */
    public function getAllTags()
    {
        $tags = Tag::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * Toggle tag status.
     */
    public function toggleStatus($id)
    {
        $tag = Tag::findOrFail($id);

        try {
            $tag->update([
                'is_active' => !$tag->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tag status toggled successfully.',
                'data' => $tag,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle tag status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
