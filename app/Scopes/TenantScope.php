<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * TenantScope — applique automatiquement le filtre etablissement_id
 * sur toutes les queries des modèles multi-tenant.
 *
 * Utilisateurs GLOBAUX (sans filtre structure) :
 *   - superadmin, admin  → voient tout, administrent
 *   - collecteur         → intervient sur toutes les structures
 *   - prestataire        → traite les déchets de toutes les structures
 *
 * Utilisateurs LOCAUX (filtrés par leur structure) :
 *   - qhse, agent        → limités à leur établissement
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) return;

        $user = Auth::user();

        // Rôles globaux → pas de filtre établissement
        if ($user->isGlobal()) return;

        // Rôles locaux : filtrer par leur établissement
        if ($user->etablissement_id) {
            $builder->where($model->getTable() . '.etablissement_id', $user->etablissement_id);
        }
    }
}
