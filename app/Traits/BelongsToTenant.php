<?php

namespace App\Traits;

use App\Models\Etablissement;
use App\Scopes\TenantScope;

/**
 * BelongsToTenant — trait à ajouter sur chaque modèle "tenant-aware".
 *
 * Rôles GLOBAUX (etablissement_id = NULL) : superadmin, admin, collecteur, prestataire
 * → pas d'auto-fill, ils créent explicitement avec l'etablissement_id cible.
 *
 * Rôles LOCAUX : qhse, agent
 * → etablissement_id rempli automatiquement au CREATE depuis l'utilisateur.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (! $model->etablissement_id && auth()->check()) {
                $user = auth()->user();
                // Auto-fill UNIQUEMENT pour les rôles strictement locaux
                // (qhse, agent). Les admin/admin_reseau/superadmin et
                // collecteur/prestataire fournissent explicitement la cible.
                if (in_array($user->role, ['qhse', 'agent'], true)
                    && $user->etablissement_id) {
                    $model->etablissement_id = $user->etablissement_id;
                }
            }
        });
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function scopeForTenant($query, int $etablissementId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where($this->getTable() . '.etablissement_id', $etablissementId);
    }

    public function scopeAllTenants($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
