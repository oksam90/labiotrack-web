<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * ReseauScope — global scope Eloquent qui filtre automatiquement
 * les requêtes selon le périmètre du réseau de l'utilisateur connecté.
 *
 * Comportement :
 *  - SUPERADMIN / collecteur / prestataire → aucun filtre (vue globale)
 *  - admin_reseau / admin → restreint aux établissements de leur reseau_id
 *  - qhse / agent → restreint à leur etablissement_id
 *
 * Le scope s'applique sur les modèles ayant soit :
 *  - une colonne `etablissement_id` (Declaration, Collecte, Transfert,
 *    Checklist, Alerte, Service…), pour lesquels on filtre via une
 *    sous-requête whereIn sur etablissements.reseau_id
 *  - une colonne `reseau_id` directe (Etablissement) — filtre direct
 */
class ReseauScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Pas d'utilisateur connecté → ne pas filtrer (CLI, jobs, login…)
        if (! Auth::check()) return;

        $user = Auth::user();

        // Vue globale → aucun filtre
        if ($user->isGlobal()) return;

        $table   = $model->getTable();
        $columns = $this->getModelColumns($model);

        // SECURITY (fail-closed) : un rôle réseau (admin_reseau, admin,
        // collecteur, prestataire) sans reseau_id ne voit RIEN plutôt que TOUT.
        if ($user->isReseauScoped() && ! $user->reseau_id) {
            $builder->whereRaw('1 = 0');
            return;
        }

        // Override par session : si l'utilisateur a "zoomé" sur un
        // établissement précis (admin_tenant_id), on ne filtre pas
        // davantage que la pile de middlewares ne le ferait déjà.
        // Le filtrage par établissement reste néanmoins appliqué.

        // 1. Modèle Etablissement → filtrer directement sur reseau_id
        if (in_array('reseau_id', $columns, true) && $table === 'etablissements') {
            if ($user->isReseauScoped() && $user->reseau_id) {
                $builder->where("$table.reseau_id", $user->reseau_id);
                return;
            }
        }

        // 2. Modèles ayant etablissement_id → filtrer via sous-requête
        if (in_array('etablissement_id', $columns, true)) {
            if ($user->isReseauScoped() && $user->reseau_id) {
                $builder->whereIn("$table.etablissement_id", function ($q) use ($user) {
                    $q->select('id')
                      ->from('etablissements')
                      ->where('reseau_id', $user->reseau_id);
                });
                return;
            }

            // qhse / agent → filtre par leur établissement
            if ($user->etablissement_id) {
                $builder->where("$table.etablissement_id", $user->etablissement_id);
            }
        }
    }

    /**
     * Cache les colonnes du modèle (mémoïsé statiquement par classe).
     */
    protected function getModelColumns(Model $model): array
    {
        static $cache = [];
        $key = get_class($model);
        if (! isset($cache[$key])) {
            $cache[$key] = $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($model->getTable());
        }
        return $cache[$key];
    }
}
