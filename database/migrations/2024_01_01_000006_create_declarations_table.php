<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_contenant_id')->constrained('type_contenants')->cascadeOnDelete();
            $table->integer('nombre_contenants')->default(1);
            $table->decimal('poids_estime_kg', 8, 2)->nullable();
            $table->decimal('poids_reel_kg', 8, 2)->nullable();
            $table->enum('statut', ['en_stock','en_transport','detruit','annule'])->default('en_stock');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->string('qr_code')->nullable();
            $table->date('date_declaration');
            $table->time('heure_declaration');
            $table->timestamps();

            // Index tenant + date pour perf
            $table->index(['etablissement_id', 'date_declaration']);
            $table->index(['etablissement_id', 'statut']);
            $table->index(['service_id', 'date_declaration']);
        });
    }

    public function down(): void { Schema::dropIfExists('declarations'); }
};
