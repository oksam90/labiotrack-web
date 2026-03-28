<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Destruction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'collecte_id','prestataire_id','etablissement_id','poids_reel_kg','methode',
        'site_traitement','certificat_numero','certificat_path',
        'date_reception','date_destruction','conforme','notes',
    ];
    protected $casts = [
        'date_reception'   => 'datetime',
        'date_destruction' => 'datetime',
        'conforme'         => 'boolean',
    ];

    public function collecte()    { return $this->belongsTo(Collecte::class); }
    public function prestataire() { return $this->belongsTo(User::class, 'prestataire_id'); }

    public function getMethodeLabelAttribute(): string
    {
        return [
            'incineration'         => 'Incinération haute température',
            'autoclave'            => 'Autoclave / Stérilisation',
            'desinfection_chimique'=> 'Désinfection chimique',
            'autre'                => 'Autre méthode homologuée',
        ][$this->methode] ?? $this->methode;
    }
}
