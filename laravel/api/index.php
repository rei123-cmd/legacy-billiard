<?php
// Ambil URL path yang diminta
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Jika mengakes root '/', arahkan ke public/index.php
if ($requestUri === '/' || $requestUri === '') {
    require __DIR__ . '/../public/index.php';
    exit;
}

// Susun path file asli yang dicari di dalam folder public
$file = __DIR__ . '/../public' . $requestUri;

// Jika filenya ada (misal: /gallery.php, /booking.php), muat file tersebut
if (file_exists($file) && !is_dir($file)) {
    require $file;
    exit;
}

// Fallback jika file tidak ditemukan
http_response_code(404);
echo "404 Not Found";