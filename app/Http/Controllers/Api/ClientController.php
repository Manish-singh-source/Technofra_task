<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\FileUpload;
use App\Http\Controllers\Controller;
use App\Mail\ClientInviteMail;
use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    //
    public function index(Request $request)
    {
        $clients = User::with([
            'businessDetail:id,user_id,company_name',
            'companies:id,user_id,client_type,company_name,industry,website',
        ])
            ->where('role', 'client')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($nested) use ($search) {
                    $nested->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->paginate(10);
        $clientsCount = $clients->total();
        $activeClientsCount = User::where('role', 'client')->where('status', 'active')->get();
        if (!$clients) {
            return ApiResponse::error('No client found');
        }
        return ApiResponse::success([
            'clients' => $clients,
            'count' => $clientsCount,
            'activeClientsCount' => $activeClientsCount,
        ], 'Clients found');
    }

    public function show($id)
    {
        $client = User::with('address', 'businessDetail', 'companies')->where('role', 'client')->findOrFail($id);
        if (!$client) {
            return ApiResponse::error('client not found');
        }

        $client->tasksCount = $client->tasks()->count();
        $client->projectsCount = $client->projects()->count();

        return ApiResponse::success($client, 'Client found');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|min:3|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|min:10|max:20',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'password' => 'required|string|min:8',
            'send_invite_mail' => 'nullable|boolean',

            'address_line_1' => 'nullable|string|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',

            'companies' => 'nullable|array',
            'companies.*.client_type' => 'nullable|in:Individual,Company,Organization',
            'companies.*.company_name' => 'nullable|string|max:255',
            'companies.*.industry' => 'nullable|string|max:255',
            'companies.*.website' => 'nullable|url|max:255',
            'client_type' => 'nullable|in:Individual,Company,Organization',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $profileImagePath = basename(FileUpload::uploadOrGenerateAvatar(
                $request->file('profile_image') ?: $request->file('profileImage'),
                trim($request->first_name . ' ' . $request->last_name),
                'uploads/clients/'
            ));

            $client = User::create([
                'profile_image' => $profileImagePath,
                'first_name' => trim((string) $request->first_name),
                'last_name' => $request->filled('last_name') ? trim((string) $request->last_name) : null,
                'email' => $request->filled('email') ? trim((string) $request->email) : null,
                'phone' => $request->filled('phone') ? trim((string) $request->phone) : null,
                'role' => 'client',
                'password' => Hash::make($request->password),
                'status' => $this->normalizeClientStatus($request->input('status')),
            ]);

            $this->assignClientRole($client);

            $address = null;
            if ($request->input('address_line_1')) {
                $address = UserAddress::withTrashed()->updateOrCreate(
                    ['user_id' => $client->id],
                    [
                        'deleted_at' => null,
                        'address_line_1' => $this->inputFirst($request, ['address_line_1', 'address_line1']),
                        'address_line_2' => $this->inputFirst($request, ['address_line_2', 'address_line2']),
                        'city' => $request->input('city'),
                        'state' => $request->input('state'),
                        'country' => $request->input('country'),
                        'pincode' => $request->input('pincode'),
                    ]
                );
            }

            $companies = $this->prepareCompaniesPayload($request);

            ClientBusinessDetail::where('user_id', $client->id)->delete();

            foreach ($companies as $company) {
                ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => $company['client_type'] ?: '',
                    'company_name' => $company['company_name'],
                    'industry' => $company['industry'],
                    'website' => $company['website'],
                ]);
            }

            if (empty($companies)) {
                ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => '',
                    'company_name' => null,
                    'industry' => null,
                    'website' => null,
                ]);
            }

            DB::commit();

            $message = 'Client created successfully.';
            $mailStatus = 'not_requested';

            if ($request->boolean('send_invite_mail') && $client->email) {
                try {
                    Mail::to($client->email)->send(new ClientInviteMail($client->name, $client->email, $request->password));
                    $message = 'Client created successfully. Invitation email sent.';
                    $mailStatus = 'sent';
                } catch (\Exception $mailException) {
                    Log::error('Failed to send client invitation email: ' . $mailException->getMessage());
                    $message = 'Client created successfully, but invitation email could not be sent.';
                    $mailStatus = 'failed';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'mail_status' => $mailStatus,
                'data' => [
                    'client' => $client->fresh(['address', 'businessDetail', 'companies']),
                    'address' => $address ?? null,
                    'companies' => ClientBusinessDetail::where('user_id', $client->id)->get(),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create client from API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create client: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function prepareCompaniesPayload(Request $request): array
    {
        $companies = collect($request->input('companies', []));

        if ($companies->isEmpty() && (
            $request->filled('client_type')
            || $request->filled('company_name')
            || $request->filled('industry')
            || $request->filled('website')
        )) {
            $companies = collect([[
                'client_type' => $request->input('client_type'),
                'company_name' => $request->input('company_name'),
                'industry' => $request->input('industry'),
                'website' => $request->input('website'),
            ]]);
        }

        return $companies
            ->map(fn($company) => [
                'client_type' => isset($company['client_type']) ? trim((string) $company['client_type']) : null,
                'company_name' => isset($company['company_name']) ? trim((string) $company['company_name']) : null,
                'industry' => isset($company['industry']) ? trim((string) $company['industry']) : null,
                'website' => isset($company['website']) ? trim((string) $company['website']) : null,
            ])
            ->filter(fn($company) => collect($company)->filter()->isNotEmpty())
            ->values()
            ->all();
    }

    private function assignClientRole(User $client): void
    {
        $role = Role::where('name', 'client')->first();
        if ($role && !$client->hasRole($role->name)) {
            $client->assignRole($role);
        }
    }

    private function normalizeClientStatus(mixed $status): string
    {
        if (in_array($status, [1, '1', true, 'true', 'active'], true)) {
            return 'active';
        }

        return 'inactive';
    }

    private function inputFirst(Request $request, array $keys): ?string
    {
        foreach ($keys as $key) {
            if ($request->filled($key)) {
                return trim((string) $request->input($key));
            }
        }

        return null;
    }

    public function update(Request $request, $id)
    {
        $client = User::query()
            ->where('role', 'client')
            ->find($id);

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|min:3|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($client->id)],
            'phone' => 'nullable|string|min:10|max:20',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'password' => 'nullable|string|min:8',
            'send_invite_mail' => 'nullable|boolean',

            'address_line_1' => 'nullable|string|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',

            'companies' => 'nullable|array',
            'companies.*.client_type' => 'nullable|in:Individual,Company,Organization',
            'companies.*.company_name' => 'nullable|string|max:255',
            'companies.*.industry' => 'nullable|string|max:255',
            'companies.*.website' => 'nullable|url|max:255',
            'client_type' => 'nullable|in:Individual,Company,Organization',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->boolean('send_invite_mail')) {
                return;
            }

            if (! $request->filled('email')) {
                $validator->errors()->add('email', 'Email is required to send the invitation.');
            }

            if (! $request->filled('password')) {
                $validator->errors()->add('password', 'Password is required to send the invitation.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $profileImagePath = $client->getRawOriginal('profile_image');
            $profileImage = $request->file('profile_image') ?: $request->file('profileImage');
            if ($profileImage) {
                $profileImagePath = basename(FileUpload::updateFileUpload(
                    $profileImage,
                    $profileImagePath ? 'uploads/clients/' . $profileImagePath : '',
                    'uploads/clients/'
                ));
            } elseif (! $profileImagePath || $profileImagePath === 'default.png') {
                $profileImagePath = basename(FileUpload::generateAvatar(
                    trim($request->first_name . ' ' . $request->last_name),
                    'uploads/clients/',
                    $profileImagePath ? 'uploads/clients/' . $profileImagePath : ''
                ));
            }

            $client->update([
                'profile_image' => $profileImagePath,
                'first_name' => trim((string) $request->first_name),
                'last_name' => $request->filled('last_name') ? trim((string) $request->last_name) : null,
                'email' => $request->filled('email') ? trim((string) $request->email) : null,
                'phone' => $request->filled('phone') ? trim((string) $request->phone) : null,
                'status' => $this->normalizeClientStatus($request->input('status')),
                'password' => $request->filled('password')
                    ? Hash::make($request->password)
                    : $client->password,
            ]);

            $this->assignClientRole($client);

            $address = UserAddress::withTrashed()->updateOrCreate(
                ['user_id' => $client->id],
                [
                    'deleted_at' => null,
                    'address_line_1' => $this->inputFirst($request, ['address_line_1', 'address_line1']),
                    'address_line_2' => $this->inputFirst($request, ['address_line_2', 'address_line2']),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'country' => $request->input('country'),
                    'pincode' => $request->input('pincode'),
                ]
            );

            $companies = $this->prepareCompaniesPayload($request);

            ClientBusinessDetail::where('user_id', $client->id)->delete();

            foreach ($companies as $company) {
                ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => $company['client_type'] ?: '',
                    'company_name' => $company['company_name'],
                    'industry' => $company['industry'],
                    'website' => $company['website'],
                ]);
            }

            if (empty($companies)) {
                ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => '',
                    'company_name' => null,
                    'industry' => null,
                    'website' => null,
                ]);
            }

            DB::commit();

            $message = 'Client updated successfully.';
            $mailStatus = 'not_requested';

            if ($request->boolean('send_invite_mail') && $client->email && $request->filled('password')) {
                try {
                    Mail::to($client->email)->send(new ClientInviteMail($client->name, $client->email, $request->password));
                    $message = 'Client updated successfully. Invitation email sent.';
                    $mailStatus = 'sent';
                } catch (\Exception $mailException) {
                    Log::error('Failed to send client invitation email: ' . $mailException->getMessage());
                    $message = 'Client updated successfully, but invitation email could not be sent.';
                    $mailStatus = 'failed';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'mail_status' => $mailStatus,
                'data' => [
                    'client' => $client->fresh(['address', 'businessDetail', 'companies']),
                    'address' => $address,
                    'companies' => ClientBusinessDetail::where('user_id', $client->id)->get(),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update client from API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update client: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Soft delete a client and its related client records.
     */
    public function destroy($id)
    {
        $client = User::withTrashed()
            ->where('role', 'client')
            ->find($id);

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found.',
            ], 404);
        }

        if ($client->trashed()) {
            return ApiResponse::error('Client is already deleted', null, 409);
        }

        DB::beginTransaction();
        try {
            $client->address()?->delete();
            $client->businessDetail()?->delete();
            $client->companies()?->delete();
            $client->delete();
            DB::commit();

            return ApiResponse::success(null, 'Client deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete client from API: ' . $e->getMessage());

            return ApiResponse::error('Failed to delete client: ' . $e->getMessage(), null, 500);
        }
    }
}
