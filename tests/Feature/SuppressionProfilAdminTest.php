<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Migration 2026_10_02_100001 : les comptes encore « admin » deviennent QHSE
 * de leur établissement (désactivés s'ils n'en ont pas), avec une trace dans
 * activites_log, puis la valeur « admin » disparaît de l'ENUM users.role.
 */
class SuppressionProfilAdminTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    /**
     * Les ALTER TABLE de la migration valident implicitement la transaction de
     * RefreshDatabase (MySQL) : on supprime explicitement les lignes créées
     * pour ne pas polluer les tests suivants.
     */
    protected function tearDown(): void
    {
        foreach (['activites_log', 'users', 'etablissements', 'reseaux'] as $table) {
            DB::table($table)->delete();
        }
        parent::tearDown();
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_10_02_100001_remove_admin_role_from_users.php');
    }

    public function test_les_comptes_admin_restants_deviennent_qhse_et_sont_traces(): void
    {
        $migration = $this->migration();
        $migration->down(); // état « avant déploiement » : la valeur admin existe

        $etab        = $this->makeEtablissement($this->makeReseau()->id, 'Hôpital Principal');
        $avecEtab    = $this->makeUser('admin', $etab->id);
        $sansEtab    = $this->makeUser('admin');
        $dejaReaffecte = $this->makeUser('admin_reseau', null, $etab->reseau_id); // non concerné

        $migration->up();

        $a = DB::table('users')->find($avecEtab->id);
        $this->assertSame('qhse', $a->role);
        $this->assertSame($etab->id, (int) $a->etablissement_id);
        $this->assertSame(1, (int) $a->actif, 'Garde l\'accès à son établissement.');

        $b = DB::table('users')->find($sansEtab->id);
        $this->assertSame('qhse', $b->role);
        $this->assertSame(0, (int) $b->actif, 'Sans établissement : désactivé.');

        $this->assertSame('admin_reseau', DB::table('users')->find($dejaReaffecte->id)->role);
        $this->assertSame(2, DB::table('activites_log')->where('type', 'role')->count());

        $this->artisan('users:anciens-admins')
            ->expectsOutputToContain($avecEtab->email)
            ->expectsOutputToContain($sansEtab->email)
            ->assertSuccessful();

        // La valeur « admin » n'est plus acceptée par la base
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $a->id)->update(['role' => 'admin']);
    }

    public function test_la_commande_ne_signale_rien_sans_conversion(): void
    {
        $this->artisan('users:anciens-admins')
            ->expectsOutputToContain('Aucun ancien compte')
            ->assertSuccessful();
    }
}
