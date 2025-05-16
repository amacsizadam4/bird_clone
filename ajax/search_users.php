<?php
require '../config.php';
require '../includes/lang.php';

$query = trim($_GET['q'] ?? '');
if ($query === '') exit;

$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE username LIKE ? LIMIT 10");
$stmt->execute(["%$query%"]);
$users = $stmt->fetchAll();

if (!$users) {
    echo "<div style='padding: 10px;'>{$t['no_users_found']}</div>";
    exit;
}

foreach ($users as $user) {
    $safeUsername = htmlspecialchars($user['username']);
    $pic = $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'default.png';
    $imgTag = "<img src='/bird_clone/uploads/$pic' width='24' height='24' style='vertical-align:middle; border-radius:50%; margin-right:8px;'>";

    echo "<div style='padding: 8px; cursor: pointer; display: flex; align-items: center;' onclick=\"window.location.href='/bird_clone/user.php?u=$safeUsername'\">
            $imgTag <span>$safeUsername</span>
          </div>";
}
