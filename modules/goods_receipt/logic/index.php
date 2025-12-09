<?php
// Головний файл логіки модуля "Прихід товару", який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/create_supplier.php';
require_once __DIR__ . '/get_suppliers.php';
require_once __DIR__ . '/get_supplier.php';
require_once __DIR__ . '/update_supplier.php';
require_once __DIR__ . '/delete_supplier.php';
require_once __DIR__ . '/create_receipt.php';
require_once __DIR__ . '/get_receipts.php';
require_once __DIR__ . '/get_receipt.php';
require_once __DIR__ . '/delete_receipt.php';
require_once __DIR__ . '/search_products.php';
require_once __DIR__ . '/get_all_products.php';

