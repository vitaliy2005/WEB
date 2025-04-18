<?php
// includes/header.php

// Старт сессии, если еще не начата
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключение необходимых файлов
$required_files = [
    __DIR__ . '/db.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Missing required file: " . $file);
    }
}

// Проверка авторизации и прав
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = false;

if ($is_logged_in && function_exists('isAdmin')) {
    try {
        $is_admin = isAdmin($_SESSION['user_id']);
    } catch (Exception $e) {
        error_log("Admin check error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CarMarket') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <header class="bg-gray-800 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <a href="index.php" class="text-xl font-bold hover:text-gray-300 transition-colors">
                    CarMarket
                </a>
                
                <!-- Мобильное меню (бургер) -->
                <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <!-- Основное меню -->
                <nav class="hidden md:block">
                    <ul class="flex space-x-6">
                        <li>
                            <a href="index.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'text-blue-300' : '' ?>">
                                Главная
                            </a>
                        </li>
                        <li>
                            <a href="catalog.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'catalog.php' ? 'text-blue-300' : '' ?>">
                                Каталог
                            </a>
                        </li>
                        
                        <?php if ($is_logged_in): ?>
                            <li>
                                <a href="profile.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'text-blue-300' : '' ?>">
                                    Профиль
                                </a>
                            </li>
                            <?php if ($is_admin): ?>
                                <li>
                                    <a href="admin.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'text-blue-300' : '' ?>">
                                        Админка
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="logout.php" class="hover:text-gray-300 transition-colors">
                                    Выход
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="login.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'text-blue-300' : '' ?>">
                                    Вход
                                </a>
                            </li>
                            <li>
                                <a href="register.php" class="hover:text-gray-300 transition-colors <?= basename($_SERVER['PHP_SELF']) === 'register.php' ? 'text-blue-300' : '' ?>">
                                    Регистрация
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        
        <!-- Мобильное меню (скрытое по умолчанию) -->
        <div id="mobile-menu" class="hidden md:hidden bg-gray-700 pb-3 px-4">
            <ul class="flex flex-col space-y-2">
                <!-- Тот же список ссылок, что и в основном меню -->
                <li><a href="index.php" class="block hover:text-gray-300 transition-colors">Главная</a></li>
                <li><a href="catalog.php" class="block hover:text-gray-300 transition-colors">Каталог</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="profile.php" class="block hover:text-gray-300 transition-colors">Профиль</a></li>
                    <?php if ($is_admin): ?>
                        <li><a href="admin.php" class="block hover:text-gray-300 transition-colors">Админка</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="block hover:text-gray-300 transition-colors">Выход</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="block hover:text-gray-300 transition-colors">Вход</a></li>
                    <li><a href="register.php" class="block hover:text-gray-300 transition-colors">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        <!-- Сюда будет вставляться контент страниц -->