<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
require 'includes/functions.php';
include 'templates/header.php';

$username = $_GET['u'] ?? '';
$section = $_GET['tab'] ?? 'posts';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p>{$t['user_not_found']}</p>";
    exit;
}

$profile_user_id = $user['id'];
$is_self = $profile_user_id === $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
$stmt->execute([$profile_user_id]);
$followerCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$stmt->execute([$profile_user_id]);
$followingCount = $stmt->fetchColumn();

$following = false;
if (!$is_self) {
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$_SESSION['user_id'], $profile_user_id]);
    $following = $stmt->fetch() ? true : false;
}
?>

<!-- PROFILE WRAPPER -->
<div style="display: flex; justify-content: center; padding: 0 20px;">
    <div style="max-width: 600px; flex: 1;">

        <!-- PROFILE HEADER -->
        <h2><?= render_user_icon($user['username'], 50) ?></h2>
        <small>
            <a href="#" onclick="loadFollowList('followers')"><?= $followerCount ?> <?= $t['followers'] ?></a> ·
            <a href="#" onclick="loadFollowList('following')"><?= $followingCount ?> <?= $t['following'] ?></a>
        </small><br>

        <?php if (!$is_self): ?>
        <form method="POST" action="actions/follow.php" style="margin-top: 10px;">
            <input type="hidden" name="user_id" value="<?= $profile_user_id ?>">
            <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
            <button type="submit"><?= $following ? $t['unfollow'] : $t['follow'] ?></button>
        </form>
        <?php endif; ?>

        <!-- TABS -->
        <div style="display: flex; gap: 10px; margin: 20px 0;">
            <a href="?u=<?= urlencode($username) ?>&tab=posts" style="text-decoration:<?= $section === 'posts' ? 'underline' : 'none' ?>;"><?= $t['posts'] ?></a>
            <a href="?u=<?= urlencode($username) ?>&tab=comments" style="text-decoration:<?= $section === 'comments' ? 'underline' : 'none' ?>;"><?= $t['comments'] ?></a>
            <a href="?u=<?= urlencode($username) ?>&tab=likes" style="text-decoration:<?= $section === 'likes' ? 'underline' : 'none' ?>;"><?= $t['liked'] ?></a>
        </div>

        <?php
        if ($section === 'comments') {
            $stmt = $pdo->prepare("
                SELECT comments.*, thoughts.content AS post_content, thoughts.id AS thought_id, tu.username AS post_author
                FROM comments
                JOIN thoughts ON comments.thought_id = thoughts.id
                JOIN users tu ON thoughts.user_id = tu.id
                WHERE comments.user_id = ?
                ORDER BY comments.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$profile_user_id]);

            foreach ($stmt->fetchAll() as $comment) {
                echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:15px;'>";
                echo "<em>{$t['commented_on']} " . render_user_icon($comment['post_author'],20) . "</em><br>";
                echo "<p>" . nl2br(htmlspecialchars($comment['content'])) . "</p>";
                echo "<small>" . $comment['created_at'] . "</small>";
                echo "</div>";
            }

        } else {
            if ($section === 'likes') {
                $stmt = $pdo->prepare("
                    SELECT t.*, u.username
                    FROM likes
                    JOIN thoughts t ON likes.thought_id = t.id
                    JOIN users u ON t.user_id = u.id
                    WHERE likes.user_id = ?
                    ORDER BY likes.created_at DESC
                    LIMIT 50
                ");
                $stmt->execute([$profile_user_id]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT t.*, u.username,
                           ot.content AS original_content,
                           ou.username AS original_username,
                           qt.content AS quote_content,
                           qu.username AS quote_user
                    FROM thoughts t
                    JOIN users u ON t.user_id = u.id
                    LEFT JOIN thoughts ot ON t.original_thought_id = ot.id
                    LEFT JOIN users ou ON ot.user_id = ou.id
                    LEFT JOIN thoughts qt ON t.quote_id = qt.id
                    LEFT JOIN users qu ON qt.user_id = qu.id
                    WHERE t.user_id = ?
                    ORDER BY t.created_at DESC
                    LIMIT 50
                ");
                $stmt->execute([$profile_user_id]);
            }

            $thoughts = $stmt->fetchAll();

            foreach ($thoughts as $thought):
                $target_id = $thought['original_thought_id'] ?: $thought['id'];
                $likeCount = $pdo->query("SELECT COUNT(*) FROM likes WHERE thought_id = $target_id")->fetchColumn();
                $repostCount = $pdo->query("SELECT COUNT(*) FROM thoughts WHERE original_thought_id = $target_id")->fetchColumn();
                $quoteCount = $pdo->query("SELECT COUNT(*) FROM thoughts WHERE quote_id = $target_id")->fetchColumn();
        ?>
        <div onclick="openModal(<?= $target_id ?>)"
             style="border: 1px solid #ccc; margin-bottom: 20px; padding: 10px; cursor: pointer; position: relative;">

            <?php if ($thought['original_thought_id']): ?>
                <em><?= $t['reposted_by'] ?> <?= render_user_icon($thought['username'], 20) ?></em><br>
                <?php if ($thought['original_content'] && $thought['original_username']): ?>
                    <strong><?= render_user_icon($thought['original_username'], 20) ?></strong><br>
                    <p><?= nl2br(htmlspecialchars($thought['original_content'])) ?></p>
                <?php else: ?>
                    <p><i><?= $t['post_deleted_or_unavailable'] ?></i></p>
                <?php endif; ?>
            <?php else: ?>
                <strong><?= render_user_icon($thought['username'], 30) ?></strong><br>
                <p><?= nl2br(htmlspecialchars($thought['content'])) ?></p>
                <?php
                $img_stmt = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
                $img_stmt->execute([$thought['id']]);
                foreach ($img_stmt->fetchAll() as $img) {
                    echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='150' style='margin: 5px;'>";
                }
                ?>
    
                <?php if ($thought['quote_id'] && $thought['quote_content']): ?>
                    <div style="border-left: 3px solid #ccc; margin-top: 10px; padding-left: 10px;">
                        <em><?= $t['quoted_from'] ?> <?= link_username($thought['quote_user']) ?></em><br>
                        <p><?= nl2br(htmlspecialchars($thought['quote_content'])) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <br><small><?= $thought['created_at'] ?></small><br><br>

            <div onclick="event.stopPropagation();" style="margin-top: 10px; display: flex; gap: 10px;">
                <form method="POST" action="actions/like.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <button type="submit">❤️ <?= $likeCount ?></button>
                </form>
                <form method="POST" action="actions/repost.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <button type="submit">🔁 <?= $repostCount ?></button>
                </form>
                <?php if (!$thought['quote_id']): ?>
                <form method="POST" action="actions/quote.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <input type="text" name="content" placeholder="<?= $t['quote_placeholder'] ?>" required>
                    <button type="submit">💬 <?= $quoteCount ?></button>
                </form>
                <?php endif; ?>
                <?php if ($_SESSION['user_id'] == $thought['user_id']): ?>
                <form method="POST" action="actions/delete_thought.php">
                    <input type="hidden" name="thought_id" value="<?= $thought['id'] ?>">
                    <button type="submit">🗑️</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; } ?>
    </div>
</div>

<!-- MODAL -->
<div id="commentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
     background: rgba(0,0,0,0.7); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; max-width:600px; max-height:80%; overflow:auto;" id="modalContent"></div>
</div>

<script>
function openModal(thoughtId) {
    fetch('thought/full_view.php?thought_id=' + thoughtId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('commentModal').style.display = 'flex';
        });
}

function loadFollowList(type) {
    const username = "<?= htmlspecialchars($user['username']) ?>";
    fetch(`followers.php?u=${username}&type=${type}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('commentModal').style.display = 'flex';
        });
}

window.addEventListener('click', function(e) {
    if (e.target.id === 'commentModal') {
        document.getElementById('commentModal').style.display = 'none';
    }
});
</script>
