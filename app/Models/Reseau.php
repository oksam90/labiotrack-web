<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Réseau — regroupement logique d'établissements géré par un AdminRéseau.
 *
 * Périmètre encapsulé : établissements rattachés, utilisateurs, contenants,
 * services. Le SUPERADMIN voit tous les réseaux, l'AdminRéseau ne voit
 * que le sien (via ReseauScope sur reseau_id).
 */
class Reseau extends Model
{
    use HasFactory;

    protected $table = 'reseaux';

    protected $fillable = [
        'nom', 'slug', 'description',
        'contact_email', 'contact_telephone',
        'actif', 'parametres',
    ];

    protected $casts = [
        'actif'      => 'boolean',
        'parametres' => 'array',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function etablissements()
    {
        return $this->hasMany(Etablissement::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'admin_reseau');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActif($q) { return $q->where('actif', true); }
}
