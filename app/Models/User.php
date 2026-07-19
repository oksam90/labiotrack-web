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
    public function isAdmin()       : bool { return in_array($this->role, ['admin', 'superadmin']); }
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
     *  - admin_reseau / admin     : administration à la maille réseau
     *  - collecteur / prestataire : opèrent sur tous les établissements
     *                               de leur réseau de rattachement
     * Le réseau est résolu via reseau_id (direct) ou etablissement.reseau_id.
     */
    public function isReseauScoped(): bool
    {
        return in_array($this->role, ['admin_reseau', 'admin', 'collecteur', 'prestataire']);
    }

    /**
     * Peut accéder aux sections d'administration (admin local, admin réseau,
     * superadmin).
     */
    public function isAdminOrSuper(): bool
    {
        return in_array($this->role, ['admin', 'admin_reseau', 'superadmin']);
    }

    /**
     * Récupère l'ID du réseau de l'utilisateur (via reseau_id direct ou
     * via etablissement.reseau_id). Retourne null si superadmin ou
     * utilisateur sans rattachement réseau.
     */
    public function getReseauIdAttribute($value)
    {
        if ($value !== null) return $value;
        return $this->etablissement?->reseau_id;
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
     *  - superadmin / collecteur / prestataire → aucun filtre (vue globale)
     *  - admin_reseau / admin → filtré par les établissements de leur réseau
     *  - qhse / agent → filtré par leur établissement
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $col Colonne établissement à filtrer
     */
    public function filtreEtab($query, string $col = 'etablissement_id')
    {
        if ($this->isGlobal()) return $query;

        if ($this->isReseauScoped() && $this->reseau_id) {
            return $query->whereIn($col, function ($q) {
                $q->select('id')
                  ->from('etablissements')
                  ->where('reseau_id', $this->reseau_id);
            });
        }

        return $query->where($col, $this->etablissement_id);
    }
}
