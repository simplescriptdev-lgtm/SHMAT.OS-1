<?php
// Функції для перевірки доступу користувачів

/**
 * Перевіряє, чи має користувач доступ до модуля
 * @param mysqli $mysqli
 * @param string $moduleName Назва модуля (dashboard, nomenclature, goods_receipt, warehouses, access, settings)
 * @return bool
 */
function hasAccessToModule($mysqli, $moduleName) {
    // Перевіряємо, чи користувач залогінений
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        return false;
    }
    
    // Технічний директор має повний доступ до всіх модулів
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technical_director') {
        return true;
    }
    
    // Звичайні користувачі не мають доступу до модулів (крім складу, якщо вони відповідальні)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
        // Модуль "warehouse" (окремий склад) - перевіряється окремо через hasAccessToWarehouse
        // Повертаємо true, щоб дозволити спробувати відкрити, але доступ перевіриться в модулі warehouse
        if ($moduleName === 'warehouse') {
            return true;
        }
        
        // Модуль "warehouses" (управління складами) - доступний тільки технічному директору
        // Але меню "Управління складами" показується, якщо користувач має склади (перевіряється в login_view.php)
        if ($moduleName === 'warehouses') {
            return false; // Звичайні користувачі не мають доступу до модуля управління складами
        }
        
        // Всі інші модулі недоступні для звичайних користувачів
        return false;
    }
    
    return false;
}

/**
 * Отримує список складів, до яких користувач має доступ
 * @param mysqli $mysqli
 * @param int $userId ID користувача
 * @return array
 */
function getUserWarehouses($mysqli, $userId) {
    $warehouses = [];
    
    $stmt = $mysqli->prepare("
        SELECT w.id, w.name, w.identification_number, w.description, w.has_scheme, w.created_at
        FROM warehouses w
        INNER JOIN warehouse_users wu ON w.id = wu.warehouse_id
        WHERE wu.user_id = ?
        ORDER BY w.name
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $warehouses[] = $row;
        }
        
        $stmt->close();
    }
    
    return $warehouses;
}

/**
 * Перевіряє, чи має користувач доступ до конкретного складу
 * @param mysqli $mysqli
 * @param int $warehouseId ID складу
 * @return bool
 */
function hasAccessToWarehouse($mysqli, $warehouseId) {
    // Перевіряємо, чи користувач залогінений
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        return false;
    }
    
    // Технічний директор має доступ до всіх складів
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technical_director') {
        return true;
    }
    
    // Для звичайних користувачів перевіряємо, чи вони відповідальні за цей склад
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user') {
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
        
        if ($userId <= 0) {
            return false;
        }
        
        $stmt = $mysqli->prepare("
            SELECT COUNT(*) as count
            FROM warehouse_users
            WHERE warehouse_id = ? AND user_id = ?
        ");
        
        if ($stmt) {
            $stmt->bind_param("ii", $warehouseId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] > 0;
        }
    }
    
    return false;
}

/**
 * Перевіряє, чи має користувач доступ до сторінки (використовується для редиректів)
 * @param mysqli $mysqli
 * @param string $page Назва сторінки
 * @return bool
 */
function hasAccessToPage($mysqli, $page) {
    // Кнопка "Вийти" завжди доступна
    if ($page === 'logout') {
        return true;
    }
    
    // Перевіряємо доступ до модуля
    return hasAccessToModule($mysqli, $page);
}
?>
