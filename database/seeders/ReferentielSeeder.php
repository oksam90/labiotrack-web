<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        // Types de déchets
        DB::table('type_dechets')->insertOrIgnore([
            ['nom'=>'Déchets Anatomiques','code'=>'DA','categorie'=>'anatomique','couleur_sac'=>'rouge','description'=>'Organes, membres, sang, liquides biologiques','created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Déchets Piquants Coupants','code'=>'DPC','categorie'=>'DASRI','couleur_sac'=>'jaune','description'=>'Aiguilles, bistouris, lancettes','created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Déchets Infectieux non Piquants','code'=>'DI','categorie'=>'DASRI','couleur_sac'=>'jaune','description'=>'Compresses souillées, tubulures, gants','created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Déchets Assimilés Ménagers','code'=>'DAM','categorie'=>'assimile','couleur_sac'=>'noir','description'=>'Emballages non souillés, papier','created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Déchets Chimiques','code'=>'DC','categorie'=>'chimique','couleur_sac'=>'blanche','description'=>'Médicaments périmés, produits chimiques','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Types de contenants
        DB::table('type_contenants')->insertOrIgnore([
            ['nom'=>'Boîte de sécurité 1L','code'=>'BS_1L','type_dechet_id'=>2,'poids_moyen_kg'=>0.20,'capacite_litres'=>1.0,'cout_unitaire'=>500,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Boîte de sécurité 5L','code'=>'BS_5L','type_dechet_id'=>2,'poids_moyen_kg'=>0.80,'capacite_litres'=>5.0,'cout_unitaire'=>1200,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Boîte de sécurité 10L','code'=>'BS_10L','type_dechet_id'=>2,'poids_moyen_kg'=>1.50,'capacite_litres'=>10.0,'cout_unitaire'=>2000,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Sac jaune 20L (DASRI)','code'=>'SJ_20','type_dechet_id'=>3,'poids_moyen_kg'=>1.50,'capacite_litres'=>20.0,'cout_unitaire'=>300,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Sac jaune 30L (DASRI)','code'=>'SJ_30','type_dechet_id'=>3,'poids_moyen_kg'=>2.50,'capacite_litres'=>30.0,'cout_unitaire'=>450,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Sac jaune 50L (DASRI)','code'=>'SJ_50','type_dechet_id'=>3,'poids_moyen_kg'=>4.00,'capacite_litres'=>50.0,'cout_unitaire'=>650,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Sac noir 20L (DAM)','code'=>'SN_20','type_dechet_id'=>4,'poids_moyen_kg'=>0.80,'capacite_litres'=>20.0,'cout_unitaire'=>150,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Sac noir 50L (DAM)','code'=>'SN_50','type_dechet_id'=>4,'poids_moyen_kg'=>1.50,'capacite_litres'=>50.0,'cout_unitaire'=>250,'created_at'=>now(),'updated_at'=>now()],
            ['nom'=>'Conteneur anatomique 10L','code'=>'CA_10','type_dechet_id'=>1,'poids_moyen_kg'=>2.00,'capacite_litres'=>10.0,'cout_unitaire'=>3000,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
