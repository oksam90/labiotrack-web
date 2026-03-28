<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'etablissement_id','service_id','user_id',
        'boites_fermees_75','sacs_correctement_etiquetes','local_ventile',
        'epi_port','sacs_noirs_non_contamines','contenants_integres',
        'score_conformite','observations','date_checklist',
    ];

    protected $casts = ['date_checklist' => 'date'];

    public function service() { return $this->belongsTo(Service::class); }
    public function agent()   { return $this->belongsTo(User::class, 'user_id'); }

    public function getNiveauAttribute(): string
    {
        if ($this->score_conformite >= 80) return 'success';
        if ($this->score_conformite >= 60) return 'warning';
        return 'danger';
    }
}
