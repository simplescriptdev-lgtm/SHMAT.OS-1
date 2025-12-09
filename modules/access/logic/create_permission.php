<?php
// Створення нового доступу
function createPermission($mysqli, $blockId, $name, $code, $notes = null) {
    $stmt = $mysqli->prepare("INSERT INTO permissions (block_id, name, code, notes) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("isss", $blockId, $name, $code, $notes);
    if ($stmt->execute()) {
        $id = $mysqli->insert_id;
        $stmt->close();
        return ['success' => true, 'id' => $id, 'message' => 'Доступ успішно створено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка створення доступу: ' . $error];
    }
}
?>



