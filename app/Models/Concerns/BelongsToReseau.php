<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ReseauScope;

/**
 * Trait BelongsToReseau
 *
 * À ajouter sur tout modèle qui doit être filtré automatiquement
 * par le réseau de l'utilisateur connecté (via ReseauScope).
 *
 * Usage :
 *   class Declaration extends Model {
 *       use Concerns\BelongsToReseau;
 *   }
 *
 * Le scope s'applique uniquement aux requêtes Eloquent ; les
 * requêtes DB::table() doivent utiliser User::filtreEtab() pour
 * obtenir le même filtrage.
 */
trait BelongsToReseau
{
    public static function bootBelongsToReseau(): void
    {
        static::addGlobalScope(new ReseauScope);
    }
}
