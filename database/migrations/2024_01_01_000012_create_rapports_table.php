<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mensuel','trimestriel','annuel','ad_hoc'])->default('mensuel');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->string('fichier_path')->nullable();
            $table->timestamp('genere_at')->useCurrent();
            $table->timestamps();

            $table->index(['etablissement_id', 'type']);
        });
    }

    public function down(): void { Schema::dropIfExists('rapports'); }
};
