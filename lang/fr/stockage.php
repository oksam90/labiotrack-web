<?php

/**
 * lang/fr/stockage.php — Module Stockage interne.
 */
return [
    'page_index_title'  => 'Stockage Central',
    'page_create_title' => 'Nouveau transfert stockage',
    'header_index'      => 'Stockage Central',
    'header_create'     => 'Nouveau transfert vers stockage central',
    'subtitle_create'   => 'Regrouper des déclarations en stock pour transfert',

    'btn_new'           => 'Nouveau transfert',
    'btn_back'          => 'Retour',
    'btn_select_all'    => 'Tout sélectionner',
    'btn_save_transfer' => 'Enregistrer le transfert',
    'btn_create_first'  => 'Créer le premier →',

    'kpi_stock_count'   => 'Déclarations en stock',

    'filter_service'    => 'Service',
    'filter_all_services' => 'Tous les services',
    'filter_status'     => 'Statut',
    'filter_all'        => 'Tous',
    'btn_filter'        => 'Filtrer',

    'status_en_attente' => 'En attente',
    'status_valide'     => 'Validé',
    'status_collecte'   => 'Collecté',

    'col_service'        => 'Service',
    'col_containers'     => 'Contenants',
    'col_weight_estimated' => 'Poids estimé',
    'col_zone'           => 'Zone stockage',
    'col_deadline'       => 'Date limite',
    'col_status'         => 'Statut',
    'col_agent'          => 'Agent',
    'col_date'           => 'Date',

    'empty_list'         => 'Aucun transfert enregistré.',

    'form_service_emitter' => 'Service émetteur',
    'form_select_placeholder' => '— Sélectionner —',
    'form_zone'          => 'Zone de stockage',
    'form_zone_ph'       => 'Ex: Zone A, Local B2…',
    'form_deadline'      => 'Date limite de collecte',
    'form_deadline_hint' => 'Une alerte sera créée si ≤ 3 jours.',
    'form_notes'         => 'Notes',
    'form_notes_ph'      => 'Observations…',
    'form_decl_label'    => 'Déclarations à transférer',
    'form_no_decl'       => 'Aucune déclaration disponible en stock.',
    'form_count_short'   => 'cont.',
    'recap_lots'         => 'Lots',
    'recap_containers'   => 'Contenants',
    'recap_weight'       => 'Poids estimé',

    'reminder_title'     => 'Rappel',
    'reminder_step1'     => '1. Sélectionnez les déclarations à regrouper',
    'reminder_step2'     => '2. Indiquez la zone de stockage',
    'reminder_step3'     => '3. Fixez une date limite de collecte',
    'reminder_warning'   => '⚠️ Les déclarations déjà rattachées à un transfert en cours ne sont pas listées.',
    'flash_created'   => 'Transfert enregistré — :count contenant(s) | :weight kg.',
    'flash_validated' => 'Réception au stockage central validée ✓',

    // Show (détail transfert)
    'page_show_title'   => 'Transfert',
    'show_header'       => 'Transfert',
    'show_zone'         => 'Zone :',
    'show_deadline'     => 'Date limite :',
    'show_status'       => 'Statut :',
    'btn_validate_receipt' => 'Valider la réception',
    'btn_receipt_validated' => 'Réception validée',
];
