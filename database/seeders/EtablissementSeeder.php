<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EtablissementSeeder extends Seeder
{
    public function run(): void
    {
        $etablissements = [
            [
                'nom'=>'Hôpital Principal de Dakar','type'=>'hopital',
                'adresse'=>'Avenue Nelson Mandela','ville'=>'Dakar',
                'telephone'=>'+221 33 839 50 00','email'=>'contact@hpd.sn',
                'responsable_qhse'=>'Dr. Aminata Diallo','nombre_lits'=>500,
                'slug'=>'hpd',
                'services'=>['Bloc Opératoire','Urgences','Réanimation','Chirurgie','Pédiatrie','Maternité','Radiologie','Laboratoire'],
            ],
            [
                'nom'=>'Clinique Pasteur','type'=>'clinique',
                'adresse'=>'Rue du Docteur Thèze','ville'=>'Dakar',
                'telephone'=>'+221 33 889 10 00','email'=>'info@cliniquepasteur.sn',
                'responsable_qhse'=>'Dr. Ibrahima Ndiaye','nombre_lits'=>120,
                'slug'=>'pasteur',
                'services'=>['Bloc Opératoire','Urgences','Médecine Interne','Cardiologie','Maternité'],
            ],
            [
                'nom'=>'Centre de Santé Thiaroye','type'=>'cabinet',
                'adresse'=>'Avenue Lamine Guèye, Thiaroye','ville'=>'Thiaroye',
                'telephone'=>'+221 77 123 45 67','email'=>'csthiaroye@sante.sn',
                'responsable_qhse'=>'Infirmier Chef Moussa Diagne','nombre_lits'=>30,
                'slug'=>'thiaroye',
                'services'=>['Consultations Générales','Maternité','Vaccination'],
            ],
            [
                'nom'=>'Laboratoire BioAnalyse','type'=>'laboratoire',
                'adresse'=>'Place de l\'Indépendance','ville'=>'Dakar',
                'telephone'=>'+221 33 821 30 00','email'=>'contact@bioanalyse.sn',
                'responsable_qhse'=>'Dr. Fatou Sall','nombre_lits'=>0,
                'slug'=>'bioanalyse',
                'services'=>['Biochimie','Hématologie','Microbiologie','Anatomopathologie'],
            ],
        ];

        foreach ($etablissements as $data) {
            $services = $data['services'];
            unset($data['services']);

            $data['actif']      = 1;
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $id = DB::table('etablissements')->insertGetId($data);

            foreach ($services as $service) {
                DB::table('services')->insert([
                    'etablissement_id' => $id,
                    'nom'              => $service,
                    'actif'            => 1,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }
}
