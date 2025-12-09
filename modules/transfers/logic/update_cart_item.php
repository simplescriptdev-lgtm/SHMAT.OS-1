<?php
// Оновлення кількості товару в кошику переміщення
function updateTransferCartItem($productId, $quantity) {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    if ($productId <= 0 || $quantity <= 0) {
        return ['success' => false, 'message' => 'Невірні параметри'];
    }
    
    foreach ($_SESSION['transfer_cart'] as $key => $item) {
        if ($item['product_id'] == $productId) {
            $_SESSION['transfer_cart'][$key]['quantity'] = $quantity;
            break;
        }
    }
    
    return ['success' => true];
}
?>



