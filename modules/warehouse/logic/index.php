<?php
// Головний файл логіки модуля окремого складу, який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/get_warehouse_by_id.php';
require_once __DIR__ . '/get_warehouse_stock.php';

