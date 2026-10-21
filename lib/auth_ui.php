<?php
declare(strict_types=1);

/** Shared pieces for the auth pages. */

function google_button(): string
{
    if ((string) cfg('google.client_id') === '') {
        return '';
    }
    $svg = '<svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.6l6.8-6.8C35.8 2.4 30.3 0 24 0 14.6 0 6.5 5.4 2.6 13.3l7.9 6.1C12.4 13.7 17.7 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9h12.7c-.6 3-2.2 5.5-4.7 7.2l7.4 5.7c4.3-4 7.1-9.9 7.1-17.4z"/><path fill="#FBBC05" d="M10.5 28.6c-.5-1.5-.8-3-.8-4.6s.3-3.1.8-4.6l-7.9-6.1C.9 16.5 0 20.1 0 24s.9 7.5 2.6 10.7l7.9-6.1z"/><path fill="#34A853" d="M24 48c6.3 0 11.7-2.1 15.6-5.7l-7.4-5.7c-2.1 1.4-4.8 2.3-8.2 2.3-6.3 0-11.6-4.2-13.5-9.9l-7.9 6.1C6.5 42.6 14.6 48 24 48z"/></svg>';
    return '<a class="btn btn-google" href="/auth/google.php">' . $svg . ' ' . e(t('auth_google')) . '</a><div class="or">' . e(t('auth_or')) . '</div>';
}

function auth_card_start(string $title, string $lead): void
{
    echo '<section class="auth"><div class="auth-card"><h1>' . e($title) . '</h1><p class="lead">' . e($lead) . '</p>';
}

function auth_card_end(): void
{
    echo '</div></section>';
}

function form_error(?string $err): void
{
    if ($err) {
        echo '<div class="flash flash-error" role="alert" style="margin:0 0 16px">' . e($err) . '</div>';
    }
}
