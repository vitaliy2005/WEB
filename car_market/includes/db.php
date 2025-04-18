<?php
// includes/db.php

$host = 'localhost';
$dbname = 'carmarket';
$username = 'user266';
$password = '226';

// Подключение
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Функция должна быть объявлена ПОСЛЕ создания $conn
function isAdmin($user_id) {
    global $conn;
    
    if (!is_numeric($user_id) || $user_id <= 0) return false;
    
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) return false;
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) return false;
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return !empty($user) && $user['is_admin'] == 1;
}

function getUserRole($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        return $user['is_admin'] ? 'admin' : 'user';
    }
    
    return 'guest';
}
?>