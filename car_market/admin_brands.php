<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Добавление новой марки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        header('Location: admin_brands.php');
        exit;
    }
}

// Удаление марки
if (isset($_GET['delete'])) {
    $brand_id = (int)$_GET['delete'];
    // $conn->query("DELETE FROM brands WHERE id = $brand_id");
    $stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    header('Location: admin_brands.php');
    exit;
}

// Получение списка марок
$brands = $conn->query("SELECT * FROM brands ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление марками - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_header.php'; ?>
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Управление марками автомобилей</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Добавить новую марку</h2>
            <form method="POST" class="flex">
                <input type="text" name="name" placeholder="Название марки" required 
                       class="flex-grow p-2 border rounded-l focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" name="add_brand" 
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
                        <?php while ($brand = $brands->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= $brand['id'] ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($brand['name']) ?></td>
                            <td class="py-2 px-4 border-b">
                                <a href="admin_models.php?brand_id=<?= $brand['id'] ?>" 
                                   class="text-blue-600 hover:text-blue-800 mr-2" 
                                   title="Модели">
                                    <i class="fas fa-list"></i>
                                </a>
                                <a href="admin_brands.php?delete=<?= $brand['id'] ?>" 
                                   class="text-red-600 hover:text-red-800" 
                                   onclick="return confirm('Удалить марку? Это также удалит все связанные модели!')"
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