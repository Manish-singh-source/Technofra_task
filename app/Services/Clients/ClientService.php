<?php

namespace App\Services\Clients;

use App\Helpers\FileUpload;
use App\Imports\ClientsImport;
use App\Mail\ClientInviteMail;
use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

class ClientService
{
    public function listClients(?string $status = null, ?string $search = null, int $perPage = 10)
    {
        $statusFilter = $this->normalizeStatusFilter($status);

        $query = User::query()
            ->with(['businessDetail:id,user_id,company_name', 'companies:id,user_id,client_type,company_name,industry,website'])
            ->where('role', 'client')
            ->when($statusFilter !== null, function ($nested) use ($statusFilter) {
                $nested->where(function ($statusQuery) use ($statusFilter) {
                    $statusQuery->where('status', $statusFilter)
                        ->orWhere('status', $statusFilter === 'active' ? '1' : '0');
                });
            })
            ->when($search, function ($nested) use ($search) {
                $nested->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            });

        return [
            'clients' => $query->orderBy('first_name')->paginate($perPage),
            'count' => (clone $query)->count(),
            'activeClientsCount' => (clone $query)->where(function ($nested) {
                $nested->where('status', 'active')->orWhere('status', '1');
            })->count(),
        ];
    }

    public function findClient(int|string $clientId, bool $withTrashed = false, bool $withServices = false): User
    {
        $query = User::query()->with(['address', 'businessDetail', 'companies']);

        if ($withServices) {
            $query->with(['services.vendor']);
        }

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->where('role', 'client')->findOrFail($clientId);
    }

