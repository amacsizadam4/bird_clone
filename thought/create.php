<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $image_paths = [];

    if (empty($content)) {
        exit('Thought cannot be empty.');
    }

    if (strlen($content) > 280) {
        exit('Thought must be under 280 characters.');
    }

    // Insert thought
    $stmt = $pdo->prepare("INSERT INTO thoughts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$user_id, $content]);
    $thought_id = $pdo->lastInsertId();

    // Handle images (up to 4)
$uploadedImages = ['image1', 'image2', 'image3', 'image4'];

foreach ($uploadedImages as $key) {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== 0) continue;

    $tmp = $_FILES[$key]['tmp_name'];
    $name = basename($_FILES[$key]['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) continue;

    $new_name = uniqid() . '.' . $ext;
    $target = '../uploads/' . $new_name;

    if (move_uploaded_file($tmp, $target)) {
        $stmt = $pdo->prepare("INSERT INTO thought_images (thought_id, image_path) VALUES (?, ?)");
        $stmt->execute([$thought_id, $new_name]);
    }
}


    header("Location: ../index.php");
    exit;
}
