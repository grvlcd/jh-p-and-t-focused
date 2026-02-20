<?php

// When Vercel rewrites e.g. /api/user to /api/index.php, the runtime may pass the
// rewritten path. Restore the original path so Laravel can route correctly.
if (isset($_SERVER['REQUEST_URI']) && (
    $_SERVER['REQUEST_URI'] === '/api/index.php'
    || str_ends_with($_SERVER['REQUEST_URI'], '/api/index.php')
)) {
    $original = $_SERVER['HTTP_X_ORIGINAL_URL'] ?? $_SERVER['HTTP_X_VERCEL_ORIGINAL_URL'] ?? null;
    if ($original !== null) {
        $_SERVER['REQUEST_URI'] = parse_url($original, PHP_URL_PATH) ?: $original;
        if (! empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
        }
    }
}

require __DIR__.'/../public/index.php';
