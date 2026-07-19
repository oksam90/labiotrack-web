<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\CreatesTestWorld;
use Tests\TestCase;

/**
 * Correctifs sécurité J+30 sur l'authentification :
 *  - un compte désactivé ne peut pas se connecter (check `actif`) ;
 *  - rate-limiting anti-brute-force sur /login ;
 *  - flux « mot de passe oublié » (token + notification).
 */
class AuthSecurityTest extends TestCase
{
    use RefreshDatabase, CreatesTestWorld;

    public function test_un_compte_desactive_ne_peut_pas_se_connecter(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $user = $this->makeUser('qhse', $etab->id, null, ['actif' => 0]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_un_compte_actif_se_connecte(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $user = $this->makeUser('qhse', $etab->id);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_le_login_est_rate_limite_apres_5_tentatives(): void
    {
        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $user = $this->makeUser('qhse', $etab->id);

        // 5 tentatives échouées autorisées, la 6e est bloquée (429 → redirigé).
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mauvais']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'mauvais']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        // Le message doit être celui du throttle, pas « identifiants incorrects ».
        $this->assertStringContainsString(
            'tentatives',
            (string) session('errors')?->first('email')
        );
    }

    public function test_le_lien_de_reinitialisation_est_envoye(): void
    {
        Notification::fake();

        $etab = $this->makeEtablissement($this->makeReseau()->id);
        $user = $this->makeUser('qhse', $etab->id);

        $this->post('/forgot-password', ['email' => $user->email])
             ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
