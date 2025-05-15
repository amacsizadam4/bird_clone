<?php
require 'config.php';
require 'includes/lang.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($username === '' || $email === '' || $password === '') {
        $error = $t['fill_all_fields'];
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = $t['username_taken'];
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $t['register'] ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: var(--bg); color: var(--text); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div class="card" style="width: 100%; max-width: 400px; padding: 30px;">
    <div class="logo-header" style="text-align: center; margin-bottom: 20px;">
        <img src="assets/logo.png" alt="Logo" style="width: 80px;">
        <h1>BIRD</h1>
    </div>

    <h2 style="text-align: center;"><?= $t['register'] ?></h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label><?= $t['username'] ?></label>
        <input type="text" name="username" required>

        <label><?= $t['email'] ?></label>
        <input type="email" name="email" required>

        <label><?= $t['password'] ?></label>
        <input type="password" name="password" required>

        <button type="submit" class="btn"><?= $t['register'] ?></button>
    </form>

    <p style="text-align: center; margin-top: 10px;">
        <a href="login.php" style="color: var(--accent);"><?= $t['already_have_account'] ?></a>
    </p>
</div>

</body>
</html>
