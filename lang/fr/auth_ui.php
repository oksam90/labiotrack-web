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
    'too_many_attempts' => 'Trop de tentatives de connexion. Réessayez dans :seconds secondes.',

    // Mot de passe oublié / réinitialisation
    'forgot_password'       => 'Mot de passe oublié ?',
    'forgot_title'          => 'Réinitialiser votre mot de passe',
    'forgot_intro'          => 'Saisissez votre adresse email : nous vous enverrons un lien de réinitialisation.',
    'send_reset_link'       => 'Envoyer le lien',
    'reset_title'           => 'Choisir un nouveau mot de passe',
    'new_password'          => 'Nouveau mot de passe',
    'confirm_password'      => 'Confirmer le mot de passe',
    'reset_submit'          => 'Réinitialiser le mot de passe',
    'back_to_login'         => 'Retour à la connexion',
    'reset_link_sent'       => 'Si un compte existe pour cette adresse, un lien de réinitialisation vient d’être envoyé.',
    'reset_success'         => 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.',
];
