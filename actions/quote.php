<?php
require '../includes/auth.php';
require '../config.php';

$user_id = $_SESSION['user_id'];
$target_id = $_POST['thought_id'];
$content = trim($_POST['content'] ?? '');

// Check if the target is already a quote
$stmt = $pdo->prepare("SELECT * FROM thoughts WHERE id = ?");
$stmt->execute([$target_id]);
$original = $stmt->fetch();

if (!$original || $original['quote_id']) {
    exit("You can't quote a quoted post.");
}

// Create quote post
if (!empty($content)) {
    $stmt = $pdo->prepare("INSERT INTO thoughts (user_id, content, quote_id) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $target_id]);
}

header("Location: ../index.php");
exit;
