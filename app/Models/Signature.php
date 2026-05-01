<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Signature électronique d'un bordereau de collecte.
 *
 * Capture l'image dessinée par le client signataire (PNG base64) et
 * conserve la preuve numérique : hash SHA-256, IP, user-agent, device,
 * horodatage serveur, identités du signataire et de l'agent témoin.
 *
 * Voir : doc/LaBioTrack_Signature_Electronique.docx
 */
class Signature extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'collecte_id',
        'etablissement_id',
        'signataire_user_id',
        'agent_user_id',
        'signataire_nom',
        'signataire_fonction',
        'signature_image_path',
        'signature_hash',
        'commentaire',
        'ip_address',
        'user_agent',
        'device_info',
        'signed_at',
        'pdf_generated_at',
        'pdf_path',
        'revoked_at',
        'revocation_reason',
        'revoked_by_user_id',
    ];

    protected $casts = [
        'device_info'      => 'array',
        'signed_at'        => 'datetime',
        'pdf_generated_at' => 'datetime',
        'revoked_at'       => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function collecte()
    {
        return $this->belongsTo(Collecte::class);
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function signataire()
    {
        return $this->belongsTo(User::class, 'signataire_user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function pdfReady(): bool
    {
        return $this->pdf_generated_at !== null && $this->pdf_path !== null;
    }

    /** Hash tronqué pour affichage rapide (8 premiers caractères). */
    public function getHashShortAttribute(): string
    {
        return substr($this->signature_hash, 0, 8);
    }
}
