<?php
declare(strict_types=1);
/** Beacon from the public form when the visitor moves past the first question (funnel "starts"). */
require dirname(__DIR__) . '/lib/bootstrap.php';
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0 && rate_limit('form_start', 60, 3600)) {
    try {
        db_exec('UPDATE forms SET starts = starts + 1 WHERE id = ?', [$id]);
    } catch (Throwable $e) {
        error_log('form_start: ' . $e->getMessage());
    }
}
http_response_code(204);
