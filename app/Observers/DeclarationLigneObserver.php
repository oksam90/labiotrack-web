<?php

namespace App\Observers;

use App\Models\Declaration;
use App\Models\DeclarationLigne;
use App\Services\CacheService;

/**
 * Invalide les caches dashboard / financier quand le détail d'une déclaration
 * change. Nécessaire car les ventilations « coût par contenant » et « coût par
 * service » sont désormais calculées à partir de declaration_lignes : une
 * modification de ligne qui ne change PAS les totaux d'en-tête (ex. changement
 * de service à quantité égale) ne déclencherait sinon aucune invalidation.
 */
class DeclarationLigneObserver
{
    public function created(DeclarationLigne $ligne): void
    {
        $this->flush($ligne);
    }

    public function updated(DeclarationLigne $ligne): void
    {
        $this->flush($ligne);
    }

    public function deleted(DeclarationLigne $ligne): void
    {
        $this->flush($ligne);
    }

    private function flush(DeclarationLigne $ligne): void
    {
        $etabId = Declaration::withoutGlobalScopes()
            ->whereKey($ligne->declaration_id)
            ->value('etablissement_id');

        CacheService::invalidateForEtablissement($etabId ? (int) $etabId : null);
    }
}
