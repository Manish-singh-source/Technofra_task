<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    //
    public function index()
    {
        $clients = User::with('businessDetail:id,user_id,company_name')->where('role', 'client')->paginate(10);
        $clientsCount = User::where('role', 'client')->count();
        if (!$clients) {
            return ApiResponse::error('No client found');
        }
        return ApiResponse::success([
            'clients' => $clients,
            'count' => $clientsCount,
        ], 'Clients found');
    }

    public function show($id)
    {
        $client = User::with('address', 'businessDetail')->where('role', 'client')->findOrFail($id);
        if (!$client) {
            return ApiResponse::error('client not found');
        }

        $client->tasksCount = $client->tasks()->count();
        $client->projectsCount = $client->projects()->count();

        return ApiResponse::success($client, 'Client found');
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'password' => 'required|string|min:8',

            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',

            'client_type' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
        ]);


        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profile_image'));
            } else {
                $fileName = Str::uuid() . '.png';
                $path = public_path('uploads/client/' . $fileName);

                $avatar = app('avatar');
                $avatar->create($request->first_name . ' ' . $request->last_name)->save($path);
                $profileImagePath = 'uploads/client/' . 'uploads/client/' . $fileName;
            }

            $client = User::create([
                'profile_image' => $profileImagePath,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'client',
                'password' => Hash::make($request->password),
                'status' => $request->status ?? 'active',
            ]);

            if ($client) {
                $address = UserAddress::create([
                    'user_id' => $client->id,
                    'address_line_1' => $request->address_line_1,
                    'address_line_2' => $request->address_line_2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'pincode' => $request->pincode,
                ]);

                $businessDetail = ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => $request->client_type,
                    'company_name' => $request->company_name,
                    'industry' => $request->industry,
                    'website' => $request->website,
                ]);

                // $staffName = $request->first_name . ' ' . $request->last_name;
                // try {
                //     Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
                // } catch (\Exception $mailException) {
                //     Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
                // }
            }

            if ($client && $address && $businessDetail) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Client created successfully. Invitation email sent.',
                    'data' => [
                        'client' => $client,
                        'address' => $address,
                        'businessDetail' => $businessDetail,
                    ]
                ], 201);
            }

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],

            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',

            'client_type' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
        ]);


        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $client = User::where('role', 'client')->find($id);

            if ($client->profile_image && $client->profile_image !== 'default.png') {
                // delete existing image if it's not the default
                $imagePath = public_path($client->profile_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $profileImagePath = $client->profile_image; // keep existing if no new upload
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profile_image'));
            } elseif (!$client->profile_image || $client->profile_image === 'default.png') {
                // generate avatar if no existing image or it's default
                $fileName = Str::uuid() . '.png';
                $path = public_path($fileName);
                $avatar = app('avatar');
                $avatar->create($request->first_name . ' ' . $request->last_name)->save($path);
                $profileImagePath = $fileName;
            }

            $client->update([
                'profile_image' => $profileImagePath,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'status' => $request->status ?? 'active',
            ]);

            if ($client) {
                $address = UserAddress::updateOrCreate(
                    ['user_id', $client->id],
                    [
                        'address_line_1' => $request->address_line_1 ?? '',
                        'address_line_2' => $request->address_line_2 ?? '',
                        'city' => $request->city ?? '',
                        'state' => $request->state ?? '',
                        'country' => $request->country ?? '',
                        'pincode' => $request->pincode ?? '',
                    ]
                );

                $businessDetail = ClientBusinessDetail::createOrUpdate(
                    ['user_id', $client->id],
                    [
                        'client_type' => $request->client_type,
                        'company_name' => $request->company_name,
                        'industry' => $request->industry,
                        'website' => $request->website,
                    ]
                );

                // $staffName = $request->first_name . ' ' . $request->last_name;
                // try {
                //     Mail::to($request->email)->send(new StaffInviteMail($staffName, $request->email, $request->password));
                // } catch (\Exception $mailException) {
                //     Log::error('Failed to send staff invitation email: ' . $mailException->getMessage());
                // }
            }

            if ($client) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Client created successfully. Invitation email sent.',
                    'data' => [
                        'client' => $client,
                        'address' => $address,
                        'businessDetail' => $businessDetail,
                    ]
                ], 201);
            }

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client.',
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * API: Soft delete a staff member.
     */
    public function destroy($id)
    {
        $client = User::withTrashed()->find($id);

        if ($client->trashed()) {
            return ApiResponse::error('Client is already deleted');
        }

        DB::beginTransaction();
        try {
            $client->delete();
            DB::commit();

            return ApiResponse::success('client deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete client' . $e->getMessage(), 500);
        }
    }

    // Helper function for uploading profile image
    private function uploadProfileImage($file)
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/client'), $fileName);
        return 'uploads/client/' . $fileName;
    }
}
