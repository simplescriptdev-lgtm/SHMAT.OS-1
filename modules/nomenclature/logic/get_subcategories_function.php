<?php
// Функція для отримання підкатегорій за ID категорії

function getSubcategories($mysqli, $categoryId = null) {
    if ($categoryId !== null) {
        $stmt = $mysqli->prepare('SELECT id, category_id, name, created_at, updated_at FROM product_subcategories WHERE category_id = ? ORDER BY created_at ASC');
        if ($stmt) {
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
            $subcategories = [];
            while ($row = $result->fetch_assoc()) {
                $subcategories[] = $row;
            }
            $stmt->close();
            return $subcategories;
        }
    } else {
        $result = $mysqli->query('SELECT id, category_id, name, created_at, updated_at FROM product_subcategories ORDER BY category_id, created_at ASC');
        $subcategories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $subcategories[] = $row;
            }
            $result->free();
        }
        return $subcategories;
    }
    return [];
}



