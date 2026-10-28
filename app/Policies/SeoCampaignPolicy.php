<?php
namespace App\Policies;
use App\Models\SeoCampaign;
use App\Models\User;
class SeoCampaignPolicy
{
    public function view(User $user, SeoCampaign $campaign): bool { return $this->privileged($user) || (int)$campaign->client_id === (int)$user->id || $campaign->members()->where('user_id',$user->id)->whereNull('unassigned_at')->exists(); }
    public function manage(User $user, SeoCampaign $campaign): bool { return $this->privileged($user) || $campaign->members()->where('user_id',$user->id)->where('campaign_role','manager')->whereNull('unassigned_at')->exists(); }
    public function contribute(User $user, SeoCampaign $campaign): bool { return $this->manage($user,$campaign) || $campaign->members()->where('user_id',$user->id)->where('campaign_role','executive')->whereNull('unassigned_at')->exists(); }
    private function privileged(User $user): bool { return in_array(strtolower((string)($user->getRawOriginal('role') ?? '')), ['admin','super-admin','super_admin','super_admin2'], true); }
}
