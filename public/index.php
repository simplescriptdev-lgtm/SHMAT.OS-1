<?php
// Публічний вхід у застосунок (frontend entrypoint)
require_once __DIR__ . '/../config/bootstrap.php';

// Поки що стартова сторінка порталу — модуль Авторизації.
header('Location: ../modules/auth/login_view.php');
exit;




