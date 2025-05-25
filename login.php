<?php
require 'config.php';
require 'includes/lang.php';
include 'templates/language_switcher.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = $t['invalid_credentials'];
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $t['login'] ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: var(--bg); color: var(--text); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
    <div class="logo-header" style="text-align: center; margin-bottom: 20px;">
        <img src="assets/logo.png" alt="Logo" style="width: 80px;">
        <h1>BIRD</h1>
    </div>

    <h2 style="text-align: center;"><?= $t['login'] ?></h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label><?= $t['username'] ?></label>
        <input type="text" name="username" required>

        <label><?= $t['password'] ?></label>
        <input type="password" name="password" required>

        <button type="submit" class="btn"><?= $t['login'] ?></button>
    </form>

    <p style="text-align: center; margin-top: 10px;">
        <a href="register.php" style="color: var(--accent);"><?= $t['dont_have_account'] ?></a>
    </p>
</div>

</body>
</html>
