<?php

namespace App\Observers;

use App\Models\ActiviteLog;
use App\Models\Alerte;
use App\Services\CacheService;

class AlerteObserver
{
    public function created(Alerte $alerte): void
    {
        ActiviteLog::log($alerte, 'created', $alerte->message, $alerte->niveau ?? 'info');
        CacheService::invalidateForEtablissement($alerte->etablissement_id);
    }

    public function updated(Alerte $alerte): void
    {
        $changed = $alerte->getChanges();
        unset($changed['updated_at']);
        if (empty($changed)) return;

        $desc = isset($changed['lu']) ? 'Alerte marquée comme lue' : 'Alerte mise à jour';

        ActiviteLog::log($alerte, 'updated', $desc, 'info', ['changed' => array_keys($changed)]);
        CacheService::invalidateForEtablissement($alerte->etablissement_id);
    }

    public function deleted(Alerte $alerte): void
    {
        ActiviteLog::log($alerte, 'deleted', 'Alerte supprimée', 'danger');
        CacheService::invalidateForEtablissement($alerte->etablissement_id);
    }
}
