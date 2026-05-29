<?php

namespace App\Actions\LeadManagement;

use App\DTOs\LeadManagement\StatusUpdateData;
use App\Models\Lead;
use App\Models\LeadConversion;
use App\Services\LeadManagement\LeadClientConversionService;
use App\Services\LeadManagement\LeadStatusService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateLeadStatusAction
{
    public function __construct(
        private readonly LeadStatusService $leadStatusService,
        private readonly LeadClientConversionService $leadClientConversionService
    ) {
    }

    /**
     * @return array{status:string,client_user_id:int|null}
     */
    public function handle(Model $sourceLead, Lead $pipelineLead, StatusUpdateData $payload): array
    {
        $normalizedStatus = $payload->status === 'won' ? 'converted' : $payload->status;
        $clientUserId = null;

        DB::transaction(function () use ($sourceLead, $pipelineLead, $payload, $normalizedStatus, &$clientUserId): void {
            $this->leadStatusService->applyStatusChange(
                $pipelineLead,
                $normalizedStatus,
                $payload->actorId,
                $payload->remarks,
                $payload->lostReason,
                $payload->wonValue
            );

            $sourceLead->status = $normalizedStatus;
            $sourceLead->save();

            if ($normalizedStatus === 'converted') {
                $clientUser = $this->leadClientConversionService->ensureClientFromLead($pipelineLead);
                $clientUserId = (int) $clientUser->id;

                LeadConversion::query()->updateOrCreate(
                    ['lead_id' => $pipelineLead->id],
                    [
                        'client_id' => $clientUser->id,
                        'converted_by' => $payload->actorId,
                        'conversion_value' => $payload->wonValue,
                        'converted_at' => now(),
                    ]
                );
            }
        });

        return [
            'status' => $normalizedStatus,
            'client_user_id' => $clientUserId,
        ];
    }
}

