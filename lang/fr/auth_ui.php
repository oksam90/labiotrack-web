<?php

/**
 * lang/fr/auth_ui.php — Écrans d'authentification (UI).
 *
 * Note : laravel-lang/lang publie déjà lang/fr/auth.php avec les
 * messages standard de Laravel (failed, password, throttle). Ce
 * fichier complète avec le vocabulaire UI propre à LaBioTrack.
 */
return [
    'login_title'       => 'Connectez-vous à votre espace LaBioTrack',
    'email'             => 'Adresse email',
    'email_placeholder' => 'votre@email.sn',
    'password'          => 'Mot de passe',
    'password_placeholder' => '••••••••',
    'remember_me'       => 'Se souvenir de moi',
    'sign_in'           => 'Se connecter',
    'logout'            => 'Déconnexion',

    // Messages d'erreur / flash
    'bad_credentials'   => 'Identifiants incorrects.',
    'account_disabled'  => 'Votre compte a été désactivé.',
    'session_expired'   => 'Votre session a expiré pour des raisons de sécurité. Veuillez vous reconnecter.',
];
