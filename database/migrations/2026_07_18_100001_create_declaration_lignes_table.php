<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Passe la déclaration d'un modèle mono-ligne (1 service + 1 contenant) à un
 * modèle multi-lignes : chaque ligne = 1 service producteur + 1 type de
 * contenant + 1 nombre de contenants pleins, avec son poids estimé propre
 * (nombre × poids_moyen_kg). Le total (nombre / poids) reste dénormalisé sur
 * l'en-tête `declarations` pour ne pas casser les agrégats existants.
 *
 * Backfill : chaque déclaration existante devient une déclaration à 1 ligne.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declaration_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')->constrained('declarations')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('type_contenant_id')->constrained('type_contenants')->cascadeOnDelete();
            $table->integer('nombre_contenants')->default(1);
            $table->decimal('poids_estime_kg', 8, 2)->nullable();
            $table->timestamps();

            $table->index('declaration_id');
            $table->index('service_id');
            $table->index('type_contenant_id');
        });

        // Backfill : 1 ligne par déclaration existante (colonnes encore présentes).
        DB::statement("
            INSERT INTO declaration_lignes
                (declaration_id, service_id, type_contenant_id, nombre_contenants, poids_estime_kg, created_at, updated_at)
            SELECT id, service_id, type_contenant_id, nombre_contenants, poids_estime_kg, NOW(), NOW()
            FROM declarations
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('declaration_lignes');
    }
};
