<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Если пользователь уже авторизован - редирект
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin.php' : 'profile.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация reCAPTCHA
    $recaptcha_secret = '6LcGGh0rAAAAAFb48IFDX9L8KKU7JPOaFrZuOHtT';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $response = json_decode($response);

    if (!$response->success) {
        $error = "Пожалуйста, подтвердите, что вы не робот.";
    } else {
        // Проверка учетных данных
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            // Успешная авторизация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['is_admin'] ? 'admin' : 'user';
            $_SESSION['username'] = $user['username'];
            
            // Редирект по роли
            header('Location: ' . ($user['is_admin'] ? 'admin.php' : 'profile.php'));
            exit;
        } else {
            $error = "Неверное имя пользователя или пароль.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-center">Вход в систему</h1>
            
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Имя пользователя</label>
                    <input type="text" id="username" name="username" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="g-recaptcha" data-sitekey="6LcGGh0rAAAAAFCBa7Ty0sXX6UbEixdhlPOoqCvp"></div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Войти
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <a href="register.php" class="text-sm text-blue-600 hover:text-blue-500">
                    Нет аккаунта? Зарегистрируйтесь
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Обработка reCAPTCHA
    function onSubmit(token) {
        document.getElementById("login-form").submit();
    }
    </script>
</body>
</html>