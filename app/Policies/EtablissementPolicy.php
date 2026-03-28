<?php

namespace App\Policies;

use App\Models\Etablissement;
use App\Models\User;

class EtablissementPolicy
{
    public function viewAny(User $user): bool  { return $user->isAdminOrSuper(); }
    public function view(User $user, Etablissement $e): bool { return $user->isAdminOrSuper(); }
    public function create(User $user): bool   { return $user->isSuperAdmin(); }

    public function update(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        // Admin local peut modifier uniquement son propre établissement
        return $user->isAdmin() && $user->etablissement_id === $e->id;
    }

    public function delete(User $user, Etablissement $e): bool
    {
        return $user->isSuperAdmin();
    }

    public function manageUsers(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->isAdmin() && $user->etablissement_id === $e->id;
    }

    public function manageServices(User $user, Etablissement $e): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $user->isAdminOrSuper() || ($user->isAdmin() && $user->etablissement_id === $e->id);
    }
}
