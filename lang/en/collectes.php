<?php

/**
 * lang/en/collectes.php — Collections module (biomedical waste transport).
 */
return [
    // ── Page titles ─────────────────────────────────────────────
    'page_index_title'  => 'Collections',
    'page_show_title'   => 'Collection details',
    'page_create_title' => 'New collection',
    'header_index'      => 'Collections management',
    'header_create'     => 'Create a collection',
    'header_show'       => 'Collection',

    // ── Actions / buttons ───────────────────────────────────────
    'btn_new'             => 'New collection',
    'btn_create_and_pdf'  => 'Create collection & generate manifest',
    'btn_bordereau_pdf'   => 'Manifest PDF',
    'btn_view'            => 'View details',
    'btn_sign'            => 'Electronic signature',
    'btn_signer_short'    => 'Sign manifest',
    'btn_view_signature'  => 'View signature',
    'btn_confirm_destruction' => 'Confirm destruction',
    'btn_download_pdf'    => 'Download manifest',

    // ── Table columns / fields ──────────────────────────────────
    'col_bordereau'        => 'Manifest #',
    'col_date'             => 'Date',
    'col_collecteur'       => 'Collector',
    'col_containers_count' => 'Containers',
    'col_weight_declared'  => 'Declared weight',
    'col_status'           => 'Status',
    'col_actions'          => 'Actions',
    'col_service'          => 'Service',
    'col_container_type'   => 'Container',
    'col_qty'              => 'Qty',
    'col_weight_estimated' => 'Est. weight',

    // ── Create form ─────────────────────────────────────────────
    'form_decl_available'   => 'Declarations available in stock',
    'form_decl_check_hint'  => 'Check the declarations to include in this collection',
    'form_no_decl_available' => 'No declarations available in stock',
    'form_collecteur'       => 'Collector',
    'form_select_placeholder' => '— Select —',
    'form_vehicle'          => 'Vehicle',
    'form_vehicle_placeholder' => 'Ex: DK-1234-AB',
    'form_photo'            => 'Photo of loaded waste (optional)',
    'form_notes'            => 'Notes',

    // ── Detail page (show) ──────────────────────────────────────
    'show_info'              => 'Information',
    'show_bordereau'         => 'Manifest:',
    'show_date'              => 'Date:',
    'show_collecteur'        => 'Collector:',
    'show_containers'        => 'Containers:',
    'show_weight_declared'   => 'Declared weight:',
    'show_vehicle'           => 'Vehicle:',
    'show_status'            => 'Status:',
    'show_destruction'       => 'Destruction:',
    'show_destruction_cert'  => 'View certificate',
    'show_decl_included'     => 'Included declarations',
    'show_total'             => 'TOTAL',

    // ── States (status enum) ────────────────────────────────────
    'status_planifie' => 'Planned',
    'status_en_cours' => 'In progress',
    'status_signee'   => 'Signed',
    'status_complete' => 'Completed',
    'status_annule'   => 'Cancelled',

    // ── Empty states / messages ─────────────────────────────────
    'empty_list'        => 'No collections recorded',
    'created_success'   => 'Collection created — Manifest no. :ref',
    'cannot_determine_etab' => 'Unable to determine the establishment.',
];
