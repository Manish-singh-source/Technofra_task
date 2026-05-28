<?php

namespace App\Http\Controllers\Web;

use App\DTOs\Vendor\VendorData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Vendor\BulkUploadVendorRequest;
use App\Http\Requests\Web\Vendor\StoreVendorRequest;
use App\Http\Requests\Web\Vendor\ToggleVendorStatusRequest;
use App\Http\Requests\Web\Vendor\UpdateVendorRequest;
use App\Services\Vendor\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VendorController extends Controller
{
    public function __construct(private VendorService $vendorService) {}

    public function index()
    {
        $vendors = $this->vendorService->listForWeb(auth()->user());

        return view('vendor1', compact('vendors'));
    }

    public function create()
    {
        return view('add-vendor');
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $this->vendorService->create(VendorData::fromArray($request->validated()));

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully!');
    }

    public function show(int $id)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);
        $vendor->load('services.client');

        return view('vendor-details', compact('vendor'));
    }

    public function edit(int $id)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);

        return view('add-vendor', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, int $id): RedirectResponse
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);
        $this->vendorService->update($vendor, VendorData::fromArray($request->validated()));

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), $id);
        $this->vendorService->delete($vendor);

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully!');
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        $ids = is_array($request->ids) ? $request->ids : explode(',', (string) $request->ids);
        $this->vendorService->deleteSelected(auth()->user(), $ids);

        return redirect()->back()->with('success', 'Selected Vendors deleted successfully.');
    }

    public function toggleStatus(ToggleVendorStatusRequest $request)
    {
        $vendor = $this->vendorService->findOrFail(auth()->user(), (int) $request->input('id'));
        $this->vendorService->toggleStatus($vendor, (string) $request->input('status'));

        return response()->json(['success' => true]);
    }

    public function bulkUpload(BulkUploadVendorRequest $request): RedirectResponse
    {
        try {
            $result = $this->vendorService->bulkUpload($request->file('file'));
            $flash = $result['ok'] ? 'success' : 'error';

            return redirect()->route('vendors.index')->with($flash, $result['message']);
        } catch (\Throwable $e) {
            return redirect()->route('vendors.index')->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = ['vendor_name', 'email', 'phone', 'address'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index).'1', $header);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'vendors_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}

