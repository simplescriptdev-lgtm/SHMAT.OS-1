<?php
// Логіка виходу (logout)
require_once __DIR__ . '/../../config/bootstrap.php';

// Видаляємо дані користувача із сесії
unset($_SESSION['is_logged_in'], $_SESSION['user_role'], $_SESSION['user_id'], $_SESSION['user_login'], $_SESSION['auth_error']);
session_regenerate_id(true);

header('Location: login_view.php');
exit;




