<?php
require '../includes/auth.php';
require '../config.php';

$thought_id = $_POST['thought_id'];
$user_id = $_SESSION['user_id'];

// Fetch thought to verify ownership
$stmt = $pdo->prepare("SELECT * FROM thoughts WHERE id = ? AND user_id = ?");
$stmt->execute([$thought_id, $user_id]);
$thought = $stmt->fetch();

if (!$thought) {
    header("Location: ../index.php");
    exit;
}

// If it's original (no repost), delete images first
if ($thought['original_thought_id'] === null) {
    $stmt2 = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
    $stmt2->execute([$thought_id]);
    foreach ($stmt2->fetchAll() as $img) {
        @unlink('../uploads/' . $img['image_path']);
    }
}

// Delete the thought (repost or original)
$pdo->prepare("DELETE FROM thoughts WHERE id = ?")->execute([$thought_id]);

header("Location: ../index.php");
exit;
