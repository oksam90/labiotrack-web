<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'etablissement_id','service_id','user_id','responsable_stockage_id',
        'nombre_contenants','poids_total_kg','zone_stockage','date_limite_collecte',
        'statut','date_transfert','notes',
    ];

    protected $casts = ['date_limite_collecte' => 'date', 'date_transfert' => 'datetime'];

    public function service()             { return $this->belongsTo(Service::class); }
    public function agent()               { return $this->belongsTo(User::class, 'user_id'); }
    public function declarations()        { return $this->belongsToMany(Declaration::class, 'transfert_declarations'); }
    public function responsableStockage() { return $this->belongsTo(\App\Models\User::class, 'responsable_stockage_id'); }

    public function getJoursRestantsAttribute(): int
    {
        if (!$this->date_limite_collecte) return 999;
        return (int) now()->startOfDay()->diffInDays($this->date_limite_collecte->startOfDay(), false);
    }
}
