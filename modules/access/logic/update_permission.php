<?php
// Оновлення доступу
function updatePermission($mysqli, $id, $blockId, $name, $code, $notes = null) {
    $stmt = $mysqli->prepare("UPDATE permissions SET block_id = ?, name = ?, code = ?, notes = ? WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("isssi", $blockId, $name, $code, $notes, $id);
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Доступ успішно оновлено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка оновлення доступу: ' . $error];
    }
}
?>



