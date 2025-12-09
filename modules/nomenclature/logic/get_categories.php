<?php
// Функція для отримання всіх категорій

function getCategories($mysqli) {
    $result = $mysqli->query('SELECT id, name, created_at, updated_at FROM product_categories ORDER BY created_at DESC');
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $result->free();
    }
    return $categories;
}



