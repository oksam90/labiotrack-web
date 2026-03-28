<?php

namespace App\Policies;

use App\Models\Destruction;
use App\Models\User;

class DestructionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse','prestataire']);
    }

    public function view(User $user, Destruction $destruction): bool
    {
        if ($user->isAdminOrSuper()) return true;
        if ($user->isPrestataire())  return true;

        // qhse : vérification via collecte.etablissement_id
        // On utilise collecte_id stocké sur destruction pour éviter N+1
        // Le filtrage TenantScope sur les collectes protège déjà la requête
        // On vérifie ici via l'id de l'établissement récupéré proprement
        if ($user->isQhse()) {
            // collecte est eager-loaded par le contrôleur, pas de N+1
            $etabId = $destruction->getAttribute('etablissement_id')   // champ virtuel jointure
                    ?? optional($destruction->collecte)->etablissement_id;
            return $user->etablissement_id === $etabId;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse','prestataire']);
    }

    public function delete(User $user, Destruction $destruction): bool
    {
        return $user->isAdminOrSuper();
    }
}
