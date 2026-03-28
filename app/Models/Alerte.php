<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'etablissement_id','service_id','type','niveau','message','lu','lu_par','lu_at',
    ];

    protected $casts = ['lu' => 'boolean', 'lu_at' => 'datetime'];

    // ── Relations ──────────────────────────────────────────
    // etablissement() est fourni par BelongsToTenant mais on le rend explicite
    public function etablissement() { return $this->belongsTo(Etablissement::class); }
    public function service()       { return $this->belongsTo(Service::class); }
    public function luPar()         { return $this->belongsTo(User::class, 'lu_par'); }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeNonLues($q)          { return $q->where('lu', false); }
    public function scopeNiveau($q, $niveau)  { return $q->where('niveau', $niveau); }
    public function scopeDanger($q)           { return $q->where('niveau', 'danger'); }
}
