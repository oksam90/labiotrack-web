<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collectes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collecteur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('numero_bordereau', 50)->unique()->nullable();
            $table->integer('nombre_contenants')->default(1);
            $table->decimal('poids_declare_kg', 8, 2)->nullable();
            $table->string('qr_code_scan')->nullable();
            $table->text('signature_etablissement')->nullable();
            $table->text('signature_collecteur')->nullable();
            $table->string('photo')->nullable();
            $table->enum('statut', ['planifie','en_cours','complete','annule'])->default('planifie');
            $table->datetime('date_collecte');
            $table->string('vehicule', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['etablissement_id', 'statut']);
        });

        Schema::create('collecte_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collecte_id')->constrained()->cascadeOnDelete();
            $table->foreignId('declaration_id')->constrained()->cascadeOnDelete();
            $table->unique(['collecte_id', 'declaration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collecte_declarations');
        Schema::dropIfExists('collectes');
    }
};
