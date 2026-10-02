<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'etablissement_id','reseau_id','nom','prenom','email','password',
        'role','telephone','actif','last_login_at','last_login_ip','locale',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'actif'             => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function reseau()
    {
        return $this->belongsTo(Reseau::class);
    }

    public function declarations()
    {
        return $this->hasMany(Declaration::class);
    }

    // ── Accesseurs ─────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    // ── Vérifications de rôle ───────────────────────────────────
    public function isSuperAdmin()  : bool { return $this->role === 'superadmin'; }
    public function isAdminReseau() : bool { return $this->role === 'admin_reseau'; }
    public function isQhse()        : bool { return $this->role === 'qhse'; }
    public function isAgent()       : bool { return $this->role === 'agent'; }
    public function isCollecteur()  : bool { return $this->role === 'collecteur'; }
    public function isPrestataire() : bool { return $this->role === 'prestataire'; }
    public function isClientSignataire() : bool { return $this->role === 'client_signataire'; }

    /**
     * Vue GLOBALE = tous réseaux, aucun filtre. Réservé au superadmin.
     *
     * NB : collecteur et prestataire NE SONT PLUS globaux — ils sont
     * rattachés à un réseau (users.reseau_id) et cantonnés à l'ensemble
     * des établissements de CE réseau (cf. isReseauScoped()).
     */
    public function isGlobal(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Vue limitée à UN RÉSEAU (et l'ensemble de ses établissements).
     *  - admin_reseau             : administration à la maille réseau
     *  - collecteur / prestataire : opèrent sur tous les établissements
     *                               de leur réseau de rattachement
     * Le réseau est résolu via reseau_id (direct) ou etablissement.reseau_id.
     */
    public function isReseauScoped(): bool
    {
        return in_array($this->role, ['admin_reseau', 'collecteur', 'prestataire']);
    }

    /**
     * Opère sur PLUSIEURS établissements (superadmin, admin_reseau) : n'a pas
     * d'établissement implicite. Pour une saisie (déclaration, checklist,
     * transfert, rapport), l'établissement cible est déduit du service choisi
     * ou de la structure sélectionnée, puis vérifié par canAccessTenant().
     */
    public function isMultiEtablissement(): bool
    {
        return $this->isGlobal() || $this->isAdminReseau();
    }

    /**
     * Peut accéder aux sections d'administration (admin réseau, superadmin).
     */
    public function isAdminOrSuper(): bool
    {
        return in_array($this->role, ['admin_reseau', 'superadmin']);
    }

    /**
     * Tous les rôles applicatifs (ordre d'affichage des formulaires).
     * Le profil « admin » (admin d'établissement) a été supprimé : ses droits
     * sont portés par l'admin réseau (migration 2026_10_02_100001).
     */
    public const ROLES = [
        'superadmin', 'admin_reseau', 'qhse', 'agent',
        'collecteur', 'prestataire', 'client_signataire',
    ];

    /**
     * Rôles que l'utilisateur courant peut ATTRIBUER (création / modification).
     * Source unique pour le formulaire ET le contrôleur : une restriction
     * appliquée uniquement dans la vue est contournable par un POST forgé.
     */
    public function assignableRoles(): array
    {
        return match ($this->role) {
            'superadmin'   => self::ROLES,
            'admin_reseau' => ['qhse', 'agent', 'collecteur', 'prestataire', 'client_signataire'],
            default        => [],
        };
    }

    /**
     * Peut-il gérer (éditer, modifier, désactiver, anonymiser) ce compte ?
     * $target : modèle User ou ligne DB::table('users').
     *
     *  - superadmin   : tous les comptes
     *  - admin_reseau : comptes de rôle attribuable, dans SON réseau (y compris
     *                   collecteurs / prestataires rattachés à ce réseau)
     *  - soi-même     : toujours (le contrôleur fige alors rôle et rattachement)
     *
     * Fail-closed : un admin_reseau sans réseau ne gère personne d'autre que lui-même.
     */
    public function canManageUser(object $target): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ((int) $target->id === (int) $this->id) return true;
        if (! in_array($target->role, $this->assignableRoles(), true)) return false;

        if ($this->isAdminReseau()) {
            if (! $this->reseau_id) return false;
            if ((int) $target->reseau_id === (int) $this->reseau_id) return true;
            return $target->etablissement_id
                && (int) Etablissement::withoutGlobalScopes()
                    ->whereKey($target->etablissement_id)->value('reseau_id') === (int) $this->reseau_id;
        }

        return false;
    }

    /**
     * Récupère l'ID du réseau de l'utilisateur (via reseau_id direct ou
     * via etablissement.reseau_id). Retourne null si superadmin ou
     * utilisateur sans rattachement réseau.
     */
    public function getReseauIdAttribute($value)
    {
        if ($value !== null) return $value;
        if (! $this->etablissement_id) return null;

        // Sans global scope : la relation `etablissement` passe par TenantScope,
        // qui relit $user->reseau_id → récursion infinie (500 sur toutes les
        // pages pour un admin rattaché à un établissement sans reseau_id direct).
        // Mémoïsé par instance : l'accesseur est lu à chaque requête scopée.
        return once(fn () => Etablissement::withoutGlobalScopes()
            ->whereKey($this->etablissement_id)->value('reseau_id'));
    }

    /**
     * Vérifie si l'utilisateur peut accéder à un établissement donné.
     */
    public function canAccessTenant(int $etablissementId): bool
    {
        if ($this->isSuperAdmin()) return true;

        if ($this->isReseauScoped()) {
            // withoutGlobalScopes : évite que le TenantScope d'Etablissement
            // ne redéclenche canAccessTenant via le bloc "zoom session"
            // (récursion) et garantit une comparaison réseau fiable.
            $etab = Etablissement::withoutGlobalScopes()->find($etablissementId);
            return $etab && (int) $etab->reseau_id === (int) $this->reseau_id;
        }

        return $this->etablissement_id === $etablissementId;
    }

    /**
     * Applique un filtre sur une query DB::table() en respectant le
     * périmètre du rôle.
     *  - superadmin                              → aucun filtre (vue globale)
     *  - admin_reseau / collecteur / prestataire → établissements de leur réseau
     *  - qhse / agent / client_signataire        → leur établissement
     *
     * Fail-closed : un rôle réseau sans réseau, ou un rôle local sans
     * établissement, ne voit RIEN (auparavant : filtre « IS NULL »).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $col Colonne établissement à filtrer
     */
    public function filtreEtab($query, string $col = 'etablissement_id')
    {
        if ($this->isGlobal()) return $query;

        if ($this->isReseauScoped()) {
            if (! $this->reseau_id) return $query->whereRaw('1 = 0');

            return $query->whereIn($col, function ($q) {
                $q->select('id')
                  ->from('etablissements')
                  ->where('reseau_id', $this->reseau_id);
            });
        }

        if (! $this->etablissement_id) return $query->whereRaw('1 = 0');

        return $query->where($col, $this->etablissement_id);
    }

    /**
     * Restreint une query sur `users` aux comptes du périmètre :
     * superadmin → tous ; rôle réseau → comptes rattachés à son réseau
     * (directement ou via leur établissement) ; sinon → son établissement.
     */
    public function filtreUtilisateurs($query, string $table = 'users')
    {
        if ($this->isGlobal()) return $query;

        if ($this->isReseauScoped()) {
            if (! $this->reseau_id) return $query->whereRaw('1 = 0');

            return $query->where(function ($q) use ($table) {
                $q->where("$table.reseau_id", $this->reseau_id)
                  ->orWhereIn("$table.etablissement_id", function ($sub) {
                      $sub->select('id')->from('etablissements')->where('reseau_id', $this->reseau_id);
                  });
            });
        }

        return $query->where("$table.etablissement_id", $this->etablissement_id ?? 0);
    }
}
