<?php

namespace App\Policies;

use App\Models\Signature;
use App\Models\Collecte;
use App\Models\User;

/**
 * Politique d'accès au module Signature.
 *
 * Suit la matrice définie dans la documentation (section 8) :
 *  - SUPERADMIN     : tous réseaux + révocation
 *  - ADMINRÉSEAU    : son réseau (consultation/PDF, pas de signature)
 *  - QHSE           : son établissement, peut signer
 *  - AGENT COLLECT. : ses collectes (consultation/PDF, pas de signature)
 */
class SignaturePolicy
{
    /**
     * Lister l'historique : tous les rôles applicables, scope géré par
     * le contrôleur via TenantScope + filtres.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role,
            ['superadmin', 'admin', 'admin_reseau', 'qhse', 'collecteur', 'prestataire']);
    }

    /**
     * Voir une signature : même règle que la collecte associée.
     */
    public function view(User $user, Signature $signature): bool
    {
        if ($user->isGlobal()) return true;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($signature->etablissement_id);
        }
        if ($user->isQhse()) {
            return $user->etablissement_id === $signature->etablissement_id;
        }
        if ($user->isCollecteur() || $user->role === 'agent') {
            return $signature->collecte && $signature->collecte->collecteur_id === $user->id;
        }
        return false;
    }

    /**
     * Ouvrir l'écran de signature pour une collecte donnée :
     * QHSE de l'établissement OU agent collecteur assigné à la collecte
     * (l'agent présente la tablette, le QHSE signe — voir doc §3.1, §8 note).
     */
    public function open(User $user, Collecte $collecte): bool
    {
        if (! in_array($collecte->statut, ['en_cours'])) return false;
        if ($collecte->signature()->exists()) return false;

        if ($user->isQhse() && $user->etablissement_id === $collecte->etablissement_id) {
            return true;
        }
        if (($user->isCollecteur() || $user->role === 'agent')
            && $collecte->collecteur_id === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Acte de signer (créer la signature) : RÉSERVÉ AU CLIENT SIGNATAIRE.
     * Conformément à la matrice : seul le QHSE de l'établissement signe.
     */
    public function sign(User $user, Collecte $collecte): bool
    {
        if ($collecte->signature()->exists()) return false;
        if ($collecte->statut !== 'en_cours') return false;

        return $user->isQhse()
            && $user->etablissement_id === $collecte->etablissement_id;
    }

    /**
     * Télécharger le PDF signé : même règle que view().
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
