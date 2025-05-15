<?php
require '../includes/auth.php';
require '../config.php';

$thought_id = $_POST['thought_id'];
$user_id = $_SESSION['user_id'];

// Toggle like
$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND thought_id = ?");
$stmt->execute([$user_id, $thought_id]);

if ($stmt->fetch()) {
    $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND thought_id = ?")->execute([$user_id, $thought_id]);
} else {
    $pdo->prepare("INSERT INTO likes (user_id, thought_id) VALUES (?, ?)")->execute([$user_id, $thought_id]);
}

header("Location: ../index.php");
exit;
