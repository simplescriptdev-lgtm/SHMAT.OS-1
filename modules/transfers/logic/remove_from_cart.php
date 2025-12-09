<?php
// Видалення товару з кошика переміщення
function removeFromTransferCart($productId) {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Невірні параметри'];
    }
    
    foreach ($_SESSION['transfer_cart'] as $key => $item) {
        if ($item['product_id'] == $productId) {
            unset($_SESSION['transfer_cart'][$key]);
            $_SESSION['transfer_cart'] = array_values($_SESSION['transfer_cart']); // Переіндексуємо масив
            break;
        }
    }
    
    return ['success' => true];
}
?>



