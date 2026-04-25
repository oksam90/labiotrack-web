<?php

namespace App\Policies;

use App\Models\Collecte;
use App\Models\User;

class CollectePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','collecteur','prestataire']);
    }

    public function view(User $user, Collecte $collecte): bool
    {
        if ($user->isGlobal()) return true; // superadmin/collecteur/prestataire
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($collecte->etablissement_id);
        }
        return $user->etablissement_id === $collecte->etablissement_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','collecteur']);
    }

    public function valider(User $user, Collecte $collecte): bool
    {
        if (! $this->view($user, $collecte)) return false;
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','collecteur']);
    }

    public function delete(User $user, Collecte $collecte): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($collecte->etablissement_id);
        }
        return $user->isQhse() && $user->etablissement_id === $collecte->etablissement_id;
    }
}
