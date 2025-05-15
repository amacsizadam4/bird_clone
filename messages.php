<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
require 'includes/functions.php';
include 'templates/header.php';

$receiver_username = $_GET['u'] ?? '';

if (!$receiver_username || $receiver_username === $_SESSION['username']) {
    echo "<p>{$t['invalid_user']}</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, profile_pic FROM users WHERE username = ?");
$stmt->execute([$receiver_username]);
$receiver = $stmt->fetch();

if (!$receiver) {
    echo "<p>{$t['user_not_found']}</p>";
    exit;
}

$receiver_id = $receiver['id'];
$me = $_SESSION['user_id'];

// Check mutual follow
$stmt = $pdo->prepare("SELECT 1 FROM follows f1 JOIN follows f2 ON f1.followed_id = f2.follower_id WHERE f1.follower_id = ? AND f1.followed_id = ? AND f2.follower_id = ? AND f2.followed_id = ?");
$stmt->execute([$me, $receiver_id, $receiver_id, $me]);
if (!$stmt->fetch()) {
    echo "<p>{$t['not_mutual_follow']}</p>";
    exit;
}

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
$stmt->execute([$me, $receiver_id, $receiver_id, $me]);
$messages = $stmt->fetchAll();
?>

<h2>💬 <?= htmlspecialchars($receiver['username']) ?></h2>
<div style="border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
    <?php foreach ($messages as $msg): ?>
        <div style="margin-bottom: 10px;">
            <strong><?= $msg['sender_id'] == $me ? 'Me' : htmlspecialchars($receiver['username']) ?>:</strong><br>
            <?= nl2br(htmlspecialchars($msg['content'])) ?><br>
            <small><?= $msg['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
</div>

<form method="POST" action="actions/send_message.php">
    <input type="hidden" name="to" value="<?= $receiver['id'] ?>">
    <textarea name="content" rows="3" required style="width: 100%;" placeholder="<?= $t['type_message'] ?>"></textarea><br>
    <button type="submit">📨 <?= $t['send'] ?></button>
</form>
