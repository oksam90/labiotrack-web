<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Intégrité métier et durcissements (audit M2, M3, M6, L1, L2, CSP) :
 * refus des cas anormaux ET parcours légitime préservé.
 */
class SecuriteIntegriteTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    // ── M2 : services d'un autre établissement dans une déclaration ─────

    public function test_m2_une_declaration_ne_melange_pas_les_services_de_plusieurs_etablissements(): void
    {
        $etabA = $this->makeEtablissement($this->makeReseau()->id, 'A');
        $etabB = $this->makeEtablissement($this->makeReseau('R2')->id, 'B');
        $qhse  = $this->makeUser('qhse', $etabA->id);
        $c     = $this->makeContenant()->id;
        $srvA  = $this->makeService($etabA->id)->id;
        $srvB  = $this->makeService($etabB->id)->id;

        $this->actingAs($qhse)->post('/declarations', ['lignes' => [
            ['service_id' => $srvA, 'type_contenant_id' => $c, 'nombre_contenants' => 1],
            ['service_id' => $srvB, 'type_contenant_id' => $c, 'nombre_contenants' => 1],
        ]])->assertSessionHasErrors('lignes');
        $this->assertSame(0, DB::table('declarations')->count());

        $this->actingAs($qhse)->post('/declarations', ['lignes' => [
            ['service_id' => $srvA, 'type_contenant_id' => $c, 'nombre_contenants' => 2],
        ]])->assertRedirect();
        $this->assertSame(1, DB::table('declarations')->count());
    }

    // ── M3 : machines à états collecte / destruction ─────────────────────

    private function contexteCollecte(): array
    {
        $r    = $this->makeReseau();
        $etab = $this->makeEtablissement($r->id, 'A');
        $qhse = $this->makeUser('qhse', $etab->id);
        return [$r, $etab, $qhse];
    }

    public function test_m3_une_declaration_deja_collectee_ne_repart_pas_dans_une_collecte(): void
    {
        [, $etab, $qhse] = $this->contexteCollecte();
        $decl = $this->makeDeclaration($etab->id, $qhse->id);

        $this->actingAs($qhse)->post('/collectes', ['declarations' => [$decl->id]])->assertRedirect();
        $this->assertSame('en_transport', DB::table('declarations')->find($decl->id)->statut);

        $this->actingAs($qhse)->post('/collectes', ['declarations' => [$decl->id]])
            ->assertSessionHasErrors('declarations');
        $this->assertSame(1, DB::table('collectes')->count());
    }

    public function test_m3_une_collecte_ne_regroupe_qu_un_etablissement(): void
    {
        $r  = $this->makeReseau();
        $a  = $this->makeEtablissement($r->id, 'A');
        $b  = $this->makeEtablissement($r->id, 'B');
        $ar = $this->makeUser('admin_reseau', null, $r->id);
        $dA = $this->makeDeclaration($a->id, $this->makeUser('qhse', $a->id)->id);
        $dB = $this->makeDeclaration($b->id, $this->makeUser('qhse', $b->id)->id);

        $this->actingAs($ar)->post('/collectes', ['declarations' => [$dA->id, $dB->id]])
            ->assertSessionHasErrors('declarations');
        $this->assertSame(0, DB::table('collectes')->count());

        $this->actingAs($ar)->post('/collectes', ['declarations' => [$dB->id]])->assertRedirect();
        $this->assertSame($b->id, (int) DB::table('collectes')->value('etablissement_id'));
    }

    public function test_m3_une_collecte_n_est_detruite_qu_une_fois(): void
    {
        Storage::fake('local');
        [$r, $etab] = $this->contexteCollecte();
        $presta   = $this->makeUser('prestataire', null, $r->id);
        $collecte = $this->makeCollecte($etab->id, 'signee');
        $form = ['collecte_id' => $collecte->id, 'poids_reel_kg' => 2, 'methode' => 'incineration',
                 'date_destruction' => now()->toDateString()];

        $this->actingAs($presta)->post('/destructions', $form)->assertRedirect();
        $premiere = DB::table('destructions')->value('id');

        $this->actingAs($presta)->post('/destructions', $form)
            ->assertRedirect(route('destructions.certificat', $premiere))
            ->assertSessionHas('error');
        $this->assertSame(1, DB::table('destructions')->count());

        $this->actingAs($presta)->get("/destructions/create/{$collecte->id}")
            ->assertRedirect(route('destructions.certificat', $premiere));
    }

    public function test_m3_une_collecte_annulee_n_est_pas_detruite(): void
    {
        [$r, $etab] = $this->contexteCollecte();
        $collecte = $this->makeCollecte($etab->id, 'annule');

        $this->actingAs($this->makeUser('prestataire', null, $r->id))->post('/destructions', [
            'collecte_id' => $collecte->id, 'poids_reel_kg' => 2, 'methode' => 'incineration',
            'date_destruction' => now()->toDateString(),
        ])->assertSessionHas('error');
        $this->assertSame(0, DB::table('destructions')->count());
    }

    // ── M6 : QR code d'un local de stockage ──────────────────────────────

    public function test_m6_qr_code_local_limite_au_perimetre(): void
    {
        $mien  = $this->makeEtablissement($this->makeReseau()->id, 'Mon Hôpital');
        $autre = $this->makeEtablissement($this->makeReseau('R2')->id, 'Clinique Secrète');
        $qhse  = $this->makeUser('qhse', $mien->id);

        $this->actingAs($qhse)->get("/qrcode/local/{$autre->id}")->assertForbidden();
        $this->actingAs($qhse)->get("/qrcode/local/{$mien->id}")->assertOk()->assertSee('Mon Hôpital');
    }

    // ── L1 : plus de changement de structure par GET ─────────────────────

    public function test_l1_le_parametre_get_switch_tenant_est_ignore(): void
    {
        $r    = $this->makeReseau();
        $etab = $this->makeEtablissement($r->id);
        $ar   = $this->makeUser('admin_reseau', null, $r->id);

        $this->actingAs($ar)->get("/superadmin?switch_tenant={$etab->id}")->assertOk();
        $this->assertNull(session('admin_tenant_id'));

        // Le changement via POST (protégé CSRF) fonctionne toujours
        $this->actingAs($ar)->post("/superadmin/switch-tenant/{$etab->id}")->assertRedirect();
        $this->assertSame($etab->id, (int) session('admin_tenant_id'));
    }

    // ── L2 / CSP : en-têtes et configuration ─────────────────────────────

    public function test_csp_et_en_tetes_de_securite_sur_les_pages(): void
    {
        $csp = $this->get('/login')->assertOk()->headers->get('Content-Security-Policy');

        foreach (["default-src 'self'", "connect-src 'self'", "object-src 'none'",
                  "base-uri 'self'", "form-action 'self'", "frame-ancestors 'self'"] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
    }

    public function test_l2_le_debug_est_force_a_off_en_production(): void
    {
        config(['app.env' => 'production', 'app.debug' => true]);

        (new \App\Providers\AppServiceProvider($this->app))->boot();

        $this->assertFalse(config('app.debug'));
    }
}
