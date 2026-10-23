<?php
/**
 * SkillBridge Local Development Server Router
 * Handles /wtproject/ URLs for PHP built-in web server.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Root redirect to platform home
if ($uri === '/' || $uri === '' || $uri === '/wtproject' || $uri === '/wtproject/') {
    require __DIR__ . '/index.php';
    return true;
}

// Strip /wtproject/ prefix if present
if (strpos($uri, '/wtproject/') === 0) {
    $relative = substr($uri, strlen('/wtproject/'));
} else {
    $relative = ltrim($uri, '/');
}

$target = __DIR__ . '/' . $relative;

if (is_dir($target)) {
    $target = rtrim($target, '/') . '/index.php';
}

if (file_exists($target) && !is_dir($target)) {
    $ext = pathinfo($target, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        require $target;
        return true;
    }
    // Serve static assets and practical files directly with appropriate MIME type
    $mimeTypes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'html' => 'text/html; charset=UTF-8',
        'htm'  => 'text/html; charset=UTF-8',
        'xml'  => 'application/xml; charset=UTF-8',
        'dtd'  => 'application/xml-dtd',
        'xsd'  => 'application/xml; charset=UTF-8',
        'txt'  => 'text/plain; charset=UTF-8',
        'pdf'  => 'application/pdf',
    ];
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    header("Content-Length: " . filesize($target));
    readfile($target);
    return true;
}

// 404 handler
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested resource " . htmlspecialchars($uri) . " was not found on this SkillBridge instance.</p><a href='/wtproject/index.php'>Return to Home</a>";
return true;

