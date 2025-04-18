<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $mileage = $_POST['mileage'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Обработка изображения
    $image = $_FILES['image']['name'];
    $target = 'images/uploads/' . basename($image);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $conn->prepare("INSERT INTO cars (user_id, brand, model, year, mileage, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisss", $user_id, $brand, $model, $year, $mileage, $price, $description, $image);
        $stmt->execute();
        header('Location: profile.php');
    } else {
        $error = "Ошибка загрузки изображения.";
    }
}
?>
<!DOCTYPE html>
<html lang="ру">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить автомобиль - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Добавить автомобиль</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="brand" class="block text-sm font-medium">Марка</label>
                <input type="text" name="brand" id="brand" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="model" class="block text-sm font-medium">Модель</label>
                <input type="text" name="model" id="model" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="year" class="block text-sm font-medium">Год</label>
                <input type="number" name="year" id="year" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="mileage" class="block text-sm font-medium">Пробег (км)</label>
                <input type="number" name="mileage" id="mileage" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium">Цена (₽)</label>
                <input type="number" name="price" id="price" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium">Описание</label>
                <textarea name="description" id="description" required class="w-full p-2 border rounded"></textarea>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium">Изображение</label>
                <input type="file" name="image" id="image" accept="image/*" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Добавить</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>