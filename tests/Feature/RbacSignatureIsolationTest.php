<?php

namespace Tests\Feature;

use App\Models\Collecte;
use App\Models\Signature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Invariant de sécurité central : un « Client signataire » rattaché à un
 * établissement ne voit et ne signe QUE les bordereaux de CET établissement,
 * même si son réseau en compte plusieurs.
 */
class RbacSignatureIsolationTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    public function test_client_signataire_ne_voit_que_les_signatures_de_son_etablissement(): void
    {
        $reseau = $this->makeReseau();
        $etabA  = $this->makeEtablissement($reseau->id, 'Étab A');
        $etabB  = $this->makeEtablissement($reseau->id, 'Étab B'); // même réseau

        $cs = $this->makeUser('client_signataire', $etabA->id, $reseau->id);

        $colA = $this->makeCollecte($etabA->id, 'signee');
        $sigA = $this->makeSignature($colA, $cs->id);
        $colB = $this->makeCollecte($etabB->id, 'signee');
        $sigB = $this->makeSignature($colB, $cs->id);

        // Le TenantScope doit restreindre la liste à l'établissement A
        $this->actingAs($cs);
        $visibles = Signature::pluck('etablissement_id')->unique()->values()->all();

        $this->assertSame([$etabA->id], $visibles,
            'Le client signataire ne doit voir que les signatures de son établissement.');
        $this->assertTrue(Signature::whereKey($sigA->id)->exists());
        $this->assertFalse(Signature::whereKey($sigB->id)->exists(),
            'La signature d’un autre établissement du réseau ne doit pas être visible.');
    }

    public function test_client_signataire_ne_peut_signer_que_dans_son_etablissement(): void
    {
        $reseau = $this->makeReseau();
        $etabA  = $this->makeEtablissement($reseau->id, 'Étab A');
        $etabB  = $this->makeEtablissement($reseau->id, 'Étab B');
        $cs     = $this->makeUser('client_signataire', $etabA->id, $reseau->id);

        $colA = $this->makeCollecte($etabA->id, 'en_cours');
        $colB = $this->makeCollecte($etabB->id, 'en_cours');

        $this->actingAs($cs);

        $this->assertTrue(Gate::forUser($cs)->allows('signatureSign', $colA),
            'Doit pouvoir signer un BRD de son établissement.');
        $this->assertFalse(Gate::forUser($cs)->allows('signatureSign', $colB),
            'Ne doit PAS pouvoir signer un BRD d’un autre établissement du réseau.');
        $this->assertTrue(Gate::forUser($cs)->allows('signatureOpen', $colA));
        $this->assertFalse(Gate::forUser($cs)->allows('signatureOpen', $colB));
    }

    public function test_client_signataire_ne_peut_pas_creer_de_collecte(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $cs   = $this->makeUser('client_signataire', $etab->id);

        $this->assertFalse(Gate::forUser($cs)->allows('create', Collecte::class));
    }
}
