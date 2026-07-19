<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Droits RGPD/CDP : export (portabilité) par le sujet, anonymisation (effacement)
 * côté admin qui préserve la traçabilité (pas de suppression physique).
 */
class RgpdAccountTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    public function test_un_utilisateur_exporte_ses_donnees_en_json(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $user = $this->makeUser('agent', $etab->id);

        $response = $this->actingAs($user)->get('/mon-compte/donnees/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $response->assertJsonPath('profil.email', $user->email);
        $response->assertJsonStructure([
            'exporte_le',
            'profil' => ['id', 'nom', 'prenom', 'email', 'role'],
            'connexions',
            'declarations_saisies',
            'signatures_apposees',
        ]);
    }

    public function test_la_suppression_admin_anonymise_sans_detruire(): void
    {
        $etab  = $this->makeEtablissement($this->makeReseau()->id);
        $admin = $this->makeUser('superadmin');
        $cible = $this->makeUser('agent', $etab->id, null, [
            'nom' => 'Diop', 'prenom' => 'Awa', 'email' => 'awa.diop@test.sn', 'telephone' => '770000000',
        ]);
        $cibleId = $cible->id;

        $this->actingAs($admin)->delete("/admin/utilisateurs/{$cibleId}")
             ->assertRedirect();

        // La ligne existe toujours (traçabilité) mais les PII sont effacées.
        $fresh = User::find($cibleId);
        $this->assertNotNull($fresh, 'Le compte ne doit pas être supprimé physiquement.');
        $this->assertNotNull($fresh->anonymized_at);
        $this->assertSame(0, (int) $fresh->actif);
        $this->assertNotSame('Diop', $fresh->nom);
        $this->assertStringNotContainsString('awa.diop@test.sn', $fresh->email);
        $this->assertNull($fresh->telephone);
    }
}
