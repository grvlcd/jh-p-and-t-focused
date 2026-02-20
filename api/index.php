<?php

// Vercel rewrites /api/:path* to /api/index.php and passes the path as a query param.
// Rebuild REQUEST_URI so Laravel receives the original path (e.g. /api/login).
if (isset($_GET['path']) && is_string($_GET['path'])) {
    $path = '/api/'.trim($_GET['path'], '/');
    $params = $_GET;
    unset($params['path']);
    $_SERVER['REQUEST_URI'] = $path.(empty($params) ? '' : '?'.http_build_query($params));
    $_SERVER['QUERY_STRING'] = http_build_query($params);
    $_GET = $params;
}
// Fallback: runtime passed rewritten path; try header or default to root.
elseif (isset($_SERVER['REQUEST_URI']) && (
    $_SERVER['REQUEST_URI'] === '/api/index.php'
    || str_ends_with($_SERVER['REQUEST_URI'], '/api/index.php')
)) {
    $original = $_SERVER['HTTP_X_ORIGINAL_URL'] ?? $_SERVER['HTTP_X_VERCEL_ORIGINAL_URL'] ?? null;
    if ($original !== null) {
        $_SERVER['REQUEST_URI'] = parse_url($original, PHP_URL_PATH) ?: $original;
        if (! empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
        }
    } else {
        // No path param: assume root (e.g. catch-all for /).
        $_SERVER['REQUEST_URI'] = empty($_SERVER['QUERY_STRING']) ? '/' : '/?'.$_SERVER['QUERY_STRING'];
    }
}

require __DIR__.'/../public/index.php';
