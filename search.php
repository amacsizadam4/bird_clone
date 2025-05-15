<?php
require 'includes/auth.php';
require 'includes/lang.php';
require 'config.php';
require 'includes/functions.php';

$query = trim($_GET['q'] ?? '');

echo "<h2>{$t['search_results_for']} '" . htmlspecialchars($query) . "'</h2>";

if ($query === '') {
    echo "<p>{$t['no_users_found']}</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT username, profile_pic, bio FROM users WHERE username LIKE ? ORDER BY username ASC LIMIT 20");
$stmt->execute(["%$query%"]);
$users = $stmt->fetchAll();

if (!$users) {
    echo "<p>{$t['no_users_found']}</p>";
    echo "Return to <a href='index.php'>main page</a>";
    exit;
}

echo "<ul style='list-style: none; padding-left: 0;'>";

foreach ($users as $user) {
    echo "<li style='margin-bottom: 10px;'>";
    echo render_user_icon($user['username'], 40);
    if (!empty($user['bio'])) {
        echo "<p style='margin-left: 50px; font-size: 0.9em;'>" . nl2br(htmlspecialchars($user['bio'])) . "</p>";
    }
    echo "</li>";
}

echo "</ul>";
