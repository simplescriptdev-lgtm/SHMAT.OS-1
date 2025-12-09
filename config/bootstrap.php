<?php
// Глобальний bootstrap-файл: сесія + підключення до БД + підготовка таблиць

// Увімкнення відображення помилок для діагностики (вимкнути на продакшені)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Запускаємо сесію один раз для всіх модулів
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --------------------------------------------------
// Налаштування бази даних (MAMP MySQL)
// --------------------------------------------------
// За замовчуванням у MAMP:
//   host: localhost
//   port: 8889 (MySQL port у MAMP, якщо не змінювали)
//   user: root
//   password: root
//   db: shmat_os (можете змінити назву нижче)
$DB_HOST = 'localhost';
$DB_PORT = 8889;            // якщо в MAMP інший порт, змініть тут
$DB_USER = 'root';
$DB_PASSWORD = 'root';
$DB_NAME = 'shmat_os';

// --------------------------------------------------
// Налаштування "директора з техніки" (технічного директора) за замовчуванням
// --------------------------------------------------
// Ці дані використовуються як логін/пароль за замовчуванням.
$TECH_DIRECTOR_LOGIN = 'techdirector';
$TECH_DIRECTOR_PASSWORD = 'secret123';

// --------------------------------------------------
// Підключення до бази даних та підготовка таблиці
// --------------------------------------------------
/** @var mysqli|null $mysqli */
$mysqli = null;
$dbError = null;

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, '', $DB_PORT);

