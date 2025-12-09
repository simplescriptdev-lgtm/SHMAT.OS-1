<?php
// Функція для оновлення товару

function updateProduct($mysqli, $id, $name, $article, $brand, $categoryId, $subcategoryId = null) {
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

    $stmt = $mysqli->prepare('UPDATE products SET name = ?, article = ?, brand = ?, category_id = ?, subcategory_id = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('sssiii', $name, $article, $brand, $categoryId, $subcategoryId, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Товар успішно оновлено.'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Помилка при оновленні товару: ' . $mysqli->error];
        }
    } else {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
}
