<?php
// Отримання кількості товарів у кошику переміщення
function getTransferCartCount() {
    // Ініціалізуємо кошик переміщення в сесії, якщо його немає
    if (!isset($_SESSION['transfer_cart'])) {
        $_SESSION['transfer_cart'] = [];
    }
    
    $count = count($_SESSION['transfer_cart'] ?? []);
    return ['success' => true, 'count' => $count];
}
?>



