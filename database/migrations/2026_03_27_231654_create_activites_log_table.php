<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activites_log', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();          // declaration, collecte, checklist, alerte, destruction
            $table->string('action', 20)->default('created'); // created, updated, deleted
            $table->unsignedBigInteger('subject_id');      // ID de l'objet source
            $table->string('subject_type', 80);            // App\Models\Declaration, etc.
            $table->timestamp('moment')->index();
            $table->string('acteur')->default('Système');
            $table->string('etablissement')->nullable();
            $table->text('description');
            $table->string('niveau', 20)->default('info'); // info, success, warning, danger, secondary
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('etablissement_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();          // données supplémentaires (ancien/nouveau)
            $table->timestamps();

            // Index composite pour les requêtes fréquentes
            $table->index(['etablissement_id', 'moment']);
            $table->index(['user_id', 'moment']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activites_log');
    }
};
