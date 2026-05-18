<?php

/**
 * lang/fr/pdf.php — Vocabulaire commun aux templates PDF (DomPDF).
 *
 * Les chaînes métier (Bordereau de collecte, certificat de destruction, etc.)
 * restent dans leurs fichiers respectifs : collectes.php, destructions.php,
 * rapports.php, signatures.php. Ce fichier rassemble les éléments
 * réutilisés entre tous les PDFs (en-tête, pied de page, mentions de
 * responsabilité, etc.).
 */
return [
    // ── En-tête / pied de page ────────────────────────────────
    'platform_name'      => 'LaBioTrack',
    'platform_full'      => 'LaBioTrack — Plateforme de gestion des déchets biomédicaux',
    'platform_legacy'    => 'BioMedWaste Platform',
    'platform_dechets'   => 'Plateforme BioMedDéchets',

    'generated_on'       => 'Bordereau généré le :date',
    'doc_generated_on'   => 'Document généré le :date',
    'report_generated_on' => 'Généré le :date',
    'period'             => 'Période',
    'from_to'            => 'au',
    'period_short'       => 'du :start au :end',

    // ── Mention légale globale ────────────────────────────────
    'auto_generated_notice' => 'Ce rapport est généré automatiquement. Toute modification est interdite.',
    'qhse_responsible'   => 'Responsable QHSE',
    'unknown_structure'  => 'Structure inconnue',
    'establishment_fallback' => 'ÉTABLISSEMENT',
    'destruction_compliance_notice' => "Ce bordereau atteste la destruction conforme des déchets d'activités de soins.",

    // ── Boîtes de signature (bordereau legacy) ────────────────
    'sig_etab_responsible'  => 'Responsable établissement',
    'sig_collecteur'        => 'Collecteur',
    'sig_name_stamp'        => 'Nom, Signature & Cachet',

    // ── Champs génériques (couvre legacy) ────────────────────
    'field_etab'        => 'Établissement',
    'field_bordereau'   => 'Bordereau N°',
    'field_date'        => 'Date de collecte',
    'field_vehicle'     => 'Véhicule',
    'field_containers'  => 'Contenants totaux',
    'field_weight'      => 'Poids total déclaré',

    // ── Détail tableau (bordereau legacy) ─────────────────────
    'table_detail_title' => 'Détail des déchets collectés',
    'col_index'         => '#',
    'col_service'       => 'Service',
    'col_container_type' => 'Type contenant',
    'col_qty'           => 'Quantité',
    'col_weight_est'    => 'Poids estimé',
    'col_total'         => 'TOTAL',

    // ── Rapport KPIs ──────────────────────────────────────────
    'report_title'      => 'RAPPORT DE GESTION DES DÉCHETS BIOMÉDICAUX',
    'report_kpi_section' => '📊 Indicateurs clés',
    'kpi_declarations'  => 'Déclarations',
    'kpi_weight_total'  => 'Poids total estimé',
    'kpi_collectes'     => 'Collectes réalisées',
    'kpi_weight_real'   => 'Poids réel détruit',
    'kpi_destructions'  => 'Destructions',
    'kpi_score_avg'     => 'Score conformité moyen',
    'kpi_alerts'        => 'Alertes générées',
    'kpi_beds_declared' => 'Lits déclarés',
    'report_service_section' => '🏥 Production par service',
    'col_decl_count'    => 'Nb déclarations',
    'col_weight_estimated_kg' => 'Poids estimé (kg)',
];
