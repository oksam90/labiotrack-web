<?php

/**
 * lang/en/pdf.php — Common vocabulary for PDF templates (DomPDF).
 *
 * Business strings (Collection manifest, Destruction certificate, etc.)
 * stay in their respective files: collectes.php, destructions.php,
 * rapports.php, signatures.php. This file gathers elements reused
 * across all PDFs (header, footer, liability notices, etc.).
 */
return [
    // ── Header / footer ───────────────────────────────────────
    'platform_name'      => 'LaBioTrack',
    'platform_full'      => 'LaBioTrack — Biomedical waste management platform',
    'platform_legacy'    => 'BioMedWaste Platform',
    'platform_dechets'   => 'BioMedWaste Platform',

    'generated_on'       => 'Manifest generated on :date',
    'doc_generated_on'   => 'Document generated on :date',
    'report_generated_on' => 'Generated on :date',
    'period'             => 'Period',
    'from_to'            => 'to',
    'period_short'       => 'from :start to :end',

    // ── Global legal notice ───────────────────────────────────
    'auto_generated_notice' => 'This report is generated automatically. Any modification is forbidden.',
    'qhse_responsible'   => 'QHSE Manager',
    'unknown_structure'  => 'Unknown structure',
    'establishment_fallback' => 'ESTABLISHMENT',
    'destruction_compliance_notice' => 'This certificate attests the compliant destruction of healthcare waste.',

    // ── Signature boxes (legacy manifest) ─────────────────────
    'sig_etab_responsible'  => 'Establishment manager',
    'sig_collecteur'        => 'Collector',
    'sig_name_stamp'        => 'Name, Signature & Stamp',

    // ── Generic fields (covers legacy) ───────────────────────
    'field_etab'        => 'Establishment',
    'field_bordereau'   => 'Manifest no.',
    'field_date'        => 'Collection date',
    'field_vehicle'     => 'Vehicle',
    'field_containers'  => 'Total containers',
    'field_weight'      => 'Total declared weight',

    // ── Detail table (legacy manifest) ────────────────────────
    'table_detail_title' => 'Collected waste details',
    'col_index'         => '#',
    'col_service'       => 'Service',
    'col_container_type' => 'Container type',
    'col_qty'           => 'Quantity',
    'col_weight_est'    => 'Estimated weight',
    'col_total'         => 'TOTAL',

    // ── Report KPIs ───────────────────────────────────────────
    'report_title'      => 'BIOMEDICAL WASTE MANAGEMENT REPORT',
    'report_kpi_section' => '📊 Key indicators',
    'kpi_declarations'  => 'Declarations',
    'kpi_weight_total'  => 'Total estimated weight',
    'kpi_collectes'     => 'Collections done',
    'kpi_weight_real'   => 'Actual weight destroyed',
    'kpi_destructions'  => 'Destructions',
    'kpi_score_avg'     => 'Average compliance score',
    'kpi_alerts'        => 'Generated alerts',
    'kpi_beds_declared' => 'Declared beds',
    'report_service_section' => '🏥 Production by service',
    'col_decl_count'    => 'Declarations count',
    'col_weight_estimated_kg' => 'Estimated weight (kg)',
];
