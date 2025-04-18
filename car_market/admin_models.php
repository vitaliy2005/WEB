<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;

// Получение информации о марке
$brand = $conn->query("SELECT * FROM brands WHERE id = $brand_id")->fetch_assoc();
if (!$brand) {
    header('Location: admin_brands.php');
    exit;
}

// Добавление новой модели
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_model'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO models (brand_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $brand_id, $name);
        $stmt->execute();
        header("Location: admin_models.php?brand_id=$brand_id");
        exit;
    }
}

// Удаление модели
if (isset($_GET['delete'])) {
    $model_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM models WHERE id = $model_id");
    header("Location: admin_models.php?brand_id=$brand_id");
    exit;
}

// Получение списка моделей
$models = $conn->query("SELECT * FROM models WHERE brand_id = $brand_id ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление моделями - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Управление моделями марки <?= htmlspecialchars($brand['name']) ?></h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Добавить новую модель</h2>
            <form method="POST" class="flex">
                <input type="text" name="name" placeholder="Название модели" required 
                       class="flex-grow p-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" name="add_model" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600">
                    <i class="fas fa-plus mr-1"></i> Добавить
                </button>
            </form>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Название</th>
                            <th class="py-2 px-4 border-b">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($model = $models->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= $model['id'] ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($model['name']) ?></td>
                            <td class="py-2 px-4 border-b">
                                <a href="admin_models.php?brand_id=<?= $brand_id ?>&delete=<?= $model['id'] ?>" 
                                   class="text-red-600 hover:text-red-800" 
                                   onclick="return confirm('Удалить модель?')"
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
        
        <div class="mt-4">
            <a href="admin_brands.php" class="text-blue-500 hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Вернуться к списку марок
            </a>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>