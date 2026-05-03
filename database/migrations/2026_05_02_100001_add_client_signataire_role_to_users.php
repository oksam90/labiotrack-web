<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute le rôle `client_signataire` à l'enum users.role.
 *
 * Profil : utilisateur lié à UN établissement, périmètre fonctionnel
 * réduit aux modules Collectes (lecture) et Signature (création +
 * historique). Sa mission unique : signer les bordereaux de collecte
 * de son établissement (matrice §8 — équivalent client signataire).
 *
 * Diffère du QHSE qui a un périmètre opérationnel beaucoup plus large
 * (déclarations, stockage, checklists, rapports).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM(
            'superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire','client_signataire'
        ) NOT NULL DEFAULT 'agent'");
    }

    public function down(): void
    {
        // Avant rollback : reconvertir les client_signataire en qhse pour
        // ne pas perdre leur rattachement à un établissement.
        DB::statement("UPDATE `users` SET `role` = 'qhse' WHERE `role` = 'client_signataire'");
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM(
            'superadmin','admin','admin_reseau','qhse','agent','collecteur','prestataire'
        ) NOT NULL DEFAULT 'agent'");
    }
};
