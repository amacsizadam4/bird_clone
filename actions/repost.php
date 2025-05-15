<?php
require '../includes/auth.php';
require '../config.php';

$original_id = $_POST['thought_id'];
$user_id = $_SESSION['user_id'];

// Prevent reposting same thing twice
$stmt = $pdo->prepare("SELECT id FROM thoughts WHERE user_id = ? AND original_thought_id = ?");
$stmt->execute([$user_id, $original_id]);
if ($stmt->fetch()) {
    header("Location: ../index.php");
    exit;
}


// Check if original post still exists
$stmt = $pdo->prepare("SELECT * FROM thoughts WHERE id = ?");
$stmt->execute([$original_id]);
$original = $stmt->fetch();

if (!$original) {
    header("Location: ../index.php?error=unavailable");
    exit;
}


// Insert new thought that references the original
$stmt = $pdo->prepare("INSERT INTO thoughts (user_id, content, original_thought_id) SELECT ?, content, id FROM thoughts WHERE id = ?");
$stmt->execute([$user_id, $original_id]);

header("Location: ../index.php");
exit;
