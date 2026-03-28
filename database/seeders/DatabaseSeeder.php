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
            UserSeeder::class,           // Comptes par structure
        ]);
    }
}
