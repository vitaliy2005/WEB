<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Проверка, что пользователь не админ
if (isAdmin($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$cars = [];
$error = '';

try {
    $stmt = $conn->prepare("
        SELECT cars.*, brands.name as brand_name, models.name as model_name 
        FROM cars 
        JOIN brands ON cars.brand_id = brands.id
        JOIN models ON cars.model_id = models.id
        WHERE cars.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cars = $stmt->get_result();
    $stmt->close();
} catch (Exception $e) {
    $error = "Ошибка при загрузке данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Мой профиль</h1>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <a href="add_car.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-8 inline-block">Добавить автомобиль</a>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if ($cars->num_rows > 0): ?>
                <?php while ($car = $cars->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md relative">
                        <?php if (!empty($car['image'])): ?>
                            <img src="images/uploads/<?= htmlspecialchars($car['image']) ?>" 
                                 alt="<?= htmlspecialchars($car['brand_name']) ?>" 
                                 class="w-full h-48 object-cover rounded mb-4">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded mb-4">
                                <span class="text-gray-500">Нет изображения</span>
                            </div>
                        <?php endif; ?>
                        
                        <h2 class="text-xl font-semibold"><?= htmlspecialchars($car['brand_name']) ?> <?= htmlspecialchars($car['model_name']) ?></h2>
                        <p class="text-gray-600">Год: <?= htmlspecialchars($car['year']) ?></p>
                        <p class="text-gray-600">Пробег: <?= htmlspecialchars($car['mileage']) ?> км</p>
                        <p class="text-lg font-bold text-blue-600"><?= number_format($car['price'], 0, '.', ' ') ?> ₽</p>
                        
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="car.php?id=<?= $car['id'] ?>" 
                               class="block text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex-1">
                                Подробнее
                            </a>
                            <a href="edit_car.php?id=<?= $car['id'] ?>" 
                               class="block text-center bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex-1">
                                Редактировать
                            </a>
                            <button onclick="confirmDelete(<?= $car['id'] ?>)" 
                                    class="block text-center bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 flex-1">
                                Удалить
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center col-span-3">У вас нет объявлений.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
    function confirmDelete(carId) {
        if (confirm('Вы уверены, что хотите удалить это объявление?')) {
            window.location.href = 'delete_car.php?id=' + carId;
        }
    }
    </script>
</body>
</html>
<?php
$conn->close();
?>