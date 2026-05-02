<?php

namespace App\Policies;

use App\Models\Destruction;
use App\Models\User;

class DestructionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','prestataire']);
    }

    public function view(User $user, Destruction $destruction): bool
    {
        if ($user->isSuperAdmin())   return true;
        if ($user->isPrestataire())  return true;

        $etabId = $destruction->getAttribute('etablissement_id')
                ?? optional($destruction->collecte)->etablissement_id;

        if ($user->isReseauScoped()) {
            return $user->canAccessTenant((int) $etabId);
        }

        if ($user->isQhse()) {
            return $user->etablissement_id === $etabId;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // QHSE retiré : la confirmation de destruction est l'acte du
        // prestataire / des admins. Le QHSE garde la consultation (view).
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','prestataire']);
    }

    public function delete(User $user, Destruction $destruction): bool
    {
        return $user->isSuperAdmin();
    }
}
