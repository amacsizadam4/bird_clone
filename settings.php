<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
require 'includes/functions.php';
include 'templates/header.php';

// Fetch current user settings
$stmt = $pdo->prepare("SELECT bio, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_settings = $stmt->fetch();
$bio = $user_settings['bio'] ?? '';
$profile_pic = $user_settings['profile_pic'] ?? '';
?>

<div style="display: flex; padding: 20px;">
    <!-- Sidebar navigation -->
    <div style="width: 200px; margin-right: 20px;">
        <h3><?= $t['settings'] ?></h3>
        <ul style="list-style: none; padding-left: 0;">
            <li><a href="#profile" onclick="showSetting('profile')">🔧 <?= $t['profile_settings'] ?></a></li>
            <li><a href="#blocked" onclick="showSetting('blocked')">🚫 <?= $t['blocked_users'] ?></a></li>
        </ul>
    </div>

    <!-- Settings content -->
    <div style="flex: 1;">

        <!-- Profile Settings -->
        <div id="setting-profile" style="display: none;">
            <h4><?= $t['profile_settings'] ?></h4>
            <form method="POST" action="actions/update_profile.php" enctype="multipart/form-data">
                <label><?= $t['change_profile_pic'] ?>:</label><br>
                <?php if ($profile_pic): ?>
                    <img src="uploads/<?= htmlspecialchars($profile_pic) ?>" width="100" style="border-radius:50%; margin:10px 0;"><br>
                <?php endif; ?>
                <input type="file" name="profile_pic" accept="image/*"><br><br>

                <label><?= $t['edit_bio'] ?>:</label><br>
                <textarea name="bio" rows="4" cols="40" maxlength="280"><?= htmlspecialchars($bio) ?></textarea><br><br>

                <button type="submit">✅ <?= $t['save_changes'] ?></button>
            </form>
        </div>

        <!-- Blocked Users Section -->
        <div id="setting-blocked" style="display: none;">
            <h4><?= $t['blocked_users'] ?></h4>
            <?php
            $stmt = $pdo->prepare("SELECT u.id, u.username FROM blocked_users bu JOIN users u ON bu.blocked_id = u.id WHERE bu.blocker_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $blocked = $stmt->fetchAll();

            if (!$blocked): ?>
                <p><?= $t['no_blocked_users'] ?? 'You have not blocked anyone.' ?></p>
            <?php else: ?>
                <ul style="padding-left: 0; list-style: none;">
                    <?php foreach ($blocked as $user): ?>
                        <li style="margin-bottom: 10px;">
                            <?= htmlspecialchars($user['username']) ?>
                            <form method="POST" action="actions/unblock_user.php" style="display:inline; margin-left: 10px;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit">❌ <?= $t['unblock_user'] ?></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function showSetting(section) {
    document.querySelectorAll('[id^=setting-]').forEach(div => div.style.display = 'none');
    document.getElementById('setting-' + section).style.display = 'block';
}

if (location.hash) {
    const hash = location.hash.replace('#', '');
    showSetting(hash);
} else {
    showSetting('profile');
}
</script>
