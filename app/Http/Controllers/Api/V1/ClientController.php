<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClientStoreRequest;
use App\Http\Requests\Api\V1\ClientUpdateRequest;
use App\Http\Resources\ClientResource;
use App\Services\Clients\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService)
    {
    }

    public function index(Request $request)
    {
        $data = $this->clientService->listClients(
            $request->input('status'),
            $request->input('search')
        );

        return ApiResponse::success([
            'clients' => ClientResource::collection($data['clients']),
            'count' => $data['count'],
            'activeClientsCount' => $data['activeClientsCount'],
        ], 'Clients found');
    }

    public function show($client)
    {
        $client = $this->clientService->findClient($client, false, false);
        $client->load(['address', 'businessDetail', 'companies']);
        $client->setAttribute('tasks_count', $client->tasks()->count());
        $client->setAttribute('projects_count', $client->projects()->count());

        return ApiResponse::success(new ClientResource($client), 'Client found');
    }

    public function store(ClientStoreRequest $request)
    {
        $client = $this->clientService->createClient($this->payload($request->validated(), $request));
        $invite = $this->clientService->inviteClientIfRequested($client, $request->validated('password'), (bool) $request->validated('send_invite_mail'));

        return ApiResponse::success([
            'client' => new ClientResource($client->fresh(['address', 'businessDetail', 'companies'])),
            'mail_status' => $invite['mail_status'],
        ], 'Client created successfully.', 201);
    }

    public function update(ClientUpdateRequest $request, $client)
    {
        $client = $this->clientService->updateClient($this->clientService->findClient($client, false, false), $this->payload($request->validated(), $request));
        $invite = $this->clientService->inviteClientIfRequested($client, $request->validated('password') ?? '', (bool) $request->validated('send_invite_mail'));

        return ApiResponse::success([
            'client' => new ClientResource($client->fresh(['address', 'businessDetail', 'companies'])),
            'mail_status' => $invite['mail_status'],
        ], 'Client updated successfully.');
    }

    public function destroy($client)
    {
        $this->clientService->deleteClient($this->clientService->findClient($client, false, false));

        return ApiResponse::success(null, 'Client deleted successfully.');
    }

    private function payload(array $data, Request $request): array
    {
        $data['profile_image_file'] = $request->file('profile_image') ?: $request->file('profileImage');
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
