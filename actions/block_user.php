<?php
require '../includes/auth.php';
require '../config.php';

$me = $_SESSION['user_id'];
$target_id = (int)($_POST['user_id'] ?? 0);

if ($target_id && $target_id !== $me) {
    // Check if already blocked
    $stmt = $pdo->prepare("SELECT 1 FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$me, $target_id]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO blocked_users (blocker_id, blocked_id) VALUES (?, ?)");
        $stmt->execute([$me, $target_id]);
    }
}

header("Location: ../user.php?u=" . urlencode($_POST['username']));
exit;
