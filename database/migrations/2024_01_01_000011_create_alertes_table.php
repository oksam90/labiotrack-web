<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['stockage_excessif','volume_anormal','absence_declaration','depassement_delai','mauvais_tri','autre']);
            $table->enum('niveau', ['info','warning','danger'])->default('warning');
            $table->text('message');
            $table->boolean('lu')->default(false);
            $table->foreignId('lu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('lu_at')->nullable();
            $table->timestamps();

            $table->index(['etablissement_id', 'lu']);
        });
    }

    public function down(): void { Schema::dropIfExists('alertes'); }
};
