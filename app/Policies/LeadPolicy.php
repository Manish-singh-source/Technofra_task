<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasRole(['admin', 'super-admin', 'super_admin'])) {
            return true;
        }

        return (int) $lead->assigned_to === (int) $user->id;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }
}
