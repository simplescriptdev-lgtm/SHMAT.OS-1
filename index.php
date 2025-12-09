<?php
// Тимчасовий файл для сумісності.
// Рекомендується в MAMP вказати Document Root на папку `public`.
// Поки що просто перекидаємо на справжній публічний entrypoint.

header('Location: public/index.php');
exit;