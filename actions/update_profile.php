<?php
require '../includes/auth.php';
require '../config.php';

$user_id = $_SESSION['user_id'];

$bio = trim($_POST['bio'] ?? '');
$profile_pic = $_FILES['profile_pic'] ?? null;

// Update bio
$stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
$stmt->execute([$bio, $user_id]);

// Handle profile picture upload
if ($profile_pic && $profile_pic['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $path = __DIR__ . '/../uploads/' . $filename;

    if (move_uploaded_file($profile_pic['tmp_name'], $path)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$filename, $user_id]);
    }
}

header('Location: ../settings.php#profile');
exit;
