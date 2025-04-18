<?php
require_once __DIR__ . '/includes/db.php';
session_start();

// Проверка прав
if (!isset($_SESSION['user_id']) || getUserRole($_SESSION['user_id']) !== 'admin') {
    header('Location: /login.php');
    exit;
}

$title = "Админ-панель";
require_once __DIR__ . '/includes/admin_header.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<div class="bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Административная панель</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="admin_users.php" class="bg-blue-100 p-4 rounded-lg hover:bg-blue-200">
            <h2 class="text-xl font-semibold">Пользователи</h2>
            <p>Управление пользователями системы</p>
        </a>
        
        <a href="admin_cars.php" class="bg-green-100 p-4 rounded-lg hover:bg-green-200">
            <h2 class="text-xl font-semibold">Объявления</h2>
            <p>Модерация автомобилей</p>
        </a>
        
        <a href="admin_models.php" class="bg-yellow-100 p-4 rounded-lg hover:bg-yellow-200">
            <h2 class="text-xl font-semibold">Марки и модели</h2>
            <p>Управление базой марок и моделей</p>
        </a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>