<?php
// Функція для отримання всіх товарів (для блоку 4 модального вікна)

function getAllProducts($mysqli, $limit = 20, $offset = 0, $excludeProductIds = []) {
    $products = [];
    
    if (empty($excludeProductIds) || !is_array($excludeProductIds)) {
        $stmt = $mysqli->prepare('
            SELECT 
                p.id,
                p.name,
                p.article,
                p.brand,
                pc.name as category_name,
                psc.name as subcategory_name
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN product_subcategories psc ON p.subcategory_id = psc.id
            ORDER BY p.name
            LIMIT ? OFFSET ?
        ');
        if ($stmt) {
            $stmt->bind_param('ii', $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $stmt->close();
        }
    } else {
        $excludeProductIds = array_filter(array_map('intval', $excludeProductIds));
        
        if (empty($excludeProductIds)) {
            $stmt = $mysqli->prepare('
                SELECT 
                    p.id,
                    p.name,
                    p.article,
                    p.brand,
                    pc.name as category_name,
                    psc.name as subcategory_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN product_subcategories psc ON p.subcategory_id = psc.id
                ORDER BY p.name
                LIMIT ? OFFSET ?
            ');
            if ($stmt) {
                $stmt->bind_param('ii', $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                $stmt->close();
            }
        } else {
            $placeholders = implode(',', array_fill(0, count($excludeProductIds), '?'));
            $stmt = $mysqli->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.article,
                    p.brand,
                    pc.name as category_name,
                    psc.name as subcategory_name
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN product_subcategories psc ON p.subcategory_id = psc.id
                WHERE p.id NOT IN ($placeholders)
                ORDER BY p.name
                LIMIT ? OFFSET ?
            ");
            if ($stmt) {
                $types = str_repeat('i', count($excludeProductIds)) . 'ii';
                $params = array_merge($excludeProductIds, [$limit, $offset]);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
                $stmt->close();
            }
        }
    }
    
    return $products;
}

