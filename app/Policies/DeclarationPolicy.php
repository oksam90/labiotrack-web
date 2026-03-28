<?php

namespace App\Policies;

use App\Models\Declaration;
use App\Models\User;

/**
 * DeclarationPolicy
 *
 * Matrice d'accès :
 * ┌──────────────┬────────┬──────┬──────────┬────────┬───────────┬────────────┐
 * │ Action       │ super  │admin │  qhse    │ agent  │collecteur │prestataire │
 * ├──────────────┼────────┼──────┼──────────┼────────┼───────────┼────────────┤
 * │ viewAny      │   ✓    │  ✓   │    ✓     │   ✓    │     ✓     │     ✓      │
 * │ view         │   ✓    │  ✓   │    ✓     │ sienne │     ✓     │     ✓      │
 * │ create       │   ✓    │  ✓   │    ✓     │   ✓    │           │            │
 * │ update       │   ✓    │  ✓   │    ✓     │ sienne │           │            │
 * │ delete       │   ✓    │  ✓   │    ✓     │        │           │            │
 * └──────────────┴────────┴──────┴──────────┴────────┴───────────┴────────────┘
 */
class DeclarationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tous les rôles authentifiés peuvent lister
    }

    public function view(User $user, Declaration $declaration): bool
    {
        // Admin/superadmin/collecteur/prestataire → accès global
        if ($user->isGlobal()) return true;

        // qhse → son établissement uniquement
        if ($user->isQhse()) {
            return $user->etablissement_id === $declaration->etablissement_id;
        }

        // agent → uniquement ses propres déclarations dans son établissement
        if ($user->isAgent()) {
            return $user->etablissement_id === $declaration->etablissement_id
                && $user->id === $declaration->user_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['superadmin','admin','qhse','agent']);
    }

    public function update(User $user, Declaration $declaration): bool
    {
        if ($declaration->statut !== 'en_stock') return false;

        if ($user->isAdminOrSuper()) return true;

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

        if ($user->isAdminOrSuper()) return true;

        if ($user->isQhse()) {
            return $user->etablissement_id === $declaration->etablissement_id;
        }

        return false;
    }
}
