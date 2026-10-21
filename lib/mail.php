<?php
declare(strict_types=1);

/**
 * Transactional email via Brevo (https://developers.brevo.com/reference/sendtransacemail).
 * Falls back to PHP mail() when no API key is configured (local dev just logs).
 */

function send_mail(string $toEmail, string $toName, string $subject, string $html): bool
{
    $key = (string) cfg('brevo.api_key');
    $fromEmail = (string) cfg('brevo.from_email');
    $fromName = (string) cfg('brevo.from_name');
    $text = trim(strip_tags(preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html) ?? ''));

    if ($key === '') {
        if (cfg('env') === 'development') {
            error_log("MAIL (dev, not sent) to=$toEmail subject=$subject\n$text");
            $dir = WSAP_ROOT . '/dev';
            @file_put_contents($dir . '/mail.log', date('c') . " to=$toEmail subject=$subject\n$text\n\n", FILE_APPEND);
            return true;
        }
        $headers = "From: $fromName <$fromEmail>\r\nContent-Type: text/html; charset=UTF-8\r\n";
        return @mail($toEmail, $subject, $html, $headers);
    }

    $payload = [
        'sender'      => ['email' => $fromEmail, 'name' => $fromName],
        'to'          => [['email' => $toEmail, 'name' => $toName !== '' ? $toName : $toEmail]],
        'subject'     => $subject,
        'htmlContent' => $html,
        'textContent' => $text,
    ];
    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['accept: application/json', 'content-type: application/json', 'api-key: ' . $key],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($status < 200 || $status >= 300) {
        error_log("Brevo send failed ($status): $err $body");
        return false;
    }
    return true;
}

/** Simple branded HTML wrapper for system emails. */
function mail_template(string $title, string $bodyHtml, string $ctaUrl = '', string $ctaText = ''): string
{
    $site = e((string) cfg('site_name'));
    $cta = $ctaUrl !== ''
        ? '<p style="margin:28px 0"><a href="' . e($ctaUrl) . '" style="background:#0b3d2e;color:#fff;text-decoration:none;padding:14px 22px;border-radius:10px;font-weight:700;display:inline-block">' . e($ctaText) . '</a></p>'
          . '<p style="color:#6b7280;font-size:13px">' . t('mail_or_copy') . '<br><a href="' . e($ctaUrl) . '" style="color:#0b3d2e">' . e($ctaUrl) . '</a></p>'
        : '';
    return '<!doctype html><html><body style="margin:0;background:#f4f6f3;font-family:Segoe UI,Helvetica,Arial,sans-serif;color:#111">'
        . '<div style="max-width:520px;margin:32px auto;background:#fff;border-radius:16px;padding:36px 32px">'
        . '<div style="font-weight:800;font-size:18px;color:#0b3d2e;margin-bottom:20px">' . $site . '</div>'
        . '<h1 style="font-size:22px;margin:0 0 12px">' . e($title) . '</h1>'
        . '<div style="font-size:15px;line-height:1.6">' . $bodyHtml . '</div>' . $cta
        . '<p style="color:#9ca3af;font-size:12px;margin-top:32px">' . $site . ' · ' . e(current_domain()) . '</p>'
        . '</div></body></html>';
}

function send_verification_email(array $user): bool
{
    $token = issue_token((int) $user['id'], 'verify', 86400 * 3);
    $url = base_url() . '/verify?t=' . $token;
    return send_mail(
        $user['email'],
        (string) $user['name'],
        t('mail_verify_subject', ['site' => cfg('site_name')]),
        mail_template(t('mail_verify_title'), '<p>' . t('mail_verify_body') . '</p>', $url, t('mail_verify_cta'))
    );
}

function send_reset_email(array $user): bool
{
    $token = issue_token((int) $user['id'], 'reset', 3600);
    $url = base_url() . '/reset?t=' . $token;
    return send_mail(
        $user['email'],
        (string) $user['name'],
        t('mail_reset_subject', ['site' => cfg('site_name')]),
        mail_template(t('mail_reset_title'), '<p>' . t('mail_reset_body') . '</p>', $url, t('mail_reset_cta'))
    );
}
