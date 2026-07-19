<?php

/**
 * lang/en/common.php — Cross-cutting vocabulary for LaBioTrack.
 * Used across all views.
 */
return [
    // ── Locale switcher ─────────────────────────────────────────
    'locale_changed' => 'Language changed to English.',
    'locale_fr'      => 'French',
    'locale_en'      => 'English',

    // ── Generic actions (buttons, links) ────────────────────────
    'save'    => 'Save',
    'cancel'  => 'Cancel',
    'delete'  => 'Delete',
    'edit'    => 'Edit',
    'create'  => 'Create',
    'view'    => 'View',
    'back'    => 'Back',
    'close'   => 'Close',
    'confirm' => 'Confirm',
    'submit'  => 'Submit',
    'reset'   => 'Reset',
    'search'  => 'Search',
    'filter'  => 'Filter',
    'export'  => 'Export',
    'import'  => 'Import',
    'download' => 'Download',
    'upload'  => 'Upload',
    'print'   => 'Print',
    'preview' => 'Preview',
    'add'     => 'Add',
    'remove'  => 'Remove',
    'duplicate' => 'Duplicate',
    'refresh' => 'Refresh',
    'previous' => 'Previous',
    'next'    => 'Next',
    'details' => 'Details',

    // ── Responses ──────────────────────────────────────────────
    'yes'     => 'Yes',
    'no'      => 'No',
    'ok'      => 'OK',

    // ── Table columns ──────────────────────────────────────────
    'actions'    => 'Actions',
    'status'     => 'Status',
    'date'       => 'Date',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',
    'name'       => 'Name',
    'firstname'  => 'First name',
    'lastname'   => 'Last name',
    'email'      => 'Email',
    'phone'      => 'Phone',
    'role'       => 'Role',
    'description' => 'Description',
    'comment'    => 'Comment',
    'notes'      => 'Notes',
    'reference'  => 'Reference',
    'amount'     => 'Amount',
    'quantity'   => 'Quantity',
    'weight'     => 'Weight',
    'unit'       => 'Unit',

    // ── States / statuses ──────────────────────────────────────
    'active'   => 'Active',
    'inactive' => 'Inactive',
    'enabled'  => 'Enabled',
    'disabled' => 'Disabled',
    'pending'  => 'Pending',
    'loading'  => 'Loading…',
    'saving'   => 'Saving…',

    // ── User messages ──────────────────────────────────────────
    'no_data'         => 'No data available',
    'no_results'      => 'No results',
    'required_field'  => 'This field is required',
    'optional'        => 'Optional',
    'success_saved'   => 'Saved successfully.',
    'success_deleted' => 'Deleted successfully.',
    'success_updated' => 'Updated successfully.',
    'error_generic'   => 'An error occurred.',
    'errors_label'    => 'Errors:',
    'access_denied'   => 'Access denied: your role does not allow access to this section.',
    'confirm_delete'  => 'Confirm deletion?',
    'confirm_action'  => 'Confirm this action?',

    // ── Pagination / volumes ──────────────────────────────────
    'showing'   => 'Showing',
    'of'        => 'of',
    'total'     => 'Total',
    'results'   => 'results',

    // ── QR Code (cross-cutting) ────────────────────────────────
    'qr_invalid' => 'Invalid QR Code.',

    // ── EnsureTenantMiddleware ─────────────────────────────────
    'tenant_view_switched' => 'You are now viewing this structure\'s data.',
    'tenant_access_denied' => 'Access denied: this establishment is not part of your scope.',
    'tenant_no_etab'       => 'No establishment associated with your account. Contact the administrator.',
];
