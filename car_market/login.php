<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptcha_secret = '6LeRCR0rAAAAABFqaxbI4ornXDLarycUV5ujUOP9'; 
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $response = json_decode($response);

    investisseurs if ($response->success) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: profile.php');
            exit;
        } else {
            $error = "Неверное имя пользователя или пароль.";
        }
    } else {
        $error = "Пожалуйста, подтвердите, что вы не робот.";
    }
}
?>
<!DOCTYPE html>
<html lang="ру">
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
        <h1 class="text-3xl font-bold mb-8">Вход</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium">Имя пользователя</label>
                <input type="text" name="username" id="username" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Пароль</label>
                <input type="password" name="password" id="password" required class="w-full p-2 border rounded">
            </div>
            <div class="g-recaptcha" data-sitekey="6LeRCR0rAAAAAFwia5LtrhX07vh2_GyXqiftyH2v"></div> <!-- Замените на ваш ключ -->
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Войти</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>