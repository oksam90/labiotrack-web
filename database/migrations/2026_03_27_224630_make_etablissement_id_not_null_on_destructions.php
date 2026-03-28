<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Rend etablissement_id NOT NULL après validation du backfill.
     * Vérifie d'abord qu'aucune ligne n'est encore à NULL.
     */
    public function up(): void
    {
        // Garde-fou : empêcher la migration si des NULL subsistent
        $nullCount = \Illuminate\Support\Facades\DB::table('destructions')
            ->whereNull('etablissement_id')
            ->count();

        if ($nullCount > 0) {
            throw new \RuntimeException(
                "[ABORT] {$nullCount} destruction(s) ont encore etablissement_id = NULL. "
                . "Exécutez le backfill avant de continuer."
            );
        }

        // 1. Supprimer l'ancienne FK (SET NULL incompatible avec NOT NULL)
        Schema::table('destructions', function (Blueprint $table) {
            $table->dropForeign(['etablissement_id']);
        });

        // 2. Passer la colonne en NOT NULL
        Schema::table('destructions', function (Blueprint $table) {
            $table->unsignedBigInteger('etablissement_id')->nullable(false)->change();
        });

        // 3. Recréer la FK avec CASCADE (cohérent avec NOT NULL)
        Schema::table('destructions', function (Blueprint $table) {
            $table->foreign('etablissement_id')
                  ->references('id')
                  ->on('etablissements')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Rollback : remettre nullable + FK SET NULL.
     */
    public function down(): void
    {
        Schema::table('destructions', function (Blueprint $table) {
            $table->dropForeign(['etablissement_id']);
        });

        Schema::table('destructions', function (Blueprint $table) {
            $table->unsignedBigInteger('etablissement_id')->nullable()->change();
        });

        Schema::table('destructions', function (Blueprint $table) {
            $table->foreign('etablissement_id')
                  ->references('id')
                  ->on('etablissements')
                  ->nullOnDelete();
        });
    }
};
