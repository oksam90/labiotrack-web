<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ReferentielSeeder::class,    // Types déchets + contenants
            EtablissementSeeder::class,  // 4 structures médicales
            ReseauSeeder::class,         // Réseau par défaut + rattachement des structures
            UserSeeder::class,           // Comptes par structure
        ]);
    }
}
