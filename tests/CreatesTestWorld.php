<?php

namespace Tests;

use App\Models\Collecte;
use App\Models\Declaration;
use App\Models\Etablissement;
use App\Models\Reseau;
use App\Models\Service;
use App\Models\Signature;
use App\Models\TypeContenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fabrique un graphe de données minimal pour les tests, sans dépendre de
 * factories (la majorité des modèles LaBioTrack n'en ont pas). Les créations
 * passent par Eloquent ; le TenantScope ne filtre pas hors requête
 * authentifiée, donc ces helpers créent librement.
 */
trait CreatesTestWorld
{
    protected function makeReseau(string $nom = 'Réseau Test'): Reseau
    {
        return Reseau::create(['nom' => $nom, 'actif' => 1]);
    }

    protected function makeEtablissement(?int $reseauId = null, string $nom = 'Étab Test'): Etablissement
    {
        return Etablissement::withoutGlobalScopes()->create([
            'reseau_id'   => $reseauId,
            'nom'         => $nom,
            'type'        => 'hopital',
            'adresse'     => 'Dakar',
            'nombre_lits' => 10,
            'actif'       => 1,
        ]);
    }

    protected function makeUser(string $role, ?int $etabId = null, ?int $reseauId = null, array $overrides = []): User
    {
        static $seq = 0;
        $seq++;
        return User::create(array_merge([
            'etablissement_id' => $etabId,
            'reseau_id'        => $reseauId,
            'nom'              => 'Test',
            'prenom'           => ucfirst($role),
            'email'            => $role . $seq . '@test.sn',
            'password'         => Hash::make('password'),
            'role'             => $role,
            'actif'            => 1,
        ], $overrides));
    }

    protected function makeService(int $etabId, string $nom = 'Service Test'): Service
    {
        return Service::withoutGlobalScopes()->create([
            'etablissement_id' => $etabId,
            'nom'              => $nom,
            'actif'            => 1,
        ]);
    }

    protected function makeTypeDechet(?string $code = null): int
    {
        static $seq = 0;
        $seq++;
        $code ??= 'TD' . $seq; // code unique (contrainte type_dechets_code_unique)
        return DB::table('type_dechets')->insertGetId([
            'nom'         => 'Déchet ' . $code,
            'code'        => $code,
            'couleur_sac' => 'jaune',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    protected function makeContenant(?int $typeDechetId = null, float $poids = 2.0, float $cout = 500, string $code = 'SJ50'): TypeContenant
    {
        $typeDechetId ??= $this->makeTypeDechet();
        return TypeContenant::create([
            'nom'            => 'Contenant ' . $code,
            'code'           => $code,
            'type_dechet_id' => $typeDechetId,
            'poids_moyen_kg' => $poids,
            'cout_unitaire'  => $cout,
        ]);
    }

    protected function makeCollecte(int $etabId, string $statut = 'en_cours', ?int $collecteurId = null): Collecte
    {
        return Collecte::withoutGlobalScopes()->create([
            'etablissement_id'  => $etabId,
            'collecteur_id'     => $collecteurId,
            'numero_bordereau'  => 'BRD-TEST-' . uniqid(),
            'nombre_contenants' => 1,
            'poids_declare_kg'  => 1,
            'statut'            => $statut,
            'date_collecte'     => now(),
        ]);
    }

    protected function makeSignature(Collecte $collecte, int $signataireId): Signature
    {
        return Signature::withoutGlobalScopes()->create([
            'collecte_id'          => $collecte->id,
            'etablissement_id'     => $collecte->etablissement_id,
            'signataire_user_id'   => $signataireId,
            'signataire_nom'       => 'Signataire Test',
            'signature_image_path' => 'signatures/test.png',
            'signature_hash'       => str_repeat('a', 64),
            'commentaire'          => 'Test',
            'ip_address'           => '127.0.0.1',
            'user_agent'           => 'phpunit',
            'signed_at'            => now(),
        ]);
    }

    protected function makeDeclaration(int $etabId, int $userId, string $statut = 'en_stock'): Declaration
    {
        return Declaration::withoutGlobalScopes()->create([
            'etablissement_id'  => $etabId,
            'user_id'           => $userId,
            'nombre_contenants' => 1,
            'poids_estime_kg'   => 1,
            'statut'            => $statut,
            'date_declaration'  => now()->toDateString(),
            'heure_declaration' => now()->toTimeString(),
        ]);
    }
}
