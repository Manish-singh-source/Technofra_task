<?php

namespace App\Http\Controllers;

use App\Helpers\FileUpload;
use App\Imports\ClientsImport;
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
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::query()
            ->with(['address', 'businessDetail', 'companies'])
            ->where('role', 'client')
            ->orderBy('first_name')
            ->get();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            $this->clientValidationRules(requirePassword: true),
            [],
            $this->clientValidationAttributes()
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $payload = $this->prepareClientPayload($request);

            $client = User::create([
                'profile_image' => $payload['profile_image'],
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'role' => 'client',
                'status' => $payload['status'],
                'password' => Hash::make($payload['password']),
            ]);

            $this->assignClientRole($client);
            $this->syncClientRelations($client, $payload);

            DB::commit();

            $message = 'Client added successfully.';

            if ($payload['send_invite_mail'] && $client->email) {
                try {
                    Mail::to($client->email)->send(new ClientInviteMail($client->name, $client->email, $payload['password']));
                    $message = 'Client added successfully. Invitation email sent.';
                } catch (\Exception $mailException) {
                    Log::error('Failed to send client invitation email: ' . $mailException->getMessage());
                    return redirect()->route('client')->with('warning', 'Client added successfully, but invitation email could not be sent.');
                }
            }

            return redirect()->route('client')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving client: ' . $e->getMessage());

            return back()->with('error', 'Failed to save client: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $client = $this->findClient($id, withTrashed: true, withServices: true);

        return view('clients.view', compact('client'));
    }

    public function edit($id)
    {
        $client = $this->findClient($id, withTrashed: true, withServices: false);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = $this->findClient($id, withTrashed: true, withServices: false);

        $validator = Validator::make(
            $request->all(),
            $this->clientValidationRules($client->id, false),
            [],
            $this->clientValidationAttributes()
        );

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
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $payload = $this->prepareClientPayload($request, $client);

            $client->update([
                'profile_image' => $payload['profile_image'],
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'],
                'status' => $payload['status'],
                'password' => $payload['password']
                    ? Hash::make($payload['password'])
                    : $client->password,
            ]);

            $this->assignClientRole($client);
            $this->syncClientRelations($client, $payload);

            DB::commit();

            $message = 'Client updated successfully.';

            if ($payload['send_invite_mail'] && $client->email && $payload['password']) {
                try {
                    Mail::to($client->email)->send(new ClientInviteMail($client->name, $client->email, $payload['password']));
                    $message = 'Client updated successfully. Invitation email sent.';
                } catch (\Exception $mailException) {
                    Log::error('Failed to send client invitation email: ' . $mailException->getMessage());
                    return redirect()->route('client.view', $client->id)->with('warning', 'Client updated successfully, but invitation email could not be sent.');
                }
            }

            return redirect()->route('client.view', $client->id)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating client: ' . $e->getMessage());

            return back()->with('error', 'Failed to update client: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $client = $this->findClient($id, withTrashed: false, withServices: false);

        DB::beginTransaction();
        try {
            $client->address()?->delete();
            $client->businessDetail()?->delete();
            $client->companies()?->delete();
            $client->delete();

            DB::commit();

            return redirect()->route('client')->with('success', 'Client deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting client: ' . $e->getMessage());

            return redirect()->route('client')->with('error', 'Failed to delete client: ' . $e->getMessage());
        }
    }

    public function deleteSelected(Request $request)
    {
        $ids = collect(is_array($request->ids) ? $request->ids : explode(',', (string) $request->ids))
            ->map(fn($id) => (int) trim((string) $id))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('client')->with('error', 'No clients selected for deletion.');
        }

        DB::beginTransaction();
        try {
            $clients = User::query()
                ->where('role', 'client')
                ->whereIn('id', $ids)
                ->get();

            foreach ($clients as $client) {
                $client->address()?->delete();
                $client->businessDetail()?->delete();
                $client->companies()?->delete();
                $client->delete();
            }

            DB::commit();

            return redirect()->route('client')->with('success', 'Selected clients deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting selected clients: ' . $e->getMessage());

            return redirect()->route('client')->with('error', 'Failed to delete selected clients: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $client = $this->findClient($id, withTrashed: false, withServices: false);
        $client->status = $this->normalizeClientStatus($request->input('status'));
        $client->save();

        return response()->json(['success' => true, 'status' => $client->status]);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new ClientsImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors();

            if ($failures->isNotEmpty() || $errors->isNotEmpty()) {
                $errorMessages = [];

                foreach ($failures as $failure) {
                    $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }

                foreach ($errors as $error) {
                    $errorMessages[] = $error;
                }

                return redirect()->route('client')->with('error', 'Import completed with errors: ' . implode(' | ', $errorMessages));
            }

            return redirect()->route('client')->with('success', 'Clients imported successfully!');
        } catch (\Exception $e) {
            Log::error('Error importing clients: ' . $e->getMessage());

            return redirect()->route('client')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'country',
            'pincode',
            'client_type',
            'industry',
            'website',
            'status',
        ];

        $sampleData = [[
            'John',
            'Doe',
            'john@example.com',
            '9876543210',
            '123 Main Street',
            'Suite 100',
            'Ahmedabad',
            'Gujarat',
            'India',
            '380001',
            'Company',
            'Technology',
            'https://example.com',
            'active',
        ]];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        foreach ($sampleData as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . ($rowIndex + 2), $value);
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'clients_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function clientValidationRules(?int $clientId = null, bool $requirePassword = true): array
    {
        $passwordRule = $requirePassword ? 'required|string|min:8' : 'nullable|string|min:8';

        return [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|min:3|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($clientId)],
            'phone' => 'nullable|string|min:10|max:20',
            'address_line1' => 'nullable|string|max:255',
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
            'status' => 'nullable|in:active,inactive',
            'send_invite_mail' => 'nullable|boolean',
            'password' => $passwordRule,
        ];
    }

    private function clientValidationAttributes(): array
    {
        return [
            'address_line1' => 'address line 1',
            'address_line2' => 'address line 2',
            'companies.*.client_type' => 'client type',
            'companies.*.company_name' => 'company name',
            'companies.*.industry' => 'industry',
            'companies.*.website' => 'website',
        ];
    }

    private function prepareClientPayload(Request $request, ?User $client = null): array
    {
        $profileImagePath = $client?->profile_image;
        if ($request->hasFile('profileImage')) {
            $profileImagePath = basename(FileUpload::updateFileUpload(
                $request->file('profileImage'),
                $client?->profile_image ? 'uploads/clients/'.$client->profile_image : '',
                'uploads/clients/'
            ));
        } elseif (! isset($profileImagePath)) {
            $profileImagePath = basename(FileUpload::generateAvatar(
                trim($request->first_name . ' ' . $request->last_name),
                'uploads/clients/'
            ));
        }

        return [
            'profile_image' => $profileImagePath,
            'first_name' => trim((string) $request->first_name),
            'last_name' => $request->filled('last_name') ? trim((string) $request->last_name) : null,
            'email' => $request->filled('email') ? trim((string) $request->email) : null,
            'phone' => $request->filled('phone') ? trim((string) $request->phone) : null,
            'address_line_1' => trim((string) $request->address_line1),
            'address_line_2' => $request->filled('address_line2') ? trim((string) $request->address_line2) : null,
            'city' => trim((string) $request->city),
            'state' => trim((string) $request->state),
            'country' => trim((string) $request->country),
            'pincode' => trim((string) $request->pincode),
            'companies' => $this->prepareCompaniesPayload($request),
            'status' => $this->normalizeClientStatus($request->input('status')),
            'send_invite_mail' => $request->boolean('send_invite_mail'),
            'password' => $request->input('password'),
        ];
    }

    private function syncClientRelations(User $client, array $payload): void
    {
        UserAddress::withTrashed()->updateOrCreate(
            ['user_id' => $client->id],
            [
                'deleted_at' => null,
                'address_line_1' => $payload['address_line_1'],
                'address_line_2' => $payload['address_line_2'],
                'city' => $payload['city'],
                'state' => $payload['state'],
                'country' => $payload['country'],
                'pincode' => $payload['pincode'],
            ]
        );

        $companies = collect($payload['companies'])
            ->filter(fn($company) => collect($company)->filter()->isNotEmpty())
            ->values();

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

        if ($companies->isEmpty()) {
            ClientBusinessDetail::create([
                'user_id' => $client->id,
                'client_type' => '',
                'company_name' => null,
                'industry' => null,
                'website' => null,
            ]);
        }
    }

    private function assignClientRole(User $client): void
    {
        $role = Role::where('name', 'client')->first();
        if ($role && !$client->hasRole($role->name)) {
            $client->assignRole($role);
        }
    }

    private function findClient(int|string $id, bool $withTrashed = false, bool $withServices = false): User
    {
        $query = User::query()
            ->with(['address', 'businessDetail', 'companies']);

        if ($withServices) {
            $query->with(['services.vendor']);
        }

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query
            ->where('role', 'client')
            ->findOrFail($id);
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
            ->all();
    }

    private function normalizeClientStatus(mixed $status): string
    {
        if (in_array($status, [1, '1', true, 'true', 'active'], true)) {
            return 'active';
        }

        return 'inactive';
    }

}
