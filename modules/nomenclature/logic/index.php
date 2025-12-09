<?php
// Головний файл логіки модуля "Номенклатура товару", який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/create_category.php';
require_once __DIR__ . '/get_categories.php';
require_once __DIR__ . '/update_category.php';
require_once __DIR__ . '/delete_category.php';
require_once __DIR__ . '/create_subcategory.php';
require_once __DIR__ . '/get_subcategories_function.php';
require_once __DIR__ . '/update_subcategory.php';
require_once __DIR__ . '/delete_subcategory.php';
require_once __DIR__ . '/create_product.php';
require_once __DIR__ . '/get_products.php';
require_once __DIR__ . '/get_product.php';
require_once __DIR__ . '/update_product.php';
require_once __DIR__ . '/delete_product.php';
require_once __DIR__ . '/upload_product_images.php';

