<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Etablissement extends Model
{
    use HasFactory;

    /**
     * Applique le TenantScope aussi sur Etablissement pour que
     * l'AdminRéseau ne voie que les établissements de SON réseau.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $table = 'etablissements';

    // ✅ FIX #3 — slug et parametres manquaient dans $fillable
    protected $fillable = [
        'reseau_id','nom','type','adresse','ville','telephone',
        'email','responsable_qhse','nombre_lits','actif','logo',
        'slug','parametres',
    ];

    protected $casts = [
        'actif'      => 'boolean',
        'parametres' => 'array',  // ✅ Cast JSON → array automatique
    ];

    // ── Relations ──────────────────────────────────────────────
    public function reseau()       { return $this->belongsTo(Reseau::class); }
    public function users()        { return $this->hasMany(User::class); }
    public function services()     { return $this->hasMany(Service::class); }
    public function declarations() { return $this->hasMany(Declaration::class); }
    public function collectes()    { return $this->hasMany(Collecte::class); }
    public function checklists()   { return $this->hasMany(Checklist::class); }
    public function alertes()      { return $this->hasMany(Alerte::class); }
    public function rapports()     { return $this->hasMany(Rapport::class); }
    public function transferts()   { return $this->hasMany(Transfert::class); }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActif($query) { return $query->where('actif', true); }

    // ── Accesseurs ─────────────────────────────────────────────
    public function getTypeLabelAttribute(): string
    {
        return [
            'clinique'    => 'Clinique',
            'hopital'     => 'Hôpital',
            'cabinet'     => 'Cabinet',
            'laboratoire' => 'Laboratoire',
        ][$this->type] ?? $this->type;
    }
}
