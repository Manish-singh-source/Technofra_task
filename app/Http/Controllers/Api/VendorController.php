<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::orderBy('created_at', 'desc')->get();

        return ApiResponse::success($vendors, 'Vendors retrieved successfully');
    }

    public function show($id)
    {
        $vendor = Vendor::with(['services.client'])->find($id);

        if (! $vendor) {
            return ApiResponse::error('Vendor not found', null, 404);
        }

        return ApiResponse::success($vendor, 'Vendor retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendors,email',
            'phone' => 'required|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:1,0',
        ], [
            'name.required' => 'The vendor name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'The phone field is required.',
            'phone.numeric' => 'The phone must be a number.',
            'phone.digits_between' => 'The phone must be between 10 and 15 digits.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $vendor = Vendor::create($validator->validated());

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:vendors,email,' . $vendor->id,
            'phone' => 'required|numeric|digits_between:10,15',
            'address' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:1,0',
        ], [
            'name.required' => 'The vendor name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.required' => 'The phone field is required.',
            'phone.numeric' => 'The phone must be a number.',
            'phone.digits_between' => 'The phone must be between 10 and 15 digits.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed.', $validator->errors(), 422);
        }

        try {
            $vendor->update($validator->validated());

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

    public function destroyAll()
    {
        try {
            Vendor::query()->delete();

            return ApiResponse::success(null, 'All vendors deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete all vendors.', ['exception' => $e->getMessage()], 500);
        }
    }
}
