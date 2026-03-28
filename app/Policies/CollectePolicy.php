<?php

namespace App\Policies;

use App\Models\Collecte;
use App\Models\User;

class CollectePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse','collecteur','prestataire']);
    }

    public function view(User $user, Collecte $collecte): bool
    {
        if ($user->isAdminOrSuper()) return true;

        // collecteur : voit toutes les collectes (inter-structures)
        if ($user->isCollecteur()) return true;

        // prestataire : voit toutes les collectes pour destruction
        if ($user->isPrestataire()) return true;

        // qhse : son établissement uniquement
        return $user->etablissement_id === $collecte->etablissement_id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse','collecteur']);
    }

    public function valider(User $user, Collecte $collecte): bool
    {
        if (! $this->view($user, $collecte)) return false;
        return in_array($user->role, ['superadmin','admin','qhse','collecteur']);
    }

    public function delete(User $user, Collecte $collecte): bool
    {
        if ($user->isAdminOrSuper()) return true;
        return $user->isQhse() && $user->etablissement_id === $collecte->etablissement_id;
    }
}
