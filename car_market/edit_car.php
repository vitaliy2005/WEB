<?php
session_start();
require_once 'includes/db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Проверка наличия ID автомобиля
if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

$car_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Получаем данные автомобиля
$stmt = $conn->prepare("
    SELECT cars.*, brands.name AS brand_name, models.name AS model_name 
    FROM cars 
    JOIN brands ON cars.brand_id = brands.id
    JOIN models ON cars.model_id = models.id
    WHERE cars.id = ? AND cars.user_id = ?
");
$stmt->bind_param("ii", $car_id, $user_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Проверка существования авто и прав доступа
if (!$car) {
    header('Location: profile.php');
    exit;
}

// Обработка формы
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = (int)$_POST['brand_id'];
    $model_id = (int)$_POST['model_id'];
    $year = (int)$_POST['year'];
    $mileage = (int)$_POST['mileage'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);

    // Валидация
    if ($brand_id <= 0 || $model_id <= 0) {
        $error = "Выберите марку и модель";
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = "Некорректный год";
    }

    if (!$error) {
        // Обработка изображения
        $image_name = $car['image'];
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_info = getimagesize($_FILES['image']['tmp_name']);
            $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
            
            if ($image_info && in_array($image_info[2], $allowed_types)) {
                $extension = $image_info[2] === IMAGETYPE_JPEG ? 'jpg' : 'png';
                $new_image_name = uniqid('car_', true) . '.' . $extension;
                $target = 'images/uploads/' . $new_image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // Удаляем старое изображение
                    if (!empty($image_name) && file_exists("images/uploads/$image_name")) {
                        unlink("images/uploads/$image_name");
                    }
                    $image_name = $new_image_name;
                }
            }
        }

        // Обновляем данные в БД
        $stmt = $conn->prepare("
            UPDATE cars SET 
                brand_id = ?, 
                model_id = ?, 
                year = ?, 
                mileage = ?, 
                price = ?, 
                description = ?, 
                image = ?
            WHERE id = ?
        ");
        $stmt->bind_param("iiiidssi", $brand_id, $model_id, $year, $mileage, $price, $description, $image_name, $car_id);
        
        if ($stmt->execute()) {
            header('Location: profile.php');
            exit;
        } else {
            $error = "Ошибка при обновлении: " . $conn->error;
        }
    }
}

// Получаем список брендов
$brands = $conn->query("SELECT * FROM brands ORDER BY name");
// Получаем модели для текущего бренда
$models = $conn->prepare("SELECT * FROM models WHERE brand_id = ? ORDER BY name");
$models->bind_param("i", $car['brand_id']);
$models->execute();
$models_result = $models->get_result();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать объявление - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Редактировать объявление</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="brand_id" class="block text-sm font-medium">Марка</label>
                <select name="brand_id" id="brand_id" required class="w-full p-2 border rounded">
                    <option value="">Выберите марку</option>
                    <?php while ($brand = $brands->fetch_assoc()): ?>
                        <option value="<?= $brand['id'] ?>" 
                            <?= $brand['id'] == $car['brand_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="model_id" class="block text-sm font-medium">Модель</label>
                <select name="model_id" id="model_id" required class="w-full p-2 border rounded">
                    <option value="">Выберите модель</option>
                    <?php while ($model = $models_result->fetch_assoc()): ?>
                        <option value="<?= $model['id'] ?>" 
                            <?= $model['id'] == $car['model_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($model['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="year" class="block text-sm font-medium">Год</label>
                <input type="number" name="year" id="year" min="1900" max="<?= date('Y') + 1 ?>" 
                       value="<?= htmlspecialchars($car['year']) ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="mileage" class="block text-sm font-medium">Пробег (км)</label>
                <input type="number" name="mileage" id="mileage" min="0" 
                       value="<?= htmlspecialchars($car['mileage']) ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium">Цена (₽)</label>
                <input type="number" name="price" id="price" step="0.01" min="0.01" 
                       value="<?= htmlspecialchars($car['price']) ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium">Описание</label>
                <textarea name="description" id="description" required class="w-full p-2 border rounded"><?= htmlspecialchars($car['description']) ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium">Изображение (оставьте пустым, чтобы не изменять)</label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png" class="w-full p-2 border rounded">
                <?php if (!empty($car['image'])): ?>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Текущее изображение:</p>
                        <img src="images/uploads/<?= htmlspecialchars($car['image']) ?>" class="h-24 mt-2">
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Сохранить изменения
            </button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Динамическая загрузка моделей при изменении марки
        $('#brand_id').change(function() {
            const brandId = $(this).val();
            if (brandId) {
                $.get('get_models.php', { brand_id: brandId }, function(data) {
                    $('#model_id').html(data);
                });
            } else {
                $('#model_id').html('<option value="">Выберите модель</option>');
            }
        });
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>