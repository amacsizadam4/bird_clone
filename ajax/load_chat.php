<?php
require '../includes/auth.php';
require '../config.php';

$me = $_SESSION['user_id'];
$them_username = $_GET['u'] ?? '';

if (!$them_username || $them_username === $_SESSION['username']) {
    exit;
}

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
$stmt->execute([$them_username]);
$them = $stmt->fetch();

if (!$them) {
    echo "<p>User not found.</p>";
    exit;
}

$them_id = $them['id'];

// Check mutual follow
$stmt = $pdo->prepare("SELECT 1 FROM follows f1 
    JOIN follows f2 ON f1.followed_id = f2.follower_id 
    WHERE f1.follower_id = ? AND f1.followed_id = ? 
    AND f2.follower_id = ? AND f2.followed_id = ?");
$stmt->execute([$me, $them_id, $them_id, $me]);
if (!$stmt->fetch()) {
    echo "<p>You must follow each other to chat.</p>";
    exit;
}

// Mark as seen
$pdo->prepare("UPDATE messages SET seen = 1 
    WHERE sender_id = ? AND receiver_id = ? AND seen = 0")
    ->execute([$them_id, $me]);

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY created_at ASC");
$stmt->execute([$me, $them_id, $them_id, $me]);
$messages = $stmt->fetchAll();

// Output messages
foreach ($messages as $msg) {
    $fromMe = $msg['sender_id'] == $me;
    $cssClass = $fromMe ? 'message message-sent' : 'message message-received';
    echo "<div class='$cssClass'>";
    echo nl2br(htmlspecialchars($msg['content']));
    echo "<br><small style='opacity: 0.6; font-size: 11px;'>" . $msg['created_at'] . "</small>";
    echo "</div>";
}
?>


