<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'etablissement_id','user_id','type','periode_debut','periode_fin','fichier_path','genere_at',
    ];

    protected $casts = ['periode_debut'=>'date','periode_fin'=>'date','genere_at'=>'datetime'];

    public function auteur() { return $this->belongsTo(User::class, 'user_id'); }
}
