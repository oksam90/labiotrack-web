<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'etablissement_id','nom','prenom','email','password',
        'role','telephone','actif','last_login_at','last_login_ip',
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
    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin()     : bool { return in_array($this->role, ['admin', 'superadmin']); }
    public function isQhse()      : bool { return $this->role === 'qhse'; }
    public function isAgent()     : bool { return $this->role === 'agent'; }
    public function isCollecteur(): bool { return $this->role === 'collecteur'; }
    public function isPrestataire():bool { return $this->role === 'prestataire'; }

    /**
     * Retourne true si l'utilisateur a une vue GLOBALE (toutes structures).
     *
     * Selon les specs : admin, superadmin, collecteur et prestataire
     * sont "sans structure fixe" (etablissement_id = NULL en base) et
     * opèrent sur l'ensemble du réseau.
     */
    public function isGlobal(): bool
    {
        return in_array($this->role, ['admin', 'superadmin', 'collecteur', 'prestataire']);
    }

    /**
     * Retourne true si l'utilisateur est admin/superadmin
     * (peut accéder aux sections d'administration).
     */
    public function isAdminOrSuper(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Vérifie si l'utilisateur peut accéder à un établissement donné.
     */
    public function canAccessTenant(int $etablissementId): bool
    {
        if ($this->isGlobal()) return true;
        return $this->etablissement_id === $etablissementId;
    }

    /**
     * Applique un filtre etablissement_id sur une query DB::table().
     * Les utilisateurs globaux (admin, superadmin, collecteur, prestataire)
     * → aucun filtre. Les autres → filtrés par leur établissement.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $col Colonne à filtrer
     */
    public function filtreEtab($query, string $col = 'etablissement_id')
    {
        if ($this->isGlobal()) return $query;
        return $query->where($col, $this->etablissement_id);
    }
}
