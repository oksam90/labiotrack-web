<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Declaration extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'etablissement_id','user_id',
        'nombre_contenants','poids_estime_kg','poids_reel_kg','statut',
        'notes','photo','qr_code','date_declaration','heure_declaration',
    ];

    protected $casts = ['date_declaration' => 'date'];

    // ── Relations ──────────────────────────────────────────
    // Le détail (service × contenant) vit désormais dans declaration_lignes.
    public function lignes() { return $this->hasMany(DeclarationLigne::class); }
    public function user()   { return $this->belongsTo(User::class); }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeEnStock($query)     { return $query->where('statut', 'en_stock'); }
    public function scopeEnTransport($query) { return $query->where('statut', 'en_transport'); }
    public function scopeDuMois($query, ?string $mois = null)
    {
        $mois ??= now()->format('Y-m');
        return $query->whereRaw("DATE_FORMAT(date_declaration,'%Y-%m') = ?", [$mois]);
    }

    // ── Accesseurs ─────────────────────────────────────────
    public function getStatutLabelAttribute(): string
    {
        return ['en_stock'=>'En stock','en_transport'=>'En transport','detruit'=>'Détruit','annule'=>'Annulé'][$this->statut] ?? $this->statut;
    }
    public function getStatutColorAttribute(): string
    {
        return ['en_stock'=>'warning','en_transport'=>'info','detruit'=>'secondary','annule'=>'danger'][$this->statut] ?? 'secondary';
    }
}
