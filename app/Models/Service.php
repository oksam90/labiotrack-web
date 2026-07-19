<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['etablissement_id','nom','description','responsable','actif'];
    protected $casts    = ['actif' => 'boolean'];

    // Le détail des déclarations passe désormais par declaration_lignes.
    public function declarationLignes() { return $this->hasMany(DeclarationLigne::class); }
    public function checklists()        { return $this->hasMany(Checklist::class); }

    public function scopeActif($query) { return $query->where('actif', true); }
}
