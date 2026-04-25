<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Étend l'enum `users.role` pour inclure le nouveau rôle `admin_reseau`.
 *
 * Ajoute également la colonne `reseau_id` (FK vers reseaux) afin que
 * l'AdminRéseau soit rattaché directement à son réseau (utile lorsqu'il
 * n'a pas d'établissement fixe).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Étendre l'enum role
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM(
            'superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire'
        ) NOT NULL DEFAULT 'agent'");

        // 2. Ajouter reseau_id sur users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('reseau_id')
                ->nullable()
                ->after('etablissement_id')
                ->constrained('reseaux')
                ->nullOnDelete();

            $table->index(['reseau_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reseau_id']);
            $table->dropIndex(['reseau_id', 'role']);
            $table->dropColumn('reseau_id');
        });

        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM(
            'superadmin','admin','qhse','agent','collecteur','prestataire'
        ) NOT NULL DEFAULT 'agent'");
    }
};
