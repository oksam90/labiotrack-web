<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->nullable()->constrained('etablissements')->nullOnDelete();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['superadmin','admin','qhse','agent','collecteur','prestataire'])->default('agent');
            $table->string('telephone', 30)->nullable();
            $table->boolean('actif')->default(true);
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();

            // Index sur les colonnes fréquemment filtrées
            $table->index(['etablissement_id', 'role']);
            $table->index(['email', 'actif']);
        });
    }

    public function down(): void { Schema::dropIfExists('users'); }
};
