<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Gestion des comptes : un administrateur n'attribue que les rôles de sa
 * liste blanche et n'agit que sur les comptes de son périmètre.
 */
class AdminUserSecurityTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Nom', 'prenom' => 'Prenom', 'telephone' => '',
            'etablissement_id' => '', 'reseau_id' => '',
        ], $overrides);
    }

    public function test_un_admin_ne_peut_pas_se_promouvoir_superadmin(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $admin = $this->makeUser('admin', $etab->id);

        $this->actingAs($admin)->put("/admin/utilisateurs/{$admin->id}", $this->payload([
            'email' => $admin->email, 'role' => 'superadmin', 'etablissement_id' => $etab->id,
        ]))->assertForbidden();

        $this->assertSame('admin', DB::table('users')->find($admin->id)->role);
    }

    public function test_un_admin_peut_modifier_son_profil_sans_changer_de_role(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $admin = $this->makeUser('admin', $etab->id);

        $this->actingAs($admin)->put("/admin/utilisateurs/{$admin->id}", $this->payload([
            'nom' => 'Renommé', 'email' => $admin->email, 'role' => 'admin',
            'etablissement_id' => $this->makeEtablissement()->id, // ignoré : rattachement figé
        ]))->assertRedirect();

        $row = DB::table('users')->find($admin->id);
        $this->assertSame('Renommé', $row->nom);
        $this->assertSame($etab->id, (int) $row->etablissement_id);
    }

    public function test_un_admin_ne_peut_pas_creer_de_superadmin_ni_d_admin_reseau(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $admin = $this->makeUser('admin', $etab->id);

        foreach (['superadmin', 'admin_reseau', 'admin'] as $i => $role) {
            $this->actingAs($admin)->post('/admin/utilisateurs', $this->payload([
                'email' => "x{$i}@test.sn", 'role' => $role,
                'password' => 'password1', 'password_confirmation' => 'password1',
            ]))->assertForbidden();
        }
        $this->assertSame(0, DB::table('users')->where('email', 'like', 'x%@test.sn')->count());
    }

    public function test_un_admin_cree_un_agent_dans_son_etablissement(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $autre = $this->makeEtablissement();
        $admin = $this->makeUser('admin', $etab->id);

        $this->actingAs($admin)->post('/admin/utilisateurs', $this->payload([
            'email' => 'agent-ok@test.sn', 'role' => 'agent', 'etablissement_id' => $autre->id,
            'password' => 'password1', 'password_confirmation' => 'password1',
        ]))->assertRedirect();

        $this->assertSame($etab->id, (int) DB::table('users')->where('email', 'agent-ok@test.sn')->value('etablissement_id'));
    }

    public function test_un_admin_reseau_ne_peut_pas_modifier_le_superadmin(): void
    {
        $r     = $this->makeReseau();
        $ar    = $this->makeUser('admin_reseau', null, $r->id);
        $super = $this->makeUser('superadmin');

        $this->actingAs($ar)->get("/admin/utilisateurs/{$super->id}/edit")->assertForbidden();
        $this->actingAs($ar)->put("/admin/utilisateurs/{$super->id}", $this->payload([
            'email' => 'attaquant@evil.test', 'role' => 'superadmin',
            'password' => 'hacked123', 'password_confirmation' => 'hacked123',
        ]))->assertForbidden();

        $this->assertSame($super->email, DB::table('users')->find($super->id)->email);
    }

    public function test_un_admin_reseau_n_agit_pas_hors_de_son_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $r2 = $this->makeReseau('R2');
        $victime = $this->makeUser('qhse', $this->makeEtablissement($r2->id)->id);
        $ar = $this->makeUser('admin_reseau', null, $r1->id);

        $this->actingAs($ar)->post("/admin/utilisateurs/{$victime->id}/toggle")->assertForbidden();
        $this->actingAs($ar)->delete("/admin/utilisateurs/{$victime->id}")->assertForbidden();
        $this->actingAs($ar)->put("/admin/utilisateurs/{$victime->id}", $this->payload([
            'email' => $victime->email, 'role' => 'qhse',
        ]))->assertForbidden();

        $row = DB::table('users')->find($victime->id);
        $this->assertSame(1, (int) $row->actif);
        $this->assertNull($row->anonymized_at);
    }

    public function test_un_admin_reseau_gere_un_qhse_de_son_reseau(): void
    {
        $r    = $this->makeReseau();
        $etab = $this->makeEtablissement($r->id);
        $qhse = $this->makeUser('qhse', $etab->id);
        $ar   = $this->makeUser('admin_reseau', null, $r->id);

        $this->actingAs($ar)->post("/admin/utilisateurs/{$qhse->id}/toggle")->assertRedirect();
        $this->assertSame(0, (int) DB::table('users')->find($qhse->id)->actif);
    }

    public function test_un_admin_reseau_ne_rattache_pas_un_compte_a_un_autre_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $etabR2 = $this->makeEtablissement($this->makeReseau('R2')->id);
        $qhse = $this->makeUser('qhse', $this->makeEtablissement($r1->id)->id);
        $ar   = $this->makeUser('admin_reseau', null, $r1->id);

        $this->actingAs($ar)->put("/admin/utilisateurs/{$qhse->id}", $this->payload([
            'email' => $qhse->email, 'role' => 'qhse', 'etablissement_id' => $etabR2->id,
        ]))->assertForbidden();
    }

    public function test_le_formulaire_ne_propose_que_les_roles_attribuables(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $admin = $this->makeUser('admin', $etab->id);

        $this->actingAs($admin)->get('/admin/utilisateurs/create')->assertOk()
            ->assertSee('value="agent"', false)
            ->assertDontSee('value="superadmin"', false)
            ->assertDontSee('value="admin_reseau"', false);

        // Auto-édition : le rôle courant reste sélectionnable (et seul choix)
        $this->actingAs($admin)->get("/admin/utilisateurs/{$admin->id}/edit")->assertOk()
            ->assertSee('value="admin"', false)
            ->assertDontSee('value="superadmin"', false);
    }

    public function test_un_admin_reseau_cree_collecteur_et_prestataire_dans_son_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $r2 = $this->makeReseau('R2');
        $ar = $this->makeUser('admin_reseau', null, $r1->id);

        foreach (['collecteur', 'prestataire'] as $role) {
            $this->actingAs($ar)->post('/admin/utilisateurs', $this->payload([
                'email' => "{$role}-r1@test.sn", 'role' => $role,
                'reseau_id' => $r2->id, // tentative d'un autre réseau : ignorée
                'password' => 'password1', 'password_confirmation' => 'password1',
            ]))->assertRedirect();

            $row = DB::table('users')->where('email', "{$role}-r1@test.sn")->first();
            $this->assertSame($role, $row->role);
            $this->assertSame($r1->id, (int) $row->reseau_id, 'Rattaché de force au réseau de l\'admin.');
        }
    }

    public function test_un_admin_reseau_modifie_et_desactive_un_collecteur_de_son_reseau(): void
    {
        $r          = $this->makeReseau();
        $ar         = $this->makeUser('admin_reseau', null, $r->id);
        $collecteur = $this->makeUser('collecteur', null, $r->id);

        $this->actingAs($ar)->put("/admin/utilisateurs/{$collecteur->id}", $this->payload([
            'nom' => 'Renommé', 'email' => $collecteur->email, 'role' => 'collecteur', 'telephone' => '770000000',
        ]))->assertRedirect();

        $row = DB::table('users')->find($collecteur->id);
        $this->assertSame('Renommé', $row->nom);
        $this->assertSame('collecteur', $row->role);
        $this->assertSame($r->id, (int) $row->reseau_id);

        $this->actingAs($ar)->post("/admin/utilisateurs/{$collecteur->id}/toggle")->assertRedirect();
        $this->assertSame(0, (int) DB::table('users')->find($collecteur->id)->actif);
    }

    public function test_un_admin_reseau_ne_touche_pas_aux_collecteurs_d_un_autre_reseau(): void
    {
        $r1 = $this->makeReseau('R1');
        $r2 = $this->makeReseau('R2');
        $ar = $this->makeUser('admin_reseau', null, $r1->id);
        $collecteurR2 = $this->makeUser('prestataire', null, $r2->id);

        $this->actingAs($ar)->get("/admin/utilisateurs/{$collecteurR2->id}/edit")->assertForbidden();
        $this->actingAs($ar)->post("/admin/utilisateurs/{$collecteurR2->id}/toggle")->assertForbidden();
        $this->actingAs($ar)->put("/admin/utilisateurs/{$collecteurR2->id}", $this->payload([
            'email' => $collecteurR2->email, 'role' => 'prestataire',
        ]))->assertForbidden();
    }

    public function test_la_liste_n_affiche_les_actions_que_pour_les_comptes_gerables(): void
    {
        $r          = $this->makeReseau();
        $ar         = $this->makeUser('admin_reseau', null, $r->id);
        $autreAr    = $this->makeUser('admin_reseau', null, $r->id); // même rang : non gérable
        $collecteur = $this->makeUser('collecteur', null, $r->id);

        $this->actingAs($ar)->get('/admin/utilisateurs')->assertOk()
            ->assertSee(route('admin.utilisateurs.edit', $collecteur->id), false)
            ->assertDontSee(route('admin.utilisateurs.edit', $autreAr->id), false)
            ->assertDontSee(route('admin.utilisateurs.toggle', $ar->id), false); // pas d'auto-désactivation
    }
}
