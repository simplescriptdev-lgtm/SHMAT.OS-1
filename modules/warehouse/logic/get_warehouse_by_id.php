<?php
// Функція для отримання складу за ID

function getWarehouseById($mysqli, $warehouseId) {
    $stmt = $mysqli->prepare('
        SELECT 
            id,
            name,
            identification_number,
            description,
            has_scheme,
            created_at,
            updated_at
        FROM warehouses
        WHERE id = ?
    ');
    
    if ($stmt) {
        $stmt->bind_param('i', $warehouseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $warehouse = $result->fetch_assoc();
        $stmt->close();
        return $warehouse;
    }
    return null;
}

