<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('boites_fermees_75')->default(false);
            $table->boolean('sacs_correctement_etiquetes')->default(false);
            $table->boolean('local_ventile')->default(false);
            $table->boolean('epi_port')->default(false);
            $table->boolean('sacs_noirs_non_contamines')->default(false);
            $table->boolean('contenants_integres')->default(false);
            $table->decimal('score_conformite', 5, 2)->default(0);
            $table->text('observations')->nullable();
            $table->date('date_checklist');
            $table->timestamps();

            $table->index(['etablissement_id', 'date_checklist']);
        });
    }

    public function down(): void { Schema::dropIfExists('checklists'); }
};
