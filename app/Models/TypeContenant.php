<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeContenant extends Model
{
    protected $table    = 'type_contenants';
    protected $fillable = ['nom','code','type_dechet_id','poids_moyen_kg','capacite_litres','cout_unitaire','description'];
    protected $casts    = ['cout_unitaire'=>'decimal:2','poids_moyen_kg'=>'decimal:2'];

    public function typeDechet()        { return $this->belongsTo(TypeDechet::class, 'type_dechet_id'); }
    // Le détail des déclarations passe désormais par declaration_lignes.
    public function declarationLignes() { return $this->hasMany(DeclarationLigne::class); }
}
