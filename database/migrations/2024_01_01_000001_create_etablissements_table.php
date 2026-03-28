<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type', ['clinique','hopital','cabinet','laboratoire']);
            $table->text('adresse');
            $table->string('ville', 100)->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('responsable_qhse')->nullable();
            $table->integer('nombre_lits')->default(0);
            $table->boolean('actif')->default(true);
            $table->string('logo')->nullable();
            $table->string('slug')->unique()->nullable(); // pour URL multi-tenant
            $table->json('parametres')->nullable();       // config spécifique par structure
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('etablissements'); }
};
