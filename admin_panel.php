<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
include 'templates/language_switcher.php';

if ($_SESSION['username'] !== 'admin') {
    echo "<p style='color:red;'>" . ($t['admin_only'] ?? 'Access restricted to admin only.') . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #eee; padding: 20px; }
        .section { margin-bottom: 30px; }
        h2 { color: #ffa73c; margin-bottom: 10px; }
        ul { list-style: none; padding-left: 0; }
        ul li { margin-bottom: 10px; }
        a { color: #ffa73c; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>🛠️ <?= $t['admin_panel'] ?? 'Admin Panel' ?></h1>

<div class="section">
    <h2>🧑‍💼 <?= $t['user_moderation'] ?? 'User Moderation' ?></h2>
    <ul>
        <li><a href="admin/admin_users.php"><?= $t['view_all_users'] ?? 'View all users' ?></a></li>
    </ul>
</div>

<div class="section">
    <h2>💬 <?= $t['content_moderation'] ?? 'Content Moderation' ?></h2>
    <ul>
        <li><a href="admin/admin_posts.php"><?= $t['view_posts'] ?? 'See all posts (thoughts)' ?></a></li>
        <li><a href="admin/admin_comments.php"><?= $t['manage_comments'] ?? 'Manage comments' ?></a></li>
    </ul>
</div>

<div class="section">
    <h2>🔍 <?= $t['search_analytics'] ?? 'Search and Analytics' ?></h2>
    <ul>
        <li><a href="#"><?= $t['search_users'] ?? 'Search users by name/email' ?></a></li>
        <li><a href="#"><?= $t['search_posts'] ?? 'Search posts by keyword or user' ?></a></li>
        <li><a href="admin/admin_stats.php"><?= $t['stats_summary'] ?? 'See total number of users, posts, likes, comments' ?></a></li>
    </ul>
</div>

<p><a href="logout.php">🚪 <?= $t['logout'] ?></a></p>
</body>
</html>
