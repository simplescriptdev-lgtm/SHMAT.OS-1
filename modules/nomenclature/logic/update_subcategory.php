<?php
// Функція для оновлення назви підкатегорії

function updateSubcategory($mysqli, $id, $name) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва підкатегорії не може бути порожньою.'];
    }

    $name = trim($name);
    $stmt = $mysqli->prepare('UPDATE product_subcategories SET name = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('si', $name, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Підкатегорію успішно оновлено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при оновленні підкатегорії: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



