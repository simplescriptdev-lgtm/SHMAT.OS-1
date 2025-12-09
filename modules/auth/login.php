<?php
// Логіка входу (login)
require_once __DIR__ . '/../../config/bootstrap.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($dbError !== null) {
        $error = 'Неможливо виконати логін через помилку бази даних.';
    } else {
        $userFound = false;
        
        // Спочатку перевіряємо технічного директора
        $stmt = $mysqli->prepare('SELECT id, login, password_hash FROM technical_directors WHERE login = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $login);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $dbLogin, $dbPasswordHash);
                $stmt->fetch();

                if (password_verify($password, $dbPasswordHash)) {
                    // Успішний логін як технічний директор
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['user_role'] = 'technical_director';
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_login'] = $dbLogin;
                    session_regenerate_id(true);
                    $userFound = true;
                }
            }
            $stmt->close();
        }
        
        // Якщо не знайдено технічного директора, перевіряємо користувачів з модуля Доступи
        if (!$userFound) {
            $stmt = $mysqli->prepare('SELECT id, login, password_hash, full_name FROM access_users WHERE login = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $login);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($id, $dbLogin, $dbPasswordHash, $fullName);
                    $stmt->fetch();

                    if (password_verify($password, $dbPasswordHash)) {
                        // Успішний логін як користувач
                        $_SESSION['is_logged_in'] = true;
                        $_SESSION['user_role'] = 'user';
                        $_SESSION['user_id'] = $id;
                        $_SESSION['user_login'] = $dbLogin;
                        $_SESSION['user_full_name'] = $fullName;
                        session_regenerate_id(true);
                        $userFound = true;
                    } else {
                        $error = 'Невірний логін або пароль.';
                    }
                } else {
                    $error = 'Користувач з таким логіном не знайдений.';
                }
                $stmt->close();
            } else {
                $error = 'Не вдалося підготувати запит до бази даних.';
            }
        }
        
        // Якщо користувач не знайдений і помилка ще не встановлена
        if (!$userFound && $error === null) {
            $error = 'Невірний логін або пароль.';
        }
    }
}

// Зберігаємо повідомлення про помилку у сесію, щоб показати на формі
if ($error !== null) {
    $_SESSION['auth_error'] = $error;
}

header('Location: login_view.php');
exit;


