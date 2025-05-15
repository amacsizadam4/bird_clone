<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';
require '../includes/functions.php'; // for isBlocked()

$me = $_SESSION['user_id'];
$target_id = (int)($_POST['user_id'] ?? 0);
$username = $_POST['username'] ?? '';

if ($target_id && $target_id !== $me) {
    // 🔒 Block check
    if (isBlocked($me, $target_id)) {
        $msg = isset($t['blocked_follow']) ? $t['blocked_follow'] : 'You cannot follow this user.';
        exit("<p>$msg</p>");
    }

    // Check if already following
    $stmt = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$me, $target_id]);

    if ($stmt->fetch()) {
        // Unfollow
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$me, $target_id]);
    } else {
        // Follow
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$me, $target_id]);
    }
}

header("Location: ../user.php?u=" . urlencode($username));
exit;
