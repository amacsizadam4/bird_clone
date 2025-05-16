<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

if ($_SESSION['username'] !== 'admin') {
    echo "<p style='color:red;'>" . ($t['admin_only'] ?? 'Access restricted to admin only.') . "</p>";
    exit;
}

$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM thoughts")->fetchColumn();
$total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['stats_summary'] ?? 'Platform Statistics' ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #eee; padding: 40px; }
        h1 { color: #ffa73c; }
        .stat-box { background: #1e1e1e; border: 1px solid #333; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .stat-box h2 { margin: 0 0 10px 0; font-size: 1.2em; }
        .stat-box p { font-size: 2em; color: #ffa73c; margin: 0; }
        a { color: #ffa73c; text-decoration: none; }
    </style>
</head>
<body>
<h1><?= $t['stats_summary'] ?? 'Platform Statistics' ?></h1>
<div class="stat-box">
    <h2><?= $t['userss'] ?? 'Total Users' ?></h2>
    <p><?= $total_users ?></p>
</div>
<div class="stat-box">
    <h2><?= $t['postss'] ?? 'Total Posts' ?></h2>
    <p><?= $total_posts ?></p>
</div>
<div class="stat-box">
    <h2><?= $t['likess'] ?? 'Total Likes' ?></h2>
    <p><?= $total_likes ?></p>
</div>
<div class="stat-box">
    <h2><?= $t['commentss'] ?? 'Total Comments' ?></h2>
    <p><?= $total_comments ?></p>
</div>
<p><a href="../admin_panel.php">← <?= $t['admin_panel'] ?? 'Back to Admin Panel' ?></a></p>
</body>
</html>
