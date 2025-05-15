<?php
require '../includes/auth.php';
require '../config.php';

$user_id = $_SESSION['user_id'];
$thought_id = $_POST['thought_id'] ?? null;
$parent_id = $_POST['parent_comment_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$thought_id || empty($content)) {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO comments (user_id, thought_id, parent_comment_id, content)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$user_id, $thought_id, $parent_id, $content]);

header("Location: ../index.php");
exit;
