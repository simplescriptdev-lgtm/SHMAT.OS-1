<?php
// Функція для отримання постачальника за ID

function getSupplier($mysqli, $supplierId) {
    $stmt = $mysqli->prepare('SELECT id, name, information, notes, created_at, updated_at FROM suppliers WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $supplierId);
        $stmt->execute();
        $result = $stmt->get_result();
        $supplier = $result->fetch_assoc();
        $stmt->close();
        return $supplier;
    }
    return null;
}



