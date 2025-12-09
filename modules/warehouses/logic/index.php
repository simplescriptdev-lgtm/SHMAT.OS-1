<?php
// Головний файл логіки модуля "Управління складами", який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/create_warehouse.php';
require_once __DIR__ . '/get_warehouses.php';
require_once __DIR__ . '/get_warehouse.php';
require_once __DIR__ . '/update_warehouse.php';
require_once __DIR__ . '/delete_warehouse.php';
require_once __DIR__ . '/get_users.php';
// require_once __DIR__ . '/get_stock.php';
// require_once __DIR__ . '/move_stock.php';
// require_once __DIR__ . '/create_inventory.php';
// require_once __DIR__ . '/generate_warehouse_report.php';

