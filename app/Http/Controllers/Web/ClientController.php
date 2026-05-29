<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ClientBulkUploadRequest;
use App\Http\Requests\Web\ClientDeleteSelectedRequest;
use App\Http\Requests\Web\ClientStatusRequest;
use App\Http\Requests\Web\ClientStoreRequest;
use App\Http\Requests\Web\ClientUpdateRequest;
use App\Services\Clients\ClientService;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService)
    {
    }

    public function index()
    {
        $data = $this->clientService->listClients();

        return view('clients.index', $data);
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(ClientStoreRequest $request): RedirectResponse
    {
        $client = $this->clientService->createClient($this->payload($request->validated(), $request));
        $invite = $this->clientService->inviteClientIfRequested($client, $request->validated('password'), (bool) $request->validated('send_invite_mail'));

        $message = 'Client added successfully.';
        if ($invite['message'] === 'Invitation email sent.') {
            $message .= ' Invitation email sent.';
        } elseif ($invite['mail_status'] === 'failed') {
            return redirect()->route('client')->with('warning', 'Client added successfully, but invitation email could not be sent.');
        }

        return redirect()->route('client')->with('success', $message);
    }

    public function show($client)
    {
        $client = $this->clientService->findClient($client, true, true);

        return view('clients.view', compact('client'));
    }

    public function edit($client)
    {
        $client = $this->clientService->findClient($client, true, false);

        return view('clients.edit', compact('client'));
    }

    public function update(ClientUpdateRequest $request, $client): RedirectResponse
    {
        $client = $this->clientService->updateClient($this->clientService->findClient($client, true, false), $this->payload($request->validated(), $request));
        $invite = $this->clientService->inviteClientIfRequested($client, $request->validated('password') ?? '', (bool) $request->validated('send_invite_mail'));

        if ($invite['mail_status'] === 'failed') {
            return redirect()->route('client.view', $client->id)->with('warning', 'Client updated successfully, but invitation email could not be sent.');
        }

        return redirect()->route('client.view', $client->id)->with('success', 'Client updated successfully.' . ($invite['mail_status'] === 'sent' ? ' Invitation email sent.' : ''));
    }

    public function destroy($client): RedirectResponse
    {
        $this->clientService->deleteClient($this->clientService->findClient($client, false, false));

        return redirect()->route('client')->with('success', 'Client deleted successfully.');
    }

    public function deleteSelected(ClientDeleteSelectedRequest $request): RedirectResponse
    {
        $ids = collect(is_array($request->input('ids')) ? $request->input('ids') : explode(',', (string) $request->input('ids')))
            ->map(fn ($id) => (int) trim((string) $id))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('client')->with('error', 'No clients selected for deletion.');
        }

        $this->clientService->deleteSelected($ids);

        return redirect()->route('client')->with('success', 'Selected clients deleted successfully.');
    }

    public function toggleStatus(ClientStatusRequest $request, $client)
    {
        $status = $this->clientService->toggleStatus($this->clientService->findClient($client, false, false), $request->validated('status'));

        return response()->json(['success' => true, 'status' => $status]);
    }

    public function bulkUpload(ClientBulkUploadRequest $request): RedirectResponse
    {
        try {
            $result = $this->clientService->importClients($request->file('file'));

            if ($result['failures']->isNotEmpty() || $result['errors']->isNotEmpty()) {
                $errorMessages = [];

                foreach ($result['failures'] as $failure) {
                    $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
                }

                foreach ($result['errors'] as $error) {
                    $errorMessages[] = $error;
                }

                return redirect()->route('client')->with('error', 'Import completed with errors: ' . implode(' | ', $errorMessages));
            }

            return redirect()->route('client')->with('success', 'Clients imported successfully!');
        } catch (\Exception $exception) {
            return redirect()->route('client')->with('error', 'Import failed: ' . $exception->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return $this->clientService->downloadTemplateFile();
    }

    private function payload(array $data, $request): array
    {
        $data['profile_image_file'] = $request->file('profileImage');
        $data['profile_image'] = $data['profile_image'] ?? null;

        if (empty($data['companies']) && (
            $request->filled('client_type')
            || $request->filled('company_name')
            || $request->filled('industry')
            || $request->filled('website')
        )) {
            $data['companies'] = [[
                'client_type' => $request->input('client_type'),
                'company_name' => $request->input('company_name'),
                'industry' => $request->input('industry'),
                'website' => $request->input('website'),
            ]];
        }

        return $data;
    }
}
