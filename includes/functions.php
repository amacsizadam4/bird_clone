<?php

require_once 'config.php';

function link_username($username) {
    return '<a href="user.php?u=' . urlencode($username) . '">' . htmlspecialchars($username) . '</a>';
}

function render_user_icon($username, $size = 40) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $img = $user && $user['profile_pic'] ? $user['profile_pic'] : 'default.png';

    $safeUsername = htmlspecialchars($username);
    $escapedImg = htmlspecialchars($img);

return "<a class='user-link' href='user.php?u={$safeUsername}' style='display: inline-flex; align-items: center; gap: 8px;'>
            <img src='uploads/{$escapedImg}' width='{$size}' height='{$size}' style='border-radius:50%; object-fit:cover;'>
            {$safeUsername}
        </a>";

}

function isBlocked($userA_id, $userB_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 1 FROM blocked_users WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)");
    $stmt->execute([$userA_id, $userB_id, $userB_id, $userA_id]);
    return (bool) $stmt->fetch();
}


// USERS
function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function getUserByUsername($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function getUserProfilePic($userId) {
    $user = getUserById($userId);
    return $user['profile_pic'] ?? null;
}

function getUserBio($userId) {
    $user = getUserById($userId);
    return $user['bio'] ?? '';
}

// FOLLOWS
function isFollowing($followerId, $followedId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$followerId, $followedId]);
    return $stmt->rowCount() > 0;
}

function getFollowers($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE followed_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getFollowing($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getFollowerCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getFollowingCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

// THOUGHTS
function getThoughtsByUser($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM thoughts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getThoughtById($thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM thoughts WHERE id = ?");
    $stmt->execute([$thoughtId]);
    return $stmt->fetch();
}

function getImagesForThought($thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
    $stmt->execute([$thoughtId]);
    return $stmt->fetchAll();
}

// LIKES
function getThoughtLikesCount($thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE thought_id = ?");
    $stmt->execute([$thoughtId]);
    return $stmt->fetchColumn();
}

function hasUserLikedThought($userId, $thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND thought_id = ?");
    $stmt->execute([$userId, $thoughtId]);
    return $stmt->rowCount() > 0;
}

// COMMENTS
function getCommentsByThought($thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE thought_id = ? AND deleted = 0 ORDER BY created_at ASC");
    $stmt->execute([$thoughtId]);
    return $stmt->fetchAll();
}

function getCommentReplies($parentCommentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE parent_comment_id = ? AND deleted = 0");
    $stmt->execute([$parentCommentId]);
    return $stmt->fetchAll();
}

// REPOSTS
function getThoughtRepostsCount($thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reposts WHERE thought_id = ?");
    $stmt->execute([$thoughtId]);
    return $stmt->fetchColumn();
}

function hasUserRepostedThought($userId, $thoughtId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM reposts WHERE user_id = ? AND thought_id = ?");
    $stmt->execute([$userId, $thoughtId]);
    return $stmt->rowCount() > 0;
}

// MESSAGES
function getMessagesBetweenUsers($userA, $userB) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
    $stmt->execute([$userA, $userB, $userB, $userA]);
    return $stmt->fetchAll();
}

function markMessagesAsSeen($senderId, $receiverId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE messages SET seen = 1 WHERE sender_id = ? AND receiver_id = ?");
    return $stmt->execute([$senderId, $receiverId]);
}

// BLOCKING
function isUserBlocked($blockerId, $blockedId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$blockerId, $blockedId]);
    return $stmt->rowCount() > 0;
}

function getBlockedUsers($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT blocked_id FROM blocked_users WHERE blocker_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getBlockers($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT blocker_id FROM blocked_users WHERE blocked_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
