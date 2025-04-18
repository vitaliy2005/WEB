<?php
session_start();
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarMarket - Покупка и продажа автомобилей</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold text-center mb-8">Добро пожаловать на CarMarket</h1>
        <div class="flex justify-center mb-8">
            <a href="catalog.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">Перейти в каталог</a>
        </div>
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Широкий выбор</h2>
                <p>Найдите автомобиль своей мечты среди тысяч объявлений.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Простая продажа</h2>
                <p>Разместите объявление за пару минут.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Безопасные сделки</h2>
                <p>Мы заботимся о вашей безопасности.</p>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>