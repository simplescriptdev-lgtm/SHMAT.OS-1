<?php
// Функція для створення нового товару

function createProduct($mysqli, $name, $article, $brand, $categoryId, $subcategoryId = null) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва товару не може бути порожньою.'];
    }

    if (empty(trim($article))) {
        return ['success' => false, 'message' => 'Артикул товару не може бути порожнім.'];
    }

    if (empty(trim($brand))) {
        return ['success' => false, 'message' => 'Бренд товару не може бути порожнім.'];
    }

    if ($categoryId <= 0) {
        return ['success' => false, 'message' => 'Необхідно вибрати категорію.'];
    }

    $name = trim($name);
    $article = trim($article);
    $brand = trim($brand);

    $stmt = $mysqli->prepare('INSERT INTO products (name, article, brand, category_id, subcategory_id) VALUES (?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sssii', $name, $article, $brand, $categoryId, $subcategoryId);
        if ($stmt->execute()) {
            $productId = $mysqli->insert_id;
            $stmt->close();
            return ['success' => true, 'message' => 'Товар успішно створено.', 'id' => $productId];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при створенні товару: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}



