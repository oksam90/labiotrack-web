<?php

namespace App\Observers;

use App\Models\ActiviteLog;
use App\Models\Destruction;
use App\Services\CacheService;

class DestructionObserver
{
    public function created(Destruction $destruction): void
    {
        ActiviteLog::log($destruction, 'created', "Destruction — {$destruction->poids_reel_kg} kg", 'secondary');
        CacheService::invalidateForEtablissement($destruction->etablissement_id);
    }

    public function updated(Destruction $destruction): void
    {
        $changed = $destruction->getChanges();
        unset($changed['updated_at']);
        if (empty($changed)) return;

        ActiviteLog::log($destruction, 'updated', "Destruction mise à jour — {$destruction->poids_reel_kg} kg",
            'secondary', ['changed' => array_keys($changed)]);
        CacheService::invalidateForEtablissement($destruction->etablissement_id);
    }

    public function deleted(Destruction $destruction): void
    {
        ActiviteLog::log($destruction, 'deleted', 'Destruction supprimée', 'danger');
        CacheService::invalidateForEtablissement($destruction->etablissement_id);
    }
}
