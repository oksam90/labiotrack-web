<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesTestWorld;
use Tests\TestCase;

class EtablissementTypeTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    public function test_un_admin_reseau_cree_un_etablissement_industriel_dans_son_reseau(): void
    {
        $r  = $this->makeReseau();
        $ar = $this->makeUser('admin_reseau', null, $r->id);

        $this->actingAs($ar)->get('/admin/create')->assertOk()
            ->assertSee('value="industrielle"', false)
            ->assertSee('Industrielle');

        $this->actingAs($ar)->post('/admin', [
            'nom' => 'Usine Test', 'type' => 'industrielle', 'adresse' => 'Zone industrielle',
        ])->assertRedirect();

        $etab = DB::table('etablissements')->where('nom', 'Usine Test')->first();
        $this->assertSame('industrielle', $etab->type);
        $this->assertSame($r->id, (int) $etab->reseau_id);
    }

    public function test_un_type_inconnu_est_refuse(): void
    {
        $super = $this->makeUser('superadmin');

        $this->actingAs($super)->post('/admin', [
            'nom' => 'X', 'type' => 'spatial', 'adresse' => 'Y',
        ])->assertSessionHasErrors('type');
    }
}
