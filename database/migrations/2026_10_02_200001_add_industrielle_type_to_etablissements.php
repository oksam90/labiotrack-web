<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute le type d'établissement « industrielle » (aligné sur
 * Etablissement::TYPES).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE etablissements MODIFY type
            ENUM('clinique','hopital','cabinet','laboratoire','industrielle') NOT NULL");
    }

    public function down(): void
    {
        // Les structures industrielles sont reclassées avant le retrait de la
        // valeur, sinon MySQL rejette l'ALTER en mode strict.
        DB::table('etablissements')->where('type', 'industrielle')->update(['type' => 'laboratoire']);

        DB::statement("ALTER TABLE etablissements MODIFY type
            ENUM('clinique','hopital','cabinet','laboratoire') NOT NULL");
    }
};
