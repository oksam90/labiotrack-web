<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Documents légaux sur disque privé + absence d'identifiants par défaut.
 */
class DocumentsAndCredentialsSecurityTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_le_rapport_est_ecrit_sur_le_disque_prive_et_servi_par_la_route(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $qhse = $this->makeUser('qhse', $etab->id);

        $this->actingAs($qhse)->post('/rapports/generer', [
            'type' => 'mensuel', 'periode_debut' => now()->startOfMonth()->toDateString(),
            'periode_fin' => now()->toDateString(),
        ])->assertRedirect();

        $path = DB::table('rapports')->value('fichier_path');
        Storage::disk('local')->assertExists($path);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $id = DB::table('rapports')->value('id');
        $this->actingAs($qhse)->get("/rapports/{$id}/pdf")->assertOk();
    }

    public function test_le_certificat_de_destruction_est_ecrit_sur_le_disque_prive(): void
    {
        $r        = $this->makeReseau();
        $etab     = $this->makeEtablissement($r->id);
        $presta   = $this->makeUser('prestataire', null, $r->id);
        $collecte = $this->makeCollecte($etab->id, 'signee');

        $this->actingAs($presta)->post('/destructions', [
            'collecte_id' => $collecte->id, 'poids_reel_kg' => 2.5, 'methode' => 'incineration',
            'date_destruction' => now()->toDateString(),
        ])->assertRedirect();

        $path = DB::table('destructions')->value('certificat_path');
        Storage::disk('local')->assertExists($path);
        $this->assertStringNotContainsString('cert_', $path, 'Nom de fichier non devinable attendu.');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_la_migration_deplace_les_documents_existants_vers_le_disque_prive(): void
    {
        Storage::disk('public')->put('certificats/cert_1.pdf', 'pdf');
        Storage::disk('public')->put('rapports/rapport_20260101_120000.pdf', 'pdf');
        Storage::disk('public')->put('bordereaux/3/bordereau_BRD-1.pdf', 'pdf');
        Storage::disk('public')->put('declarations/photos/abc.jpg', 'img'); // non concerné

        (require database_path('migrations/2026_10_01_200001_move_legal_documents_to_private_disk.php'))->up();

        foreach (['certificats/cert_1.pdf', 'rapports/rapport_20260101_120000.pdf', 'bordereaux/3/bordereau_BRD-1.pdf'] as $f) {
            Storage::disk('local')->assertExists($f);
            Storage::disk('public')->assertMissing($f);
        }
        Storage::disk('public')->assertExists('declarations/photos/abc.jpg');
    }

    public function test_la_commande_detecte_et_verrouille_les_mots_de_passe_par_defaut(): void
    {
        $expose = $this->makeUser('superadmin'); // CreatesTestWorld : mot de passe "password"
        $sain   = $this->makeUser('qhse', null, null, ['password' => Hash::make('Un-vrai-secret-42')]);

        $this->artisan('security:default-passwords')
            ->expectsOutputToContain($expose->email)
            ->assertFailed();

        $this->artisan('security:default-passwords --lock')->assertSuccessful();

        $this->assertFalse(Hash::check('password', DB::table('users')->find($expose->id)->password));
        $this->assertTrue(Hash::check('Un-vrai-secret-42', DB::table('users')->find($sain->id)->password));
        $this->artisan('security:default-passwords')->assertSuccessful();
    }

    public function test_le_seeder_de_comptes_demo_ne_tourne_pas_en_production(): void
    {
        $this->app['env'] = 'production';

        (new UserSeeder())->run();

        $this->assertSame(0, DB::table('users')->count());
    }
}
