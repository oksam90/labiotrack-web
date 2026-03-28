<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiviteLog extends Model
{
    protected $table = 'activites_log';

    protected $fillable = [
        'type',
        'action',
        'subject_id',
        'subject_type',
        'moment',
        'acteur',
        'etablissement',
        'description',
        'niveau',
        'user_id',
        'etablissement_id',
        'metadata',
    ];

    protected $casts = [
        'moment'   => 'datetime',
        'metadata' => 'array',
    ];

    /* ── Relations ─────────────────────────────────────── */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function etablissementRelation()
    {
        return $this->belongsTo(Etablissement::class, 'etablissement_id');
    }

    /**
     * Relation polymorphe vers l'objet source (Declaration, Collecte, etc.)
     */
    public function subject()
    {
        return $this->morphTo('subject');
    }

    /* ── Scopes pratiques ──────────────────────────────── */

    public function scopeForTenant($query, int $etablissementId)
    {
        return $query->where('etablissement_id', $etablissementId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, int $limit = 60)
    {
        return $query->orderByDesc('moment')->limit($limit);
    }

    /* ── Helper statique pour logger une activité ──────── */

    public static function log(
        Model  $subject,
        string $action,
        string $description,
        string $niveau = 'info',
        ?array $metadata = null
    ): self {
        $user = auth()->user();

        // Résoudre le type lisible
        $typeMap = [
            Declaration::class => 'declaration',
            Collecte::class    => 'collecte',
            Checklist::class   => 'checklist',
            Alerte::class      => 'alerte',
            Destruction::class => 'destruction',
        ];

        $type = $typeMap[get_class($subject)] ?? strtolower(class_basename($subject));

        // Résoudre l'établissement
        $etabId  = $subject->etablissement_id ?? null;
        $etabNom = null;
        if ($etabId) {
            $etabNom = Etablissement::find($etabId)?->nom;
        }

        return self::create([
            'type'              => $type,
            'action'            => $action,
            'subject_id'        => $subject->getKey(),
            'subject_type'      => get_class($subject),
            'moment'            => $subject->created_at ?? now(),
            'acteur'            => $user ? trim($user->prenom . ' ' . $user->nom) : 'Système',
            'etablissement'     => $etabNom,
            'description'       => $description,
            'niveau'            => $niveau,
            'user_id'           => $user?->id,
            'etablissement_id'  => $etabId,
            'metadata'          => $metadata,
        ]);
    }
}
