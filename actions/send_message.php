<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Check mutual follow
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

// Insert message
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
$stmt->execute([$me, $to_id, $content]);

// Reload chat window content
$_GET['u'] = $to_username; // pass username to load_chat
include '../ajax/load_chat.php';