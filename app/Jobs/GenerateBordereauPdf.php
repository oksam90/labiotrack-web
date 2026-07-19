<?php

namespace App\Jobs;

use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Génère le bordereau PDF signé après création d'une Signature.
 *
 * - Charge la collecte avec ses déclarations enrichies (service / contenant)
 * - Convertit l'image PNG de la signature en data-URL base64 pour DomPDF
 * - Persiste le PDF sur le disque privé (storage/app/private)
 * - Met à jour Signature::pdf_path et pdf_generated_at
 *
 * NOTE i18n : ce job s'exécute en queue, qui hérite du contexte serveur,
 * pas de la requête. La locale du signataire est capturée au dispatch
 * dans le constructeur, puis re-injectée dans handle() pour que les
 * traductions du PDF correspondent à la langue de l'utilisateur.
 *
 * Voir : doc/LaBioTrack_Signature_Electronique.docx — section 6
 */
class GenerateBordereauPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param  Signature  $signature
     * @param  string|null $locale  Locale active au moment du dispatch (i18n).
     *                              Fallback config('app.locale') si non fourni.
     */
    public function __construct(
        public Signature $signature,
        public ?string $locale = null,
    ) {}

    public function handle(): void
    {
        // Restaure la locale du signataire pour le rendu PDF (queue async)
        if ($this->locale) {
            app()->setLocale($this->locale);
        }

        $signature = $this->signature->fresh();
        if (! $signature) {
            Log::warning('GenerateBordereauPdf: signature introuvable', [
                'id' => $this->signature->id,
            ]);
            return;
        }

        $collecte = $signature->collecte()->withoutGlobalScopes()->firstOrFail();

        // Données dénormalisées (pattern existant des bordereaux) : une ligne
        // par ligne de déclaration (service × contenant).
        $declarations = DB::table('collecte_declarations')
            ->join('declarations', 'collecte_declarations.declaration_id', '=', 'declarations.id')
            ->join('declaration_lignes', 'declaration_lignes.declaration_id', '=', 'declarations.id')
            ->join('services', 'declaration_lignes.service_id', '=', 'services.id')
            ->join('type_contenants', 'declaration_lignes.type_contenant_id', '=', 'type_contenants.id')
            ->select(
                'declaration_lignes.nombre_contenants',
                'declaration_lignes.poids_estime_kg',
                'services.nom as service_nom',
                'type_contenants.nom as contenant_nom'
            )
            ->where('collecte_declarations.collecte_id', $collecte->id)
            ->get();

        $etablissement = DB::table('etablissements')->find($collecte->etablissement_id);

        // Image signature → data-URL pour intégration directe dans DomPDF
        $imgBase64 = base64_encode(
            Storage::disk('local')->get($signature->signature_image_path)
        );
        $signatureImg = 'data:image/png;base64,' . $imgBase64;

        $pdf = Pdf::loadView('pdf.bordereau-collecte', [
            'collecte'      => $collecte,
            'signature'     => $signature,
            'declarations'  => $declarations,
            'etablissement' => $etablissement,
            'signatureImg'  => $signatureImg,
        ]);

        $pdfPath = sprintf(
            'bordereaux/%d/collecte_%s.pdf',
            $collecte->etablissement_id,
            $collecte->numero_bordereau
        );

        Storage::disk('local')->put($pdfPath, $pdf->output());

        $signature->update([
            'pdf_path'         => $pdfPath,
            'pdf_generated_at' => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateBordereauPdf échec', [
            'signature_id' => $this->signature->id,
            'message'      => $e->getMessage(),
        ]);
    }
}
