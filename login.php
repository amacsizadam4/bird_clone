<?php
require 'config.php';
require 'includes/lang.php';
include 'templates/language_switcher.php';

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

<h2><?= $t['login'] ?></h2>
<?php if ($error): ?>
<p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label><?= $t['username'] ?>:</label><br>
    <input type="text" name="username" required><br><br>
    
    <label><?= $t['password'] ?>:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit"><?= $t['login'] ?></button>
</form>

<p><a href="register.php"><?= $t['dont_have_account'] ?></a></p>
