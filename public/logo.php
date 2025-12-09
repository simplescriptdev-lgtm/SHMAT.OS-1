<?php
// Віддає поточний логотип проєкту як зображення

require_once __DIR__ . '/../config/bootstrap.php';

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Шукаємо активний логотип
$mime = 'image/png';
$filePath = null;

if ($dbError === null && $mysqli instanceof mysqli) {
    $res = $mysqli->query("SELECT file_path, mime_type FROM project_logos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
    if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $mime = $row['mime_type'] ?: $mime;
        // file_path зберігається як шлях від кореня проєкту, наприклад /public/uploads/logos/...
        $relative = $row['file_path'];
        $filePath = dirname(__DIR__) . $relative;
    }
    if ($res instanceof mysqli_result) {
        $res->free();
    }
}

// Якщо логотип з БД не знайшовся або файл відсутній — віддаємо дефолтний
if (!$filePath || !is_file($filePath)) {
    $filePath = __DIR__ . '/assets/shmatos-logo.png';
    $mime = 'image/png';
}

if (!is_file($filePath)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mime);
readfile($filePath);
exit;




