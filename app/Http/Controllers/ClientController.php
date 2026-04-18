<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use App\Models\Client;
use App\Models\ClientBusinessDetail;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;


class ClientController extends Controller
{
    //
    public function index()
    {
        $clients = User::where('role', 'client')->get();
        return view('clients.index', compact('clients'));
    }


    public function create()
    {
        $roles = Role::get();
        return view('clients.create', compact('roles'));
    }


    /**
     * Store a newly created client.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profileImage' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|min:10',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'pincode' => 'nullable|string',
            'client_type' => 'nullable|string',
            'industry' => 'nullable|string',
            'website' => 'nullable|string',
            'role' => 'required|string',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $profileImagePath = null;
            if ($request->hasFile('profileImage')) {
                $profileImagePath = $this->uploadProfileImage($request->file('profileImage'));
            }

            DB::beginTransaction();

            $client = User::create([
                'profile_image' => $profileImagePath,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'client',
                'password' => $request->password,
            ]);

            if ($client) {
                $clientAddress = UserAddress::create([
                    'user_id' => $client->id,
                    'address_line_1' => $request->address_line1,
                    'address_line_2' => $request->address_line2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'country' => $request->country,
                    'pincode' => $request->pincode,
                ]);

                $clientBusinessDetails = ClientBusinessDetail::create([
                    'user_id' => $client->id,
                    'client_type' => $request->client_type,
                    'industry' => $request->industry,
                    'website' => $request->website
                ]);
            }

            DB::commit();


            return redirect()->route('client')->with('success', 'Client added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving client: ' . $e->getMessage());
            return back()->with('error', 'Failed to save client: ' . $e->getMessage())->withInput();
        }
    }


    public function view($id)
    {
        $client = User::with('services.vendor')->findOrFail($id); // Load client with services and vendor relationships
        return view('clients.view', compact('client'));
    }


    public function edit($id)
    {
        $client = User::findOrFail($id);
        $roles = Role::get();
        return view('clients.edit', compact('client', 'roles'));
    }


    public function updateclient(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cname' => 'required|min:3',
            'coname' => 'required|min:3',
            'email' => 'required|email|unique:clients,email,' . $id,
            'phone' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $client = Client::findOrFail($id);
        $client->cname = $request->cname;
        $client->coname = $request->coname;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->save();

        return redirect()->route('client')->with('success', 'Client updated successfully.');
    }


    public function deleteclient($id)
    {
        $client = client::findOrFail($id);
        $client->delete();

        return redirect()->route('client')->with('success', 'client deleted successfully.');
    }

    public function deleteSelected(Request $request)
    {
        $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
        Client::destroy($ids);
        return redirect()->back()->with('success', 'Selected clients deleted successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $clients = Client::findOrFail($request->id);
        $clients->status = $request->status;
        $clients->save();

        return response()->json(['success' => true]);
    }






    /**
     * Handle bulk upload of clients from Excel file
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
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
            return redirect()->route('client')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample Excel template for clients
     */
    public function downloadTemplate()
    {
        $headers = [
            'client_name',
            'company_name',
            'email',
            'phone',
            'address',
            'status'
        ];

        $sampleData = [
            [],

        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(chr(65 + $index) . '1', $header);
        }

        // Set sample data
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
}
