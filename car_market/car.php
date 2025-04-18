<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header('Location: catalog.php');
    exit;
}

$car_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT cars.*, brands.name AS brand_name, models.name AS model_name, users.phone 
                        FROM cars 
                        JOIN brands ON cars.brand_id = brands.id 
                        JOIN models ON cars.model_id = models.id 
                        JOIN users ON cars.user_id = users.id 
                        WHERE cars.id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    header('Location: catalog.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ру">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['brand_name']) ?> <?= htmlspecialchars($car['model_name']) ?> - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <img src="images/uploads/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand_name']) ?>" class="w-full h-64 object-cover rounded mb-4">
            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($car['brand_name']) ?> <?= htmlspecialchars($car['model_name']) ?></h1>
            <p class="text-gray-600 mb-2">Год: <?= htmlspecialchars($car['year']) ?></p>
            <p class="text-gray-600 mb-2">Пробег: <?= htmlspecialchars($car['mileage']) ?> км</p>
            <p class="text-lg font-bold text-blue-600 mb-4"><?= number_format($car['price'], 0, '.', ' ') ?> ₽</p>
            <p class="text-gray-800"><?= nl2br(htmlspecialchars($car['description'])) ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="show-phone" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Посмотреть номер</button>
                <p id="phone" style="display:none;"><?= htmlspecialchars($car['phone']) ?></p>
                <script>
                    document.getElementById('show-phone').addEventListener('click', function() {
                        document.getElementById('phone').style.display = 'block';
                        this.style.display = 'none';
                    });
                </script>
            <?php else: ?>
                <p class="mt-4 text-gray-600">Чтобы увидеть номер, <a href="login.php" class="text-blue-500">войдите</a> или <a href="register.php" class="text-blue-500">зарегистрируйтесь</a>.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>