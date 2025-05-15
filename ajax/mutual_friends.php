<?php
require '../config.php';
require '../includes/auth.php';

$user_id = $_SESSION['user_id'];

// Fetch mutual followers excluding blocked users
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.profile_pic
    FROM users u
    WHERE 
      EXISTS (
        SELECT 1 FROM follows f1
        WHERE f1.follower_id = ? AND f1.followed_id = u.id
      )
      AND EXISTS (
        SELECT 1 FROM follows f2
        WHERE f2.follower_id = u.id AND f2.followed_id = ?
      )
      AND NOT EXISTS (
        SELECT 1 FROM blocked_users b
        WHERE (b.blocker_id = ? AND b.blocked_id = u.id)
           OR (b.blocker_id = u.id AND b.blocked_id = ?)
      )
      AND u.id != ?
    ORDER BY u.username ASC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$users = $stmt->fetchAll();

if (!$users) {
    echo "<p>No mutual followers yet.</p>";
    exit;
}

foreach ($users as $u) {
    $userId = (int)$u['id'];
    $username = htmlspecialchars($u['username']);
    $profilePic = !empty($u['profile_pic']) ? htmlspecialchars($u['profile_pic']) : 'default.png';

    // Fetch unread message count
    $stmt2 = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE sender_id = ? AND receiver_id = ? AND seen = 0
    ");
    $stmt2->execute([$userId, $user_id]);
    $unreadCount = $stmt2->fetchColumn();

    // Output user in chat list
    echo "<div onclick=\"openChatWindow('$username')\" style='
        padding: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #eee;
    '>";

    echo "<div style='display: flex; align-items: center;'>";
    echo "<img src='/bird_clone/uploads/$profilePic' width='24' height='24' style='border-radius: 50%; margin-right: 8px;'>";
    echo $username;
    echo "</div>";

    if ($unreadCount > 0) {
        echo "<span style='
            background: red;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 10px;
        '>$unreadCount</span>";
    }

    echo "</div>";
}
