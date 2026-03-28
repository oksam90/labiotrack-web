<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;

class ChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        // prestataire retiré : il n'est pas responsable des checklists internes
        return in_array($user->role, ['superadmin','admin','qhse','agent']);
    }

    public function view(User $user, Checklist $checklist): bool
    {
        if ($user->isAdminOrSuper()) return true;
        return $user->etablissement_id === $checklist->etablissement_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse']);
    }

    public function update(User $user, Checklist $checklist): bool
    {
        if ($user->isAdminOrSuper()) return true;
        return $user->isQhse() && $user->etablissement_id === $checklist->etablissement_id;
    }

    public function delete(User $user, Checklist $checklist): bool
    {
        return $user->isAdminOrSuper();
    }
}
