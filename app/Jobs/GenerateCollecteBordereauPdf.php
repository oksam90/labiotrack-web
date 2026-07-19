<?php

namespace App\Jobs;

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
 * Génère le bordereau PDF (non signé) d'une collecte de façon asynchrone,
 * le stocke sur le disque public et renseigne collectes.bordereau_pdf_path /
 * bordereau_generated_at. Remplace la génération synchrone bloquante.
 *
 * NOTE : avec QUEUE_CONNECTION=sync (défaut local), s'exécute inline. En prod,
 * basculer QUEUE_CONNECTION=database + lancer un worker pour l'offload réel.
 */
class GenerateCollecteBordereauPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public int $collecteId,
        public ?string $locale = null,
    ) {}

    public function handle(): void
    {
        if ($this->locale) {
            app()->setLocale($this->locale);
        }

        $collecte = DB::table('collectes')->where('id', $this->collecteId)->first();
        if (! $collecte) {
            Log::warning('GenerateCollecteBordereauPdf: collecte introuvable', ['id' => $this->collecteId]);
            return;
        }

        // Une ligne de bordereau par ligne de déclaration (service × contenant).
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

        $pdf  = Pdf::loadView('collectes.bordereau_pdf', compact('collecte', 'declarations', 'etablissement'));
        $path = sprintf('bordereaux/%d/bordereau_%s.pdf', $collecte->etablissement_id, $collecte->numero_bordereau);

        Storage::disk('public')->put($path, $pdf->output());

        DB::table('collectes')->where('id', $collecte->id)->update([
            'bordereau_pdf_path'     => $path,
            'bordereau_generated_at' => now(),
            'updated_at'             => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateCollecteBordereauPdf échec', [
            'collecte_id' => $this->collecteId,
            'message'     => $e->getMessage(),
        ]);
    }
}
