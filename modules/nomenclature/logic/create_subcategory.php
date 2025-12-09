<?php
// Функція для створення нової підкатегорії

function createSubcategory($mysqli, $categoryId, $name) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва підкатегорії не може бути порожньою.'];
    }

    if ($categoryId <= 0) {
        return ['success' => false, 'message' => 'Невірний ID категорії.'];
    }

    $name = trim($name);
    $stmt = $mysqli->prepare('INSERT INTO product_subcategories (category_id, name) VALUES (?, ?)');
    if ($stmt) {
        $stmt->bind_param('is', $categoryId, $name);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Підкатегорію успішно створено.', 'id' => $mysqli->insert_id];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при створенні підкатегорії: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



