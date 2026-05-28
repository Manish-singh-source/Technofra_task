<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class VendorsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Vendor([
            'name'      => $row['vendor_name'],
            'email'     => $row['email'],
            'phone'     => $row['phone'],
            'address'   => $row['address'] ?? null,
            'status'    => 1,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'vendor_name' => 'required|string|max:255|unique:vendors,name',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'nullable|numeric|digits:10',
            'address' => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'vendor_name.unique' => 'Vendor name is required.',
            'vendor_name.required' => 'Vendor name is already registered.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.min' => 'Phone number must be at least 10 characters.',
        ];
    }
}
