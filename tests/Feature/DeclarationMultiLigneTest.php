<?php

namespace Tests\Feature;

use App\Models\Declaration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Parcours déclaration multi-lignes : plusieurs services × contenants, poids
 * estimé calculé par ligne (nombre × poids_moyen), totaux d'en-tête agrégés.
 */
class DeclarationMultiLigneTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    public function test_un_agent_cree_une_declaration_multi_lignes_avec_poids_calcule_par_ligne(): void
    {
        Storage::fake('public'); // isole le QR code généré

        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $agent = $this->makeUser('agent', $etab->id);

        $svc1 = $this->makeService($etab->id, 'Bloc');
        $svc2 = $this->makeService($etab->id, 'Urgences');
        $tcA  = $this->makeContenant(poids: 0.80, cout: 1200, code: 'B5L');
        $tcB  = $this->makeContenant(poids: 4.00, cout: 650, code: 'SJ50');

        $response = $this->actingAs($agent)->post('/declarations', [
            'lignes' => [
                ['service_id' => $svc1->id, 'type_contenant_id' => $tcA->id, 'nombre_contenants' => 3],
                ['service_id' => $svc2->id, 'type_contenant_id' => $tcB->id, 'nombre_contenants' => 5],
            ],
            'notes' => 'Test multi-lignes',
        ]);

        $response->assertRedirect();

        $decl = Declaration::withoutGlobalScopes()->with('lignes')->latest('id')->first();

        $this->assertNotNull($decl);
        $this->assertSame($etab->id, (int) $decl->etablissement_id, 'RBAC : établissement de l’agent');
        $this->assertCount(2, $decl->lignes);
        $this->assertSame(8, (int) $decl->nombre_contenants);          // 3 + 5
        $this->assertEquals(22.40, (float) $decl->poids_estime_kg);    // 0.8*3 + 4*5

        $ligneA = $decl->lignes->firstWhere('type_contenant_id', $tcA->id);
        $ligneB = $decl->lignes->firstWhere('type_contenant_id', $tcB->id);
        $this->assertEquals(2.40, (float) $ligneA->poids_estime_kg);   // 0.8 * 3
        $this->assertEquals(20.00, (float) $ligneB->poids_estime_kg);  // 4 * 5
    }

    public function test_les_lignes_en_double_service_contenant_sont_fusionnees(): void
    {
        Storage::fake('public');

        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $agent = $this->makeUser('agent', $etab->id);
        $svc   = $this->makeService($etab->id, 'Bloc');
        $tc    = $this->makeContenant(poids: 1.00, cout: 500, code: 'B1L');

        // Deux lignes IDENTIQUES (même service + même contenant) → doivent fusionner.
        $this->actingAs($agent)->post('/declarations', [
            'lignes' => [
                ['service_id' => $svc->id, 'type_contenant_id' => $tc->id, 'nombre_contenants' => 4],
                ['service_id' => $svc->id, 'type_contenant_id' => $tc->id, 'nombre_contenants' => 6],
            ],
        ])->assertRedirect();

        $decl = Declaration::withoutGlobalScopes()->with('lignes')->latest('id')->first();

        $this->assertCount(1, $decl->lignes, 'Les lignes en double doivent être fusionnées en une seule.');
        $this->assertSame(10, (int) $decl->lignes->first()->nombre_contenants); // 4 + 6
        $this->assertEquals(10.00, (float) $decl->poids_estime_kg);              // 1.0 * 10
    }

    public function test_une_declaration_sans_ligne_est_refusee(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $agent = $this->makeUser('agent', $etab->id);

        $response = $this->actingAs($agent)->post('/declarations', ['lignes' => []]);

        $response->assertSessionHasErrors('lignes');
        $this->assertSame(0, Declaration::withoutGlobalScopes()->count());
    }
}
