<?php
// Функція для створення нового складу

function createWarehouse($mysqli, $name, $identificationNumber, $description = null, $hasScheme = false, $userIds = []) {
    if (empty(trim($name))) {
        return ['success' => false, 'message' => 'Назва складу не може бути порожньою.'];
    }

    if (empty(trim($identificationNumber))) {
        return ['success' => false, 'message' => 'Ідентифікаційний номер складу не може бути порожнім.'];
    }

    $name = trim($name);
    $identificationNumber = trim($identificationNumber);
    $description = $description ? trim($description) : null;
    $hasScheme = (bool) $hasScheme;

    // Перевірка на унікальність ідентифікаційного номера
    $checkStmt = $mysqli->prepare('SELECT id FROM warehouses WHERE identification_number = ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('s', $identificationNumber);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows > 0) {
            $checkStmt->close();
            return ['success' => false, 'message' => 'Склад з таким ідентифікаційним номером вже існує.'];
        }
        $checkStmt->close();
    }

    // Початок транзакції
    $mysqli->begin_transaction();

    try {
        // Створення складу
        $stmt = $mysqli->prepare('INSERT INTO warehouses (name, identification_number, description, has_scheme) VALUES (?, ?, ?, ?)');
        if (!$stmt) {
            throw new Exception('Помилка підготовки запиту: ' . $mysqli->error);
        }

        $stmt->bind_param('sssi', $name, $identificationNumber, $description, $hasScheme);
        if (!$stmt->execute()) {
            throw new Exception('Помилка при створенні складу: ' . $mysqli->error);
        }

        $warehouseId = $mysqli->insert_id;
        $stmt->close();

        // Додавання користувачів до складу
        if (!empty($userIds) && is_array($userIds)) {
            $userStmt = $mysqli->prepare('INSERT INTO warehouse_users (warehouse_id, user_id) VALUES (?, ?)');
            if (!$userStmt) {
                throw new Exception('Помилка підготовки запиту для користувачів: ' . $mysqli->error);
            }

            foreach ($userIds as $userId) {
                $userId = (int) $userId;
                if ($userId > 0) {
                    $userStmt->bind_param('ii', $warehouseId, $userId);
                    if (!$userStmt->execute()) {
                        // Ігноруємо помилки дублікатів
                        if (strpos($mysqli->error, 'Duplicate') === false) {
                            throw new Exception('Помилка при додаванні користувача: ' . $mysqli->error);
                        }
                    }
                }
            }
            $userStmt->close();
        }

        $mysqli->commit();
        return ['success' => true, 'message' => 'Склад успішно створено.', 'id' => $warehouseId];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



