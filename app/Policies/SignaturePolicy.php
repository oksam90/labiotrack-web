<?php

namespace App\Policies;

use App\Models\Signature;
use App\Models\Collecte;
use App\Models\User;

/**
 * Politique d'accès au module Signature.
 *
 * Suit la matrice définie dans la documentation (section 8) :
 *
 * | Fonctionnalité       | SUPERADMIN  | ADMINRÉSEAU | QHSE          | COLLECTEUR        |
 * |----------------------|-------------|-------------|---------------|-------------------|
 * | Ouvrir l'écran       | Tous rés.   | Son réseau  | Son étab.     | Collectes assignées |
 * | Signer (dessiner)    | N/A         | N/A         | Son étab.     | N/A               |
 * | Consulter / PDF      | Tous rés.   | Son réseau  | Son étab.     | Ses collectes     |
 * | Révoquer             | Complet     | Non         | Non           | Non               |
 *
 * NOTE : utilisation de == (non strict) pour les comparaisons d'IDs.
 * Les FK ne sont pas typées explicitement dans $casts, donc selon la
 * configuration PDO (PDO::ATTR_EMULATE_PREPARES) elles peuvent être
 * renvoyées en string ou en int. `===` produit alors des faux négatifs.
 */
class SignaturePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role,
            ['superadmin', 'admin', 'admin_reseau', 'qhse', 'collecteur', 'prestataire', 'agent', 'client_signataire']);
    }

    /**
     * Voir une signature : superadmin partout, admin_reseau dans son réseau,
     * QHSE dans son étab, collecteur sur ses collectes.
     */
    public function view(User $user, Signature $signature): bool
    {
        if ($user->isSuperAdmin()) return true;
        // collecteur / agent : uniquement les signatures de LEURS collectes
        // (priorité sur le périmètre réseau).
        if ($user->isCollecteur() || $user->role === 'agent') {
            return $signature->collecte
                && (int) $signature->collecte->collecteur_id === (int) $user->id;
        }
        // admin, admin_reseau, prestataire → périmètre réseau
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($signature->etablissement_id);
        }
        if ($user->isQhse() || $user->isClientSignataire()) {
            return (int) $user->etablissement_id === (int) $signature->etablissement_id;
        }
        return false;
    }

    // NOTE : les méthodes open() et sign() opèrent sur une Collecte —
    // elles vivent dans CollectePolicy (signatureOpen / signatureSign)
    // car Laravel route Gate::can($ability, $model) selon la classe du modèle.

    /**
     * Télécharger le PDF signé : même règle que view() + PDF disponible.
     */
    public function downloadPdf(User $user, Signature $signature): bool
    {
        return $this->view($user, $signature) && $signature->pdfReady();
    }

    /**
     * Révocation : SUPERADMIN UNIQUEMENT (matrice §8).
     */
    public function revoke(User $user, Signature $signature): bool
    {
        return $user->isSuperAdmin() && ! $signature->isRevoked();
    }
}
