<?php

namespace App\Policies;

use App\Models\Etablissement;
use App\Models\User;

/**
 * EtablissementPolicy — matrice d'accès LaBioTrack.
 *
 * superadmin    → CRUD complet sur tous les établissements / réseaux
 * admin_reseau  → CRUD sur les établissements de SON réseau (sauf delete)
 * admin         → modifie son propre établissement
 */
class EtablissementPolicy
{
    public function viewAny(User $user): bool { return $user->isAdminOrSuper(); }

    public function view(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->canAccessTenant($e->id);
    }

    public function create(User $user): bool
    {
        // Superadmin partout, admin_reseau dans son réseau (validé en form)
        return $user->isSuperAdmin() || $user->isAdminReseau();
    }

    public function update(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isAdminReseau()) {
            return $user->reseau_id === $e->reseau_id;
        }
        return $user->isAdmin() && $user->etablissement_id === $e->id;
    }

    /**
     * Suppression réservée au superadmin (action irréversible).
     */
    public function delete(User $user, Etablissement $e): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageUsers(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isAdminReseau()) {
            return $user->reseau_id === $e->reseau_id;
        }
        return $user->isAdmin() && $user->etablissement_id === $e->id;
    }

    public function manageServices(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($user->isAdminReseau()) {
            return $user->reseau_id === $e->reseau_id;
        }
        return $user->isAdmin() && $user->etablissement_id === $e->id;
    }
}
