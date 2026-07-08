<?php

namespace App\Policies;

use App\Models\Collecte;
use App\Models\User;

class CollectePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','collecteur','prestataire','client_signataire']);
    }

    public function view(User $user, Collecte $collecte): bool
    {
        if ($user->isGlobal()) return true; // superadmin/collecteur/prestataire
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($collecte->etablissement_id);
        }
        // qhse, agent, client_signataire → leur étab uniquement
        return (int) $user->etablissement_id === (int) $collecte->etablissement_id;
    }

    public function create(User $user): bool
    {
        // client_signataire EXCLU : son rôle est uniquement de signer
        // les bordereaux existants, pas de créer de nouvelles collectes.
        return in_array($user->role,
            ['superadmin','admin','admin_reseau','qhse','collecteur']);
    }

    // L'ability `valider` (double signature texte) a été retirée :
    // la validation passe désormais par la signature électronique
    // → voir CollectePolicy::signatureOpen() / signatureSign().

    public function delete(User $user, Collecte $collecte): bool
    {
        if ($user->isSuperAdmin()) return true;
        // collecteur / prestataire : rôles opérationnels, pas de suppression
        if ($user->isCollecteur() || $user->isPrestataire()) return false;
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($collecte->etablissement_id);
        }
        return $user->isQhse() && $user->etablissement_id === $collecte->etablissement_id;
    }

    // ── Signature électronique (matrice §8) ─────────────────────
    // Ces deux méthodes vivent ici (et non dans SignaturePolicy) car
    // Laravel route Gate::can($ability, $model) selon la classe de $model.
    // Elles opèrent sur une Collecte → doivent être dans CollectePolicy.

    /**
     * Ouvrir l'écran de signature pour cette collecte.
     *  - superadmin              : toutes
     *  - admin_reseau            : son réseau
     *  - qhse, client_signataire : leur établissement
     *  - collecteur/agent        : collecte qui leur est assignée
     */
    public function signatureOpen(User $user, Collecte $collecte): bool
    {
        if ($collecte->statut !== 'en_cours') return false;
        if ($collecte->signature()->exists()) return false;

        if ($user->isSuperAdmin()) return true;

        // collecteur / agent : uniquement la collecte qui LEUR est assignée
        // (règle plus fine que le simple périmètre réseau — priorité).
        if ($user->isCollecteur() || $user->role === 'agent') {
            return $collecte->collecteur_id !== null
                && (int) $collecte->collecteur_id === (int) $user->id;
        }

        // admin, admin_reseau, prestataire → périmètre réseau
        if ($user->isReseauScoped()) {
            return $user->canAccessTenant($collecte->etablissement_id);
        }

        if ($user->isQhse() || $user->isClientSignataire()) {
            return (int) $user->etablissement_id === (int) $collecte->etablissement_id;
        }

        return false;
    }

    /**
     * Acte de signer (créer la signature) — QHSE et client_signataire
     * de l'établissement, + superadmin pour cas exceptionnels.
     */
    public function signatureSign(User $user, Collecte $collecte): bool
    {
        if ($collecte->signature()->exists()) return false;
        if ($collecte->statut !== 'en_cours') return false;

        if ($user->isSuperAdmin()) return true;

        return ($user->isQhse() || $user->isClientSignataire())
            && (int) $user->etablissement_id === (int) $collecte->etablissement_id;
    }
}
