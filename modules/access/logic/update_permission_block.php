<?php
// Оновлення блоку дозволів
function updatePermissionBlock($mysqli, $id, $name, $notes = null) {
    $stmt = $mysqli->prepare("UPDATE permission_blocks SET name = ?, notes = ? WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("ssi", $name, $notes, $id);
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Блок дозволів успішно оновлено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка оновлення блоку: ' . $error];
    }
}
?>



