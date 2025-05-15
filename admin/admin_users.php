<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

if ($_SESSION['username'] !== 'admin') {
    echo "<p style='color:red;'>" . ($t['admin_only'] ?? 'Access restricted to admin only.') . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user_id'])) {
        $deleteId = (int) $_POST['delete_user_id'];
        if ($deleteId !== 1) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);
        }
    }
    if (isset($_POST['reset_user_id']) && isset($_POST['new_password'])) {
        $resetId = (int) $_POST['reset_user_id'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPassword, $resetId]);
    }
}

$stmt = $pdo->query("SELECT id, username, email, bio, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['view_all_users'] ?? 'All Users' ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #eee; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #333; text-align: left; }
        th { background-color: #222; }
        tr:nth-child(even) { background-color: #1a1a1a; }
        a { color: #ffa73c; text-decoration: none; }
        a:hover { text-decoration: underline; }
        form { display: inline; }
        button { background: red; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        button:hover { background: darkred; }
        .reset-form { margin-top: 5px; display: flex; gap: 5px; }
        .reset-form input[type="password"] { padding: 4px; border-radius: 4px; border: 1px solid #444; background: #222; color: #fff; }
        .reset-form button { background: #ffa73c; color: #000; }
    </style>
</head>
<body>
    <h1><?= $t['view_all_users'] ?? 'All Users' ?></h1>
    <table>
        <tr>
            <th>ID</th>
            <th><?= $t['username'] ?? 'Username' ?></th>
            <th><?= $t['email'] ?? 'Email' ?></th>
            <th><?= $t['edit_bio'] ?? 'Bio' ?></th>
            <th><?= $t['created'] ?? 'Created' ?></th>
            <th><?= $t['delete'] ?? 'Delete' ?></th>
            <th><?= $t['reset_password'] ?? 'Reset Password' ?></th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($user['bio'])) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <?php if ($user['username'] !== 'admin'): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                        <button type="submit">🗑️ <?= $t['delete'] ?? 'Delete' ?></button>
                    </form>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
            <td>
                <form method="POST" class="reset-form">
                    <input type="hidden" name="reset_user_id" value="<?= $user['id'] ?>">
                    <input type="password" name="new_password" placeholder="<?= $t['new_password'] ?? 'New password' ?>" required>
                    <button type="submit">🔁</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="../admin_panel.php">← <?= $t['admin_panel'] ?? 'Back to Admin Panel' ?></a></p>
</body>
</html>
