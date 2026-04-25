<?php

namespace Database\Seeders;

use App\Models\Reseau;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ReseauSeeder
 *
 * Crée un réseau "LaBioTrack — Réseau par défaut" et y rattache
 * tous les établissements actuels pour que la migration vers la
 * nouvelle architecture réseau soit transparente.
 */
class ReseauSeeder extends Seeder
{
    public function run(): void
    {
        $reseau = Reseau::firstOrCreate(
            ['nom' => 'LaBioTrack — Réseau principal'],
            [
                'slug'              => 'labiotrack-principal',
                'description'       => 'Réseau par défaut créé lors de la migration vers la matrice multi-réseaux.',
                'contact_email'     => 'contact@labiotrack.local',
                'actif'             => true,
            ]
        );

        // Rattacher tous les établissements sans réseau à ce réseau par défaut
        DB::table('etablissements')
            ->whereNull('reseau_id')
            ->update(['reseau_id' => $reseau->id, 'updated_at' => now()]);

        $this->command->info("Réseau '{$reseau->nom}' créé et établissements rattachés.");
    }
}
