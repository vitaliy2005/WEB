<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$cars = $conn->query("
    SELECT cars.*, users.username, brands.name as brand_name, models.name as model_name 
    FROM cars
    JOIN users ON cars.user_id = users.id
    JOIN brands ON cars.brand_id = brands.id
    JOIN models ON cars.model_id = models.id
    ORDER BY cars.id DESC
");

if (isset($_GET['delete'])) {
    $car_id = (int)$_GET['delete'];
    $car = $conn->query("SELECT image FROM cars WHERE id = $car_id")->fetch_assoc();
    
    if ($car['image'] && file_exists("images/uploads/{$car['image']}")) {
        unlink("images/uploads/{$car['image']}");
    }
    
    $conn->query("DELETE FROM cars WHERE id = $car_id");
    header('Location: admin_cars.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление объявлениями - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Управление объявлениями</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Автомобиль</th>
                            <th class="py-2 px-4 border-b">Пользователь</th>
                            <th class="py-2 px-4 border-b">Цена</th>
                            <th class="py-2 px-4 border-b">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($car = $cars->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= $car['id'] ?></td>
                            <td class="py-2 px-4 border-b">
                                <?= htmlspecialchars($car['brand_name']) ?> <?= htmlspecialchars($car['model_name']) ?>
                                <div class="text-sm text-gray-600">
                                    <?= $car['year'] ?> год, <?= $car['mileage'] ?> км
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($car['username']) ?></td>
                            <td class="py-2 px-4 border-b"><?= number_format($car['price'], 0, '.', ' ') ?> ₽</td>
                            <td class="py-2 px-4 border-b">
                                <a href="car.php?id=<?= $car['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800 mr-2" 
                                   title="Просмотреть">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="admin_cars.php?delete=<?= $car['id'] ?>" 
                                   class="text-red-600 hover:text-red-800" 
                                   onclick="return confirm('Удалить объявление?')"
                                   title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>