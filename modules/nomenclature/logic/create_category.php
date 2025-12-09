<?php
// Функція для створення нової категорії

function createCategory($mysqli, $name) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва категорії не може бути порожньою.'];
    }

    $name = trim($name);
    $stmt = $mysqli->prepare('INSERT INTO product_categories (name) VALUES (?)');
    if ($stmt) {
        $stmt->bind_param('s', $name);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Категорію успішно створено.', 'id' => $mysqli->insert_id];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при створенні категорії: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



