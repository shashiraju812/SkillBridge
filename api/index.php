<?php
/**
 * Vercel Serverless Front Controller for SkillBridge
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// Strip /wtproject/ prefix if present
if (strpos($uri, '/wtproject/') === 0) {
    $relative = substr($uri, strlen('/wtproject/'));
} else {
    $relative = ltrim($uri, '/');
}

if ($relative === '' || $relative === '/') {
    require __DIR__ . '/../index.php';
    exit;
}

$target = dirname(__DIR__) . '/' . $relative;

if (is_dir($target)) {
    $target = rtrim($target, '/') . '/index.php';
}

if (file_exists($target) && !is_dir($target)) {
    $ext = pathinfo($target, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        require $target;
        exit;
    }
}

// Fallback to home page
require dirname(__DIR__) . '/index.php';
