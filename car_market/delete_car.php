<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

$car_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Проверка владельца объявления
$stmt = $conn->prepare("SELECT image FROM cars WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $car_id, $user_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if ($car) {
    // Удаление изображения
    if (!empty($car['image']) && file_exists("images/uploads/{$car['image']}")) {
        unlink("images/uploads/{$car['image']}");
    }

    // Удаление записи
    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
}

header('Location: profile.php');
exit;