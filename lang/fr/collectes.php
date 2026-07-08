<?php

/**
 * lang/fr/collectes.php — Module Collectes (transport des déchets biomédicaux).
 */
return [
    // ── Titres de pages ─────────────────────────────────────────
    'page_index_title'  => 'Collectes',
    'page_show_title'   => 'Détail Collecte',
    'page_create_title' => 'Nouvelle Collecte',
    'header_index'      => 'Gestion des collectes',
    'header_create'     => 'Créer une collecte',
    'header_show'       => 'Collecte',

    // ── Actions / boutons ──────────────────────────────────────
    'btn_new'             => 'Nouvelle collecte',
    'btn_create_and_pdf'  => 'Créer la collecte & générer le bordereau',
    'btn_bordereau_pdf'   => 'Bordereau PDF',
    'btn_view'            => 'Voir détail',
    'btn_sign'            => 'Signature électronique',
    'btn_signer_short'    => 'Signer le bordereau',
    'btn_view_signature'  => 'Voir signature',
    'btn_confirm_destruction' => 'Confirmer destruction',
    'btn_download_pdf'    => 'Télécharger bordereau',

    // ── Colonnes / champs ──────────────────────────────────────
    'col_bordereau'        => 'Bordereau',
    'col_date'             => 'Date',
    'col_collecteur'       => 'Collecteur',
    'col_containers_count' => 'Contenants',
    'col_weight_declared'  => 'Poids déclaré',
    'col_status'           => 'Statut',
    'col_actions'          => 'Actions',
    'col_service'          => 'Service',
    'col_container_type'   => 'Contenant',
    'col_qty'              => 'Qté',
    'col_weight_estimated' => 'Poids est.',

    // ── Formulaire de création ──────────────────────────────────
    'form_decl_available'   => 'Déclarations disponibles en stock',
    'form_decl_check_hint'  => 'Cochez les déclarations à inclure dans cette collecte',
    'form_no_decl_available' => 'Aucune déclaration en stock disponible',
    'form_collecteur'       => 'Collecteur',
    'form_select_placeholder' => '— Sélectionner —',
    'form_vehicle'          => 'Véhicule',
    'form_vehicle_placeholder' => 'Ex: DK-1234-AB',
    'form_photo'            => 'Photo des déchets chargés (optionnel)',
    'form_notes'            => 'Notes',

    // ── Page détail (show) ─────────────────────────────────────
    'show_info'              => 'Informations',
    'show_bordereau'         => 'Bordereau :',
    'show_date'              => 'Date :',
    'show_collecteur'        => 'Collecteur :',
    'show_containers'        => 'Contenants :',
    'show_weight_declared'   => 'Poids déclaré :',
    'show_vehicle'           => 'Véhicule :',
    'show_status'            => 'Statut :',
    'show_destruction'       => 'Destruction :',
    'show_destruction_cert'  => 'Voir certificat',
    'show_decl_included'     => 'Déclarations incluses',
    'show_total'             => 'TOTAL',

    // ── États (statut enum) ────────────────────────────────────
    'status_planifie' => 'Planifiée',
    'status_en_cours' => 'En cours',
    'status_signee'   => 'Signée',
    'status_complete' => 'Terminée',
    'status_annule'   => 'Annulée',

    // ── Empty states / messages ────────────────────────────────
    'empty_list'        => 'Aucune collecte enregistrée',
    'created_success'   => 'Collecte créée — Bordereau n° :ref',
    'cannot_determine_etab' => "Impossible de déterminer l'établissement.",
    'declarations_out_of_scope' => "Certaines déclarations sélectionnées n'appartiennent pas à votre réseau.",
    'collecteur_out_of_scope' => "Le collecteur sélectionné n'appartient pas à votre réseau.",
];
