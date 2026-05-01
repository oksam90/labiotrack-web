<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table signatures — Preuve numérique du bordereau de collecte.
 *
 * Stocke chaque signature manuscrite capturée (PNG base64), ses
 * métadonnées de preuve (IP, user-agent, hash SHA-256, horodatage)
 * et le chemin du PDF signé généré par le job GenerateBordereauPdf.
 *
 * Contrainte UNIQUE sur collecte_id : une collecte ne peut être signée
 * qu'une seule fois (la révocation marque revoked_at sans supprimer).
 *
 * Voir : doc/LaBioTrack_Signature_Electronique.docx — section 2.2
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();

            // Relation 1:1 avec collecte
            $table->foreignId('collecte_id')
                ->unique()
                ->constrained('collectes')
                ->cascadeOnDelete();

            // Tenant scoping
            $table->foreignId('etablissement_id')
                ->constrained('etablissements')
                ->cascadeOnDelete();

            // Acteurs : signataire (client) et agent (témoin)
            $table->foreignId('signataire_user_id')
                ->constrained('users');
            $table->foreignId('agent_user_id')
                ->nullable()
                ->constrained('users');

            // Identité affichée du signataire
            $table->string('signataire_nom');
            $table->string('signataire_fonction')->nullable();

            // Image et preuve d'intégrité
            $table->string('signature_image_path');
            $table->string('signature_hash', 64);

            // Mention légale
            $table->string('commentaire')->default('Lu et Approuvé');

            // Métadonnées de preuve numérique
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('device_info')->nullable();
            $table->timestamp('signed_at');

            // PDF généré (asynchrone)
            $table->timestamp('pdf_generated_at')->nullable();
            $table->string('pdf_path')->nullable();

            // Révocation (superadmin uniquement, voir section 9.3)
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            $table->foreignId('revoked_by_user_id')
                ->nullable()
                ->constrained('users');

            $table->timestamps();

            $table->index(['etablissement_id', 'signed_at']);
            $table->index('signataire_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
