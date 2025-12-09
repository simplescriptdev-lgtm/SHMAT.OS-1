<?php
// Додавання товару до кошика переміщення
function addToTransferCart($mysqli, $productId, $quantity, $warehouseId) {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    if ($productId <= 0 || $quantity <= 0 || $warehouseId <= 0) {
        return ['success' => false, 'message' => 'Невірні параметри'];
    }
    
    // Перевіряємо, чи товар вже є в кошику
    $found = false;
    foreach ($_SESSION['transfer_cart'] as $key => $item) {
        if ($item['product_id'] == $productId && $item['warehouse_id'] == $warehouseId) {
            // Оновлюємо кількість
            $_SESSION['transfer_cart'][$key]['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        // Отримуємо інформацію про товар
        $productStmt = $mysqli->prepare("SELECT name, article, brand FROM products WHERE id = ?");
        $productStmt->bind_param("i", $productId);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $product = $productResult->fetch_assoc();
        $productStmt->close();
        
        if ($product) {
            $_SESSION['transfer_cart'][] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'product_article' => $product['article'],
                'quantity' => $quantity,
                'warehouse_id' => $warehouseId
            ];
        } else {
            return ['success' => false, 'message' => 'Товар не знайдено'];
        }
    }
    
    return ['success' => true];
}
?>



