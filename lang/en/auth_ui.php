<?php

/**
 * lang/en/auth_ui.php — Authentication screens (UI).
 *
 * Note: laravel-lang/lang publishes lang/en/auth.php with Laravel's
 * standard auth messages (failed, password, throttle). This file
 * adds LaBioTrack-specific UI vocabulary.
 */
return [
    'login_title'       => 'Sign in to your LaBioTrack account',
    'email'             => 'Email address',
    'email_placeholder' => 'your@email.com',
    'password'          => 'Password',
    'password_placeholder' => '••••••••',
    'remember_me'       => 'Remember me',
    'sign_in'           => 'Sign in',
    'logout'            => 'Sign out',

    // Error / flash messages
    'bad_credentials'   => 'Invalid credentials.',
    'account_disabled'  => 'Your account has been disabled.',
    'session_expired'   => 'Your session has expired for security reasons. Please sign in again.',
];
