<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index composite (etablissement_id, created_at DESC)
     * Optimise les requêtes : "activités récentes d'un établissement"
     *
     * Note : Laravel Blueprint ne supporte pas DESC sur un index composite,
     * on utilise donc un raw statement pour MySQL 8+.
     */
    public function up(): void
    {
        // 1. Créer les nouveaux index AVANT de supprimer l'ancien
        //    (MySQL a besoin d'un index sur etablissement_id pour la FK)
        DB::statement('
            CREATE INDEX activites_log_etab_created_desc
            ON activites_log (etablissement_id, created_at DESC)
        ');

        DB::statement('
            CREATE INDEX activites_log_etab_moment_desc
            ON activites_log (etablissement_id, moment DESC)
        ');

        // 2. Maintenant on peut supprimer l'ancien index (la FK utilise le nouveau)
        Schema::table('activites_log', function (Blueprint $table) {
            $table->dropIndex(['etablissement_id', 'moment']);
        });
    }

    public function down(): void
    {
        Schema::table('activites_log', function (Blueprint $table) {
            // Supprimer les index raw
            $table->dropIndex('activites_log_etab_created_desc');
            $table->dropIndex('activites_log_etab_moment_desc');

            // Restaurer l'ancien index simple
            $table->index(['etablissement_id', 'moment']);
        });
    }
};
