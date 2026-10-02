<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Cloisonnement entre réseaux / établissements (audit H1 à H6, M1) :
 * chaque test vérifie le refus de l'action hors périmètre ET le bon
 * fonctionnement du parcours légitime.
 */
class SecuriteCloisonnementTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    // ── H1 : établissements et services hors périmètre ───────────────────

    public function test_h1_admin_reseau_ne_modifie_que_les_etablissements_de_son_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $mien  = $this->makeEtablissement($r1->id, 'Hôpital R1');
        $autre = $this->makeEtablissement($this->makeReseau('R2')->id, 'Hôpital R2');
        $ar = $this->makeUser('admin_reseau', null, $r1->id);
        $form = ['type' => 'hopital', 'adresse' => 'Dakar'];

        $this->actingAs($ar)->get("/admin/{$autre->id}/edit")->assertForbidden();
        $this->actingAs($ar)->put("/admin/{$autre->id}", $form + ['nom' => 'PIRATÉ'])->assertForbidden();
        $this->assertSame('Hôpital R2', DB::table('etablissements')->find($autre->id)->nom);

        $this->actingAs($ar)->put("/admin/{$mien->id}", $form + ['nom' => 'Renommé'])->assertRedirect();
        $this->assertSame('Renommé', DB::table('etablissements')->find($mien->id)->nom);
    }

    public function test_h1_admin_reseau_sans_reseau_ne_cree_pas_d_etablissement(): void
    {
        $ar = $this->makeUser('admin_reseau');

        $this->actingAs($ar)->post('/admin', [
            'nom' => 'Orphelin', 'type' => 'clinique', 'adresse' => 'X', 'reseau_id' => $this->makeReseau()->id,
        ])->assertForbidden();
        $this->assertNull(DB::table('etablissements')->where('nom', 'Orphelin')->first());
    }

    public function test_h1_admin_reseau_ne_gere_que_les_services_de_son_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $srvMien  = $this->makeService($this->makeEtablissement($r1->id)->id, 'Bloc R1');
        $srvAutre = $this->makeService($this->makeEtablissement($this->makeReseau('R2')->id)->id, 'Bloc R2');
        $ar = $this->makeUser('admin_reseau', null, $r1->id);

        $this->actingAs($ar)->put("/admin/services/{$srvAutre->id}", ['nom' => 'PIRATÉ'])->assertForbidden();
        $this->actingAs($ar)->post("/admin/services/{$srvAutre->id}/toggle")->assertForbidden();
        $this->actingAs($ar)->delete("/admin/services/{$srvAutre->id}")->assertForbidden();
        $row = DB::table('services')->find($srvAutre->id);
        $this->assertSame('Bloc R2', $row->nom);
        $this->assertSame(1, (int) $row->actif);

        $this->actingAs($ar)->put("/admin/services/{$srvMien->id}", ['nom' => 'Bloc renommé'])->assertRedirect();
        $this->assertSame('Bloc renommé', DB::table('services')->find($srvMien->id)->nom);
    }

    // ── H2 : XSS dans le flux d'activités ────────────────────────────────

    public function test_h2_le_flux_d_activites_echappe_les_textes(): void
    {
        $page = $this->actingAs($this->makeUser('superadmin'))->get('/admin/activites')->assertOk();

        foreach (['description', 'acteur', 'etablissement'] as $champ) {
            $page->assertSee('${esc(item.' . $champ . ')}', false);
            $page->assertDontSee('${item.' . $champ . '}', false);
        }
    }

    // ── H3 : stockage ────────────────────────────────────────────────────

    public function test_h3_transfert_refuse_les_declarations_d_un_autre_etablissement(): void
    {
        $etabA = $this->makeEtablissement($this->makeReseau()->id, 'A');
        $etabB = $this->makeEtablissement($this->makeReseau('R2')->id, 'B');
        $agentA = $this->makeUser('agent', $etabA->id);
        $srvA   = $this->makeService($etabA->id);
        $declA  = $this->makeDeclaration($etabA->id, $agentA->id);
        $declB  = $this->makeDeclaration($etabB->id, $this->makeUser('qhse', $etabB->id)->id);
        $declCollectee = $this->makeDeclaration($etabA->id, $agentA->id, 'en_transport');

        foreach ([[$declA->id, $declB->id], [$declCollectee->id]] as $ids) {
            $this->actingAs($agentA)->post('/stockage', ['service_id' => $srvA->id, 'declaration_ids' => $ids])
                ->assertSessionHasErrors('declaration_ids');
        }
        $this->assertSame(0, DB::table('transferts')->count());

        $this->actingAs($agentA)->post('/stockage', ['service_id' => $srvA->id, 'declaration_ids' => [$declA->id]])
            ->assertRedirect();
        $this->assertSame(1, DB::table('transfert_declarations')->where('declaration_id', $declA->id)->count());
    }

    public function test_h3_un_collecteur_ne_cree_pas_de_transfert(): void
    {
        $r    = $this->makeReseau();
        $etab = $this->makeEtablissement($r->id);
        $decl = $this->makeDeclaration($etab->id, $this->makeUser('qhse', $etab->id)->id);

        $this->actingAs($this->makeUser('collecteur', null, $r->id))->post('/stockage', [
            'service_id' => $this->makeService($etab->id)->id, 'declaration_ids' => [$decl->id],
        ])->assertForbidden();
    }

    // ── H4 : cache de l'analyse financière ───────────────────────────────

    public function test_h4_analyse_financiere_cloisonnee_par_reseau_malgre_le_cache(): void
    {
        $r1 = $this->makeReseau('R1');
        $r2 = $this->makeReseau('R2');
        $etabR2 = $this->makeEtablissement($r2->id, 'B');
        $decl = $this->makeDeclaration($etabR2->id, $this->makeUser('qhse', $etabR2->id)->id);
        $decl->lignes()->create(['service_id' => $this->makeService($etabR2->id, 'Bloc Op R2')->id,
            'type_contenant_id' => $this->makeContenant()->id, 'nombre_contenants' => 3, 'poids_estime_kg' => 6]);

        // Le superadmin remplit le cache en premier
        $this->actingAs($this->makeUser('superadmin'))->get('/rapports/analyse-financiere')
            ->assertOk()->assertSee('Bloc Op R2');

        $this->actingAs($this->makeUser('prestataire', null, $r1->id))->get('/rapports/analyse-financiere')
            ->assertOk()->assertDontSee('Bloc Op R2');

        // Parcours légitime : l'admin du réseau R2 voit bien les coûts de son réseau
        $this->actingAs($this->makeUser('admin_reseau', null, $r2->id))->get('/rapports/analyse-financiere')
            ->assertOk()->assertSee('Bloc Op R2');
    }

    // ── H5 : compte désactivé ────────────────────────────────────────────

    public function test_h5_un_compte_desactive_est_deconnecte_immediatement(): void
    {
        $qhse = $this->makeUser('qhse', $this->makeEtablissement($this->makeReseau()->id)->id);
        $this->actingAs($qhse)->get('/declarations')->assertOk();

        DB::table('users')->where('id', $qhse->id)->update(['actif' => 0]);
        $qhse->refresh();

        $this->actingAs($qhse)->get('/declarations')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_h5_un_compte_anonymise_est_deconnecte(): void
    {
        $agent = $this->makeUser('agent', $this->makeEtablissement($this->makeReseau()->id)->id);
        DB::table('users')->where('id', $agent->id)->update(['anonymized_at' => now()]);
        $agent->refresh();

        $this->actingAs($agent)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ── H6 : flux d'activités ────────────────────────────────────────────

    public function test_h6_le_flux_d_activites_est_limite_au_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $etabR1 = $this->makeEtablissement($r1->id, 'Hôpital Visible');
        $etabR2 = $this->makeEtablissement($this->makeReseau('R2')->id, 'Clinique Confidentielle');

        foreach ([$etabR1, $etabR2] as $etab) {
            $qhse = $this->makeUser('qhse', $etab->id);
            $this->actingAs($qhse);
            $this->makeDeclaration($etab->id, $qhse->id); // observer → activites_log
        }

        $this->actingAs($this->makeUser('admin_reseau', null, $r1->id))->getJson('/admin/activites/data')
            ->assertOk()
            ->assertJsonFragment(['etablissement' => 'Hôpital Visible'])
            ->assertJsonMissing(['etablissement' => 'Clinique Confidentielle'])
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('stats.declarations_today', 1);

        $this->actingAs($this->makeUser('superadmin'))->getJson('/admin/activites/data')
            ->assertOk()
            ->assertJsonFragment(['etablissement' => 'Clinique Confidentielle'])
            ->assertJsonPath('pagination.total', 2);
    }

    // ── M1 : création de déclarations ────────────────────────────────────

    public function test_m1_seuls_les_roles_habilites_creent_une_declaration(): void
    {
        $r    = $this->makeReseau();
        $etab = $this->makeEtablissement($r->id);
        $srv  = $this->makeService($etab->id);
        $ligne = ['lignes' => [['service_id' => $srv->id, 'type_contenant_id' => $this->makeContenant()->id, 'nombre_contenants' => 1]]];

        foreach ([
            $this->makeUser('client_signataire', $etab->id),
            $this->makeUser('collecteur', null, $r->id),
            $this->makeUser('prestataire', null, $r->id),
        ] as $u) {
            $this->actingAs($u)->get('/declarations/create')->assertForbidden();
            $this->actingAs($u)->post('/declarations', $ligne)->assertForbidden();
        }
        $this->assertSame(0, DB::table('declarations')->count());

        $this->actingAs($this->makeUser('agent', $etab->id))->post('/declarations', $ligne)->assertRedirect();
        $this->assertSame(1, DB::table('declarations')->count());
    }

    public function test_m1_le_lien_nouvelle_declaration_est_masque_sans_droit(): void
    {
        $r = $this->makeReseau();
        $this->makeEtablissement($r->id);

        $this->actingAs($this->makeUser('prestataire', null, $r->id))->get('/declarations')
            ->assertOk()
            ->assertDontSee(route('declarations.create'), false);
    }
}
