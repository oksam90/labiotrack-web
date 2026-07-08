<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * TenantScope — applique automatiquement le filtre etablissement_id /
 * reseau_id sur toutes les queries des modèles tenant-aware.
 *
 * Vue GLOBALE (aucun filtre) :
 *   - superadmin     → voit tous les réseaux
 *   - collecteur     → intervient sur toutes les structures
 *   - prestataire    → traite les déchets de toutes les structures
 *
 * Vue RÉSEAU (filtré par reseau_id) :
 *   - admin_reseau   → son réseau uniquement
 *   - admin          → son réseau uniquement (admin local promu à la maille réseau)
 *
 * Vue ÉTABLISSEMENT :
 *   - qhse, agent, client_signataire → limités à leur établissement
 *
 * Override : si `admin_tenant_id` est en session ET autorisé,
 * filtre sur cet établissement précis (zoom).
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) return;

        $user  = Auth::user();
        $table = $model->getTable();

        // Override par session "zoom" sur un établissement précis
        $sessionTenant = session('admin_tenant_id');
        if ($sessionTenant && $user->canAccessTenant((int) $sessionTenant)) {
            if ($table === 'etablissements') {
                $builder->where("$table.id", $sessionTenant);
            } else {
                $builder->where("$table.etablissement_id", $sessionTenant);
            }
            return;
        }

        // Vue globale → pas de filtre
        if ($user->isGlobal()) return;

        // Vue réseau (admin, admin_reseau, collecteur, prestataire)
        if ($user->isReseauScoped()) {
            // SECURITY (fail-closed) : un rôle réseau sans reseau_id ne
            // voit RIEN plutôt que TOUT.
            if (! $user->reseau_id) {
                $builder->whereRaw('1 = 0');
                return;
            }
            if ($table === 'etablissements') {
                $builder->where("$table.reseau_id", $user->reseau_id);
            } else {
                $builder->whereIn("$table.etablissement_id", function ($q) use ($user) {
                    $q->select('id')->from('etablissements')
                      ->where('reseau_id', $user->reseau_id);
                });
            }
            return;
        }

        // Vue locale (qhse, agent, client_signataire) → leur établissement
        if ($user->etablissement_id) {
            if ($table === 'etablissements') {
                $builder->where("$table.id", $user->etablissement_id);
            } else {
                $builder->where("$table.etablissement_id", $user->etablissement_id);
            }
        }
    }
}
