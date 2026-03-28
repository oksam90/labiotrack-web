<?php

namespace App\Observers;

use App\Models\ActiviteLog;
use App\Models\Collecte;
use App\Services\CacheService;

class CollecteObserver
{
    public function created(Collecte $collecte): void
    {
        ActiviteLog::log($collecte, 'created', "Collecte créée — statut: {$collecte->statut}", 'success');
        CacheService::invalidateForEtablissement($collecte->etablissement_id);
    }

    public function updated(Collecte $collecte): void
    {
        $changed = $collecte->getChanges();
        unset($changed['updated_at']);
        if (empty($changed)) return;

        $desc = isset($changed['statut'])
            ? "Collecte — statut: {$changed['statut']}"
            : 'Collecte mise à jour';

        ActiviteLog::log($collecte, 'updated', $desc, 'success', ['changed' => array_keys($changed)]);
        CacheService::invalidateForEtablissement($collecte->etablissement_id);
    }

    public function deleted(Collecte $collecte): void
    {
        ActiviteLog::log($collecte, 'deleted', 'Collecte supprimée', 'danger');
        CacheService::invalidateForEtablissement($collecte->etablissement_id);
    }
}
