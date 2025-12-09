<?php
// Функція для отримання всіх товарів

function getProducts($mysqli) {
    $sql = "
        SELECT 
            p.id,
            p.name,
            p.article,
            p.brand,
            p.category_id,
            p.subcategory_id,
            p.created_at,
            p.updated_at,
            c.name as category_name,
            sc.name as subcategory_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        LEFT JOIN product_subcategories sc ON p.subcategory_id = sc.id
        ORDER BY p.created_at DESC
    ";
    $result = $mysqli->query($sql);
    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $result->free();
    }
    return $products;
}



