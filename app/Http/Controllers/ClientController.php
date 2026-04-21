<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::query()
            ->with(['address', 'businessDetail'])
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

            return redirect()->route('client')->with('success', 'Client added successfully.');
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

            return redirect()->route('client.view', $client->id)->with('success', 'Client updated successfully.');
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
            'last_name' => 'required|string|min:3|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($clientId)],
            'phone' => 'required|string|min:10|max:20',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'client_type' => 'nullable|in:Individual,Company,Organization',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'status' => 'nullable|in:active,inactive',
            'password' => $passwordRule,
        ];
    }

    private function clientValidationAttributes(): array
    {
        return [
            'address_line1' => 'address line 1',
            'address_line2' => 'address line 2',
            'client_type' => 'client type',
        ];
    }

    private function prepareClientPayload(Request $request, ?User $client = null): array
    {
        $profileImagePath = $client?->profile_image;
        if ($request->hasFile('profileImage')) {
            $profileImagePath = $this->uploadProfileImage(
                $request->file('profileImage'),
                $client?->profile_image,
                $client?->id
            );
        } else {
            if (!isset($profileImagePath)) {
                $fileName = Str::uuid() . '.png';
                $path = public_path('uploads/clients/' . $fileName);

                $avatar = app('avatar');
                $avatar->create($request->first_name . ' ' . $request->last_name)->save($path);
                $profileImagePath = $fileName;
            }
        }

        return [
            'profile_image' => $profileImagePath,
            'first_name' => trim((string) $request->first_name),
            'last_name' => trim((string) $request->last_name),
            'email' => trim((string) $request->email),
            'phone' => trim((string) $request->phone),
            'address_line_1' => trim((string) $request->address_line1),
            'address_line_2' => $request->filled('address_line2') ? trim((string) $request->address_line2) : null,
            'city' => trim((string) $request->city),
            'state' => trim((string) $request->state),
            'country' => trim((string) $request->country),
            'pincode' => trim((string) $request->pincode),
            'client_type' => trim((string) $request->client_type),
            'company_name' => trim((string) $request->company_name),
            'industry' => $request->filled('industry') ? trim((string) $request->industry) : null,
            'website' => $request->filled('website') ? trim((string) $request->website) : null,
            'status' => $this->normalizeClientStatus($request->input('status')),
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

        ClientBusinessDetail::withTrashed()->updateOrCreate(
            ['user_id' => $client->id],
            [
                'deleted_at' => null,
                'client_type' => $payload['client_type'],
                'company_name' => $payload['company_name'],
                'industry' => $payload['industry'],
                'website' => $payload['website'],
            ]
        );
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
            ->with(['address', 'businessDetail']);

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

    private function normalizeClientStatus(mixed $status): string
    {
        if (in_array($status, [1, '1', true, 'true', 'active'], true)) {
            return 'active';
        }

        return 'inactive';
    }

    private function uploadProfileImage($image, ?string $oldImage = null, ?int $clientId = null): string
    {
        $extension = $image->getClientOriginalExtension();
        $imageName = $clientId
            ? time() . '_' . $clientId . '.' . $extension
            : time() . '.' . $extension;

        $image->move(public_path('uploads/clients'), $imageName);

        if ($oldImage) {
            $oldImagePath = public_path('uploads/clients/' . $oldImage);
            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }

        return $imageName;
    }
}
