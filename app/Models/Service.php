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

    public function declarations() { return $this->hasMany(Declaration::class); }
    public function checklists()   { return $this->hasMany(Checklist::class); }

    public function scopeActif($query) { return $query->where('actif', true); }
}
