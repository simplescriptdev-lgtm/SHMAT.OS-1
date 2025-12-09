<?php
// Функція для пошуку товарів

function searchProducts($mysqli, $searchTerm, $excludeProductIds = []) {
    $products = [];
    
    if (empty($searchTerm)) {
        return $products;
    }

    $term = '%' . trim($searchTerm) . '%';
    
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
            WHERE p.name LIKE ? OR p.article LIKE ? OR p.brand LIKE ?
            ORDER BY p.name
            LIMIT 20
        ');
        if ($stmt) {
            $stmt->bind_param('sss', $term, $term, $term);
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
            WHERE (p.name LIKE ? OR p.article LIKE ? OR p.brand LIKE ?)
            AND p.id NOT IN ($placeholders)
            ORDER BY p.name
            LIMIT 20
        ");
        if ($stmt) {
            $types = 'sss' . str_repeat('i', count($excludeProductIds));
            $params = array_merge([$term, $term, $term], $excludeProductIds);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $stmt->close();
        }
    }
    
    return $products;
}



