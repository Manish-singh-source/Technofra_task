<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if ($this->isPrivileged($user)) {
            return true;
        }

        if ((string) $user->role === 'client') {
            return (int) $project->customer_id === (int) $user->id;
        }

        $members = collect($project->members ?? [])->map(fn ($id) => (int) $id);

        return $members->contains((int) $user->id);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function comment(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    private function isPrivileged(User $user): bool
    {
        $rawRole = strtolower((string) ($user->getRawOriginal('role') ?? $user->role ?? ''));

        return method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['super-admin', 'super_admin', 'admin', 'super_admin2'])
            : in_array($rawRole, ['super-admin', 'super_admin', 'admin', 'super_admin2'], true);
    }
}

