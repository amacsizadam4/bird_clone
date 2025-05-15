<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

if ($_SESSION['username'] !== 'admin') {
    echo "<p style='color:red;'>" . ($t['admin_only'] ?? 'Access restricted to admin only.') . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $deleteId = (int) $_POST['delete_comment_id'];
    $stmt = $pdo->prepare("UPDATE comments SET deleted = 1 WHERE id = ?");
    $stmt->execute([$deleteId]);
}

$stmt = $pdo->query("SELECT c.*, u.username, t.content AS thought_content
                      FROM comments c
                      JOIN users u ON c.user_id = u.id
                      JOIN thoughts t ON c.thought_id = t.id
                      ORDER BY c.created_at DESC");
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['manage_comments'] ?? 'Manage Comments' ?></title>
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
<h1><?= $t['manage_comments'] ?? 'Manage Comments' ?></h1>
<?php foreach ($comments as $comment): ?>
    <div class="card">
        <div class="meta">
            <?= htmlspecialchars($comment['username']) ?> · <?= $comment['created_at'] ?><br>
            On: <em><?= mb_strimwidth(htmlspecialchars($comment['thought_content']), 0, 100, '...') ?></em>
        </div>
        <p><?= $comment['deleted'] ? '<i>' . ($t['comment_deleted'] ?? 'This comment was deleted.') . '</i>' : nl2br(htmlspecialchars($comment['content'])) ?></p>
        <?php if (!$comment['deleted']): ?>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');">
            <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
            <button type="submit">🗑️ <?= $t['delete'] ?? 'Delete' ?></button>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<p><a href="../admin_panel.php">← <?= $t['admin_panel'] ?? 'Back to Admin Panel' ?></a></p>
</body>
</html>
