<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * L'admin réseau dispose des droits de l'ancien admin d'établissement,
 * sur tous les établissements de SON réseau (et uniquement ceux-là).
 */
class AdminReseauDroitsTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    private $r1;
    private $etabR1;
    private $srvR1;
    private $srvR2;
    private $ar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->r1     = $this->makeReseau('R1');
        $this->etabR1 = $this->makeEtablissement($this->r1->id, 'Hôpital R1');
        $this->srvR1  = $this->makeService($this->etabR1->id, 'Bloc R1');
        $this->srvR2  = $this->makeService($this->makeEtablissement($this->makeReseau('R2')->id, 'Hôpital R2')->id, 'Bloc R2');
        $this->ar     = $this->makeUser('admin_reseau', null, $this->r1->id);
    }

    private function assertRefuse($response): void
    {
        $this->assertContains($response->status(), [403, 404], 'Action hors réseau acceptée.');
    }

    public function test_declaration_dans_son_reseau_uniquement(): void
    {
        $contenant = $this->makeContenant();

        $this->actingAs($this->ar)->post('/declarations', ['lignes' => [
            ['service_id' => $this->srvR1->id, 'type_contenant_id' => $contenant->id, 'nombre_contenants' => 2],
        ]])->assertRedirect();
        $this->assertSame($this->etabR1->id, (int) DB::table('declarations')->value('etablissement_id'));

        $this->assertRefuse($this->actingAs($this->ar)->post('/declarations', ['lignes' => [
            ['service_id' => $this->srvR2->id, 'type_contenant_id' => $contenant->id, 'nombre_contenants' => 2],
        ]]));
        $this->assertSame(1, DB::table('declarations')->count());
    }

    public function test_checklist_dans_son_reseau_uniquement(): void
    {
        $this->actingAs($this->ar)->post('/checklists', ['service_id' => $this->srvR1->id, 'local_ventile' => 1])
            ->assertRedirect(route('checklists.index'));
        $this->assertSame($this->etabR1->id, (int) DB::table('checklists')->value('etablissement_id'));

        $this->assertRefuse($this->actingAs($this->ar)->post('/checklists', ['service_id' => $this->srvR2->id]));

        // Ni service ni structure sélectionnée → message, aucune checklist orpheline
        $this->actingAs($this->ar)->post('/checklists', [])->assertSessionHas('error');
        $this->assertSame(1, DB::table('checklists')->count());
    }

    public function test_transfert_de_stockage_dans_son_reseau_uniquement(): void
    {
        $qhse = $this->makeUser('qhse', $this->etabR1->id);
        $decl = $this->makeDeclaration($this->etabR1->id, $qhse->id);

        $this->actingAs($this->ar)->post('/stockage', [
            'service_id' => $this->srvR1->id, 'declaration_ids' => [$decl->id],
        ])->assertRedirect();
        $this->assertSame($this->etabR1->id, (int) DB::table('transferts')->value('etablissement_id'));

        $this->assertRefuse($this->actingAs($this->ar)->post('/stockage', [
            'service_id' => $this->srvR2->id, 'declaration_ids' => [$decl->id],
        ]));
        $this->assertSame(1, DB::table('transferts')->count());
    }

    public function test_rapport_pour_une_structure_de_son_reseau_uniquement(): void
    {
        Storage::fake('local');
        $periode = ['type' => 'mensuel', 'periode_debut' => now()->startOfMonth()->toDateString(),
                    'periode_fin' => now()->toDateString()];

        $this->actingAs($this->ar)->post('/rapports/generer', $periode + ['etablissement_id' => $this->etabR1->id])
            ->assertRedirect();
        $this->assertSame($this->etabR1->id, (int) DB::table('rapports')->value('etablissement_id'));

        $this->actingAs($this->ar)
            ->post('/rapports/generer', $periode + ['etablissement_id' => $this->srvR2->etablissement_id])
            ->assertForbidden();
        $this->assertSame(1, DB::table('rapports')->count());
    }

    public function test_les_menus_de_l_admin_sont_visibles(): void
    {
        $page = $this->actingAs($this->ar)->get('/superadmin')->assertOk();

        foreach (['declarations.index', 'stockage.index', 'collectes.index', 'destructions.index',
                  'checklists.index', 'rapports.index', 'admin.utilisateurs.index', 'admin.services'] as $route) {
            $page->assertSee(route($route), false);
        }
    }

    /** Constat utilisateur n°1 : les 7 types plastiques dans « Type déchet » (Contenants). */
    public function test_le_formulaire_contenants_propose_les_types_plastiques(): void
    {
        $page = $this->actingAs($this->ar)->get('/admin/contenants')->assertOk();

        foreach (['PET (Polyéthylène Téréphtalate)', 'PEHD ou PE-HD (Polyéthylène Haute Densité)',
                  'PVC (Polychlorure de Vinyle)', 'PELD ou LDPE (Polyéthylène Basse Densité)',
                  'PP (Polypropylène)', 'PS (Polystyrène)',
                  'Autres plastiques (mélanges ou polycarbonate)'] as $type) {
            $page->assertSee($type);
        }
    }

    /** Constat utilisateur n°2 : rôles proposés à l'admin réseau à la création d'un compte. */
    public function test_le_formulaire_utilisateur_de_l_admin_reseau(): void
    {
        $this->actingAs($this->ar)->get('/admin/utilisateurs/create')->assertOk()
            ->assertSee('value="collecteur"', false)
            ->assertSee('value="prestataire"', false)
            ->assertDontSee('value="admin"', false)
            ->assertDontSee('Admin (établissement)');
    }
}
