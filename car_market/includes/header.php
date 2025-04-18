<header class="bg-blue-600 text-white py-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-bold">CarMarket</a>
        <nav>
            <a href="catalog.php" class="px-4">Каталог</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="px-4">Профиль</a>
                <a href="logout.php" class="px-4">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="px-4">Вход</a>
                <a href="register.php" class="px-4">Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>