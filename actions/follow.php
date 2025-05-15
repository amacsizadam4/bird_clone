<?php
require '../includes/auth.php';
require '../config.php';

$current_id = $_SESSION['user_id'];
$target_id = (int) $_POST['user_id'];

if ($target_id !== $current_id) {
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$current_id, $target_id]);

    if ($stmt->fetch()) {
        // Unfollow
        $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?")
            ->execute([$current_id, $target_id]);
    } else {
        // Follow
        $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)")
            ->execute([$current_id, $target_id]);
    }
}

header("Location: ../user.php?u=" . urlencode($_POST['username']));
exit;
