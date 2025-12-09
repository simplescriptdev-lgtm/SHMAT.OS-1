<?php
// Головний файл логіки модуля переміщення товарів, який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/add_to_cart.php';
require_once __DIR__ . '/get_cart.php';
require_once __DIR__ . '/get_cart_count.php';
require_once __DIR__ . '/update_cart_item.php';
require_once __DIR__ . '/remove_from_cart.php';
require_once __DIR__ . '/create_transfer.php';
require_once __DIR__ . '/get_all_warehouses.php';
require_once __DIR__ . '/get_pending_transfers.php';
require_once __DIR__ . '/approve_transfer.php';
require_once __DIR__ . '/reject_transfer.php';
?>

