<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute la catégorie `plastique` à l'ENUM `type_dechets.categorie` puis
 * insère les 7 familles de plastiques (codes d'identification des résines 1 → 7).
 *
 * Passe par une migration (et non le seul ReferentielSeeder) pour que les
 * bases déjà en service reçoivent aussi les nouveaux types. insertOrIgnore
 * s'appuie sur l'unicité de `code` : rejouable sans doublon.
 */
return new class extends Migration
{
    private const CODES = ['PET', 'PEHD', 'PVC', 'PELD', 'PP', 'PS', 'PL_AUTRES'];

    public function up(): void
    {
        DB::statement("ALTER TABLE type_dechets MODIFY categorie
            ENUM('DASRI','assimile','anatomique','chimique','radioactif','plastique')
            NOT NULL DEFAULT 'DASRI'");

        DB::table('type_dechets')->insertOrIgnore(self::rows());
    }

    public function down(): void
    {
        // Les contenants rattachés passent à NULL (FK nullOnDelete).
        DB::table('type_dechets')->whereIn('code', self::CODES)->delete();

        // Tout type `plastique` créé ensuite via l'admin est reclassé avant
        // le retrait de la valeur, sinon MySQL rejette l'ALTER en mode strict.
        DB::table('type_dechets')->where('categorie', 'plastique')->update(['categorie' => 'assimile']);

        DB::statement("ALTER TABLE type_dechets MODIFY categorie
            ENUM('DASRI','assimile','anatomique','chimique','radioactif')
            NOT NULL DEFAULT 'DASRI'");
    }

    /** @return array<int, array<string, mixed>> */
    private static function rows(): array
    {
        $types = [
            ['PET',       'PET (Polyéthylène Téréphtalate)',          'Résine n°1 — flacons, bouteilles et emballages transparents'],
            ['PEHD',      'PEHD ou PE-HD (Polyéthylène Haute Densité)', 'Résine n°2 — bidons, flacons opaques, bouchons'],
            ['PVC',       'PVC (Polychlorure de Vinyle)',             'Résine n°3 — tubulures, poches de perfusion, gants vinyle'],
            ['PELD',      'PELD ou LDPE (Polyéthylène Basse Densité)', 'Résine n°4 — films, sachets, sacs souples'],
            ['PP',        'PP (Polypropylène)',                       'Résine n°5 — seringues (corps), bouchons, barquettes'],
            ['PS',        'PS (Polystyrène)',                         'Résine n°6 — boîtes de Petri, gobelets, calages'],
            ['PL_AUTRES', 'Autres plastiques (mélanges ou polycarbonate)', 'Résine n°7 — plastiques mélangés, polycarbonate (PC) et autres'],
        ];

        $now = now();

        return array_map(fn (array $t) => [
            'nom'         => $t[1],
            'code'        => $t[0],
            'categorie'   => 'plastique',
            'couleur_sac' => null,
            'description' => $t[2],
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $types);
    }
};
