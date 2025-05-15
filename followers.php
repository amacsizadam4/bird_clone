<?php
require 'includes/auth.php';
require 'includes/lang.php';
require 'config.php';

$username = $_GET['u'] ?? '';
$type = $_GET['type'] ?? '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p>{$t['user_not_found']}</p>";
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
    echo "<h4>{$t['followers']}</h4>";

} elseif ($type === 'following') {
    $stmt = $pdo->prepare("
        SELECT u.username FROM follows f
        JOIN users u ON f.followed_id = u.id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$uid]);
    echo "<h4>{$t['following']}</h4>";

} else {
    echo "<p>{$t['invalid_request']}</p>";
    exit;
}

$users = $stmt->fetchAll();
if (!$users) {
    echo "<p>{$t['no_users_found']}</p>";
    exit;
}

echo "<ul style='padding-left: 20px;'>";
foreach ($users as $u) {
    $safe = htmlspecialchars($u['username']);
    echo "<li><a href='user.php?u={$safe}'>{$safe}</a></li>";
}
echo "</ul>";
