<?php
// Отримання призначених доступів для користувача
function getUserPermissions($mysqli, $userId) {
    $permissions = [];
    
    $stmt = $mysqli->prepare("
        SELECT permission_id
        FROM user_permissions
        WHERE user_id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $permissions[] = (int) $row['permission_id'];
        }
        
        $stmt->close();
    }
    
    return $permissions;
}
?>



