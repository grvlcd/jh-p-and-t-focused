<?php

// Vercel rewrites send requests here. Rebuild REQUEST_URI so Laravel sees the original path (e.g. /api/register).
$originalUri = $_SERVER['HTTP_X_VERCEL_ORIGINAL_URL'] ?? $_SERVER['HTTP_X_ORIGINAL_URL'] ?? null;
if ($originalUri !== null && $originalUri !== '') {
    $path = parse_url($originalUri, PHP_URL_PATH) ?: $originalUri;
    $query = parse_url($originalUri, PHP_URL_QUERY);
    $_SERVER['REQUEST_URI'] = $query ? $path.'?'.$query : $path;
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['QUERY_STRING'] = $query ?? '';
} elseif (isset($_GET['path']) && is_string($_GET['path'])) {
    $path = '/api/'.trim($_GET['path'], '/');
    $params = $_GET;
    unset($params['path']);
    $query = http_build_query($params);
    $_SERVER['REQUEST_URI'] = $path.(empty($params) ? '' : '?'.$query);
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['QUERY_STRING'] = $query;
    $_GET = $params;
} elseif (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/api/index.php')) {
    $_SERVER['REQUEST_URI'] = empty($_SERVER['QUERY_STRING']) ? '/' : '/?'.$_SERVER['QUERY_STRING'];
    $_SERVER['PATH_INFO'] = '/';
}

require __DIR__.'/../public/index.php';
