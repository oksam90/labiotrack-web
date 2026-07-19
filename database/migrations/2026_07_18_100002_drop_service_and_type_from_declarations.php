<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Retire `service_id` et `type_contenant_id` de l'en-tête `declarations` :
 * le détail (service × contenant) vit désormais dans `declaration_lignes`
 * (cf. migration 2026_07_18_100001). Les totaux `nombre_contenants` et
 * `poids_estime_kg` restent sur l'en-tête (dénormalisation volontaire).
 *
 * À exécuter APRÈS le backfill de declaration_lignes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            // Les FK doivent tomber AVANT l'index composite qui les supporte
            // (MySQL error 1553 sinon).
            $table->dropForeign(['service_id']);
            $table->dropForeign(['type_contenant_id']);

            // Index composite (service_id, date_declaration) créé à l'origine
            $table->dropIndex('declarations_service_id_date_declaration_index');

            $table->dropColumn(['service_id', 'type_contenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('etablissement_id')
                ->constrained('services')->cascadeOnDelete();
            $table->foreignId('type_contenant_id')->nullable()->after('user_id')
                ->constrained('type_contenants')->cascadeOnDelete();
            $table->index(['service_id', 'date_declaration']);
        });

        // Restaure les valeurs depuis la 1re ligne de chaque déclaration.
        DB::statement("
            UPDATE declarations d
            JOIN (
                SELECT declaration_id, MIN(id) AS min_id
                FROM declaration_lignes
                GROUP BY declaration_id
            ) f ON f.declaration_id = d.id
            JOIN declaration_lignes dl ON dl.id = f.min_id
            SET d.service_id = dl.service_id,
                d.type_contenant_id = dl.type_contenant_id
        ");
    }
};
