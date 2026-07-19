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
    'too_many_attempts' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Forgot / reset password
    'forgot_password'       => 'Forgot your password?',
    'forgot_title'          => 'Reset your password',
    'forgot_intro'          => 'Enter your email address and we will send you a reset link.',
    'send_reset_link'       => 'Send reset link',
    'reset_title'           => 'Choose a new password',
    'new_password'          => 'New password',
    'confirm_password'      => 'Confirm password',
    'reset_submit'          => 'Reset password',
    'back_to_login'         => 'Back to sign in',
    'reset_link_sent'       => 'If an account exists for this address, a reset link has just been sent.',
    'reset_success'         => 'Your password has been reset. You can now sign in.',
];
