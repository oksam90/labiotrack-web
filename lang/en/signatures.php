<?php

/**
 * lang/en/signatures.php — Electronic signature module.
 */
return [
    // ── Page titles ─────────────────────────────────────────────
    'page_index_title'  => 'Signature history',
    'page_show_title'   => 'Signature details',
    'page_sign_title'   => 'Manifest signature',
    'header_index'      => 'Signature history',
    'header_show'       => 'Digital proof details',
    'header_sign'       => 'Collection manifest signature',

    // ── Capture screen (canvas) ─────────────────────────────────
    'collecte_summary'      => 'Collection summary',
    'lbl_bordereau'         => 'Manifest:',
    'lbl_etab'              => 'Establishment:',
    'lbl_date_collecte'     => 'Collection date:',
    'lbl_agent_collecteur'  => 'Collector agent:',
    'lbl_containers'        => 'Containers:',
    'lbl_weight_declared'   => 'Declared weight:',

    'canvas_label'          => 'Client signature',
    'canvas_hint'           => 'Draw your signature here',
    'btn_clear'             => 'Clear',

    'form_signer_name'      => 'Signer full name *',
    'form_signer_name_ph'   => 'First name Last name',
    'form_signer_function'  => 'Role',
    'form_signer_function_ph' => 'Ex: QHSE Manager',
    'form_legal_mention'    => 'Legal statement *',
    'form_legal_default'    => 'Read and Approved',
    'form_legal_checkbox'   => 'I certify having read the collection information above and confirm its accuracy. This electronic signature has the same legal value as a handwritten signature.',

    'btn_cancel'            => 'Cancel',
    'btn_submit'            => 'Submit signature',
    'btn_submitting'        => 'Saving…',

    'js_empty_signature'    => 'Please draw your signature.',
    'js_confirm_legal'      => 'Please confirm the legal statement.',

    // ── Index (history) ────────────────────────────────────────
    'filter_etab'           => 'Establishment',
    'filter_from'           => 'From',
    'filter_to'             => 'To',
    'filter_pdf_status'     => 'PDF status',
    'filter_all'            => '— All —',
    'filter_pdf_generated'  => 'Generated',
    'filter_pdf_pending'    => 'Pending',
    'btn_filter'            => 'Filter',
    'btn_reset'             => 'Reset',

    'col_bordereau'         => 'Manifest #',
    'col_etab'              => 'Establishment',
    'col_signer'            => 'Signer',
    'col_signed_at'         => 'Signed at',
    'col_pdf'               => 'PDF',
    'col_status'            => 'Status',
    'col_actions'           => 'Actions',

    'badge_generated'       => 'Generated',
    'badge_pending'         => 'Pending',
    'badge_revoked'         => 'Revoked',
    'badge_active'          => 'Active',

    'action_details'        => 'Proof details',
    'action_download_pdf'   => 'Download PDF',
    'empty_list'            => 'No signatures recorded',

    // ── Detail (show) ──────────────────────────────────────────
    'btn_back_to_list'      => 'Back to list',
    'btn_download_signed_pdf' => 'Download signed PDF',
    'card_info'             => 'Information',
    'lbl_ref_collecte'      => 'Collection reference',
    'lbl_etab_full'         => 'Establishment',
    'lbl_signer_full'       => 'Signer',
    'lbl_account_lbt'       => 'LaBioTrack account:',
    'lbl_agent_witness'     => 'Collector agent (witness)',
    'lbl_signed_at_full'    => 'Signature date and time',
    'lbl_ip_addr'           => 'IP address',
    'lbl_device'            => 'Device',
    'lbl_resolution'        => 'Resolution:',
    'lbl_mobile'            => 'Mobile',
    'lbl_tablet'            => 'Tablet',
    'lbl_hash'              => 'Integrity hash (SHA-256)',
    'lbl_legal_mention'     => 'Legal statement',

    'card_revoked'          => 'Signature revoked',
    'lbl_revoked_at'        => 'Date:',
    'lbl_revoked_by'        => 'By:',
    'lbl_revoke_reason'     => 'Reason:',

    'card_preview'          => 'Signature preview',
    'preview_unavailable'   => 'Image unavailable',
    'hash_short'            => 'Short hash:',

    'card_revoke'           => 'Revoke signature',
    'revoke_hint'           => 'Revocation marks the signature as invalid and sets the collection back to "in progress" to allow a new signature. The previous PDF remains archived.',
    'revoke_reason_ph'      => 'Revocation reason (required)',
    'btn_revoke'            => 'Revoke',
    'confirm_revoke'        => 'Confirm revocation?',

    // ── Signed PDF ─────────────────────────────────────────────
    'pdf_title'             => 'Signed manifest :ref',
    'pdf_h1'                => 'Collection manifest — Electronically signed',
    'pdf_table_detail'      => 'Collected waste details',
    'pdf_sig_block_head'    => '✍ Client electronic signature',
    'pdf_field_signer'      => 'Signer:',
    'pdf_field_datetime'    => 'Date and time:',
    'pdf_field_ip'          => 'IP address:',
    'pdf_field_device'      => 'Device:',
    'pdf_footer_label'      => 'Manifest electronically signed — generated on :date — LaBioTrack',
    'pdf_integrity_label'   => 'Integrity hash (SHA-256):',

    // ── Flash messages ─────────────────────────────────────────
    'created_success'       => 'Signature saved. The PDF is being generated.',
    'created_async_pending' => 'Signature saved successfully.',
    'invalid_image'         => 'Invalid signature image.',
    'already_signed'        => 'This slip has just been signed or is no longer signable. Please refresh the page.',
    'pdf_not_ready'         => 'The PDF is not yet available. Try again in a few seconds.',
    'revoked_success'       => 'Signature revoked. The collection can be signed again.',
];
