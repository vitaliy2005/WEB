<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = (int)$_POST['brand_id'];
    $model_id = (int)$_POST['model_id'];
    $year = (int)$_POST['year'];
    $mileage = (int)$_POST['mileage'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    // Валидация данных
    if ($brand_id <= 0 || $model_id <= 0) {
        $error = "Необходимо выбрать марку и модель";
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = "Некорректный год выпуска";
    } elseif ($mileage < 0) {
        $error = "Пробег не может быть отрицательным";
    } elseif ($price <= 0) {
        $error = "Цена должна быть положительной";
    } elseif (empty($description)) {
        $error = "Описание не может быть пустым";
    }

    if (!$error) {
        // Обработка изображения
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = "Ошибка загрузки изображения: " . $_FILES['image']['error'];
        } else {
            $image_info = getimagesize($_FILES['image']['tmp_name']);
            $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
            
            if (!$image_info || !in_array($image_info[2], $allowed_types)) {
                $error = "Недопустимый тип файла. Разрешены только JPEG и PNG.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Файл слишком большой. Максимальный размер - 5MB.";
            } else {
                $extension = $image_info[2] === IMAGETYPE_JPEG ? 'jpg' : 'png';
                $image_name = uniqid('car_', true) . '.' . $extension;
                $target = 'images/uploads/' . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $stmt = $conn->prepare("INSERT INTO cars (user_id, brand_id, model_id, year, mileage, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiiidsss", $user_id, $brand_id, $model_id, $year, $mileage, $price, $description, $image_name);
                    
                    if ($stmt->execute()) {
                        header('Location: profile.php');
                        exit;
                    } else {
                        $error = "Ошибка при сохранении данных: " . $conn->error;
                        // Удаляем загруженное изображение, если запись не сохранилась
                        if (file_exists($target)) {
                            unlink($target);
                        }
                    }
                } else {
                    $error = "Не удалось сохранить изображение. Проверьте права на папку.";
                }
            }
        }
    }
}

$brands = $conn->query("SELECT * FROM brands ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ru">
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
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
            
            <div class="mb-4">
                <label for="brand_id" class="block text-sm font-medium">Марка</label>
                <select name="brand_id" id="brand_id" required class="w-full p-2 border rounded">
                    <option value="">Выберите марку</option>
                    <?php while ($brand = $brands->fetch_assoc()): ?>
                        <option value="<?= $brand['id'] ?>" <?= isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="model_id" class="block text-sm font-medium">Модель</label>
                <select name="model_id" id="model_id" required class="w-full p-2 border rounded">
                    <option value="">Сначала выберите марку</option>
                    <?php if (isset($_POST['brand_id'])): ?>
                        <?php 
                        $models = $conn->prepare("SELECT * FROM models WHERE brand_id = ? ORDER BY name");
                        $models->bind_param("i", $_POST['brand_id']);
                        $models->execute();
                        $models_result = $models->get_result();
                        ?>
                        <?php while ($model = $models_result->fetch_assoc()): ?>
                            <option value="<?= $model['id'] ?>" <?= isset($_POST['model_id']) && $_POST['model_id'] == $model['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($model['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <!-- Остальные поля формы остаются без изменений -->
            <div class="mb-4">
                <label for="year" class="block text-sm font-medium">Год</label>
                <input type="number" name="year" id="year" min="1900" max="<?= date('Y') + 1 ?>" 
                       value="<?= htmlspecialchars($_POST['year'] ?? '') ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="mileage" class="block text-sm font-medium">Пробег (км)</label>
                <input type="number" name="mileage" id="mileage" min="0" 
                       value="<?= htmlspecialchars($_POST['mileage'] ?? '') ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="price" class="block text-sm font-medium">Цена (₽)</label>
                <input type="number" name="price" id="price" step="0.01" min="0.01" 
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required class="w-full p-2 border rounded">
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium">Описание</label>
                <textarea name="description" id="description" required class="w-full p-2 border rounded"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="image" class="block text-sm font-medium">Изображение (JPEG или PNG, до 5MB)</label>
                <input type="file" name="image" id="image" accept="image/jpeg,image/png" required class="w-full p-2 border rounded">
            </div>
            
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Добавить</button>
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
                $('#model_id').html('<option value="">Сначала выберите марку</option>');
            }
        });
    });
    </script>
</body>
</html>