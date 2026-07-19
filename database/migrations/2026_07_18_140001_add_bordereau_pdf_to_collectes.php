<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stockage du bordereau PDF (non signé) généré de façon asynchrone : évite la
 * génération DomPDF synchrone bloquante (~60 s) à chaque téléchargement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->string('bordereau_pdf_path')->nullable()->after('photo');
            $table->timestamp('bordereau_generated_at')->nullable()->after('bordereau_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->dropColumn(['bordereau_pdf_path', 'bordereau_generated_at']);
        });
    }
};
