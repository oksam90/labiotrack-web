<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collecte_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prestataire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('poids_reel_kg', 8, 2);
            $table->enum('methode', ['incineration','autoclave','desinfection_chimique','autre'])->default('incineration');
            $table->string('site_traitement')->nullable();
            $table->string('certificat_numero', 100)->nullable();
            $table->string('certificat_path')->nullable();
            $table->datetime('date_reception')->nullable();
            $table->datetime('date_destruction');
            $table->boolean('conforme')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('destructions'); }
};
