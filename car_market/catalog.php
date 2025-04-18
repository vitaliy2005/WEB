<?php
session_start();
require_once 'includes/db.php';

// Получение фильтров
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Формирование запроса
$query = "SELECT cars.*, brands.name AS brand_name, models.name AS model_name 
          FROM cars 
          JOIN brands ON cars.brand_id = brands.id 
          JOIN models ON cars.model_id = models.id 
          WHERE 1=1";
$params = [];
if ($brand_id) {
    $query .= " AND cars.brand_id = ?";
    $params[] = $brand_id;
}
if ($year) {
    $query .= " AND cars.year = ?";
    $params[] = $year;
}
if ($price_min) {
    $query .= " AND cars.price >= ?";
    $params[] = $price_min;
}
if ($price_max) {
    $query .= " AND cars.price <= ?";
    $params[] = $price_max;
}
if ($search) {
    $query .= " AND (brands.name LIKE ? OR models.name LIKE ? OR cars.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}
$query .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($query);
if ($params) {
    $types = str_repeat('s', count($params) - 2) . 'ii';
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$cars = $stmt->get_result();

// Получение брендов для фильтра
$brands = $conn->query("SELECT * FROM brands ORDER BY name");

// Подсчет общего количества для пагинации
$count_query = "SELECT COUNT(*) as total 
                FROM cars 
                JOIN brands ON cars.brand_id = brands.id 
                JOIN models ON cars.model_id = models.id 
                WHERE 1=1";
$count_params = [];
if ($brand_id) {
    $count_query .= " AND cars.brand_id = ?";
    $count_params[] = $brand_id;
}
if ($year) {
    $count_query .= " AND cars.year = ?";
    $count_params[] = $year;
}
if ($price_min) {
    $count_query .= " AND cars.price >= ?";
    $count_params[] = $price_min;
}
if ($price_max) {
    $count_query .= " AND cars.price <= ?";
    $count_params[] = $price_max;
}
if ($search) {
    $count_query .= " AND (brands.name LIKE ? OR models.name LIKE ? OR cars.description LIKE ?)";
    $count_params[] = $search_param;
    $count_params[] = $search_param;
    $count_params[] = $search_param;
}
$count_stmt = $conn->prepare($count_query);
if ($count_params) {
    $count_stmt->bind_param(str_repeat('s', count($count_params)), ...$count_params);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="ру">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог автомобилей - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Каталог автомобилей</h1>
        
        <!-- Фильтры и поиск -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="brand_id" class="block text-sm font-medium">Марка</label>
                    <select name="brand_id" id="brand_id" class="w-full p-2 border rounded">
                        <option value="">Все марки</option>
                        <?php while ($row = $brands->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>" <?= $brand_id == $row['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium">Год</label>
                    <input type="number" name="year" id="year" value="<?= $year ?: '' ?>" class="w-full p-2 border rounded" placeholder="Год">
                </div>
                <div>
                    <label for="price_min" class="block text-sm font-medium">Цена от</label>
                    <input type="number" name="price_min" id="price_min" value="<?= $price_min ?: '' ?>" class="w-full p-2 border rounded" placeholder="Мин. цена">
                </div>
                <div>
                    <label for="price_max" class="block text-sm font-medium">Цена до</label>
                    <input type="number" name="price_max" id="price_max" value="<?= $price_max ?: '' ?>" class="w-full p-2 border rounded" placeholder="Макс. цена">
                </div>
                <div class="md:col-span-4">
                    <label for="search" class="block text-sm font-medium">Поиск</label>
                    <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" class="w-full p-2 border rounded" placeholder="Поиск по марке, модели или описанию">
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Применить</button>
                </div>
            </form>
        </div>

        <!-- Список автомобилей -->
        <div id="cars-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if ($cars->num_rows > 0): ?>
                <?php while ($car = $cars->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <img src="images/uploads/<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand_name']) ?>" class="w-full h-48 object-cover rounded mb-4">
                        <h2 class="text-xl font-semibold"><?= htmlspecialchars($car['brand_name']) ?> <?= htmlspecialchars($car['model_name']) ?></h2>
                        <p class="text-gray-600">Год: <?= htmlspecialchars($car['year']) ?></p>
                        <p class="text-gray-600">Пробег: <?= htmlspecialchars($car['mileage']) ?> км</p>
                        <p class="text-lg font-bold text-blue-600"><?= number_format($car['price'], 0, '.', ' ') ?> ₽</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button onclick="showPhone(<?= $car['id'] ?>)" class="mt-4 block text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Посмотреть номер</button>
                            <p id="phone-<?= $car['id'] ?>" style="display:none;"></p>
                        <?php else: ?>
                            <p class="mt-4 text-gray-600">Чтобы увидеть номер, <a href="login.php" class="text-blue-500">войдите</a> или <a href="register.php" class="text-blue-500">зарегистрируйтесь</a>.</p>
                        <?php endif; ?>
                        <a href="car.php?id=<?= $car['id'] ?>" class="mt-4 block text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Подробнее</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center col-span-3">Автомобили не найдены.</p>
            <?php endif; ?>
        </div>

        <!-- Пагинация -->
        <?php if ($total_pages > 1): ?>
            <div class="flex justify-center mt-8">
                <nav class="inline-flex">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="catalog.php?page=<?= $i ?>&brand_id=<?= $brand_id ?>&year=<?= $year ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-2 mx-1 rounded <?= $page == $i ? 'bg-blue-500 text-white' : 'bg-gray-200' ?> hover:bg-blue-400">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/scripts.js"></script>
</body>
</html>