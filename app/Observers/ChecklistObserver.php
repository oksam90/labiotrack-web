<?php

namespace App\Observers;

use App\Models\ActiviteLog;
use App\Models\Checklist;
use App\Services\CacheService;

class ChecklistObserver
{
    public function created(Checklist $checklist): void
    {
        $score  = $checklist->score_conformite;
        $niveau = $score < 60 ? 'danger' : ($score < 80 ? 'warning' : 'success');

        ActiviteLog::log($checklist, 'created', "Checklist — score: {$score}%", $niveau);
        CacheService::invalidateForEtablissement($checklist->etablissement_id);
    }

    public function updated(Checklist $checklist): void
    {
        $changed = $checklist->getChanges();
        unset($changed['updated_at']);
        if (empty($changed)) return;

        $score  = $checklist->score_conformite;
        $niveau = $score < 60 ? 'danger' : ($score < 80 ? 'warning' : 'success');

        ActiviteLog::log($checklist, 'updated', "Checklist mise à jour — score: {$score}%", $niveau,
            ['changed' => array_keys($changed)]);
        CacheService::invalidateForEtablissement($checklist->etablissement_id);
    }

    public function deleted(Checklist $checklist): void
    {
        ActiviteLog::log($checklist, 'deleted', 'Checklist supprimée', 'danger');
        CacheService::invalidateForEtablissement($checklist->etablissement_id);
    }
}
