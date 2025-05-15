<?php
require 'config.php';
require 'includes/lang.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['login'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input[type=text], input[type=password] {
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
    <h2><?= $t['login'] ?></h2>
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label><?= $t['username'] ?>:</label>
        <input type="text" name="username" required>

        <label><?= $t['password'] ?>:</label>
        <input type="password" name="password" required>

        <button type="submit"><?= $t['login'] ?></button>
    </form>
    <a href="register.php" class="link"><?= $t['dont_have_account'] ?></a>
</div>
</body>
</html>
