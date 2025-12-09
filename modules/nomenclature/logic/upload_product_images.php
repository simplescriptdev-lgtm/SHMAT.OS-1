<?php
// Функція для завантаження фотографій товару

function uploadProductImages($mysqli, $productId, $uploadedFiles) {
    if (empty($uploadedFiles) || !isset($uploadedFiles['name'])) {
        return ['success' => false, 'message' => 'Немає файлів для завантаження.'];
    }

    $uploadDir = __DIR__ . '/../../../public/uploads/products';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowed = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    $uploadedPaths = [];
    $displayOrder = 0;
    $fileCount = is_array($uploadedFiles['name']) ? count($uploadedFiles['name']) : 1;

    for ($i = 0; $i < $fileCount && $i < 3; $i++) {
        if (is_array($uploadedFiles['name'])) {
            $fileName = $uploadedFiles['name'][$i];
            $fileTmp = $uploadedFiles['tmp_name'][$i];
            $fileError = $uploadedFiles['error'][$i];
            $fileSize = $uploadedFiles['size'][$i];
        } else {
            $fileName = $uploadedFiles['name'];
            $fileTmp = $uploadedFiles['tmp_name'];
            $fileError = $uploadedFiles['error'];
            $fileSize = $uploadedFiles['size'];
            $i = 3; // щоб вийти з циклу після першої ітерації
        }

        if ($fileError !== UPLOAD_ERR_OK) {
            continue;
        }

        // Перевірка розміру (до 5 МБ)
        if ($fileSize > 5 * 1024 * 1024) {
            continue;
        }

        // Перевірка MIME-типу
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileTmp);
        if (!isset($allowed[$mime])) {
            continue;
        }

        $ext = $allowed[$mime];
        $uniqueFileName = 'product_' . $productId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $uniqueFileName;

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $webPath = '/public/uploads/products/' . $uniqueFileName;
            $stmt = $mysqli->prepare('INSERT INTO product_images (product_id, file_path, mime_type, display_order) VALUES (?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('issi', $productId, $webPath, $mime, $displayOrder);
                $stmt->execute();
                $stmt->close();
                $uploadedPaths[] = $webPath;
                $displayOrder++;
            }
        }
    }

    return ['success' => true, 'message' => 'Фотографії успішно завантажено.', 'paths' => $uploadedPaths];
}

