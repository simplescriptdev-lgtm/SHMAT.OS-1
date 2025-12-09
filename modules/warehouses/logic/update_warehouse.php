<?php
// Функція для оновлення складу

function updateWarehouse($mysqli, $id, $name, $identificationNumber, $description = null, $hasScheme = false, $userIds = []) {
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

    // Перевірка на унікальність ідентифікаційного номера (крім поточного складу)
    $checkStmt = $mysqli->prepare('SELECT id FROM warehouses WHERE identification_number = ? AND id != ? LIMIT 1');
    if ($checkStmt) {
        $checkStmt->bind_param('si', $identificationNumber, $id);
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
        // Оновлення складу
        $stmt = $mysqli->prepare('UPDATE warehouses SET name = ?, identification_number = ?, description = ?, has_scheme = ? WHERE id = ?');
        if (!$stmt) {
            throw new Exception('Помилка підготовки запиту: ' . $mysqli->error);
        }

        $stmt->bind_param('sssii', $name, $identificationNumber, $description, $hasScheme, $id);
        if (!$stmt->execute()) {
            throw new Exception('Помилка при оновленні складу: ' . $mysqli->error);
        }
        $stmt->close();

        // Видалення всіх поточних зв'язків користувачів зі складом
        $deleteStmt = $mysqli->prepare('DELETE FROM warehouse_users WHERE warehouse_id = ?');
        if (!$deleteStmt) {
            throw new Exception('Помилка підготовки запиту для видалення користувачів: ' . $mysqli->error);
        }
        $deleteStmt->bind_param('i', $id);
        if (!$deleteStmt->execute()) {
            throw new Exception('Помилка при видаленні користувачів: ' . $mysqli->error);
        }
        $deleteStmt->close();

        // Додавання нових користувачів до складу
        if (!empty($userIds) && is_array($userIds)) {
            $userStmt = $mysqli->prepare('INSERT INTO warehouse_users (warehouse_id, user_id) VALUES (?, ?)');
            if (!$userStmt) {
                throw new Exception('Помилка підготовки запиту для користувачів: ' . $mysqli->error);
            }

            foreach ($userIds as $userId) {
                $userId = (int) $userId;
                if ($userId > 0) {
                    $userStmt->bind_param('ii', $id, $userId);
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
        return ['success' => true, 'message' => 'Склад успішно оновлено.'];
    } catch (Exception $e) {
        $mysqli->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}



