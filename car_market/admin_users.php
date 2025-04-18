<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) { // Нельзя удалить себя
        $conn->query("DELETE FROM users WHERE id = $delete_id");
        header('Location: admin_users.php');
        exit;
    }
}

if (isset($_GET['toggle_admin'])) {
    $user_id = (int)$_GET['toggle_admin'];
    $conn->query("UPDATE users SET is_admin = NOT is_admin WHERE id = $user_id");
    header('Location: admin_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - CarMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/admin_header.php'; ?>
    
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Управление пользователями</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Логин</th>
                            <th class="py-2 px-4 border-b">Email</th>
                            <th class="py-2 px-4 border-b">Телефон</th>
                            <th class="py-2 px-4 border-b">Статус</th>
                            <th class="py-2 px-4 border-b">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= $user['id'] ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['phone']) ?></td>
                            <td class="py-2 px-4 border-b">
                                <?php if ($user['is_admin']): ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Админ</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Пользователь</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 border-b">
                                <a href="admin_users.php?toggle_admin=<?= $user['id'] ?>" 
                                   class="text-yellow-600 hover:text-yellow-800 mr-2" 
                                   title="<?= $user['is_admin'] ? 'Снять админа' : 'Сделать админом' ?>">
                                    <i class="fas fa-user-shield"></i>
                                </a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="admin_users.php?delete=<?= $user['id'] ?>" 
                                   class="text-red-600 hover:text-red-800" 
                                   onclick="return confirm('Удалить пользователя?')"
                                   title="Удалить">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
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