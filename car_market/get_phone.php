<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit;
}

$car_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT users.phone 
                        FROM cars 
                        JOIN users ON cars.user_id = users.id 
                        WHERE cars.id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    echo htmlspecialchars($result['phone']);
} else {
    http_response_code(404);
}
?>