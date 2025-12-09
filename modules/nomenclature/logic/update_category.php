<?php
// Функція для оновлення назви категорії

function updateCategory($mysqli, $id, $name) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва категорії не може бути порожньою.'];
    }

    $name = trim($name);
    $stmt = $mysqli->prepare('UPDATE product_categories SET name = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('si', $name, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Категорію успішно оновлено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при оновленні категорії: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



