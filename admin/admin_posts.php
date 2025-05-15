<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

if ($_SESSION['username'] !== 'admin') {
    echo "<p style='color:red;'>" . ($t['admin_only'] ?? 'Access restricted to admin only.') . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_thought_id'])) {
    $deleteId = (int) $_POST['delete_thought_id'];
    $stmt = $pdo->prepare("DELETE FROM thoughts WHERE id = ?");
    $stmt->execute([$deleteId]);
}

$stmt = $pdo->query("SELECT t.*, u.username FROM thoughts t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$thoughts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['view_posts'] ?? 'All Posts' ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #eee; padding: 20px; }
        .card { background: #1e1e1e; border: 1px solid #333; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .meta { font-size: 0.85em; color: #aaa; margin-bottom: 8px; }
        form { display: inline; }
        button { background: red; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        button:hover { background: darkred; }
        a { color: #ffa73c; text-decoration: none; }
    </style>
</head>
<body>
<h1><?= $t['view_posts'] ?? 'All Posts' ?></h1>
<?php foreach ($thoughts as $thought): ?>
    <div class="card">
        <div class="meta">
            <?= htmlspecialchars($thought['username']) ?> · <?= $thought['created_at'] ?>
        </div>
        <p><?= nl2br(htmlspecialchars($thought['content'])) ?></p>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
            <input type="hidden" name="delete_thought_id" value="<?= $thought['id'] ?>">
            <button type="submit">🗑️ <?= $t['delete'] ?? 'Delete' ?></button>
        </form>
    </div>
<?php endforeach; ?>
<p><a href="../admin_panel.php">← <?= $t['admin_panel'] ?? 'Back to Admin Panel' ?></a></p>
</body>
</html>
