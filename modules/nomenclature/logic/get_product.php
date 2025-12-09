<?php
// Функція для отримання товару за ID з фотографіями

function getProduct($mysqli, $productId) {
    $stmt = $mysqli->prepare("
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
        WHERE p.id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            // Отримуємо фотографії товару
            $imagesStmt = $mysqli->prepare("
                SELECT id, file_path, mime_type, display_order 
                FROM product_images 
                WHERE product_id = ? 
                ORDER BY display_order ASC
            ");
            if ($imagesStmt) {
                $imagesStmt->bind_param('i', $productId);
                $imagesStmt->execute();
                $imagesResult = $imagesStmt->get_result();
                $product['images'] = [];
                while ($row = $imagesResult->fetch_assoc()) {
                    $product['images'][] = $row;
                }
                $imagesStmt->close();
            }
        }
        
        return $product;
    }
    
    return null;
}
