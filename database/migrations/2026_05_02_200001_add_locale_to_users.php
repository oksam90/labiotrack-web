<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute users.locale (VARCHAR 2, nullable) pour stocker la préférence
 * de langue de l'utilisateur (fr ou en).
 *
 * NULL = la locale par défaut de l'application est utilisée (config app.locale).
 *
 * Le SetLocaleMiddleware résout la locale dans cet ordre :
 *   1. users.locale (si user authentifié et locale non NULL)
 *   2. session('locale') (sélection en cours de session, ex: avant login)
 *   3. config('app.locale') (fallback applicatif)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Position après reseau_id pour rester groupé avec les préférences user
            $table->string('locale', 2)
                ->nullable()
                ->after('reseau_id')
                ->comment('Préférence de langue utilisateur (fr, en). NULL = locale par défaut.');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
