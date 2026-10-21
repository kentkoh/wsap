<?php
declare(strict_types=1);

/**
 * Forms: a branded questionnaire; answers are composed into the first WhatsApp message.
 * questions JSON: [{id, type, label, help, required, options:[...], max}]
 */

const FORM_TYPES = ['short', 'paragraph', 'email', 'phone', 'number', 'dropdown', 'single', 'multiple', 'time', 'date', 'daterange'];
const FORM_MAX_QUESTIONS = 20;

function form_default_theme(): array
{
    return ['bg' => '#f7f6f1', 'text' => '#10201a', 'title' => '#0b3d2e', 'bar' => '#25d366', 'button' => '#0b3d2e'];
}

function form_theme(array $form): array
{
    $t = json_decode((string) $form['theme'], true);
    $out = form_default_theme();
    foreach ($out as $k => $v) {
        if (isset($t[$k]) && preg_match('/^#[0-9a-f]{6}$/i', (string) $t[$k])) {
            $out[$k] = $t[$k];
        }
    }
    return $out;
}

function form_questions(array $form): array
{
    $q = json_decode((string) $form['questions'], true);
    return is_array($q) ? $q : [];
}

function form_is_published(array $form, ?array $owner = null): bool
{
    if ((int) $form['active'] !== 1) {
        return false;
    }
    if (!empty($form['premium_until']) && $form['premium_until'] > now()) {
        return true;
    }
    return $owner !== null && is_premium($owner);
}

function form_url(array $form): string
{
    if (cfg('env') === 'development') {
        return base_url() . '/' . $form['slug'];
    }
    return 'https://' . $form['domain'] . '/' . $form['slug'];
}

function form_display(array $form): string
{
    return $form['domain'] . '/' . $form['slug'];
}

function user_forms(int $userId): array
{
    return db_rows('SELECT * FROM forms WHERE user_id = ? ORDER BY created_at DESC', [$userId]);
}

function find_form(string $slug): ?array
{
    $slug = strtolower($slug);
    return preg_match('/^[a-z0-9-]{3,30}$/', $slug) ? db_row('SELECT * FROM forms WHERE slug = ?', [$slug]) : null;
}

/** Validate questions from the editor (JSON string). Returns clean array. */
function clean_questions(string $json): array
{
    $in = json_decode($json, true);
    if (!is_array($in)) {
        throw new InvalidArgumentException(t('fm_err_questions'));
    }
    $out = [];
    foreach ($in as $i => $q) {
        if (!is_array($q)) {
            continue;
        }
        $type = in_array($q['type'] ?? '', FORM_TYPES, true) ? $q['type'] : 'short';
        $label = mb_substr(trim((string) ($q['label'] ?? '')), 0, 120);
        if ($label === '') {
            continue;
        }
        $opts = [];
        if (in_array($type, ['dropdown', 'single', 'multiple'], true)) {
            foreach ((array) ($q['options'] ?? []) as $o) {
                $o = mb_substr(trim((string) $o), 0, 80);
                if ($o !== '') {
                    $opts[] = $o;
                }
            }
            if (count($opts) < 2) {
                throw new InvalidArgumentException(t('fm_err_options', ['q' => $label]));
            }
        }
        $out[] = [
            'id'       => preg_match('/^q[a-z0-9]{4,12}$/', (string) ($q['id'] ?? '')) ? $q['id'] : 'q' . substr(bin2hex(random_bytes(4)), 0, 6),
            'type'     => $type,
            'label'    => $label,
            'help'     => mb_substr(trim((string) ($q['help'] ?? '')), 0, 160),
            'required' => !empty($q['required']),
            'options'  => $opts,
            'max'      => max(0, min(1000, (int) ($q['max'] ?? 0))),
        ];
        if (count($out) >= FORM_MAX_QUESTIONS) {
            break;
        }
    }
    if (!$out) {
        throw new InvalidArgumentException(t('fm_err_questions'));
    }
    return $out;
}

/** Validate submitted answers against the questions. Returns [{q, a}] or throws with the first problem. */
function validate_answers(array $questions, array $post): array
{
    $out = [];
    foreach ($questions as $q) {
        $key = $q['id'];
        $raw = $post[$key] ?? '';
        if ($q['type'] === 'multiple') {
            $vals = array_values(array_intersect($q['options'], array_map('strval', (array) $raw)));
            if ($q['required'] && !$vals) {
                throw new InvalidArgumentException(t('fm_err_required', ['q' => $q['label']]));
            }
            $out[] = ['q' => $q['label'], 'a' => implode(', ', $vals)];
            continue;
        }
        if ($q['type'] === 'daterange') {
            $from = trim((string) ($post[$key . '_from'] ?? ''));
            $to = trim((string) ($post[$key . '_to'] ?? ''));
            if ($q['required'] && ($from === '' || $to === '')) {
                throw new InvalidArgumentException(t('fm_err_required', ['q' => $q['label']]));
            }
            $out[] = ['q' => $q['label'], 'a' => $from !== '' || $to !== '' ? $from . ' → ' . $to : ''];
            continue;
        }
        $v = trim((string) (is_array($raw) ? '' : $raw));
        if ($q['required'] && $v === '') {
            throw new InvalidArgumentException(t('fm_err_required', ['q' => $q['label']]));
        }
        if ($v !== '') {
            switch ($q['type']) {
                case 'email':
                    if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
                        throw new InvalidArgumentException(t('fm_err_email', ['q' => $q['label']]));
                    }
                    break;
                case 'phone':
                    if (!preg_match('/^\+?[\d\s()-]{6,20}$/', $v)) {
                        throw new InvalidArgumentException(t('fm_err_phone', ['q' => $q['label']]));
                    }
                    break;
                case 'number':
                    if (!is_numeric($v)) {
                        throw new InvalidArgumentException(t('fm_err_number', ['q' => $q['label']]));
                    }
                    break;
                case 'dropdown':
                case 'single':
                    if (!in_array($v, $q['options'], true)) {
                        throw new InvalidArgumentException(t('fm_err_required', ['q' => $q['label']]));
                    }
                    break;
            }
            $limit = $q['max'] > 0 ? $q['max'] : ($q['type'] === 'paragraph' ? 1000 : 200);
            $v = mb_substr($v, 0, $limit);
        }
        $out[] = ['q' => $q['label'], 'a' => $v];
    }
    return $out;
}

/** The WhatsApp message: opening line, then *Label* / answer pairs. */
function compose_form_message(array $form, array $answers): string
{
    $lines = [];
    $open = trim((string) $form['opening_line']);
    if ($open !== '') {
        $lines[] = $open;
        $lines[] = '';
    }
    foreach ($answers as $a) {
        if ($a['a'] === '') {
            continue;
        }
        $lines[] = '*' . str_replace('*', '', $a['q']) . '*';
        $lines[] = $a['a'];
        $lines[] = '';
    }
    return trim(implode("\n", $lines));
}
