<?php

namespace App\Actions\Vendor;

use App\Imports\VendorsImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class BulkUploadVendorsAction
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function execute(UploadedFile $file): array
    {
        $import = new VendorsImport();
        Excel::import($import, $file);

        $failures = $import->failures();
        $errors = $import->errors();

        if ($failures->isNotEmpty() || $errors->isNotEmpty()) {
            $messages = [];
            foreach ($failures as $failure) {
                $messages[] = "Row {$failure->row()}: ".implode(', ', $failure->errors());
            }
            foreach ($errors as $error) {
                $messages[] = (string) $error;
            }

            return [
                'ok' => false,
                'message' => 'Import completed with errors: '.implode(' | ', $messages),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Vendors imported successfully!',
        ];
    }
}

