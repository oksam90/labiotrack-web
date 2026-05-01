<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Collecte extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'etablissement_id','collecteur_id','numero_bordereau','nombre_contenants',
        'poids_declare_kg','signature_etablissement','signature_collecteur',
        'photo','statut','date_collecte','vehicule','notes',
    ];

    protected $casts = ['date_collecte' => 'datetime'];

    public function collecteur()    { return $this->belongsTo(User::class, 'collecteur_id'); }
    public function declarations()  { return $this->belongsToMany(Declaration::class, 'collecte_declarations'); }
    public function destruction()   { return $this->hasOne(Destruction::class); }
    public function signature()     { return $this->hasOne(Signature::class); }

    // ── Helpers statut ────────────────────────────────────────────
    public function isSignee(): bool   { return $this->statut === 'signee'; }
    public function isComplete(): bool { return $this->statut === 'complete'; }
    public function canBeSigned(): bool
    {
        return $this->statut === 'en_cours' && ! $this->signature()->exists();
    }
}
