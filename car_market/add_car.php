<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = (int)$_POST['brand_id'];
    $model_id = (int)$_POST['model_id'];
    $year = (int)$_POST['year'];
    $mileage = (int)$_POST['mileage'];
    $price = (float)$_POST['price'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Обработка изображения
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_type = $_FILES['image']['type'];
    $target = 'images/uploads/' . basename($image);

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($image_type, $allowed_types)) {
        $error = "Недопустимый тип файла. Разрешены только JPEG и PNG.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Ошибка загрузки: Код {$_FILES['image']['error']}";
    } elseif (!move_uploaded_file($image_tmp, $target)) {
        $error = "Не удалось переместить файл. Проверьте права на папку.";
    } else {
        $stmt = $conn->prepare("INSERT INTO cars (user_id, brand_id, model_id, year, mileage, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisiss", $user_id, $brand_id, $model_id, $year, $mileage, $price, $description, $image);
        $stmt->execute();
        header('Location: profile.php');
        exit;
    }
}

$brands = $conn->query("SELECT * FROM brands ORDER BY name");
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
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
            <div class="mb-4">
                <label for="brand_id" class="block text-sm font-medium">Марка</label>
                <select name="brand_id" id="brand_id" required class="w-full p-2 border rounded">
                    <option value="">Выберите марку</option>
                    <?php while ($brand = $brands->fetch_assoc()): ?>
                        <option value="<?= $brand['id'] ?>"><?= htmlspecialchars($brand['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="model_id" class="block text-sm font-medium">Модель</label>
                <select name="model_id" id="model_id" required class="w-full p-2 border rounded">
                    <option value="">Выберите модель</option>
                </select>
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
                <input type="number" name="price" id="price" step="0.01" required class="w-full p-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium">Описание</label>
                <textarea name="description" id="description" required class="w-full p-2 border rounded"></textarea>
            </div>
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium">Изображение (JPEG или PNG)</label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/jpg,image/png" required class="w-full p-2 border rounded">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Добавить</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>