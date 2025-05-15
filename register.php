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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['register'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input[type=text], input[type=email], input[type=password] {
            width: 100%; padding: 10px; margin-top: 8px; margin-bottom: 16px; border: 1px solid #ccc; border-radius: 4px;
        }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #0056b3; }
        .error { color: red; margin-bottom: 10px; }
        .link { margin-top: 15px; text-align: center; display: block; }
    </style>
</head>
<body>
<div class="container">
    <h2><?= $t['register'] ?></h2>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label><?= $t['username'] ?>:</label>
        <input type="text" name="username" required>

        <label><?= $t['email'] ?>:</label>
        <input type="email" name="email" required>

        <label><?= $t['password'] ?>:</label>
        <input type="password" name="password" required>

        <button type="submit"><?= $t['register'] ?></button>
    </form>
    <a href="login.php" class="link"><?= $t['already_have_account'] ?></a>
</div>
</body>
</html>
