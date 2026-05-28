<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return ApiResponse::success($vendors, 'Vendors retrieved successfully');
    }

    public function show($id)
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return ApiResponse::error('Vendor not found', null, 404);
        }

        return ApiResponse::success($vendor, 'Vendor retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vendors,name',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'nullable|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive',
        ], [
            'name.unique' => 'Vendor Name is already registered.',
            'name.required' => 'The vendor name field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.numeric' => 'The phone must be a number.',
            'phone.digits_between' => 'The phone must be between 10 and 15 digits.',
            'status.nullable' => 'The status field is optional.',
            'status.in' => 'The status must be either "active" or "inactive".',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            // format status 
            $data = $validator->validated();
            $data['status'] = '1';

            $vendor = Vendor::create($data);

            return ApiResponse::success($vendor, 'Vendor created successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create vendor.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return ApiResponse::error('Vendor not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vendors,name,' . $vendor->id,
            'email' => 'nullable|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'nullable|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:active,inactive',
        ], [
            'name.unique' => 'Vendor Name is already registered.',
            'name.required' => 'The vendor name field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.numeric' => 'The phone must be a number.',
            'phone.digits_between' => 'The phone must be between 10 and 15 digits.',
            'status.nullable' => 'The status field is optional.',
            'status.in' => 'The status must be either "active" or "inactive".',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $data = $validator->validated();

            $data['status'] = $data['status'] == 'active' ? '1' : '0';

            $vendor->update($data);

            return ApiResponse::success($vendor, 'Vendor updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update vendor.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if (! $vendor) {
            return ApiResponse::error('Vendor not found', null, 404);
        }

        try {
            $vendor->delete();

            return ApiResponse::success(null, 'Vendor deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete vendor.', ['exception' => $e->getMessage()], 500);
        }
    }
}
