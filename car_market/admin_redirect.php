<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = getUserRole($_SESSION['user_id']);

if ($role === 'admin') {
    header('Location: admin.php');
} else {
    header('Location: profile.php');
}
exit;
?>