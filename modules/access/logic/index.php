<?php
// Головний файл логіки модуля "Доступи", який збирає всі функції докупи.

// Підключаємо bootstrap для доступу до БД
require_once __DIR__ . '/../../../config/bootstrap.php';

// Підключаємо всі файли з логікою (одна функція = один файл)
require_once __DIR__ . '/create_user.php';
require_once __DIR__ . '/get_users.php';
require_once __DIR__ . '/get_user.php';
require_once __DIR__ . '/update_user.php';
require_once __DIR__ . '/delete_user.php';

// Логіка для блоків дозволів
require_once __DIR__ . '/get_permission_blocks.php';
require_once __DIR__ . '/get_permission_block.php';
require_once __DIR__ . '/create_permission_block.php';
require_once __DIR__ . '/update_permission_block.php';
require_once __DIR__ . '/delete_permission_block.php';

// Логіка для доступів
require_once __DIR__ . '/get_permissions.php';
require_once __DIR__ . '/get_permission.php';
require_once __DIR__ . '/create_permission.php';
require_once __DIR__ . '/update_permission.php';
require_once __DIR__ . '/delete_permission.php';

// Функції перевірки доступу
require_once __DIR__ . '/check_access.php';

// Функції для роботи з призначеними доступами
require_once __DIR__ . '/get_user_permissions.php';
require_once __DIR__ . '/save_user_permissions.php';

