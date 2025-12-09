<?php
// Отримання кошика переміщення
function getTransferCart() {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    $items = $_SESSION['transfer_cart'] ?? [];
    return ['success' => true, 'items' => $items];
}
?>



