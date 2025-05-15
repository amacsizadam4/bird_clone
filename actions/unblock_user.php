<?php
require '../includes/auth.php';
require '../config.php';

$me = $_SESSION['user_id'];
$target_id = (int)($_POST['user_id'] ?? 0);

if ($target_id && $target_id !== $me) {
    // Delete the block record if it exists
    $stmt = $pdo->prepare("DELETE FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$me, $target_id]);
}

// Optional redirect back to settings or profile
header("Location: ../settings.php#blocked");
exit;
