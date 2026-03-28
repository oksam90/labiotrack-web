<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('responsable_stockage_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('nombre_contenants')->default(1);
            $table->decimal('poids_total_kg', 8, 2)->nullable();
            $table->string('zone_stockage', 100)->nullable();
            $table->date('date_limite_collecte')->nullable();
            $table->text('signature_agent')->nullable();
            $table->text('signature_responsable')->nullable();
            $table->enum('statut', ['en_attente','valide','collecte'])->default('en_attente');
            $table->datetime('date_transfert');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['etablissement_id', 'statut']);
        });

        Schema::create('transfert_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('declaration_id')->constrained()->cascadeOnDelete();
            $table->unique(['transfert_id', 'declaration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfert_declarations');
        Schema::dropIfExists('transferts');
    }
};
