<?php
// Включение отображения ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Убедимся, что нет вывода перед заголовками
ob_start();

session_start();

// Очистка всех переменных сессии
$_SESSION = [];

// Уничтожение сессии
session_destroy();

// Удаление cookie сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Предотвращение кэширования
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Перенаправление
header('Location: index.php');
exit;

ob_end_flush();
?>