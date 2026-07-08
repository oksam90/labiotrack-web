<?php

namespace App\Policies;

use App\Models\Checklist;
use App\Models\User;

class ChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        // prestataire exclu : il n'est pas responsable des checklists internes
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','agent']);
    }

    public function view(User $user, Checklist $checklist): bool
    {
        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : exclus des checklists internes
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($checklist->etablissement_id);
        }
        return $user->etablissement_id === $checklist->etablissement_id;
    }

    public function create(User $user): bool
    {
        // Prestataire exclu — admin_reseau / admin / QHSE uniquement
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse']);
    }

    public function update(User $user, Checklist $checklist): bool
    {
        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : exclus des checklists internes
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($checklist->etablissement_id);
        }
        return $user->isQhse() && $user->etablissement_id === $checklist->etablissement_id;
    }

    public function delete(User $user, Checklist $checklist): bool
    {
        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : exclus des checklists internes
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($checklist->etablissement_id);
        }
        return false;
    }
}
