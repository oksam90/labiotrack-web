<?php

namespace App\Observers;

use App\Models\ActiviteLog;
use App\Models\Declaration;
use App\Services\CacheService;

class DeclarationObserver
{
    public function created(Declaration $declaration): void
    {
        ActiviteLog::log($declaration, 'created', 'Déclaration de déchets créée', 'info');
        CacheService::invalidateForEtablissement($declaration->etablissement_id);
    }

    public function updated(Declaration $declaration): void
    {
        $changed = $declaration->getChanges();
        unset($changed['updated_at']);
        if (empty($changed)) return;

        $desc = isset($changed['statut'])
            ? "Déclaration passée au statut : {$changed['statut']}"
            : 'Déclaration mise à jour';

        ActiviteLog::log($declaration, 'updated', $desc, 'info',
            ['changed' => array_keys($changed), 'old' => $declaration->getOriginal()]);
        CacheService::invalidateForEtablissement($declaration->etablissement_id);
    }

    public function deleted(Declaration $declaration): void
    {
        ActiviteLog::log($declaration, 'deleted', 'Déclaration supprimée', 'danger');
        CacheService::invalidateForEtablissement($declaration->etablissement_id);
    }
}