if ($mysqli->connect_errno) {
    $dbError = 'Помилка підключення до MySQL: ' . $mysqli->connect_error;
} else {
    // Створюємо БД, якщо її ще немає
    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$DB_NAME}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        $dbError = 'Не вдалося створити базу даних: ' . $mysqli->error;
    } else {
        // Переходимо в цю БД
        if (!$mysqli->select_db($DB_NAME)) {
            $dbError = 'Не вдалося вибрати базу даних: ' . $mysqli->error;
        } else {
            // Створення таблиці користувачів, якщо її немає
            $createTableSql = "
                CREATE TABLE IF NOT EXISTS technical_directors (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    login VARCHAR(191) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            if (!$mysqli->query($createTableSql)) {
                $dbError = 'Не вдалося створити таблицю technical_directors: ' . $mysqli->error;
            } else {
                // Переконуємося, що користувач технічного директора за замовчуванням існує
                $safeLogin = $mysqli->real_escape_string($TECH_DIRECTOR_LOGIN);
                $checkSql = "SELECT id FROM technical_directors WHERE login = '{$safeLogin}' LIMIT 1";
                $result = $mysqli->query($checkSql);
                if ($result && $result->num_rows === 0) {
                    $passwordHash = password_hash($TECH_DIRECTOR_PASSWORD, PASSWORD_DEFAULT);
                    $safePasswordHash = $mysqli->real_escape_string($passwordHash);
                    $insertSql = "
                        INSERT INTO technical_directors (login, password_hash)
                        VALUES ('{$safeLogin}', '{$safePasswordHash}')
                    ";
                    if (!$mysqli->query($insertSql)) {
                        $dbError = 'Не вдалося створити користувача технічного директора: ' . $mysqli->error;
                    }
                }
                if ($result instanceof mysqli_result) {
                    $result->free();
                }
            }

            // Створення таблиці логотипів проєкту
            if ($dbError === null) {
                $createLogosSql = "
                    CREATE TABLE IF NOT EXISTS project_logos (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        file_path VARCHAR(255) NOT NULL,
                        mime_type VARCHAR(100) NOT NULL,
                        is_active TINYINT(1) NOT NULL DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createLogosSql)) {
                    $dbError = 'Не вдалося створити таблицю project_logos: ' . $mysqli->error;
                }
            }

            // Створення таблиці категорій товарів
            if ($dbError === null) {
                $createCategoriesSql = "
                    CREATE TABLE IF NOT EXISTS product_categories (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createCategoriesSql)) {
                    $dbError = 'Не вдалося створити таблицю product_categories: ' . $mysqli->error;
                }
            }

            // Створення таблиці підкатегорій товарів
            if ($dbError === null) {
                $createSubcategoriesSql = "
                    CREATE TABLE IF NOT EXISTS product_subcategories (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        category_id INT UNSIGNED NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE,
                        INDEX idx_category_id (category_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createSubcategoriesSql)) {
                    $dbError = 'Не вдалося створити таблицю product_subcategories: ' . $mysqli->error;
                }
            }

            // Створення таблиці товарів
            if ($dbError === null) {
                $createProductsSql = "
                    CREATE TABLE IF NOT EXISTS products (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        article VARCHAR(100) NOT NULL,
                        brand VARCHAR(255) NOT NULL,
                        category_id INT UNSIGNED NOT NULL,
                        subcategory_id INT UNSIGNED NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT,
                        FOREIGN KEY (subcategory_id) REFERENCES product_subcategories(id) ON DELETE SET NULL,
                        INDEX idx_category_id (category_id),
                        INDEX idx_subcategory_id (subcategory_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createProductsSql)) {
                    $dbError = 'Не вдалося створити таблицю products: ' . $mysqli->error;
                }
            }

            // Створення таблиці фотографій товарів
            if ($dbError === null) {
                $createProductImagesSql = "
                    CREATE TABLE IF NOT EXISTS product_images (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        product_id INT UNSIGNED NOT NULL,
                        file_path VARCHAR(255) NOT NULL,
                        mime_type VARCHAR(100) NOT NULL,
                        display_order INT UNSIGNED DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        INDEX idx_product_id (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createProductImagesSql)) {
                    $dbError = 'Не вдалося створити таблицю product_images: ' . $mysqli->error;
                }
            }

            // Створення таблиці користувачів для модуля Доступи
            if ($dbError === null) {
                $createUsersSql = "
                    CREATE TABLE IF NOT EXISTS access_users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        full_name VARCHAR(255) NOT NULL,
                        login VARCHAR(191) NOT NULL UNIQUE,
                        password_hash VARCHAR(255) NOT NULL,
                        notes TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_login (login)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createUsersSql)) {
                    $dbError = 'Не вдалося створити таблицю access_users: ' . $mysqli->error;
                }
            }

            // Створення таблиці складів
            if ($dbError === null) {
                $createWarehousesSql = "
                    CREATE TABLE IF NOT EXISTS warehouses (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        identification_number VARCHAR(191) NOT NULL UNIQUE,
                        description TEXT NULL,
                        has_scheme BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_identification_number (identification_number)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createWarehousesSql)) {
                    $dbError = 'Не вдалося створити таблицю warehouses: ' . $mysqli->error;
                }
            }

            // Створення таблиці зв'язку склад-користувач (many-to-many)
            if ($dbError === null) {
                $createWarehouseUsersSql = "
                    CREATE TABLE IF NOT EXISTS warehouse_users (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        warehouse_id INT UNSIGNED NOT NULL,
                        user_id INT UNSIGNED NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES access_users(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_warehouse_user (warehouse_id, user_id),
                        INDEX idx_warehouse_id (warehouse_id),
                        INDEX idx_user_id (user_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createWarehouseUsersSql)) {
                    $dbError = 'Не вдалося створити таблицю warehouse_users: ' . $mysqli->error;
                }
            }

            // Створення таблиці постачальників для модуля Прихід товару
            if ($dbError === null) {
                $createSuppliersSql = "
                    CREATE TABLE IF NOT EXISTS suppliers (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        information TEXT NULL,
                        notes TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_name (name)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createSuppliersSql)) {
                    $dbError = 'Не вдалося створити таблицю suppliers: ' . $mysqli->error;
                }
            }

            // Створення таблиці приходів товару
            if ($dbError === null) {
                $createGoodsReceiptsSql = "
                    CREATE TABLE IF NOT EXISTS goods_receipts (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        supplier_id INT UNSIGNED NOT NULL,
                        receipt_number VARCHAR(191) NOT NULL UNIQUE,
                        total_amount DECIMAL(15,2) DEFAULT 0.00,
                        items_count INT UNSIGNED DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
                        INDEX idx_supplier_id (supplier_id),
                        INDEX idx_receipt_number (receipt_number),
                        INDEX idx_created_at (created_at)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createGoodsReceiptsSql)) {
                    $dbError = 'Не вдалося створити таблицю goods_receipts: ' . $mysqli->error;
                }
            }

            // Створення таблиці позицій приходу товару
            if ($dbError === null) {
                $createGoodsReceiptItemsSql = "
                    CREATE TABLE IF NOT EXISTS goods_receipt_items (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        receipt_id INT UNSIGNED NOT NULL,
                        product_id INT UNSIGNED NOT NULL,
                        quantity DECIMAL(10,3) NOT NULL DEFAULT 1,
                        unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                        total_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
                        INDEX idx_receipt_id (receipt_id),
                        INDEX idx_product_id (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createGoodsReceiptItemsSql)) {
                    $dbError = 'Не вдалося створити таблицю goods_receipt_items: ' . $mysqli->error;
                }
            }

            // Створення таблиці товарів на складах
            if ($dbError === null) {
                $createWarehouseStockSql = "
                    CREATE TABLE IF NOT EXISTS warehouse_stock (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        warehouse_id INT UNSIGNED NOT NULL,
                        product_id INT UNSIGNED NOT NULL,
                        quantity DECIMAL(10,3) NOT NULL DEFAULT 0,
                        sector VARCHAR(50) NULL,
                        `row_number` VARCHAR(50) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
                        UNIQUE KEY unique_warehouse_product (warehouse_id, product_id, sector, `row_number`),
                        INDEX idx_warehouse_id (warehouse_id),
                        INDEX idx_product_id (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createWarehouseStockSql)) {
                    $dbError = 'Не вдалося створити таблицю warehouse_stock: ' . $mysqli->error;
                }
            }

            // Створення таблиці переміщень між складами
            if ($dbError === null) {
                $createWarehouseTransfersSql = "
                    CREATE TABLE IF NOT EXISTS warehouse_transfers (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        from_warehouse_id INT UNSIGNED NOT NULL,
                        to_warehouse_id INT UNSIGNED NOT NULL,
                        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                        created_by_user_id INT UNSIGNED NULL,
                        approved_by_user_id INT UNSIGNED NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
                        FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
                        FOREIGN KEY (created_by_user_id) REFERENCES access_users(id) ON DELETE SET NULL,
                        FOREIGN KEY (approved_by_user_id) REFERENCES access_users(id) ON DELETE SET NULL,
                        INDEX idx_from_warehouse (from_warehouse_id),
                        INDEX idx_to_warehouse (to_warehouse_id),
                        INDEX idx_status (status)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createWarehouseTransfersSql)) {
                    $dbError = 'Не вдалося створити таблицю warehouse_transfers: ' . $mysqli->error;
                }
            }

            // Створення таблиці позицій переміщення
            if ($dbError === null) {
                $createWarehouseTransferItemsSql = "
                    CREATE TABLE IF NOT EXISTS warehouse_transfer_items (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        transfer_id INT UNSIGNED NOT NULL,
                        product_id INT UNSIGNED NOT NULL,
                        quantity DECIMAL(10,3) NOT NULL,
                        from_sector VARCHAR(50) NULL,
                        from_row_number VARCHAR(50) NULL,
                        to_sector VARCHAR(50) NULL,
                        to_row_number VARCHAR(50) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (transfer_id) REFERENCES warehouse_transfers(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
                        INDEX idx_transfer_id (transfer_id),
                        INDEX idx_product_id (product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createWarehouseTransferItemsSql)) {
                    $dbError = 'Не вдалося створити таблицю warehouse_transfer_items: ' . $mysqli->error;
                }
            }

            // Створення таблиці блоків дозволів
            if ($dbError === null) {
                $createPermissionBlocksSql = "
                    CREATE TABLE IF NOT EXISTS permission_blocks (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        notes TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_name (name)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createPermissionBlocksSql)) {
                    $dbError = 'Не вдалося створити таблицю permission_blocks: ' . $mysqli->error;
                }
            }

            // Створення таблиці доступів (permissions)
            if ($dbError === null) {
                $createPermissionsSql = "
                    CREATE TABLE IF NOT EXISTS permissions (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        block_id INT UNSIGNED NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        code VARCHAR(100) NOT NULL,
                        notes TEXT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (block_id) REFERENCES permission_blocks(id) ON DELETE CASCADE,
                        INDEX idx_block_id (block_id),
                        INDEX idx_code (code),
                        UNIQUE KEY unique_code (code)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createPermissionsSql)) {
                    $dbError = 'Не вдалося створити таблицю permissions: ' . $mysqli->error;
                }
            }

            // Створення таблиці призначених доступів користувачам
            if ($dbError === null) {
                $createUserPermissionsSql = "
                    CREATE TABLE IF NOT EXISTS user_permissions (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        user_id INT UNSIGNED NOT NULL,
                        permission_id INT UNSIGNED NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES access_users(id) ON DELETE CASCADE,
                        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_user_permission (user_id, permission_id),
                        INDEX idx_user_id (user_id),
                        INDEX idx_permission_id (permission_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ";
                if (!$mysqli->query($createUserPermissionsSql)) {
                    $dbError = 'Не вдалося створити таблицю user_permissions: ' . $mysqli->error;
                }
            }

            // Зчитуємо поточний активний логотип, якщо він є
            if ($dbError === null) {
                $CURRENT_LOGO_PATH = null;
                $logoResult = $mysqli->query("SELECT file_path FROM project_logos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
                if ($logoResult && $logoResult->num_rows === 1) {
                    $row = $logoResult->fetch_assoc();
                    $CURRENT_LOGO_PATH = $row['file_path'];
                }
                if ($logoResult instanceof mysqli_result) {
                    $logoResult->free();
                }
            }
        }
    }
}


