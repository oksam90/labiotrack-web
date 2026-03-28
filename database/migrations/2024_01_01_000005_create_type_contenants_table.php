<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_contenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code', 50)->unique();
            $table->foreignId('type_dechet_id')->nullable()->constrained('type_dechets')->nullOnDelete();
            $table->decimal('poids_moyen_kg', 6, 2)->default(0.50);
            $table->decimal('capacite_litres', 6, 2)->nullable();
            $table->decimal('cout_unitaire', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('type_contenants'); }
};
