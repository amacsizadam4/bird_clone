<?php
require '../includes/auth.php';
require '../config.php';

$comment_id = $_POST['comment_id'] ?? null;

if (!$comment_id) {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if ($comment && $comment['user_id'] == $_SESSION['user_id']) {
    $stmt = $pdo->prepare("UPDATE comments SET deleted = 1 WHERE id = ?");
    $stmt->execute([$comment_id]);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
