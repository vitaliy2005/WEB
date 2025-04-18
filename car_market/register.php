<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Проверка, существует ли username
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Имя пользователя уже занято.";
        } else {
            // Проверка, существует ли email
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email уже зарегистрирован.";
            } else {
                // Вставка нового пользователя
                $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $phone, $password);
                if ($stmt->execute()) {
                    header('Location: login.php');
                    exit;
                } else {
                    $error = "Ошибка при регистрации. Попробуйте снова.";
                }
            }
        }
    } catch (mysqli_sql_exception $e) {
        $error = "Ошибка базы данных: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Регистрация</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium">Имя пользователя</label>
                <input type="text" name="username" id="username" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" name="email" id="email" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium">Телефон</label>
                <input type="tel" name="phone" id="phone" required class="w-full p-2 border rounded" pattern="\+?[0-9]{10,15}">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Пароль</label>
                <input type="password" name="password" id="password" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Зарегистрироваться</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>