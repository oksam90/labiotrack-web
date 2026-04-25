<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute `reseau_id` aux établissements.
 *
 * Permet l'application du ReseauScope Eloquent qui filtre automatiquement
 * les requêtes pour l'AdminRéseau.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->foreignId('reseau_id')
                ->nullable()
                ->after('id')
                ->constrained('reseaux')
                ->nullOnDelete();

            $table->index('reseau_id');
        });
    }

    public function down(): void
    {
        Schema::table('etablissements', function (Blueprint $table) {
            $table->dropForeign(['reseau_id']);
            $table->dropIndex(['reseau_id']);
            $table->dropColumn('reseau_id');
        });
    }
};
