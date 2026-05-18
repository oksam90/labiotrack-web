<?php

/**
 * lang/fr/signatures.php — Module Signature électronique du bordereau.
 */
return [
    // ── Titres de pages ─────────────────────────────────────────
    'page_index_title'  => 'Historique des signatures',
    'page_show_title'   => 'Détail signature',
    'page_sign_title'   => 'Signature bordereau',
    'header_index'      => 'Historique des signatures',
    'header_show'       => 'Détail de preuve numérique',
    'header_sign'       => 'Signature du bordereau de collecte',

    // ── Écran de capture (canvas) ───────────────────────────────
    'collecte_summary'      => 'Résumé collecte',
    'lbl_bordereau'         => 'Bordereau :',
    'lbl_etab'              => 'Établissement :',
    'lbl_date_collecte'     => 'Date collecte :',
    'lbl_agent_collecteur'  => 'Agent collecteur :',
    'lbl_containers'        => 'Contenants :',
    'lbl_weight_declared'   => 'Poids déclaré :',

    'canvas_label'          => 'Signature du client',
    'canvas_hint'           => 'Dessinez votre signature ici',
    'btn_clear'             => 'Effacer',

    'form_signer_name'      => 'Nom complet du signataire *',
    'form_signer_name_ph'   => 'Nom Prénom',
    'form_signer_function'  => 'Fonction',
    'form_signer_function_ph' => 'Ex: Responsable QHSE',
    'form_legal_mention'    => 'Mention légale *',
    'form_legal_default'    => 'Lu et Approuvé',
    'form_legal_checkbox'   => "Je certifie avoir pris connaissance des informations de collecte ci-dessus et confirme leur exactitude. Cette signature électronique a la même valeur juridique qu'une signature manuscrite.",

    'btn_cancel'            => 'Annuler',
    'btn_submit'            => 'Valider la signature',
    'btn_submitting'        => 'Enregistrement…',

    'js_empty_signature'    => 'Veuillez dessiner votre signature.',
    'js_confirm_legal'      => 'Veuillez confirmer la mention légale.',

    // ── Index (historique) ─────────────────────────────────────
    'filter_etab'           => 'Établissement',
    'filter_from'           => 'Du',
    'filter_to'             => 'Au',
    'filter_pdf_status'     => 'Statut PDF',
    'filter_all'            => '— Tous —',
    'filter_pdf_generated'  => 'Généré',
    'filter_pdf_pending'    => 'En attente',
    'btn_filter'            => 'Filtrer',
    'btn_reset'             => 'Réinitialiser',

    'col_bordereau'         => 'Bordereau',
    'col_etab'              => 'Établissement',
    'col_signer'            => 'Signataire',
    'col_signed_at'         => 'Date signature',
    'col_pdf'               => 'PDF',
    'col_status'            => 'Statut',
    'col_actions'           => 'Actions',

    'badge_generated'       => 'Généré',
    'badge_pending'         => 'En cours',
    'badge_revoked'         => 'Révoquée',
    'badge_active'          => 'Active',

    'action_details'        => 'Détails preuve',
    'action_download_pdf'   => 'Télécharger PDF',
    'empty_list'            => 'Aucune signature enregistrée',

    // ── Détail (show) ──────────────────────────────────────────
    'btn_back_to_list'      => 'Retour à la liste',
    'btn_download_signed_pdf' => 'Télécharger PDF signé',
    'card_info'             => 'Informations',
    'lbl_ref_collecte'      => 'Référence collecte',
    'lbl_etab_full'         => 'Établissement',
    'lbl_signer_full'       => 'Signataire',
    'lbl_account_lbt'       => 'Compte LaBioTrack :',
    'lbl_agent_witness'     => 'Agent collecteur (témoin)',
    'lbl_signed_at_full'    => 'Date et heure de signature',
    'lbl_ip_addr'           => 'Adresse IP',
    'lbl_device'            => 'Appareil',
    'lbl_resolution'        => 'Résolution :',
    'lbl_mobile'            => 'Mobile',
    'lbl_tablet'            => 'Tablette',
    'lbl_hash'              => "Hash d'intégrité (SHA-256)",
    'lbl_legal_mention'     => 'Mention légale',

    'card_revoked'          => 'Signature révoquée',
    'lbl_revoked_at'        => 'Date :',
    'lbl_revoked_by'        => 'Par :',
    'lbl_revoke_reason'     => 'Motif :',

    'card_preview'          => 'Aperçu signature',
    'preview_unavailable'   => 'Image indisponible',
    'hash_short'            => 'Hash court :',

    'card_revoke'           => 'Révoquer la signature',
    'revoke_hint'           => "La révocation marque la signature comme invalide et remet la collecte au statut « en cours » pour autoriser une nouvelle signature. L'ancien PDF reste archivé.",
    'revoke_reason_ph'      => 'Motif de révocation (obligatoire)',
    'btn_revoke'            => 'Révoquer',
    'confirm_revoke'        => 'Confirmer la révocation ?',

    // ── PDF bordereau signé ────────────────────────────────────
    'pdf_title'             => 'Bordereau signé :ref',
    'pdf_h1'                => 'Bordereau de Collecte — Signé électroniquement',
    'pdf_table_detail'      => 'Détail des déchets collectés',
    'pdf_sig_block_head'    => '✍ Signature électronique du client',
    'pdf_field_signer'      => 'Signataire :',
    'pdf_field_datetime'    => 'Date et heure :',
    'pdf_field_ip'          => 'Adresse IP :',
    'pdf_field_device'      => 'Appareil :',
    'pdf_footer_label'      => 'Bordereau signé électroniquement — généré le :date — LaBioTrack',
    'pdf_integrity_label'   => "Hash d'intégrité (SHA-256) :",

    // ── Messages flash ─────────────────────────────────────────
    'created_success'       => 'Signature enregistrée. Le PDF est en cours de génération.',
    'created_async_pending' => 'Signature enregistrée avec succès.',
    'invalid_image'         => 'Image de signature invalide.',
    'pdf_not_ready'         => "Le PDF n'est pas encore disponible. Réessayez dans quelques secondes.",
    'revoked_success'       => 'Signature révoquée. La collecte peut être signée à nouveau.',
];
