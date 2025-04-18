<?php
require_once 'includes/db.php';

if (!isset($_GET['brand_id'])) {
    exit;
}

$brand_id = (int)$_GET['brand_id'];
$stmt = $conn->prepare("SELECT * FROM models WHERE brand_id = ? ORDER BY name");
$stmt->bind_param("i", $brand_id);
$stmt->execute();
$models = $stmt->get_result();

echo '<option value="">Выберите модель</option>';
while ($model = $models->fetch_assoc()) {
    echo '<option value="' . $model['id'] . '">' . htmlspecialchars($model['name']) . '</option>';
}
?>