    public function createClient(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $client = User::create([
                'profile_image' => $this->resolveProfileImageForCreate($data),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => 'client',
                'status' => $this->normalizeStatus($data['status'] ?? null),
                'password' => Hash::make($data['password']),
            ]);

            $this->assignClientRole($client);
            $this->syncClientRelations($client, $data);

            return $client;
        });
    }

    public function updateClient(User $client, array $data): User
    {
        return DB::transaction(function () use ($client, $data) {
            $profileImagePath = $this->resolveProfileImageForUpdate($client, $data);

            $client->update([
                'profile_image' => $profileImagePath,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'status' => $this->normalizeStatus($data['status'] ?? null),
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $client->password,
            ]);

            $this->assignClientRole($client);
            $this->syncClientRelations($client, $data);

            return $client->fresh(['address', 'businessDetail', 'companies']);
        });
    }

    public function deleteClient(User $client): void
    {
        DB::transaction(function () use ($client) {
            $client->address()?->delete();
            $client->businessDetail()?->delete();
            $client->companies()?->delete();
            $client->delete();
        });
    }

    public function deleteSelected(Collection $ids): void
    {
        DB::transaction(function () use ($ids) {
            User::query()
                ->where('role', 'client')
                ->whereIn('id', $ids)
                ->get()
                ->each(function (User $client) {
                    $client->address()?->delete();
                    $client->businessDetail()?->delete();
                    $client->companies()?->delete();
                    $client->delete();
                });
        });
    }

    public function toggleStatus(User $client, mixed $status): string
    {
        $client->status = $this->normalizeStatus($status);
        $client->save();

        return $client->status;
    }

    public function importClients($file): array
    {
        $import = new ClientsImport();
        Excel::import($import, $file);

        return [
            'failures' => $import->failures(),
            'errors' => $import->errors(),
        ];
    }

    public function downloadTemplateFile()
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        foreach ($sampleData as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(chr(65 + $colIndex) . ($rowIndex + 2), $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'clients_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function inviteClientIfRequested(User $client, string $password, bool $sendInviteMail): array
    {
        if (! $sendInviteMail || ! $client->email) {
            return ['message' => null, 'mail_status' => 'not_requested'];
        }

        try {
            Mail::to($client->email)->send(new ClientInviteMail($client->name, $client->email, $password));

            return ['message' => 'Invitation email sent.', 'mail_status' => 'sent'];
        } catch (\Exception $exception) {
            Log::error('Failed to send client invitation email: ' . $exception->getMessage());

            return ['message' => 'Invitation email could not be sent.', 'mail_status' => 'failed'];
        }
    }

    public function clientStats(User $client): array
    {
        return [
            'tasks_count' => $client->tasks()->count(),
            'projects_count' => $client->projects()->count(),
        ];
    }

    private function syncClientRelations(User $client, array $data): void
    {
        $addressData = $this->normalizeAddressData($data);
        if ($this->hasAddressData($addressData)) {
            UserAddress::withTrashed()->updateOrCreate(
                ['user_id' => $client->id],
                array_merge(['deleted_at' => null], $addressData)
            );
        }

        $companies = $this->normalizeCompaniesData($data)
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

    private function normalizeAddressData(array $data): array
    {
        return [
            'address_line_1' => $this->firstFilled($data, ['address_line_1', 'address_line1']),
            'address_line_2' => $this->firstFilled($data, ['address_line_2', 'address_line2']),
            'city' => $this->firstFilled($data, ['city']),
            'state' => $this->firstFilled($data, ['state']),
            'country' => $this->firstFilled($data, ['country']),
            'pincode' => $this->firstFilled($data, ['pincode']),
        ];
    }

    private function hasAddressData(array $addressData): bool
    {
        return collect($addressData)->contains(fn ($value) => filled($value));
    }

    private function normalizeCompaniesData(array $data): Collection
    {
        $companies = collect($data['companies'] ?? []);

        if ($companies->isEmpty() && (
            filled($data['client_type'] ?? null)
            || filled($data['company_name'] ?? null)
            || filled($data['industry'] ?? null)
            || filled($data['website'] ?? null)
        )) {
            $companies = collect([[
                'client_type' => $data['client_type'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'industry' => $data['industry'] ?? null,
                'website' => $data['website'] ?? null,
            ]]);
        }

        return $companies->map(function ($company) {
            return [
                'client_type' => isset($company['client_type']) ? trim((string) $company['client_type']) : null,
                'company_name' => isset($company['company_name']) ? trim((string) $company['company_name']) : null,
                'industry' => isset($company['industry']) ? trim((string) $company['industry']) : null,
                'website' => isset($company['website']) ? trim((string) $company['website']) : null,
            ];
        });
    }

    private function firstFilled(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (filled($data[$key] ?? null)) {
                return trim((string) $data[$key]);
            }
        }

        return null;
    }

    private function resolveProfileImageForCreate(array $data): string
    {
        if (! empty($data['profile_image'])) {
            return $data['profile_image'];
        }

        if (! empty($data['profile_image_file']) && $data['profile_image_file'] instanceof UploadedFile) {
            return basename(FileUpload::uploadOrGenerateAvatar(
                $data['profile_image_file'],
                trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'uploads/clients/'
            ));
        }

        return basename(FileUpload::generateAvatar(
            trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'uploads/clients/'
        ));
    }

    private function resolveProfileImageForUpdate(User $client, array $data): string
    {
        $profileImagePath = $client->getRawOriginal('profile_image');
        $profileImage = $data['profile_image_file'] ?? null;

        if ($profileImage instanceof UploadedFile) {
            $profileImagePath = basename(FileUpload::updateFileUpload(
                $profileImage,
                $profileImagePath ? 'uploads/clients/' . $profileImagePath : '',
                'uploads/clients/'
            ));
        } elseif ((! $profileImagePath || $profileImagePath === 'default.png') && empty($data['profile_image'])) {
            $profileImagePath = basename(FileUpload::generateAvatar(
                trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'uploads/clients/',
                $profileImagePath ? 'uploads/clients/' . $profileImagePath : ''
            ));
        }

        return $profileImagePath;
    }

    private function assignClientRole(User $client): void
    {
        $role = Role::where('name', 'client')->first();

        if ($role && ! $client->hasRole($role->name)) {
            $client->assignRole($role);
        }
    }

    private function normalizeStatus(mixed $status): string
    {
        if (in_array($status, [1, '1', true, 'true', 'active'], true)) {
            return 'active';
        }

        return 'inactive';
    }

    private function normalizeStatusFilter(mixed $status): ?string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'inactive';
        }

        if (is_numeric($status)) {
            return (int) $status === 1 ? 'active' : ((int) $status === 0 ? 'inactive' : null);
        }

        if (! is_string($status)) {
            return null;
        }

        $normalized = strtolower(trim($status));

        return match ($normalized) {
            'active', '1', 'true' => 'active',
            'inactive', '0', 'false' => 'inactive',
            default => null,
        };
    }
}
