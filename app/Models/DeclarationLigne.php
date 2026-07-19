<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ligne de détail d'une déclaration : 1 service producteur + 1 type de
 * contenant + 1 nombre de contenants pleins, avec son poids estimé
 * (nombre × poids_moyen_kg du contenant).
 *
 * Pas de TenantScope : l'accès est contrôlé par la déclaration parente
 * (elle-même tenant-aware via BelongsToTenant).
 */
class DeclarationLigne extends Model
{
    protected $table = 'declaration_lignes';

    protected $fillable = [
        'declaration_id',
        'service_id',
        'type_contenant_id',
        'nombre_contenants',
        'poids_estime_kg',
    ];

    protected $casts = [
        'poids_estime_kg' => 'decimal:2',
    ];

    public function declaration()   { return $this->belongsTo(Declaration::class); }
    public function service()       { return $this->belongsTo(Service::class); }
    public function typeContenant() { return $this->belongsTo(TypeContenant::class); }
}
