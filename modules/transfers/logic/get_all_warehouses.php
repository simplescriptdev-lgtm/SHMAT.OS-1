<?php
// Отримання списку всіх складів
function getAllWarehouses($mysqli) {
    $warehouses = [];
    
    $stmt = $mysqli->prepare("SELECT id, name, identification_number FROM warehouses ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $warehouses[] = $row;
    }
    
    $stmt->close();
    
    return ['success' => true, 'warehouses' => $warehouses];
}
?>



