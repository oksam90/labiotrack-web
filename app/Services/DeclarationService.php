<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Declaration;
use App\Models\Service;
use App\Models\TypeContenant;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Facades\DB;

/**
 * Logique métier des déclarations multi-lignes (services × contenants).
 *
 * Centralise ce qui était dupliqué entre DeclarationController::store() et
 * update() : normalisation/fusion des lignes, calcul du poids par ligne
 * (nombre × poids_moyen), agrégation des totaux d'en-tête, persistance
 * transactionnelle et détection d'anomalie de volume.
 */
class DeclarationService
{
    /**
     * Crée une déclaration + ses lignes dans une transaction, puis lève les
     * éventuelles alertes de volume. Retourne l'en-tête créé.
     *
     * @param array<int,array{service_id:int,type_contenant_id:int,nombre_contenants:int}> $lignes
     */
    public function create(User $user, array $lignes, ?string $notes, ?string $photoPath): Declaration
    {
        [$lignesData, $totalNombre, $totalPoids] = $this->buildLignes($lignes);

        // Établissement : celui du 1er service pour un rôle global, sinon celui
        // de l'utilisateur (qhse/agent). Les lignes ont déjà été normalisées.
        $premierService = Service::findOrFail($lignesData[0]['service_id']);
        $etabId = $user->isGlobal() ? $premierService->etablissement_id : $user->etablissement_id;

        $decl = DB::transaction(function () use ($etabId, $user, $totalNombre, $totalPoids, $notes, $photoPath, $lignesData) {
            $decl = Declaration::withoutGlobalScope(TenantScope::class)->create([
                'etablissement_id'  => $etabId,
                'user_id'           => $user->id,
                'nombre_contenants' => $totalNombre,
                'poids_estime_kg'   => $totalPoids,
                'statut'            => 'en_stock',
                'notes'             => $notes,
                'photo'             => $photoPath,
                'date_declaration'  => now()->toDateString(),
                'heure_declaration' => now()->toTimeString(),
            ]);

            foreach ($lignesData as $l) {
                $decl->lignes()->create($l);
            }

            return $decl;
        });

        $this->detecterAnomalies($etabId, $lignesData);

        return $decl;
    }

    /**
     * Remplace les lignes d'une déclaration existante et recalcule les totaux.
     *
     * @param array<int,array{service_id:int,type_contenant_id:int,nombre_contenants:int}> $lignes
     */
    public function update(Declaration $declaration, array $lignes, ?string $notes): Declaration
    {
        [$lignesData, $totalNombre, $totalPoids] = $this->buildLignes($lignes);

        DB::transaction(function () use ($declaration, $lignesData, $totalNombre, $totalPoids, $notes) {
            $declaration->lignes()->delete();
            foreach ($lignesData as $l) {
                $declaration->lignes()->create($l);
            }
            $declaration->update([
                'nombre_contenants' => $totalNombre,
                'poids_estime_kg'   => $totalPoids,
                'notes'             => $notes,
            ]);
        });

        return $declaration->refresh();
    }

    /**
     * Normalise les lignes saisies :
     *  - fusionne les doublons (même service + même contenant) en sommant les nombres ;
     *  - calcule le poids de chaque ligne (nombre × poids_moyen du contenant) ;
     *  - renvoie [lignesData, totalNombre, totalPoids].
     *
     * @return array{0: array<int,array>, 1: int, 2: float}
     */
    private function buildLignes(array $lignes): array
    {
        // Fusion des doublons service×contenant.
        $merged = [];
        foreach ($lignes as $l) {
            $key = $l['service_id'] . ':' . $l['type_contenant_id'];
            $merged[$key] = ($merged[$key] ?? 0) + (int) $l['nombre_contenants'];
        }

        $contenants = TypeContenant::whereIn('id', collect(array_keys($merged))
            ->map(fn ($k) => (int) explode(':', $k)[1])->unique())
            ->get()->keyBy('id');

        $lignesData  = [];
        $totalNombre = 0;
        $totalPoids  = 0.0;
        foreach ($merged as $key => $nombre) {
            [$serviceId, $contenantId] = array_map('intval', explode(':', $key));
            $poidsMoyen = (float) ($contenants[$contenantId]->poids_moyen_kg ?? 0);
            $poidsLigne = $poidsMoyen * $nombre;
            $totalNombre += $nombre;
            $totalPoids  += $poidsLigne;
            $lignesData[] = [
                'service_id'        => $serviceId,
                'type_contenant_id' => $contenantId,
                'nombre_contenants' => $nombre,
                'poids_estime_kg'   => $poidsLigne,
            ];
        }

        return [$lignesData, $totalNombre, $totalPoids];
    }

    /**
     * Détection d'anomalie de volume, agrégée par service (poids > 1.5× la
     * moyenne des lignes du service sur 3 mois).
     */
    private function detecterAnomalies(int $etabId, array $lignesData): void
    {
        $poidsParService = collect($lignesData)
            ->groupBy('service_id')
            ->map(fn ($lignes) => $lignes->sum('poids_estime_kg'));

        foreach ($poidsParService as $serviceId => $poids) {
            $moyenne = DB::table('declaration_lignes')
                ->join('declarations', 'declaration_lignes.declaration_id', '=', 'declarations.id')
                ->where('declarations.etablissement_id', $etabId)
                ->where('declaration_lignes.service_id', $serviceId)
                ->where('declarations.date_declaration', '>=', now()->subMonths(3))
                ->avg('declaration_lignes.poids_estime_kg');

            if ($moyenne && $poids > $moyenne * 1.5) {
                $service = Service::find($serviceId);
                if ($service) {
                    Alerte::withoutGlobalScope(TenantScope::class)->create([
                        'etablissement_id' => $etabId,
                        'service_id'       => $serviceId,
                        'type'             => 'volume_anormal',
                        'niveau'           => 'warning',
                        'message'          => "Volume anormal au service «{$service->nom}» : {$poids} kg (moyenne: " . round($moyenne, 1) . " kg).",
                    ]);
                }
            }
        }
    }
}
