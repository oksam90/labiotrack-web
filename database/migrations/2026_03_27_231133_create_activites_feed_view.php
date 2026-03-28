<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Crée la vue SQL activites_feed :
     * flux unifié de toutes les activités du système.
     */
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS activites_feed");

        DB::statement("
            CREATE VIEW activites_feed AS

            -- ── Déclarations ────────────────────────────────────
            SELECT 'declaration'    AS type,
                   d.id,
                   d.created_at     AS moment,
                   CONCAT(u.prenom, ' ', u.nom) AS acteur,
                   e.nom            AS etablissement,
                   'Déclaration de déchets créée' AS description,
                   d.user_id,
                   d.etablissement_id,
                   'info'           AS niveau
            FROM declarations d
            JOIN users u            ON u.id = d.user_id
            JOIN etablissements e   ON e.id = d.etablissement_id

            UNION ALL

            -- ── Collectes ───────────────────────────────────────
            SELECT 'collecte'       AS type,
                   c.id,
                   c.created_at     AS moment,
                   COALESCE(CONCAT(u.prenom, ' ', u.nom), 'N/A') AS acteur,
                   e.nom            AS etablissement,
                   CONCAT('Collecte — statut: ', c.statut) AS description,
                   c.collecteur_id  AS user_id,
                   c.etablissement_id,
                   'success'        AS niveau
            FROM collectes c
            LEFT JOIN users u       ON u.id = c.collecteur_id
            JOIN etablissements e   ON e.id = c.etablissement_id

            UNION ALL

            -- ── Checklists ──────────────────────────────────────
            SELECT 'checklist'      AS type,
                   ch.id,
                   ch.created_at    AS moment,
                   CONCAT(u.prenom, ' ', u.nom) AS acteur,
                   e.nom            AS etablissement,
                   CONCAT('Checklist — score: ', ch.score_conformite, '%') AS description,
                   ch.user_id,
                   ch.etablissement_id,
                   IF(ch.score_conformite < 60, 'danger',
                      IF(ch.score_conformite < 80, 'warning', 'success')) AS niveau
            FROM checklists ch
            JOIN users u            ON u.id = ch.user_id
            JOIN etablissements e   ON e.id = ch.etablissement_id

            UNION ALL

            -- ── Alertes ─────────────────────────────────────────
            SELECT 'alerte'         AS type,
                   a.id,
                   a.created_at     AS moment,
                   'Système'        AS acteur,
                   e.nom            AS etablissement,
                   a.message        AS description,
                   NULL             AS user_id,
                   a.etablissement_id,
                   a.niveau         AS niveau
            FROM alertes a
            JOIN etablissements e   ON e.id = a.etablissement_id

            UNION ALL

            -- ── Destructions ────────────────────────────────────
            SELECT 'destruction'    AS type,
                   dest.id,
                   dest.created_at  AS moment,
                   COALESCE(CONCAT(u.prenom, ' ', u.nom), 'N/A') AS acteur,
                   e.nom            AS etablissement,
                   CONCAT('Destruction — ', dest.poids_reel_kg, ' kg') AS description,
                   dest.prestataire_id AS user_id,
                   dest.etablissement_id,
                   'secondary'      AS niveau
            FROM destructions dest
            LEFT JOIN users u       ON u.id = dest.prestataire_id
            JOIN etablissements e   ON e.id = dest.etablissement_id
        ");
    }

    /**
     * Supprime la vue SQL.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS activites_feed");
    }
};
