<?php
// Обробка завантаження логотипу

require_once __DIR__ . '/../../../config/bootstrap.php';

if ($dbError !== null) {
    $_SESSION['logo_upload_error'] = 'Помилка бази даних: неможливо зберегти логотип.';
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

if (!isset($_FILES['logo_rect']) || $_FILES['logo_rect']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['logo_upload_error'] = 'Не вдалося завантажити файл.';
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

$file = $_FILES['logo_rect'];

// Перевірка розміру (до 2 МБ)
if ($file['size'] > 2 * 1024 * 1024) {
    $_SESSION['logo_upload_error'] = 'Файл завеликий. Максимальний розмір — 2 МБ.';
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

// Перевірка MIME-типу
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = [
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
    $_SESSION['logo_upload_error'] = 'Неприпустимий формат файлу. Дозволені: PNG, JPG, WEBP.';
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

$ext = $allowed[$mime];

$uploadDir = __DIR__ . '/../../../public/uploads/logos';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = 'logo_rect_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $uploadDir . '/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $_SESSION['logo_upload_error'] = 'Не вдалося зберегти файл на сервері.';
    header('Location: /modules/auth/login_view.php?page=settings&tab=6');
    exit;
}

// Веб-шлях до файлу (для поточного налаштування DocumentRoot = корінь проєкту)
// Файли зберігаємо в папці public/uploads/logos, тому у браузері шлях виглядає як /public/uploads/logos/...
$webPath = '/public/uploads/logos/' . $fileName;

// Деактивуємо попередні логотипи
$mysqli->query('UPDATE project_logos SET is_active = 0');

// Додаємо новий запис
$stmt = $mysqli->prepare('INSERT INTO project_logos (file_path, mime_type, is_active) VALUES (?, ?, 1)');
if ($stmt) {
    $stmt->bind_param('ss', $webPath, $mime);
    if ($stmt->execute()) {
        $_SESSION['logo_upload_success'] = 'Логотип успішно оновлено.';
    } else {
        $_SESSION['logo_upload_error'] = 'Не вдалося зберегти логотип у базі даних.';
    }
    $stmt->close();
} else {
    $_SESSION['logo_upload_error'] = 'Помилка підготовки запиту до бази даних.';
}

header('Location: /modules/auth/login_view.php?page=settings&tab=6');
exit;


