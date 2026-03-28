<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeDechet extends Model
{
    protected $table    = 'type_dechets';
    protected $fillable = ['nom','code','categorie','couleur_sac','description','reglementation'];

    public function contenants() { return $this->hasMany(TypeContenant::class, 'type_dechet_id'); }
}
