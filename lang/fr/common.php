<?php

/**
 * lang/fr/common.php — Vocabulaire transverse de LaBioTrack.
 * Réutilisé dans toutes les vues.
 */
return [
    // ── Locale switcher ─────────────────────────────────────────
    'locale_changed' => 'Langue modifiée en français.',
    'locale_fr'      => 'Français',
    'locale_en'      => 'Anglais',

    // ── Actions génériques (boutons, liens) ─────────────────────
    'save'    => 'Enregistrer',
    'cancel'  => 'Annuler',
    'delete'  => 'Supprimer',
    'edit'    => 'Modifier',
    'create'  => 'Créer',
    'view'    => 'Voir',
    'back'    => 'Retour',
    'close'   => 'Fermer',
    'confirm' => 'Confirmer',
    'submit'  => 'Valider',
    'reset'   => 'Réinitialiser',
    'search'  => 'Rechercher',
    'filter'  => 'Filtrer',
    'export'  => 'Exporter',
    'import'  => 'Importer',
    'download' => 'Télécharger',
    'upload'  => 'Téléverser',
    'print'   => 'Imprimer',
    'preview' => 'Aperçu',
    'add'     => 'Ajouter',
    'remove'  => 'Supprimer',
    'duplicate' => 'Dupliquer',
    'refresh' => 'Actualiser',
    'previous' => 'Précédent',
    'next'    => 'Suivant',
    'details' => 'Détails',

    // ── Réponses ───────────────────────────────────────────────
    'yes'     => 'Oui',
    'no'      => 'Non',
    'ok'      => 'OK',

    // ── Colonnes de tableau ────────────────────────────────────
    'actions'    => 'Actions',
    'status'     => 'Statut',
    'date'       => 'Date',
    'created_at' => 'Créé le',
    'updated_at' => 'Modifié le',
    'name'       => 'Nom',
    'firstname'  => 'Prénom',
    'lastname'   => 'Nom',
    'email'      => 'Email',
    'phone'      => 'Téléphone',
    'role'       => 'Rôle',
    'description' => 'Description',
    'comment'    => 'Commentaire',
    'notes'      => 'Notes',
    'reference'  => 'Référence',
    'amount'     => 'Montant',
    'quantity'   => 'Quantité',
    'weight'     => 'Poids',
    'unit'       => 'Unité',

    // ── États / statuts ────────────────────────────────────────
    'active'   => 'Actif',
    'inactive' => 'Inactif',
    'enabled'  => 'Activé',
    'disabled' => 'Désactivé',
    'pending'  => 'En attente',
    'loading'  => 'Chargement…',
    'saving'   => 'Enregistrement…',

    // ── Messages utilisateur ───────────────────────────────────
    'no_data'         => 'Aucune donnée disponible',
    'no_results'      => 'Aucun résultat',
    'required_field'  => 'Ce champ est obligatoire',
    'optional'        => 'Facultatif',
    'success_saved'   => 'Enregistré avec succès.',
    'success_deleted' => 'Supprimé avec succès.',
    'success_updated' => 'Modifié avec succès.',
    'error_generic'   => 'Une erreur est survenue.',
    'errors_label'    => 'Erreurs :',
    'access_denied'   => 'Accès non autorisé : votre rôle ne vous permet pas d’accéder à cette section.',
    'confirm_delete'  => 'Confirmer la suppression ?',
    'confirm_action'  => 'Confirmer cette action ?',

    // ── Pagination / volumes ──────────────────────────────────
    'showing'   => 'Affichage de',
    'of'        => 'sur',
    'total'     => 'Total',
    'results'   => 'résultats',

    // ── QR Code (transverse) ───────────────────────────────────
    'qr_invalid' => 'QR Code invalide.',

    // ── EnsureTenantMiddleware ─────────────────────────────────
    'tenant_view_switched' => 'Vous visualisez maintenant les données de cette structure.',
    'tenant_access_denied' => 'Accès refusé : cet établissement ne fait pas partie de votre périmètre.',
    'tenant_no_etab'       => "Aucun établissement associé à votre compte. Contactez l'administrateur.",
];
