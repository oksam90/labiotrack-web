<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Création de la table `reseaux`.
 *
 * Un Réseau est un regroupement logique d'établissements géré par un
 * AdminRéseau. Le SUPERADMIN voit tous les réseaux, l'AdminRéseau ne
 * voit/gère que le sien (filtré via ReseauScope sur reseau_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->string('slug', 150)->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_telephone', 30)->nullable();
            $table->boolean('actif')->default(true);
            $table->json('parametres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('reseaux'); }
};
