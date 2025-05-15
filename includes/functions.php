<?php
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

    return "<a href='user.php?u={$safeUsername}' style='display: inline-flex; align-items: center; gap: 8px; text-decoration: none;'>
                <img src='uploads/{$escapedImg}' width='{$size}' height='{$size}' style='border-radius:50%; object-fit:cover;'>
                {$safeUsername}
            </a>";
}

