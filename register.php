<?php
require 'config.php';
require 'includes/lang.php';
include 'templates/language_switcher.php';


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $error = $t['fill_all_fields'];
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = $t['username_taken'];
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed]);
            header('Location: login.php');
            exit;
        }
    }
}
?>

<h2><?= $t['register'] ?></h2>
<?php if ($error): ?>
<p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <label><?= $t['username'] ?>:</label><br>
    <input type="text" name="username" required><br><br>
    
    <label><?= $t['password'] ?>:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit"><?= $t['register'] ?></button>
</form>

<p><a href="login.php"><?= $t['already_have_account'] ?></a></p>
