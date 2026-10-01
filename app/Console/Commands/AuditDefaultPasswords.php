<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Détecte les comptes actifs dont le mot de passe est un mot de passe par
 * défaut connu (seeders de développement publiés dans le dépôt).
 *
 *   php artisan security:default-passwords          → liste les comptes exposés
 *   php artisan security:default-passwords --lock   → leur attribue un mot de
 *       passe aléatoire inconnu (l'utilisateur passera par « mot de passe oublié »)
 */
class AuditDefaultPasswords extends Command
{
    protected $signature = 'security:default-passwords
                            {--lock : Remplace le mot de passe des comptes exposés par une valeur aléatoire}';

    protected $description = 'Détecte (et verrouille) les comptes utilisant un mot de passe par défaut';

    /** Mots de passe publiés dans le dépôt ou trivialement devinables. */
    private const DEFAULTS = ['password', 'Password', 'password123', '12345678', 'labiotrack', 'biomed'];

    public function handle(): int
    {
        $exposes = DB::table('users')
            ->whereNull('anonymized_at')
            ->select('id', 'email', 'role', 'actif', 'password')
            ->orderBy('id')
            ->get()
            ->filter(fn ($u) => collect(self::DEFAULTS)->contains(fn ($pw) => Hash::check($pw, $u->password)));

        if ($exposes->isEmpty()) {
            $this->info('Aucun compte avec un mot de passe par défaut.');
            return self::SUCCESS;
        }

        $this->error($exposes->count() . ' compte(s) avec un mot de passe par défaut :');
        $this->table(['id', 'email', 'rôle', 'actif'],
            $exposes->map(fn ($u) => [$u->id, $u->email, $u->role, $u->actif ? 'oui' : 'non'])->all());

        if (! $this->option('lock')) {
            $this->warn('Relancez avec --lock pour les verrouiller.');
            return self::FAILURE;
        }

        foreach ($exposes as $u) {
            DB::table('users')->where('id', $u->id)->update([
                'password'       => Hash::make(Str::random(48)),
                'remember_token' => null,
                'updated_at'     => now(),
            ]);
        }
        $this->info($exposes->count() . ' compte(s) verrouillé(s) — réinitialisation via « mot de passe oublié ».');

        return self::SUCCESS;
    }
}
