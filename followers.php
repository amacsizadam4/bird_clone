<?php
require 'includes/auth.php';
require 'includes/lang.php';
require 'includes/functions.php';
require 'config.php';

$username = $_GET['u'] ?? '';
$type = $_GET['type'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='card'><p>{$t['user_not_found']}</p></div>";
    exit;
}

$uid = $user['id'];

if ($type === 'followers') {
    $stmt = $pdo->prepare("
        SELECT u.username FROM follows f
        JOIN users u ON f.follower_id = u.id
        WHERE f.followed_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$uid]);
    $heading = $t['followers'];

} elseif ($type === 'following') {
    $stmt = $pdo->prepare("
        SELECT u.username FROM follows f
        JOIN users u ON f.followed_id = u.id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$uid]);
    $heading = $t['following'];

} else {
    echo "<div class='card'><p>{$t['invalid_request']}</p></div>";
    exit;
}

$users = $stmt->fetchAll();

echo "<div class='card'>";
echo "<h3 style='margin-bottom: 15px;'>$heading</h3>";

if (!$users) {
    echo "<p>{$t['no_users_found']}</p>";
} else {
    echo "<ul style='list-style: none; padding: 0;'>";
    foreach ($users as $u) {
        $safe = htmlspecialchars($u['username']);
        
    echo "<li style='margin-bottom: 10px;'>" . render_user_icon($safe, 24) . "</li>";

    }
    echo "</ul>";
}
echo "</div>";
