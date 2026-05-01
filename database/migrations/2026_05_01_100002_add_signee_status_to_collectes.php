<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Étend l'enum `statut` de la table `collectes` avec la valeur 'signee',
 * pour matérialiser le passage du workflow de signature électronique
 * (voir doc section 10.2).
 *
 * Statuts existants : planifie, en_cours, complete, annule
 * Statut ajouté    : signee  (entre en_cours et complete)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `collectes` MODIFY `statut` ENUM(
            'planifie','en_cours','signee','complete','annule'
        ) NOT NULL DEFAULT 'planifie'");
    }

    public function down(): void
    {
        // Rétablit l'enum d'origine. ATTENTION : les lignes au statut 'signee'
        // doivent être migrées vers 'complete' avant rollback.
        DB::statement("UPDATE `collectes` SET `statut` = 'complete' WHERE `statut` = 'signee'");
        DB::statement("ALTER TABLE `collectes` MODIFY `statut` ENUM(
            'planifie','en_cours','complete','annule'
        ) NOT NULL DEFAULT 'planifie'");
    }
};
