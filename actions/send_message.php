<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';
require '../includes/functions.php'; // for isBlocked()

$me = $_SESSION['user_id'];
$to_username = $_POST['to'] ?? '';
$content = trim($_POST['content'] ?? '');

// Validate input
if ($to_username === '' || $content === '' || $to_username === $_SESSION['username']) {
    exit;
}

// Fetch recipient
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$to_username]);
$to = $stmt->fetch();

if (!$to) {
    exit("<p>{$t['user_not_found']}</p>");
}

$to_id = $to['id'];

// 🔐 Check for block in either direction
if (isBlocked($me, $to_id)) {
    $blocked_msg = isset($t['blocked_dm']) ? $t['blocked_dm'] : 'You cannot message this user.';
    exit("<p>$blocked_msg</p>");
}


// ✅ Check mutual follow
$stmt = $pdo->prepare("
    SELECT 1 FROM follows f1
    JOIN follows f2 ON f1.followed_id = f2.follower_id
    WHERE f1.follower_id = ? AND f1.followed_id = ?
      AND f2.follower_id = ? AND f2.followed_id = ?
");
$stmt->execute([$me, $to_id, $to_id, $me]);

if (!$stmt->fetch()) {
    exit("<p>{$t['not_mutual_follow']}</p>");
}

// ✅ Save message
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->execute([$me, $to_id, $content]);

http_response_code(200);
exit;
