<?php
// generate_hash.php
$plain_password = 'new_password'; // Замените на желаемый пароль
$hash = password_hash($plain_password, PASSWORD_DEFAULT);

echo "Хэш пароля: " . $hash;
?>