<?php

namespace App\Policies;

use App\Models\Declaration;
use App\Models\User;

/**
 * DeclarationPolicy — matrice d'accès LaBioTrack.
 *
 * superadmin / collecteur / prestataire → toutes déclarations
 * admin_reseau / admin                  → déclarations de leur réseau
 * qhse                                  → déclarations de leur établissement
 * agent                                 → ses propres déclarations
 */
class DeclarationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tous authentifiés ; le scope filtre les résultats
    }

    public function view(User $user, Declaration $declaration): bool
    {
        if ($user->isGlobal()) return true;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($declaration->etablissement_id);
        }
        if ($user->isQhse()) {
            return $user->etablissement_id === $declaration->etablissement_id;
        }
        if ($user->isAgent()) {
            return $user->etablissement_id === $declaration->etablissement_id
                && $user->id === $declaration->user_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','admin_reseau','qhse','agent']);
    }

    public function update(User $user, Declaration $declaration): bool
    {
        if ($declaration->statut !== 'en_stock') return false;

        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : ne créent ni ne modifient de déclaration
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($declaration->etablissement_id);
        }
        if ($user->isQhse()) {
            return $user->etablissement_id === $declaration->etablissement_id;
        }
        if ($user->isAgent()) {
            return $user->etablissement_id === $declaration->etablissement_id
                && $user->id === $declaration->user_id;
        }
        return false;
    }

    public function delete(User $user, Declaration $declaration): bool
    {
        if ($declaration->statut !== 'en_stock') return false;

        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : ne suppriment pas de déclaration
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($declaration->etablissement_id);
        }
        if ($user->isQhse()) {
            return $user->etablissement_id === $declaration->etablissement_id;
        }
        return false;
    }
}
