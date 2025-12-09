<?php
// Створення нового блоку дозволів
function createPermissionBlock($mysqli, $name, $notes = null) {
    $stmt = $mysqli->prepare("INSERT INTO permission_blocks (name, notes) VALUES (?, ?)");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Помилка підготовки запиту: ' . $mysqli->error];
    }
    $stmt->bind_param("ss", $name, $notes);
    if ($stmt->execute()) {
        $id = $mysqli->insert_id;
        $stmt->close();
        return ['success' => true, 'id' => $id, 'message' => 'Блок дозволів успішно створено'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => 'Помилка створення блоку: ' . $error];
    }
}
?>



