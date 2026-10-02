<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * SECURITY : déplace les documents générés du disque `public` (servi tel quel
 * par le serveur web sous /storage, SANS authentification) vers le disque
 * privé `local`. Les chemins relatifs sont conservés : certificat_path,
 * fichier_path et bordereau_pdf_path restent valides sans mise à jour en base.
 *
 * Concerne : certificats de destruction, rapports, bordereaux non signés.
 * Idempotente : un fichier déjà présent côté privé n'est pas écrasé.
 */
return new class extends Migration
{
    private const DOSSIERS = ['certificats', 'rapports', 'bordereaux'];

    public function up(): void
    {
        $public = Storage::disk('public');
        $prive  = Storage::disk('local');

        foreach (self::DOSSIERS as $dossier) {
            if (! $public->exists($dossier)) {
                continue;
            }

            foreach ($public->allFiles($dossier) as $fichier) {
                if (! $prive->exists($fichier)) {
                    File::ensureDirectoryExists(dirname($prive->path($fichier)));
                    File::move($public->path($fichier), $prive->path($fichier));
                } else {
                    $public->delete($fichier);
                }
            }

            $public->deleteDirectory($dossier);
        }
    }

    public function down(): void
    {
        // Volontairement vide : republier des documents légaux sans
        // authentification n'est jamais un retour arrière souhaitable.
    }
};
